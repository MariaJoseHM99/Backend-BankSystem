<?php

namespace App\Enums;

abstract class TransactionType {
    const TRANSFER_OF_FUNDS = 0;
    const DEPOSIT = 1;
    const WITHDRAWAL = 2;
    const PAYMENT = 3;
    const MONTHLY_PAYMENT = 4;
    const SURCHARGE_PAYMENT = 5;

    public $types = [
        TRANSFER_OF_FUNDS => 0,
        DEPOSIT => 1,
        WITHDRAWAL => 2,
        PAYMENT => 3,
        MONTHLY_PAYMENT => 4,
        SURCHARGE_PAYMENT => 5
    ];
}