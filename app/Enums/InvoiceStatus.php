<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'DRAFT';
    case Approved = 'APPROVED';
    case Posted = 'POSTED';
    case PartiallyPaid = 'PARTIALLY_PAID';
    case Paid = 'PAID';
    case Overdue = 'OVERDUE';
    case Cancelled = 'CANCELLED';
    case Void = 'VOID';
}
