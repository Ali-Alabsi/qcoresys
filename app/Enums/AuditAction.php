<?php

namespace App\Enums;

enum AuditAction: string
{
    case Create = 'CREATE';
    case Update = 'UPDATE';
    case Delete = 'DELETE';
    case Restore = 'RESTORE';
    case Approve = 'APPROVE';
    case Reject = 'REJECT';
    case Send = 'SEND';
    case Post = 'POST';
    case Void = 'VOID';
    case Cancel = 'CANCEL';
    case Login = 'LOGIN';
    case Logout = 'LOGOUT';
    case Payment = 'PAYMENT';
    case JournalPost = 'JOURNAL_POST';
    case JournalReverse = 'JOURNAL_REVERSE';
}
