<?php

namespace App\Enums;

abstract class TransactionType {
    const TRANSFER_OF_FUNDS = 0;
    const DEPOSIT = 1;
    const WITHDRAWAL = 2;
    const PAYMENT = 3;
    const MONTHLY_PAYMENT = 4;
    const SURCHARGE_PAYMENT = 5;
}