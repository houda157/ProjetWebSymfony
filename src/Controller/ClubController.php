<?php
// src/Controller/ClubController.php
//
// Remplace ces fichiers PHP natifs :
//   • public/club.php (partie GET — affichage du profil)     → méthode show()
//   • public/club.php (partie POST — update_profile)          → méthode updateProfile()
//   • public/actions/do-follow.php (toggle Follow/Unfollow)   → méthode toggleFollow()
//
// Chaque route = une méthode. Le #[Route] remplace l'URL directe du fichier.

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Follow;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use App\Repository\FollowRepository;
use App\Form\ClubProfileType;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClubController extends AbstractController
{
    // Services partagés injectés une fois — accessibles via $this->xxx dans toutes les méthodes
    public function __construct(
        private ClubRepository $clubRepo,
        private FollowRepository $followRepo,
        private EntityManagerInterface $em,
        private FileUploadService $uploader,
        private EventRepository $eventRepo,
    ) {}

    // ────────────────────────────────────────────────────────────────────────
    // Remplace : public/club.php (partie GET — affichage du profil)
    // ────────────────────────────────────────────────────────────────────────
    #[Route('/club/{id}', name: 'club_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): Response
    {
        // Remplace : $club = $clubModel->findById($club_id)
        $club = $this->clubRepo->findByUserId($id);

        // Remplace : if (!$club) { die("Club introuvable."); }
        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();

        // Remplace : $is_owner = ($session_id == $club_id)
        $isOwner = $currentUser && $currentUser->getId() === $id;
        //$isOwner  = true; // Simplification pour le développement — à corriger plus tard
        // Remplace : $isFollowing = $followModel->isFollowing($session_id, $club_id)
        // Seulement pertinent pour les étudiants qui ne sont pas le propriétaire
        $isFollowing = false;
        if ($currentUser && !$isOwner && $this->isGranted('ROLE_STUDENT')) {
            $isFollowing = $this->followRepo->isFollowing(
                $currentUser->getStudent(),
                $club
            );
        }

        $form = null;
        if ($isOwner) {
            $form = $this->createForm(ClubProfileType::class, $club, [
                'action' => $this->generateUrl('club_edit', ['id' => $id]),
                'method' => 'POST',
                'csrf_token_id' => 'club_edit',
            ])->createView();
        }

        return $this->render('club/clubProfile.html.twig', [
            'club'        => $club,
            'events'      => $club->getEvents(),
            'followers'   => $club->getFollows(),
            'isOwner'     => $isOwner,
            'isFollowing' => $isFollowing,
            'form'        => $form,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Remplace : public/club.php (partie POST — update_profile)
    // ────────────────────────────────────────────────────────────────────────
    #[Route('/club/{id}/edit', name: 'club_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_CLUB_CONFIRMED')]
    public function updateProfile(int $id, Request $request): Response
    {
        $club = $this->clubRepo->findByUserId($id);

        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser->getId() !== $id) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que votre propre club.');
        }

        $form = $this->createForm(ClubProfileType::class, $club, [
            'csrf_token_id' => 'club_edit',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profileFile = $form->get('profileImgFile')->getData();
            if ($profileFile) {
                $path = $this->uploader->upload($profileFile, 'users/profile_img', (string) $id);
                $club->getUser()->setProfileImg($path);
            }

            $coverFile = $form->get('coverImgFile')->getData();
            if ($coverFile) {
                $path = $this->uploader->upload($coverFile, 'users/cover_img', (string) $id);
                $club->setCoverImg($path);
            }

            $this->em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès !');

            return $this->redirectToRoute('club_show', ['id' => $id]);
        }
        $isOwner = $currentUser->getId() === $id;
        return $this->render('club/clubProfile.html.twig', [
            'club'        => $club,
            'events'      => $club->getEvents(),
            'followers'   => $club->getFollows(),
            'isOwner'     => $isOwner,
            'isFollowing' => false,
            'form'        => $form->createView(),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Remplace : public/actions/do-follow.php
    // Toggle Follow / Unfollow — seuls les étudiants peuvent suivre
    // ────────────────────────────────────────────────────────────────────────
    #[Route('/club/{id}/follow', name: 'club_follow_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function toggleFollow(int $id, Request $request): Response
    {
        $club = $this->clubRepo->findByUserId($id);

        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        // Vérification du token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('follow' . $id, $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $student = $currentUser->getStudent();

        // Cherche un follow existant
        $existingFollow = $this->followRepo->findOneBy([
            'student' => $student,
            'club'    => $club,
        ]);

        if ($existingFollow) {
            // Déjà follower → unfollow
            $this->em->remove($existingFollow);
            $this->em->flush();
            $this->addFlash('success', 'Vous ne suivez plus ' . $club->getName() . '.');
        } else {
            // Pas encore follower → follow
            $follow = new Follow();
            $follow->setStudent($student);
            $follow->setClub($club);
            $follow->setCreatedAt(new \DateTime());
            $this->em->persist($follow);
            $this->em->flush();
            $this->addFlash('success', 'Vous suivez maintenant ' . $club->getName() . ' !');
        }

        return $this->redirectToRoute('club_show', ['id' => $id]);
    }
    #[Route('/club/feed',name:'club_feed',methods:['GET'])]
    #[IsGranted('ROLE_CLUB_CONFIRMED')]
    public function feedShow():Response
    {
        $posts=$this->eventRepo->getAllPosts();
        //dd($posts);
        return $this->render('club/clubFeed.html.twig',[

            'events'=>$posts
            
        ]);
    }
        
}