<?php

namespace App\Enums;

enum ServiceRequestOptionType: string
{
    case ProjectType = 'PROJECT_TYPE';
    case BudgetRange = 'BUDGET_RANGE';
    case Timeline = 'TIMELINE';
    case ContactMethod = 'CONTACT_METHOD';
}
