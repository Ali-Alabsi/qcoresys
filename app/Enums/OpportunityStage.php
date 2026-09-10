<?php

namespace App\Enums;

enum OpportunityStage: string
{
    case Qualification = 'QUALIFICATION';
    case NeedsAnalysis = 'NEEDS_ANALYSIS';
    case Proposal = 'PROPOSAL';
    case Negotiation = 'NEGOTIATION';
    case ClosedWon = 'CLOSED_WON';
    case ClosedLost = 'CLOSED_LOST';
}
