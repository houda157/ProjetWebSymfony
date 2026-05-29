<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Club;
use App\Entity\User;
use App\Entity\Event;
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


    #[Route('/admin/students', name: 'app_admin_show_students')]
    public function showStudents(ManagerRegistry $doctrine): Response {
        $repository = $doctrine->getRepository(Student::class);
        $students = $repository->findAll();
        return $this->render('admin/student.html.twig', ['students' => $students]);
    }

    #[Route('/admin/clubs/confirmed', name: 'app_admin_show_confirmed')]
    public function showConfirmed(ManagerRegistry $doctrine): Response {
        $repository = $doctrine->getRepository(Club::class);
        $clubs = $repository->findAll();
        return $this->render('admin/confirmed.html.twig', ['clubs' => $clubs]);
    }

    #[Route('/admin/clubs/not_confirmed', name: 'app_admin_show_not_confirmed')]
    public function showNotConfirmed(ManagerRegistry $doctrine): Response {
        $repository = $doctrine->getRepository(Club::class);
        $clubs = $repository->findAll();
        return $this->render('admin/notconfirmed.html.twig', ['clubs' => $clubs]);
    }


    #[Route('/admin/events', name: 'app_admin_show_events')]
    public function showEvents(ManagerRegistry $doctrine): Response {
        $repository = $doctrine->getRepository(Event::class);
        $events = $repository->findAll();
        return $this->render('admin/events.html.twig', ['events' => $events]);
    }


}
