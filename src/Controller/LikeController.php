<?php

namespace App\Controller;

use App\Entity\Like;
use App\Repository\EventRepository;
use App\Repository\LikeRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
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
            $like = new Like();
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
}
