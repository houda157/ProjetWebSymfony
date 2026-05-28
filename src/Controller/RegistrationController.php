<?php

namespace App\Controller;

use App\Entity\Club;
use App\Entity\Student;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


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
        
        $data = [
            'fullname' => trim((string) $request->request->get('fullname')),
            'username' => trim((string) $request->request->get('username')),
            'email' => trim((string) $request->request->get('email')),
        ];
        $errors = [];

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password');
            $repeatPassword = (string) $request->request->get('repeat-password');
            $errors = $this->validateAccountData($request, $userRepository, $data['username'], $data['email'], $password, $repeatPassword, 'register_student');

            if ($data['fullname'] === '') {
                $errors['fullname'] = 'Full name is required.';
            }

            if ($errors === []) {
                $user = $this->createUser($data['username'], $data['email'], $password, UserRole::STUDENT->value, $passwordHasher);

                $student = new Student();
                $student->setFullname($data['fullname']);
                $student->setUser($user);

                $entityManager->persist($user);
                $entityManager->persist($student);
                $entityManager->flush();

                $this->addFlash('success', 'Student account created. You can log in now.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/student.html.twig', [
            'data' => $data,
            'errors' => $errors,
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

        $clubName = trim((string) $request->request->get('clubname'));
        $data = [
            'clubname' => $clubName,
            'email' => trim((string) $request->request->get('email')),
        ];
        $errors = [];

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password');
            $repeatPassword = (string) $request->request->get('repeat-password');
            $username = $this->makeClubUsername($clubName);
            $errors = $this->validateAccountData($request, $userRepository, $username, $data['email'], $password, $repeatPassword, 'register_club');

            if ($clubName === '') {
                $errors['clubname'] = 'Club name is required.';
            }

            if ($errors === []) {
                $user = $this->createUser($username, $data['email'], $password, UserRole::CLUB_NOT_CONFIRMED->value, $passwordHasher);

                $club = new Club();
                $club->setName($clubName);
                $club->setUser($user);

                $entityManager->persist($user);
                $entityManager->persist($club);
                $entityManager->flush();

                $this->addFlash('success', 'Club request sent. You can log in while waiting for approval.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/club.html.twig', [
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    private function validateAccountData(
        Request $request,
        UserRepository $userRepository,
        string $username,
        string $email,
        string $password,
        string $repeatPassword,
        string $csrfId,
    ): array {
        $errors = [];

        if (!$this->isCsrfTokenValid($csrfId, (string) $request->request->get('_token'))) {
            $errors['_global'] = 'Invalid form token. Refresh the page and try again.';
        }

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif ($userRepository->findOneBy(['username' => $username])) {
            $errors['username'] = 'This username is already used.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($userRepository->findOneBy(['email' => $email])) {
            $errors['email'] = 'This email is already used.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must have at least 8 characters.';
        } elseif ($password !== $repeatPassword) {
            $errors['password'] = 'Verify your password.';
        }

        return $errors;
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

        $user->setRole($role === UserRole::CLUB_NOT_CONFIRMED->value ? UserRole::CLUB_NOT_CONFIRMED : UserRole::STUDENT);
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
