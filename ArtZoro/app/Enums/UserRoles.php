<?php

namespace App\Enums;
use BenSampo\Enum\Enum;

final class UserRoles extends Enum
{
    const SUPER_ADMIN = 'super';
    const ADMIN = 'admin';
    const TUTOR = 'tutor';
    const STUDENT = 'student';
}
