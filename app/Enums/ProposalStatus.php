<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case Draft = 'DRAFT';
    case InReview = 'IN_REVIEW';
    case Approved = 'APPROVED';
    case Sent = 'SENT';
    case Accepted = 'ACCEPTED';
    case Rejected = 'REJECTED';
    case Expired = 'EXPIRED';
    case Cancelled = 'CANCELLED';
}
