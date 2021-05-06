<?php

namespace App\Enums;

abstract class TransactionStatus {
    const PENDING = 0;
    const PAID = 1;
    const CANCELED = 2;
}