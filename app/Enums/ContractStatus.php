<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Draft = 'DRAFT';
    case PendingSignature = 'PENDING_SIGNATURE';
    case Active = 'ACTIVE';
    case Expired = 'EXPIRED';
    case Terminated = 'TERMINATED';
    case Cancelled = 'CANCELLED';
}
