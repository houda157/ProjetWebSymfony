<?php
// src/Controller/EventController.php
//
// Remplace ces fichiers PHP natifs :
//   • public/create-event.php              → méthode new() (partie GET)
//   • public/actions/do-create-event.php    → méthode new() (partie POST)
//
// Une seule route /event/new gère les deux méthodes HTTP (GET et POST).
// Symfony détecte si c'est un affichage ou une soumission via le formulaire.

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\LikeRepository;
use App\Repository\StudentRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FileUploadService $uploader,
    ) {}

    // ────────────────────────────────────────────────────────────────────────
    // Création d'un événement.
    //   GET  /event/new  → affiche le formulaire vide
    //   POST /event/new  → traite la soumission, enregistre l'event, redirige
    //
    // Remplace : public/create-event.php (formulaire) + public/actions/do-create-event.php (POST)
    // ────────────────────────────────────────────────────────────────────────
    #[Route('/event/new', name: 'event_create_form', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CLUB_CONFIRMED')]
    public function new(Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Création d'un nouvel Event vide — le formulaire le remplira
        $event = new Event();

        // Construction du formulaire à partir d'EventType.
        // Symfony génère le HTML, le CSRF, et bind les champs sur l'entité automatiquement.
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        // Si on est en POST et que la validation passe → on enregistre
        if ($form->isSubmitted() && $form->isValid()) {

            // Remplace : if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK)
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $path = $this->uploader->upload($imageFile, 'events', uniqid());
                $event->setImage($path);
            }

            // L'event appartient au club connecté — récupéré depuis l'utilisateur
            $event->setClub($currentUser->getClub());
            $event->setCreatedAt(new \DateTime());

            $this->em->persist($event);
            $this->em->flush();

            // addFlash() remplace : ?success=1 — affiché par base.html.twig
            $this->addFlash('success', 'Event published successfully!');

            // Remplace : header("Location: club.php"); exit();
            return $this->redirectToRoute('club_show', [
                'id' => $currentUser->getId(),
            ]);
        }

        // GET (ou POST invalide) → on affiche le formulaire
        return $this->render('event/createEvent.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/event/{id}/delete', name: 'event_delete', methods: ['POST'])]
    #[IsGranted('ROLE_CLUB_CONFIRMED')]
    public function delete(Event $event, Request $request): Response
    {
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $club = $event->getClub();

        if (!$club || $club->getUser()->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException("Vous n'avez pas l'autorisation de supprimer cet événement.");
        }

        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_event_' . $event->getId(), $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $this->em->remove($event);
        $this->em->flush();

        $this->addFlash('success', 'L\'événement a été supprimé avec succès.');

        return $this->redirectToRoute('club_show', ['id' => $currentUser->getId()]);
    }
    // go to event controller
    #[Route('/event/{id}', name: 'event_show')]
    public function eventShow(
        int $id,
        EventRepository $eventRepo,
        LikeRepository $likeRepo,
        StudentRepository $studentRepo
    ): Response {
        $event = $eventRepo->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Event introuvable.');
        }

        $user = $this->getUser();
        $student = $user ? $studentRepo->findOneBy(['user' => $user]) : null;
        $hasLiked = false;
        $likeCount = count($event->getLikes());

        if ($student) {
            $hasLiked = (bool) $likeRepo->findOneBy([
                'student' => $student,
                'event'   => $event,
            ]);
        }

        return $this->render('feed/event.html.twig', [
            'event'     => $event,
            'hasLiked'  => $hasLiked,
            'likeCount' => $likeCount,
        ]);
    }


}
