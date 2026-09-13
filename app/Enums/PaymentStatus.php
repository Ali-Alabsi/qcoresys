<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Draft = 'DRAFT';
    case Posted = 'POSTED';
    case Cancelled = 'CANCELLED';
    case Void = 'VOID';
}
