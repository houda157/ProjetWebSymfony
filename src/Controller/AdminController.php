<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Club;
use App\Entity\User;
use App\Entity\Event;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/template.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }


    #[Route('/admin/students/{page?1}/{nbre?6}', name: 'app_admin_show_students')]
    public function showStudents(ManagerRegistry $doctrine, int $page, int $nbre): Response {
        $repository = $doctrine->getRepository(Student::class);
        $students = $repository->findBy([], [],$nbre, ($page - 1 ) * $nbre);
        $nbstudent = $repository->count([]);
        $nbrePage = ceil($nbstudent / $nbre) ;
        return $this->render('admin/student.html.twig', ['students' => $students,
            'isPaginated' => true,
            'nbrePage' => $nbrePage,
            'page' => $page,
            'nbre' => $nbre]);
    }


    #[Route('/admin/clubs/confirmed/{page?1}/{nbre?6}', name: 'app_admin_show_confirmed')]
    public function showConfirmed(ClubRepository $clubRepository, int $page, int $nbre): Response
    {
        $role = 'ROLE_CLUB_CONFIRMED';


        $confirmedClubs = $clubRepository->findClubsByRolePaginated($role, $page, $nbre);
        $nbConfirmedClubs = $clubRepository->countClubsByRole($role);

        $nbrePage = ceil($nbConfirmedClubs / $nbre);

        return $this->render('admin/confirmed.html.twig', [
            'clubs' => $confirmedClubs,
            'isPaginated' => true,
            'nbrePage' => $nbrePage,
            'page' => $page,
            'nbre' => $nbre
        ]);
    }

    #[Route('/admin/clubs/not_confirmed/{page?1}/{nbre?6}', name: 'app_admin_show_not_confirmed')]
    public function showNotConfirmed(ClubRepository $clubRepository, int $page, int $nbre): Response
    {

        $role = 'ROLE_CLUB_NOT_CONFIRMED';

        $unconfirmedClubs = $clubRepository->findClubsByRolePaginated($role, $page, $nbre);
        $nbUnconfirmedClubs = $clubRepository->countClubsByRole($role);

        $nbrePage = ceil($nbUnconfirmedClubs / $nbre);

        return $this->render('admin/notconfirmed.html.twig', [
            'clubs' => $unconfirmedClubs,
            'isPaginated' => true,
            'nbrePage' => $nbrePage,
            'page' => $page,
            'nbre' => $nbre
        ]);
    }


    #[Route('/admin/events/{page?1}/{nbre?6}', name: 'app_admin_show_events')]
    public function showEvents(ManagerRegistry $doctrine, int $page, int $nbre): Response {
        $repository = $doctrine->getRepository(Event::class);
        $events = $repository->findBy([], [], $nbre, ($page - 1) * $nbre);
        $nbevent = $repository->count([]);
        $nbrePage = ceil($nbevent / $nbre);

        return $this->render('admin/events.html.twig', [
            'events' => $events,
            'isPaginated' => true,
            'nbrePage' => $nbrePage,
            'page' => $page,
            'nbre' => $nbre
        ]);
    }

    #[Route('/admin/student/delete/{id}', name: 'delete_student', methods: ['POST', 'DELETE','GET'])]
    public function deleteStudent(Student $student, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($student);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_show_students');
    }

    #[Route('/admin/club/delete/{id}', name: 'delete_club', methods: ['POST', 'DELETE','GET'])]
    public function deleteClub(Club $club, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($club);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_show_confirmed');
    }

    #[Route('/admin/club/approve/{id}', name: 'approve_club', methods: ['GET', 'POST'])]
    public function approveClub(Club $club, EntityManagerInterface $entityManager): Response
    {
        $user = $club->getUser();

        if ($user) {
            $user->setRole('ROLE_CLUB_CONFIRMED');
            $entityManager->flush();

        }

        // 3. Redirect back to the pending requests queue page
        return $this->redirectToRoute('app_admin_show_not_confirmed');
    }

    #[Route('/admin/events/{id}', name: 'delete_event', methods: ['POST', 'DELETE','GET'])]
    public function deleteEvent(Event $event, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_show_events');
    }

}
