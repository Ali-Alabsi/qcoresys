<?php

namespace App\Enums;

enum ProjectType: string
{
    case Software = 'SOFTWARE';
    case Mobile = 'MOBILE';
    case Web = 'WEB';
    case Infrastructure = 'INFRASTRUCTURE';
    case Network = 'NETWORK';
    case CyberSecurity = 'CYBER_SECURITY';
    case Cloud = 'CLOUD';
    case Devops = 'DEVOPS';
    case Consulting = 'CONSULTING';
    case Integration = 'INTEGRATION';
    case Maintenance = 'MAINTENANCE';
    case Other = 'OTHER';
}
