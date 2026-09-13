<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'CASH';
    case BankTransfer = 'BANK_TRANSFER';
    case Cheque = 'CHEQUE';
    case CreditCard = 'CREDIT_CARD';
    case Online = 'ONLINE';
    case Other = 'OTHER';
}
