<?php
// src/Controller/ClubController.php
//
// Remplace ces 3 fichiers PHP natifs :
//   • public/club.php              → méthodes show() et updateProfile()
//   • public/create-event.php      → méthode createEventForm()
//   • public/actions/do-create-event.php → méthode doCreateEvent()
//
// Chaque route = une méthode. Le #[Route] remplace l'URL directe du fichier.

namespace App\Controller;

use App\Entity\Event;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ClubController extends AbstractController
{

    // Remplace : public/club.php (la partie GET — affichage du profil)
    #[Route('/club/{id}', name: 'club_show', methods: ['GET'])]
    public function show(int $id, ClubRepository $clubRepo): Response
    {
        $club = $clubRepo->findByUserId($id);

        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        $currentUser = $this->getUser();

        // Remplace : $is_owner = ($session_id == $club_id)
        $isOwner = $currentUser && $currentUser->getId() === $id;


        return $this->render('club/clubProfile.html.twig', [
            'club'       => $club,
            'isOwner'    => $isOwner,
            'events'     => $club->getEvents(),
            'followers'  => $club->getFollows(),
        ]);
    }
    // ROUTE : /club/{id}/edit  (POST)
    #[Route('/club/{id}/edit', name: 'club_edit', methods: ['POST'])]
    //#[IsGranted('ROLE_USER')] // Remplace : if (!isset($_SESSION['user'])) header('Location: login.php')
    public function updateProfile(
        int $id,
        Request $request,
        ClubRepository $clubRepo,
        EntityManagerInterface $em,
        FileUploadService $uploader
    ): Response {
        $club = $clubRepo->findByUserId($id);
            // if (!$club || $this->getUser()->getId() !== $id)
        if (!$club) {
            throw $this->createAccessDeniedException();
        }
        $club->setName($request->request->get('name'));
        $club->setCategory($request->request->get('category'));
        $club->setDescription($request->request->get('description'));

        $profileImgFile = $request->files->get('profile_img');
        if ($profileImgFile) {
            $path = $uploader->upload($profileImgFile, 'users/profile_img', (string) $id);
            //$club->getUser()->setProfileImg($path);
        }

        $coverImgFile = $request->files->get('cover_img');
        if ($coverImgFile) {
            $path = $uploader->upload($coverImgFile, 'users/cover_img', (string) $id);
            $club->setCoverImg($path);
        }
        $em->flush();
        return $this->redirectToRoute('club_show', ['id' => $id, 'success' => 1]);
    }

}
