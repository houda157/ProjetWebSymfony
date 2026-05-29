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
use App\Form\ClubFormType;
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
        $club = $this->clubRepo->findByUserId($id);

        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        $currentUser = $this->getUser();
        assert($currentUser instanceof User);
        $isOwner     = $currentUser->getId() === $id;
        $isFollowing = false;
        $likedEventIds = [];
        $form = null;

        if ($this->isGranted('ROLE_CLUB_CONFIRMED') && $isOwner) {
            $form = $this->createForm(ClubFormType::class, $club, [
                'action'        => $this->generateUrl('club_edit', ['id' => $id]),
                'method'        => 'POST',
                'csrf_token_id' => 'club_edit',
            ])->createView();
        }

        if ($this->isGranted('ROLE_STUDENT')) {
            $student = $currentUser->getStudent();
            $isFollowing = $this->followRepo->isFollowing($student, $club);
            foreach ($student->getLikes() as $like) {
                $likedEventIds[] = $like->getEvent()->getId();
            }
        }

        return $this->render('club/clubProfile.html.twig', [
            'club'          => $club,
            'events'        => $club->getEvents(),
            'followers'     => $club->getFollows(),
            'isOwner'       => $isOwner,
            'isFollowing'   => $isFollowing,
            'likedEventIds' => $likedEventIds,
            'form'          => $form,
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

        $form = $this->createForm(ClubFormType::class, $club, [
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
