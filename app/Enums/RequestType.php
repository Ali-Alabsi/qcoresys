<?php

namespace App\Enums;

enum RequestType: string
{
    case TechnicalConsulting = 'TECHNICAL_CONSULTING';
    case SoftwareDevelopment = 'SOFTWARE_DEVELOPMENT';
    case MobileApplication = 'MOBILE_APPLICATION';
    case WebApplication = 'WEB_APPLICATION';
    case SystemIntegration = 'SYSTEM_INTEGRATION';
    case CyberSecurity = 'CYBER_SECURITY';
    case Network = 'NETWORK';
    case Infrastructure = 'INFRASTRUCTURE';
    case Cloud = 'CLOUD';
    case Devops = 'DEVOPS';
    case Database = 'DATABASE';
    case ItSupport = 'IT_SUPPORT';
    case Maintenance = 'MAINTENANCE';
    case Training = 'TRAINING';
    case Other = 'OTHER';
}
