<?php

namespace App\Enums;

enum CustomerType: string
{
    case Individual = 'INDIVIDUAL';
    case Company = 'COMPANY';
    case Government = 'GOVERNMENT';
    case Organization = 'ORGANIZATION';
    case Other = 'OTHER';
}
