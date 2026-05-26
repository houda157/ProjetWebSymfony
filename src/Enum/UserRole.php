<?php

namespace App\Enum;

enum UserRole: string
{
    case STUDENT = 'student';
    case CLUB_CONFIRMED = 'club_Confirmed';
    case ADMIN = 'admin';
    case CLUB_NOT_CONFIRMED = 'club_NotConfirmed';
}
