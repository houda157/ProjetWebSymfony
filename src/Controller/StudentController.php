<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEtudiantFormType;
use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use App\Repository\StudentRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StudentController extends AbstractController
{
    public function __construct(
        private FileUploadService $uploader,
        private EventRepository $eventRepo,
        private StudentRepository $studentRepo,
        private ClubRepository $clubRepo,
    ) {}

    #[Route('/', name: 'app_home')]
    #[Route('/', name: 'app_feed')]
    #[IsGranted('ROLE_STUDENT')]
    public function index(): Response {
        $user    = $this->getUser();
        $student = $user ? $this->studentRepo->findOneBy(['user' => $user]) : null;
        $events  = $this->eventRepo->findBy([], ['eventDate' => 'DESC']);
        $now     = new \DateTime();

        $likedEventIds       = [];
        $followedClubUserIds = [];
        $upcoming            = [];

        if ($student) {
            foreach ($student->getLikes() as $like) {
                $event = $like->getEvent();
                $likedEventIds[] = $event->getId();
                if ($event->getEventDate() > $now) {
                    $upcoming[] = $event;
                }
            }
            foreach ($student->getFollows() as $follow) {
                $followedClubUserIds[] = $follow->getClub()->getUser()->getId();
            }
            usort($upcoming, fn($a, $b) => $a->getEventDate() <=> $b->getEventDate());
        }

        return $this->render('student/studentFeed.html.twig', [
            'events'              => $events,
            'upcoming'            => $upcoming,
            'student'             => $student,
            'likedEventIds'       => $likedEventIds,
            'followedClubUserIds' => $followedClubUserIds,
            'now'                 => $now,
        ]);
    }

    #[Route('/profile', name: 'app_profile_etudiant_show', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function show(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $student = $this->studentRepo->find($user->getStudent()->getId());

        if (!$student) {
            throw $this->createNotFoundException('Student not found');
        }

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $isOwner = $currentUser
            && $currentUser->getStudent()
            && $currentUser->getStudent()->getId() === $student->getId();

        $form = null;
        if ($isOwner) {
            $form = $this->createForm(ProfileEtudiantFormType::class, $student);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $photoFile = $form->get('photoFile')->getData();
                if ($photoFile) {
                    $path = $this->uploader->upload($photoFile, 'users/profile_img', (string) $student->getId());
                    $student->getUser()->setProfileImg($path);
                    $em->persist($student->getUser());
                }
                $em->flush();
                $this->addFlash('success', 'Profile updated successfully!');
                return $this->redirectToRoute('app_profile_etudiant_show');
            }
        }
        //liked and followed events
        $likedEventIds = [];
        $followedClubUserIds = [];
        if ($currentUser && $this->isGranted('ROLE_STUDENT')) {
            foreach ($currentUser->getStudent()->getLikes() as $like) {
                $likedEventIds[] = $like->getEvent()->getId();
            }
            foreach ($currentUser->getStudent()->getFollows() as $follow) {
                $followedClubUserIds[] = $follow->getClub()->getUser()->getId();
            }
        }

        return $this->render('student/studentProfile.html.twig', [
            'student' => $student,
            'form'    => $form,
            'isOwner' => $isOwner,
            'likedEventIds' => $likedEventIds,
            'followedClubUserIds' => $followedClubUserIds,
        ]);
    }

    #[Route('/search', name: 'do_search')]
    public function search(Request $request): JsonResponse {
        $q = trim($request->query->get('q', ''));

        if (strlen($q) < 1) {
            return new JsonResponse(['clubs' => [], 'events' => []]);
        }

        $clubs  = $this->clubRepo->searchByName($q);
        $events = $this->eventRepo->searchByTitle($q);

        $clubsData = array_map(fn($c) => [
            'id'   => $c->getUser()->getId(),
            'name' => $c->getName(),
        ], $clubs);

        $eventsData = array_map(fn($e) => [
            'id'         => $e->getId(),
            'title'      => $e->getTitle(),
            'event_date' => $e->getEventDate()->format('d/m/Y'),
        ], $events);

        return new JsonResponse([
            'clubs'  => $clubsData,
            'events' => $eventsData,
        ]);
    }
}
