<?php

namespace App\Controller;

use App\Repository\ClubRepository;
use App\Repository\EventRepository;
use App\Repository\LikeRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FeedController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[Route('/', name: 'app_feed')]
    public function index(
        EventRepository $eventRepo,
        LikeRepository $likeRepo,
        StudentRepository $studentRepo
    ): Response {
        $user    = $this->getUser();
        $student = $user ? $studentRepo->findOneBy(['user' => $user]) : null;
        $events  = $eventRepo->findBy([], ['eventDate' => 'DESC']);
        $now     = new \DateTime();

        $upcoming = [];
        if ($student) {
            foreach ($student->getLikes() as $like) {
                $event = $like->getEvent();
                if ($event->getEventDate() > $now) {
                    $upcoming[] = $event;
                }
            }
            usort($upcoming, fn($a, $b) => $a->getEventDate() <=> $b->getEventDate());
        }

        return $this->render('feed/index.html.twig', [
            'events'   => $events,
            'upcoming' => $upcoming,
            'student'  => $student,
            'likeRepo' => $likeRepo,
            'now'      => $now,
        ]);
    }

    #[Route('/like/{id}', name: 'app_like', methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function like(
        int $id,
        Request $request,
        EventRepository $eventRepo,
        LikeRepository $likeRepo,
        StudentRepository $studentRepo,
        EntityManagerInterface $em
    ): Response {
        $user    = $this->getUser();
        $student = $user ? $studentRepo->findOneBy(['user' => $user]) : null;
        $event   = $eventRepo->find($id);

        if (!$student || !$event) {
            return $this->redirectToRoute('app_home');
        }

        $existingLike = $likeRepo->findOneBy([
            'student' => $student,
            'event'   => $event,
        ]);

        if ($existingLike) {
            $em->remove($existingLike);
        } else {
            $like = new \App\Entity\Like();
            $like->setStudent($student);
            $like->setEvent($event);
            $em->persist($like);
        }

        $em->flush();

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirectToRoute('app_home');
    }

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

    #[Route('/student/profile/{id}', name: 'student_profile')]
    public function studentProfile(int $id): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/calendar', name: 'calendar')]
    public function calendar(EventRepository $eventRepo): Response
    {
        $events = $eventRepo->findBy([], ['eventDate' => 'ASC']);

        $calendarEvents = [];
        foreach ($events as $event) {
            $calendarEvents[] = [
                'title' => $event->getTitle() . ' — ' . $event->getClub()->getName(),
                'start' => $event->getEventDate()->format('Y-m-d'),
                'color' => '#8F1402'
            ];
        }

        return $this->render('feed/calendar.html.twig', [
            'calendarEvents' => $calendarEvents
        ]);
    }

    #[Route('/search', name: 'do_search')]
    public function search(
        Request $request,
        EventRepository $eventRepo,
        ClubRepository $clubRepo
    ): JsonResponse {
        $q = trim($request->query->get('q', ''));

        if (strlen($q) < 1) {
            return new JsonResponse(['clubs' => [], 'events' => []]);
        }

        $clubs = $clubRepo->createQueryBuilder('c')
            ->where('c.name LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $events = $eventRepo->createQueryBuilder('e')
            ->where('e.title LIKE :q')
            ->setParameter('q', '%' . $q . '%')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

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
