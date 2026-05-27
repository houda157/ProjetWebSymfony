<?php

namespace App\Enum;

enum UserRole: string
{
    case STUDENT = 'ROLE_STUDENT';
    case CLUB_CONFIRMED = 'ROLE_CLUB_CONFIRMED';
    case ADMIN = 'ROLE_ADMIN';
    case CLUB_NOT_CONFIRMED = 'ROLE_CLUB_NOT_CONFIRMED';
}
