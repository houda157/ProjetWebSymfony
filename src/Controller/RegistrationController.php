<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Student;
use App\Entity\User;
use App\Enum\UserRole;
use App\Form\ClubRegistrationFormType;
use App\Form\StudentRegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

//do a form type
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET'])]
    public function choose(): Response
    {
        return $this->getUser() ? $this->redirectToRoute('app_home') : $this->render('registration/choose.html.twig');
    }

    #[Route('/register/student', name: 'app_register_student', methods: ['GET', 'POST'])]
    public function student(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(StudentRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->addUserConflictErrors($form, $userRepository, $data['username'], $data['email']);

            if ($form->isValid()) {
                $user = $this->createUser($data['username'], $data['email'], $data['plainPassword'], UserRole::STUDENT->value, $passwordHasher);
                $student = (new Student())
                    ->setFullname($data['fullname'])
                    ->setUser($user);

                $entityManager->persist($student);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/student.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/register/club', name: 'app_register_club', methods: ['GET', 'POST'])]
    public function club(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ClubRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $username = $this->makeClubUsername($data['clubname']);
            $this->addUserConflictErrors($form, $userRepository, $username, $data['email'], 'clubname', 'This club name is already used for an account.');

            if ($form->isValid()) {
                $user = $this->createUser($username, $data['email'], $data['plainPassword'], UserRole::CLUB_NOT_CONFIRMED->value, $passwordHasher);
                $club = (new Club())
                    ->setName($data['clubname'])
                    ->setUser($user);

                $entityManager->persist($club);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/club.html.twig', [
            'form' => $form,
        ]);
    }

    private function addUserConflictErrors(
        FormInterface $form,
        UserRepository $userRepository,
        string $username,
        string $email,
        string $usernameField = 'username',
        string $usernameMessage = 'This username is already used.',
    ): void {
        if ($userRepository->findOneBy(['username' => $username])) {
            $form->get($usernameField)->addError(new FormError($usernameMessage));
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            $form->get('email')->addError(new FormError('This email is already used.'));
        }
    }

    private function createUser(
        string $username,
        string $email,
        string $plainPassword,
        string $role,
        UserPasswordHasherInterface $passwordHasher,
    ): User {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRole($role);
        $user->setCreatedAt(new \DateTime());
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        return $user;
    }

    private function makeClubUsername(string $clubName): string
    {
        $username = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $clubName) ?? '', '_'));

        return substr($username !== '' ? $username : 'club', 0, 50);
    }
}
