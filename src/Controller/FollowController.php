<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Repository\ClubRepository;
use App\Repository\FollowRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FollowController extends AbstractController
{
    public function __construct(
        private ClubRepository $clubRepo,
        private FollowRepository $followRepo,
        private EntityManagerInterface $em,
        private StudentRepository $studentRepo,
    ) {}

    #[Route('/club/{id}/follow', name: 'club_follow_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_STUDENT')]
    public function toggleFollow(int $id, Request $request): Response
    {
        $club = $this->clubRepo->findByUserId($id);

        if (!$club) {
            throw $this->createNotFoundException('Club introuvable.');
        }

        // Vérification du token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('follow' . $id, $submittedToken)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $student = $currentUser->getStudent();
        $existingFollow = $this->followRepo->findOneBy([
            'student' => $student,
            'club' => $club
        ]);

        if ($existingFollow) {
            // Déjà follower → unfollow
            $this->em->remove($existingFollow);
            $this->em->flush();
            $this->addFlash('success', 'Vous ne suivez plus ' . $club->getName() . '.');
        } else {
            // Pas encore follower → follow
            $follow = new Follow();
            $follow->setStudent($student);
            $follow->setClub($club);
            $follow->setCreatedAt(new \DateTimeImmutable());
            
            $this->em->persist($follow);
            $this->em->flush();
            $this->addFlash('success', 'Vous suivez maintenant ' . $club->getName() . ' !');
        }

        // Redirection vers la page précédente
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}