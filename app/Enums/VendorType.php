<?php

namespace App\Enums;

enum VendorType: string
{
    case Supplier = 'SUPPLIER';
    case Subcontractor = 'SUBCONTRACTOR';
    case CloudProvider = 'CLOUD_PROVIDER';
    case SoftwareVendor = 'SOFTWARE_VENDOR';
    case Other = 'OTHER';
}
