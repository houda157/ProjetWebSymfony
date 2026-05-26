<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\StudentRepository;

final class ProfileEtudiantController extends AbstractController
{

    #[Route('profile/{id}', name: 'app_profile_etudiant_show')]
    public function show(int $id,StudentRepository $studentRepository): Response
    {
        $student = $studentRepository->find($id);

        if (!$student) {
            throw $this->createNotFoundException('Student not found');
        }

        return $this->render('profile_etudiant/show.html.twig', [
            'student' => $student
        ]);
    }
}
