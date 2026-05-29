<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\User;
use App\Entity\Student;
use App\Entity\Event;
use App\Entity\Follow;
use App\Entity\Like;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // ============ ADMIN ============
        $admin = new User();
        $admin->setEmail('admin@insat.tn');
        $admin->setUsername('admin1');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRole('ROLE_ADMIN');
        $admin->setCreatedAt(new \DateTime());
        $manager->persist($admin);

        // ============ CLUBS (Doubled to 6) ============
        $clubsData = [
            ['email' => 'club1@insat.tn', 'username' => 'gdsc',       'password' => 'abc123',  'role' => 'ROLE_CLUB_CONFIRMED',                 'name' => 'GDSC INSAT',        'description' => 'Google Developer Student Club', 'category' => 'Tech'],
            ['email' => 'club2@insat.tn', 'username' => 'ieee',       'password' => 'password','role' => 'ROLE_CLUB_CONFIRMED',                 'name' => 'IEEE INSAT',        'description' => 'Engineering and innovation',    'category' => 'Engineering'],
            ['email' => 'club3@insat.tn', 'username' => 'gamingclub', 'password' => 'qwerty',  'role' => 'ROLE_CLUB_NOT_CONFIRMED', 'name' => 'Gaming Club',       'description' => 'For gamers and esports lovers', 'category' => 'Fun'],
            ['email' => 'club4@insat.tn', 'username' => 'acm',        'password' => 'acm2026', 'role' => 'ROLE_CLUB_CONFIRMED',                 'name' => 'ACM INSAT',         'description' => 'Competitive programming hub',   'category' => 'Tech'],
            ['email' => 'club5@insat.tn', 'username' => 'aerobotix',  'password' => 'aero99',  'role' => 'ROLE_CLUB_CONFIRMED',                 'name' => 'Aerobotix',         'description' => 'Aerospace and Robotics research', 'category' => 'Engineering'],
            ['email' => 'club6@insat.tn', 'username' => 'enactus',    'password' => 'social1', 'role' => 'ROLE_CLUB_NOT_CONFIRMED', 'name' => 'Enactus INSAT',     'description' => 'Social entrepreneurship actions', 'category' => 'Business'],
        ];

        $clubs = [];
        foreach ($clubsData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setUsername($data['username']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $user->setRole($data['role']);
            $user->setCreatedAt(new \DateTime());
            $manager->persist($user);

            $club = new Club();
            $club->setName($data['name']);
            $club->setDescription($data['description']);
            $club->setCategory($data['category']);
            $club->setUser($user);
            $manager->persist($club);

            $clubs[$data['username']] = $club;
        }

        // ============ STUDENTS (Doubled to 12) ============
        $studentsData = [
            ['email' => 'student1@insat.tn',  'username' => 'taz',     'password' => '123456',   'fullname' => 'Taz B',       'major' => 'Software Engineering', 'birthday' => '2002-05-10'],
            ['email' => 'student2@insat.tn',  'username' => 'amira',   'password' => 'letmein',  'fullname' => 'Amira K',     'major' => 'Data Science',         'birthday' => '2003-03-22'],
            ['email' => 'student3@insat.tn',  'username' => 'youssef', 'password' => 'admin',    'fullname' => 'Youssef M',   'major' => 'Networks',             'birthday' => '2001-11-15'],
            ['email' => 'student4@insat.tn',  'username' => 'sarra',   'password' => 'welcome',  'fullname' => 'Sarra L',     'major' => 'AI',                   'birthday' => '2002-07-30'],
            ['email' => 'student5@insat.tn',  'username' => 'karim',   'password' => 'iloveyou', 'fullname' => 'Karim H',     'major' => 'Cybersecurity',        'birthday' => '2000-01-19'],
            ['email' => 'student6@insat.tn',  'username' => 'leila',   'password' => 'abc123',   'fullname' => 'Leila S',     'major' => 'Embedded Systems',     'birthday' => '2003-09-12'],
            ['email' => 'student7@insat.tn',  'username' => 'ahmed',   'password' => 'pass1',    'fullname' => 'Ahmed R',     'major' => 'Software Engineering', 'birthday' => '2002-12-05'],
            ['email' => 'student8@insat.tn',  'username' => 'meriam',  'password' => 'pass2',    'fullname' => 'Meriam G',    'major' => 'Industrial Chemistry', 'birthday' => '2003-04-14'],
            ['email' => 'student9@insat.tn',  'username' => 'omar',    'password' => 'pass3',    'fullname' => 'Omar F',      'major' => 'Instrumentation',      'birthday' => '2001-08-25'],
            ['email' => 'student10@insat.tn', 'username' => 'nour',    'password' => 'pass4',    'fullname' => 'Nour E',      'major' => 'Data Science',         'birthday' => '2002-01-30'],
            ['email' => 'student11@insat.tn', 'username' => 'fedi',    'password' => 'pass5',    'fullname' => 'Fedi K',      'major' => 'AI',                   'birthday' => '2000-10-11'],
            ['email' => 'student12@insat.tn', 'username' => 'rim',     'password' => 'pass6',    'fullname' => 'Rim Z',       'major' => 'Networks',             'birthday' => '2003-06-20'],
        ];

        $students = [];
        foreach ($studentsData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setUsername($data['username']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $user->setRole('ROLE_STUDENT');
            $user->setCreatedAt(new \DateTime());
            $manager->persist($user);

            $student = new Student();
            $student->setFullname($data['fullname']);
            $student->setMajor($data['major']);
            $student->setBirthday(new \DateTime($data['birthday']));
            $student->setUser($user);
            $manager->persist($student);

            $students[$data['username']] = $student;
        }

        // ============ EVENTS (Doubled to 12) ============
        $eventsData = [
            ['club' => 'gdsc',       'title' => 'Flutter Workshop',     'description' => 'Learn Flutter basics',       'date' => '2026-05-01 10:00:00', 'place' => 'INSAT Hall A'],
            ['club' => 'gdsc',       'title' => 'Hackathon 2026',       'description' => '24h coding challenge',       'date' => '2026-06-15 09:00:00', 'place' => 'INSAT'],
            ['club' => 'ieee',       'title' => 'AI Conference',        'description' => 'Talks about AI trends',      'date' => '2026-04-20 14:00:00', 'place' => 'Auditorium'],
            ['club' => 'ieee',       'title' => 'Robotics Workshop',    'description' => 'Build your robot',           'date' => '2026-05-10 10:00:00', 'place' => 'Lab 3'],
            ['club' => 'gamingclub', 'title' => 'FIFA Tournament',      'description' => 'Compete and win',            'date' => '2026-04-25 16:00:00', 'place' => 'Gaming Room'],
            ['club' => 'gamingclub', 'title' => 'LAN Party',            'description' => 'Multiplayer games night',    'date' => '2026-05-30 20:00:00', 'place' => 'INSAT Basement'],
            ['club' => 'acm',        'title' => 'CPC Preparation',      'description' => 'Master dynamic programming',  'date' => '2026-04-12 13:00:00', 'place' => 'Cisco Lab'],
            ['club' => 'acm',        'title' => 'Algorithms Bootcamp',  'description' => 'Graph theory fundamentals',  'date' => '2026-05-18 09:00:00', 'place' => 'Room 102'],
            ['club' => 'aerobotix',  'title' => 'Drone Race Elite',     'description' => 'Autonomous drone racing FPV', 'date' => '2026-06-02 11:00:00', 'place' => 'Sports Complex'],
            ['club' => 'aerobotix',  'title' => 'SolidWorks 3D Modeling','description' => 'CAD fundamentals training',  'date' => '2026-04-29 15:00:00', 'place' => 'CAD Studio'],
            ['club' => 'enactus',    'title' => 'Green Project Pitch',  'description' => 'Present ecological ideas',    'date' => '2026-05-05 14:00:00', 'place' => 'Auditorium'],
            ['club' => 'enactus',    'title' => 'Social Business Intro','description' => 'Impact optimization model',   'date' => '2026-05-25 10:00:00', 'place' => 'Meeting Room B'],
        ];

        $events = [];
        foreach ($eventsData as $i => $data) {
            $event = new Event();
            $event->setTitle($data['title']);
            $event->setDescription($data['description']);
            $event->setEventDate(new \DateTime($data['date']));
            $event->setPlace($data['place']);
            $event->setCreatedAt(new \DateTime());
            $event->setClub($clubs[$data['club']]);
            $manager->persist($event);

            $events[$i + 1] = $event;
        }

        // ============ FOLLOWS (Expanded for 12 students) ============
        $followsData = [
            'taz'     => ['gdsc', 'ieee', 'acm'],
            'amira'   => ['gdsc', 'gamingclub', 'enactus'],
            'youssef' => ['ieee', 'aerobotix'],
            'sarra'   => ['gdsc', 'ieee', 'gamingclub', 'acm'],
            'karim'   => ['gamingclub', 'aerobotix'],
            'leila'   => ['gdsc', 'ieee', 'enactus'],
            'ahmed'   => ['acm', 'gdsc'],
            'meriam'  => ['enactus', 'ieee'],
            'omar'    => ['aerobotix', 'acm'],
            'nour'    => ['gdsc', 'enactus', 'aerobotix'],
            'fedi'    => ['gamingclub', 'acm'],
            'rim'     => ['ieee', 'gdsc'],
        ];

        foreach ($followsData as $studentName => $clubNames) {
            foreach ($clubNames as $clubName) {
                $follow = new Follow();
                $follow->setStudent($students[$studentName]);
                $follow->setClub($clubs[$clubName]);
                $follow->setCreatedAt(new \DateTime());
                $manager->persist($follow);
            }
        }

        // ============ LIKES (Expanded for 12 students and 12 events) ============
        $likesData = [
            'taz'     => [1, 2, 3, 7, 8],
            'amira'   => [1, 4, 11, 12],
            'youssef' => [3, 4, 9, 10],
            'sarra'   => [1, 2, 5, 7, 8],
            'karim'   => [5, 6, 9],
            'leila'   => [2, 3, 4, 11],
            'ahmed'   => [1, 7, 8],
            'meriam'  => [3, 11, 12],
            'omar'    => [7, 9, 10],
            'nour'    => [1, 2, 9, 11, 12],
            'fedi'    => [5, 6, 7, 8],
            'rim'     => [3, 4, 1, 2],
        ];

        foreach ($likesData as $studentName => $eventIds) {
            foreach ($eventIds as $eventId) {
                $like = new Like();
                $like->setStudent($students[$studentName]);
                $like->setEvent($events[$eventId]);
                $like->setCreatedAt(new \DateTime());
                $manager->persist($like);
            }
        }

        // ============ FLUSH ============
        $manager->flush();
    }
}
