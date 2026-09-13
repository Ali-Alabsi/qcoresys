<?php

namespace App\Enums;

enum PricingType: string
{
    case Fixed = 'FIXED';
    case StartingFrom = 'STARTING_FROM';
    case Custom = 'CUSTOM';
    case ContactUs = 'CONTACT_US';
    case QuoteRequired = 'QUOTE_REQUIRED';
}
