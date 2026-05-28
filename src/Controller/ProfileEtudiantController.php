<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\StudentRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProfileEtudiantFormType;
use App\Service\FileUploadService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
final class ProfileEtudiantController extends AbstractController
{
    public function __construct(
        private FileUploadService $uploader
    ) {}
//id dans etudiant
    #[Route('profile/{id}', name: 'app_profile_etudiant_show', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function show(int $id, StudentRepository $studentRepository, Request $request, EntityManagerInterface $em): Response
    {
        $student = $studentRepository->find($id);

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
                    $path = $this->uploader->upload($photoFile, 'users/profile_img', (string) $id);
                    $student->getUser()->setProfileImg($path);
                }
                $em->flush();
                $this->addFlash('success', 'Profile updated successfully!');
                return $this->redirectToRoute('app_profile_etudiant_show', ['id' => $id]);
            }
        }

        return $this->render('student/studentProfile.html.twig', [
            'student' => $student,
            'form'    => $form,
            'isOwner' => $isOwner,
        ]);
    }
}
