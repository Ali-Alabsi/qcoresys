<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case Active = 'ACTIVE';
    case OnLeave = 'ON_LEAVE';
    case Terminated = 'TERMINATED';
    case Probation = 'PROBATION';
}
