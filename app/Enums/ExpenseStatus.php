<?php

namespace App\Enums;

enum ExpenseStatus: string
{
    case Draft = 'DRAFT';
    case Approved = 'APPROVED';
    case Posted = 'POSTED';
    case Rejected = 'REJECTED';
    case Cancelled = 'CANCELLED';
}
