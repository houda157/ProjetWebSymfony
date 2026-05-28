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
    public function _construct(){
        /// 3abihom
    }
    #[Route('/', name: 'app_home')]
    #[Route('/', name: 'app_feed')]
    //is granted for student only
    #[IsGranted('ROLE_STUDENT')]
    public function index(
        EventRepository $eventRepo,
        StudentRepository $studentRepo
    ): Response {
        $user    = $this->getUser();
        $student = $user ? $studentRepo->findOneBy(['user' => $user]) : null;
        $events  = $eventRepo->findBy([], ['eventDate' => 'DESC']);
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
// chnage this to like controller
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

        return $this->render('student/studentEvent.html.twig', [
            'event'     => $event,
            'hasLiked'  => $hasLiked,
            'likeCount' => $likeCount,
        ]);
    }
// normalement zeyed
    // #[Route('/student/profile/{id}', name: 'student_profile')]
    // public function studentProfile(int $id): Response
    // {
    //     return $this->redirectToRoute('app_home');
    // }


//evetn controller
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
//query in the repository
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
