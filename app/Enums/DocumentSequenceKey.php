<?php

namespace App\Enums;

enum DocumentSequenceKey: string
{
    case Customer = 'CUS';
    case Request = 'REQ';
    case Consultation = 'CON';
    case Proposal = 'PRO';
    case Quotation = 'QUO';
    case Contract = 'CTR';
    case Project = 'PRJ';
    case Invoice = 'INV';
    case Payment = 'PAY';
    case Expense = 'EXP';
    case Journal = 'JRN';
    case Vendor = 'VEN';
    case Opportunity = 'OPP';
    case Employee = 'EMP';
    case Contact = 'CTC';
}
