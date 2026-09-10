<?php

namespace App\Enums;

enum RequestStatus: string
{
    case New = 'NEW';
    case UnderReview = 'UNDER_REVIEW';
    case RequirementsGathering = 'REQUIREMENTS_GATHERING';
    case Consultation = 'CONSULTATION';
    case Estimation = 'ESTIMATION';
    case QuotationPreparation = 'QUOTATION_PREPARATION';
    case QuotationSent = 'QUOTATION_SENT';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case ConvertedToProject = 'CONVERTED_TO_PROJECT';
    case Closed = 'CLOSED';
    case Cancelled = 'CANCELLED';
}
