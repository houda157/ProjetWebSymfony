<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\LikeRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

        return $this->render('feed/index.html.twig', [
            'events'   => $events,
            'upcoming' => [],
            'student'  => $student,
            'likeRepo' => $likeRepo,
            'now'      => $now,
        ]);
    }

    #[Route('/like/{id}', name: 'app_like', methods: ['POST'])]
    public function like(
        int $id,
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
        return $this->redirectToRoute('app_home');
    }

    #[Route('/student/profile/{id}', name: 'student_profile')]
    public function studentProfile(int $id): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/calendar', name: 'calendar')]
    public function calendar(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/search', name: 'do_search')]
    public function search(): JsonResponse
    {
        return new JsonResponse(['clubs' => [], 'events' => []]);
    }
}