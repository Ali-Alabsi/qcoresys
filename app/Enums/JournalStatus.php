<?php

namespace App\Enums;

enum JournalStatus: string
{
    case Draft = 'DRAFT';
    case Posted = 'POSTED';
    case Reversed = 'REVERSED';
}
