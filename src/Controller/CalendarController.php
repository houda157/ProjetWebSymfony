<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
#[IsGranted('ROLE_USER')]
class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'calendar')]
    public function index(Request $request, EventRepository $eventRepo): Response
    {
        $likedOnly = $request->query->getBoolean('liked');
        /** @var User $user */
        $user = $this->getUser();
        $isStudent = $this->isGranted('ROLE_STUDENT');

        if ($likedOnly && $isStudent) {
            
            $student = $user->getStudent();
            $events = array_map(
                fn($like) => $like->getEvent(),
                $student->getLikes()->toArray()
            );
            usort($events, fn($a, $b) => $a->getEventDate() <=> $b->getEventDate());
        } else {
            $events = $eventRepo->findBy([], ['eventDate' => 'ASC']);
        }

        $calendarEvents = [];
        foreach ($events as $event) {
            $calendarEvents[] = [
                'title' => $event->getTitle() . ' — ' . $event->getClub()->getName(),
                'start' => $event->getEventDate()->format('Y-m-d'),
                'color' => $likedOnly ? '#1a6b3c' : '#8F1402',
            ];
        }

        return $this->render('feed/calendar.html.twig', [
            'calendarEvents' => $calendarEvents,
            'likedOnly'      => $likedOnly,
            'isStudent'      => $isStudent,
        ]);
    }
}
