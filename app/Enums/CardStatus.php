<?php

namespace App\Enums;

abstract class CardStatus {
    const ACTIVE = 0;
    const SUSPENDED = 1;
    const BLOCKED = 2;
    const CANCELED = 3;
}