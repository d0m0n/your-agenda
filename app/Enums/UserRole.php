<?php

namespace App\Enums;

enum UserRole: string
{
    case General = 'general';
    case Observer = 'observer';
    case SuperAdmin = 'super_admin';
}
