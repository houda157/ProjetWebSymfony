<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\Event;
use App\Entity\Follow;
use App\Entity\Like;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ============ ADMIN ============
        $admin = new User();
        $admin->setEmail('admin@insat.tn');
        $admin->setUsername('admin1');
        $admin->setPassword(md5('admin123'));
        $admin->setRole('admin');
        $admin->setCreatedAt(new \DateTime());
        $manager->persist($admin);

        // ============ CLUBS ============
        $clubsData = [
            ['email' => 'club1@insat.tn', 'username' => 'gdsc',       'password' => 'abc123',  'role' => 'club_Confirmed',     'name' => 'GDSC INSAT',  'description' => 'Google Developer Student Club', 'category' => 'Tech'],
            ['email' => 'club2@insat.tn', 'username' => 'ieee',       'password' => 'password','role' => 'club_Confirmed',     'name' => 'IEEE INSAT',  'description' => 'Engineering and innovation',    'category' => 'Engineering'],
            ['email' => 'club3@insat.tn', 'username' => 'gamingclub', 'password' => 'qwerty',  'role' => 'club_NotConfirmed',  'name' => 'Gaming Club', 'description' => 'For gamers and esports lovers', 'category' => 'Fun'],
        ];

        $clubs = [];
        foreach ($clubsData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setUsername($data['username']);
            $user->setPassword(md5($data['password']));
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

        // ============ STUDENTS ============
        $studentsData = [
            ['email' => 'student1@insat.tn', 'username' => 'taz',     'password' => '123456',   'fullname' => 'Taz B',     'major' => 'Software Engineering', 'birthday' => '2002-05-10'],
            ['email' => 'student2@insat.tn', 'username' => 'amira',   'password' => 'letmein',  'fullname' => 'Amira K',   'major' => 'Data Science',         'birthday' => '2003-03-22'],
            ['email' => 'student3@insat.tn', 'username' => 'youssef', 'password' => 'admin',    'fullname' => 'Youssef M', 'major' => 'Networks',             'birthday' => '2001-11-15'],
            ['email' => 'student4@insat.tn', 'username' => 'sarra',   'password' => 'welcome',  'fullname' => 'Sarra L',   'major' => 'AI',                   'birthday' => '2002-07-30'],
            ['email' => 'student5@insat.tn', 'username' => 'karim',   'password' => 'iloveyou', 'fullname' => 'Karim H',   'major' => 'Cybersecurity',        'birthday' => '2000-01-19'],
            ['email' => 'student6@insat.tn', 'username' => 'leila',   'password' => 'abc123',   'fullname' => 'Leila S',   'major' => 'Embedded Systems',     'birthday' => '2003-09-12'],
        ];

        $students = [];
        foreach ($studentsData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setUsername($data['username']);
            $user->setPassword(md5($data['password']));
            $user->setRole('student');
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

        // ============ EVENTS ============
        $eventsData = [
            ['club' => 'gdsc',       'title' => 'Flutter Workshop',   'description' => 'Learn Flutter basics',     'date' => '2026-05-01 10:00:00', 'place' => 'INSAT Hall A'],
            ['club' => 'gdsc',       'title' => 'Hackathon 2026',     'description' => '24h coding challenge',     'date' => '2026-06-15 09:00:00', 'place' => 'INSAT'],
            ['club' => 'ieee',       'title' => 'AI Conference',      'description' => 'Talks about AI trends',    'date' => '2026-04-20 14:00:00', 'place' => 'Auditorium'],
            ['club' => 'ieee',       'title' => 'Robotics Workshop',  'description' => 'Build your robot',         'date' => '2026-05-10 10:00:00', 'place' => 'Lab 3'],
            ['club' => 'gamingclub', 'title' => 'FIFA Tournament',    'description' => 'Compete and win',          'date' => '2026-04-25 16:00:00', 'place' => 'Gaming Room'],
            ['club' => 'gamingclub', 'title' => 'LAN Party',          'description' => 'Multiplayer games night',  'date' => '2026-05-30 20:00:00', 'place' => 'INSAT Basement'],
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

        // ============ FOLLOWS ============
        $followsData = [
            'taz'     => ['gdsc', 'ieee'],
            'amira'   => ['gdsc', 'gamingclub'],
            'youssef' => ['ieee'],
            'sarra'   => ['gdsc', 'ieee', 'gamingclub'],
            'karim'   => ['gamingclub'],
            'leila'   => ['gdsc', 'ieee'],
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

        // ============ LIKES ============
        $likesData = [
            'taz'     => [1, 2, 3],
            'amira'   => [1, 4],
            'youssef' => [3, 4],
            'sarra'   => [1, 2, 5],
            'karim'   => [5, 6],
            'leila'   => [2, 3, 4],
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
