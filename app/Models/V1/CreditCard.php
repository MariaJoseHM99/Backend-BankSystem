<?php

namespace App\Models\V1;

use Auth;
use App\Enums\RoleType;
use App\Enums\TransactionType;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCard extends Card {
    use HasFactory;

    /**
     * Table in database.
     *
     * @var string
     */
    protected $table = "credit_card";

    /**
     * Primary key in table.
     *
     * @var string
     */
    protected $primaryKey = "cardId";

    /**
     * True if there are columns for creation and update dates.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "creditCardTypeId" => "integer",
        "credit" => "float",
        "payday" => "integer",
        "positiveBalance" => "float",
    ];

    /**
     * The attributes that must be casted to date type.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Returns card data from this credit card.
     *
     * @return Card
     */
    public function card() {
        return $this->hasOne(Card::class, "cardId", "cardId");
    }

    /**
     * Returns this credit card type.
     *
     * @return void
     */
    public function creditCardType() {
        return $this->hasOne(CreditCardType::class, "creditCardTypeId", "creditCardTypeId");
    }

    /**
     * Returns an array with total debt of this credit card.
     * This array has "data" and "stack" keys, where "data" has the tatals
     * and "stack" the sum stack.
     *
     * @return array
     */
    public function getDebt() {
        if ($this->card->accountId != Auth::user()->accountId && Auth::user()->role == RoleType::CLIENT) {
            throw new \Exception("Not authorized.");
        }
        $config = DB::table("configuration")->orderBy("createdAt", "desc")->take(1)->get()->first();
        $CUT_DAY = 15;
        $SURCHARGE_RATE = $config->surchargeRate;
        $INTEREST_RATE = $this->creditCardType->interestRate;
        $MIN_AMOUNT_RATE = $config->minAmountRate;
        $transactions = Transaction::where("destinationCardId", $this->cardId)->orderBy("createdAt", "asc")->get();
        $transactionsArr = [
            "subtotal" => 0,
            "totalSurcharge" => 0,
            "interest" => 0,
            "generalInterest" => 0,
            "total" => 0,
        ];
        $currentDate = new \Carbon\Carbon();
        // $currentDate = \Carbon\Carbon::createFromDate(2021, 6, 15, "America/Mexico_City");
        $applyGeneralInterest = false;
        $sumStack = [];
        $monthlyPaymentCounter = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->type == TransactionType::PAYMENT || $transaction->type == TransactionType::WITHDRAWAL) {
                $latestMonthlyPayment = $transactions->toQuery()->where("type", TransactionType::MONTHLY_PAYMENT)
                    ->get()->skip($monthlyPaymentCounter)->first();
                if ($latestMonthlyPayment != null) {
                    $surchargeRate = $transaction->surchargeRate;
                    $interestRate = $transaction->interestRate;
                } else {
                    $surchargeRate = $SURCHARGE_RATE;
                    $interestRate = $INTEREST_RATE;
                }
                if ($currentDate->year == $transaction->createdAt->year && $currentDate->month >= $transaction->createdAt->month) {
                    if ($transaction->createdAt->month == $currentDate->month && $transaction->createdAt->day >= $CUT_DAY) {
                        continue;
                    }
                }
                $existsMonthlyPayment = $latestMonthlyPayment != null;
                if (!$existsMonthlyPayment) {
                    $date = $currentDate;
                } else {
                    $date = $latestMonthlyPayment->createdAt;
                }
                $transactionsArr["subtotal"] += $transaction->amount;
                $sumStack[] = [
                    "amount" => $transaction->amount,
                    "date" => $transaction->createdAt,
                    "type" => "payment"
                ];
                $daysSincePayment = $transaction->createdAt->diffInDays($date);
                if ($daysSincePayment > 30) {
                    $transactionsArr["totalSurcharge"] += $transaction->amount * $surchargeRate;
                    $sumStack[] = [
                        "amount" => $transaction->amount * $surchargeRate,
                        "date" => $transaction->createdAt,
                        "type" => "surcharge"
                    ];
                    if ($daysSincePayment > 45) {
                        $applyGeneralInterest = true;
                        $transactionsArr["interest"] += round($transaction->amount * $interestRate, 2, PHP_ROUND_HALF_EVEN);
                        $sumStack[] = [
                            "amount" => $transaction->amount * $interestRate,
                            "date" => $transaction->createdAt,
                            "type" => "interest"
                        ];
                    }
                }
            } elseif ($transaction->type == TransactionType::MONTHLY_PAYMENT) {
                $monthlyPaymentCounter++;
                if ($applyGeneralInterest) {
                    $transactionsArr["generalInterest"] = round(
                        ($transactionsArr["subtotal"] + $transactionsArr["totalSurcharge"]) * $transaction->interestRate,
                        2,
                        PHP_ROUND_HALF_EVEN
                    );
                    $sumStack[] = [
                        "amount" => $transactionsArr["generalInterest"],
                        "date" => null,
                        "type" => "generalInterest"
                    ];
                    $applyGeneralInterest = false;
                }
                $transactionsArr["subtotal"] -= (
                    $transaction->amount - $transactionsArr["totalSurcharge"] - 
                    $transactionsArr["interest"] - $transactionsArr["generalInterest"]
                );
                $transactionsArr["totalSurcharge"] = 0;
                $transactionsArr["interest"] = 0;
                $transactionsArr["generalInterest"] = 0;
                // The next IF might need changes
                if ($transaction->createdAt->diffInDays($currentDate) > 30 && $transactionsArr["subtotal"] > 0) {
                    $nextMonthlyPayment = $transactions->toQuery()->where("type", TransactionType::MONTHLY_PAYMENT)
                        ->get()->skip($monthlyPaymentCounter)->first();
                    if ($nextMonthlyPayment != null) {
                        $daysUntilNextMonthlyPayment = $transaction->createdAt->diffInDays($nextMonthlyPayment->createdAt);
                        while ($daysUntilNextMonthlyPayment > 30) { // 30 or 45?
                            $transactionsArr["interest"] += $transactionsArr["subtotal"] * $interestRate;
                            $daysUntilNextMonthlyPayment -= 45;
                        }
                    } else {
                        $days = $transaction->createdAt->diffInDays($currentDate);
                        while ($days > 30) {
                            $transactionsArr["interest"] += $transactionsArr["subtotal"] * $interestRate;
                            $days -= 30;
                        }
                    }
                }
                $sumStack[] = [
                    "amount" => -$transaction->amount,
                    "date" => $transaction->createdAt,
                    "type" => "monthly"
                ];
            }
        }
        if ($applyGeneralInterest) {
            $transactionsArr["generalInterest"] = round(
                ($transactionsArr["subtotal"] + $transactionsArr["totalSurcharge"]) * $INTEREST_RATE, 
                2, 
                PHP_ROUND_HALF_EVEN
            );
            $sumStack[] = [
                "amount" => $transactionsArr["generalInterest"],
                "date" => null,
                "type" => "generalInterest"
            ];
        }
        $transactionsArr["total"] = round(
            $transactionsArr["subtotal"] + $transactionsArr["totalSurcharge"] + 
            $transactionsArr["generalInterest"] + $transactionsArr["interest"],
            2,
            PHP_ROUND_HALF_EVEN
        );

        $transactionsArr["minAmount"] = round($transactionsArr["total"] * $MIN_AMOUNT_RATE, 2, PHP_ROUND_HALF_EVEN);

        return [
            "data" => $transactionsArr,
            "stack" => $sumStack
        ];
    }

    /**
     * Creates a monthly payment.
     *
     * @param float $amount
     * @param string $reference
     * @param string $concept
     * @return void
     */
    public function createMonthlyPayment($amount, $reference, $concept) {
        if ($this->card->accountId != Auth::user()->accountId && Auth::user()->role == RoleType::CLIENT) {
            throw new \Exception("Not authorized.");
        }
        if ($amount <= 0) {
            throw new \Exception("Amount must be higher than zero.");
        }
        $lastMonthlyPayment = Transaction::where("destinationCardId", $this->cardId)
            ->where("type", TransactionType::MONTHLY_PAYMENT)
            ->orderBy("createdAt", "desc")->take(1)->get()->first();
        if ($lastMonthlyPayment != null) {
            $date = $lastMonthlyPayment->createdAt;
            $currentDate = new \Carbon\Carbon();
            if ($date->year == $currentDate->year) {
                if ($date->day >= 15 && $currentDate->day >= 15 && $date->month == $currentDate->month) {
                    throw new \Exception("There is already a monthly payment registered for this month.");
                }
                if ($date->day >= 15 && $currentDate->day < 15 && $date->month < $currentDate->month) {
                    throw new \Exception("There is already a monthly payment registered for this month.");
                }
            }
        }
        $debt = $this->getDebt()["data"];
        if ($debt["total"] <= 0) {
            throw new \Exception("There is no debt amount.");
        }
        if ($amount >= $debt["total"]) {
            $positiveBalance = $amount - $debt["total"];
            $this->positiveBalance += $positiveBalance;
            $amount -= $positiveBalance;
            $debt["total"] = 0;
        } elseif ($this->positiveBalance > 0) {
            $difference = $debt["total"] - $this->positiveBalance;
            if ($difference < 0) {
                $this->positiveBalance -= $debt["total"];
                $debt["total"] = 0;
            } else {
                $this->positiveBalance = 0;
                $debt["total"] = $difference;
            }
        }
        if ($amount < $debt["minAmount"]) {
            throw new \Exception("Amount must be higher or equal than minimum amount.");
        }
        $transaction = new Transaction();
        $transaction->destinationCardId = $this->cardId;
        $transaction->type = TransactionType::MONTHLY_PAYMENT;
        $transaction->createdAt = date("Y-m-d H:i:s", time());
        $transaction->amount = $amount;
        $transaction->reference = $reference;
        $transaction->concept = $concept;
        $transaction->interestRate = $this->creditCardType->interestRate;
        $transaction->surchargeRate = DB::table("configuration")->orderBy("createdAt", "desc")->take(1)->get()->first()->surchargeRate;
        try {
            DB::beginTransaction();
            if (!$this->save()) {
                throw new \Exception("An error occurred on saving card.");
            }
            $transaction->saveTransaction();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Withdraws an amount from the credit.
     *
     * @param float $amount
     * @param string $reference
     * @param string $concept
     * @throws Exception
     * @return void
     */
    public function withdraw($amount, $reference, $concept) {
        if ($this->card->accountId != Auth::user()->accountId && Auth::user()->role == RoleType::CLIENT) {
            throw new \Exception("Not authorized.");
        }
        if ($amount <= 0) {
            throw new \Exception("Amount must be higher than zero.");
        }
        if ($this->credit < $amount) {
            throw new \Exception("Amount must be lower or equal than current balance.");
        }
        $this->credit -= $amount;
        $transaction = new Transaction();
        $transaction->destinationCardId = $this->cardId;
        $transaction->type = TransactionType::WITHDRAWAL;
        $transaction->createdAt = date("Y-m-d H:i:s", time());
        $transaction->amount = $amount;
        $transaction->reference = $reference;
        $transaction->concept = $concept;
        try {
            DB::beginTransaction();
            if (!$this->save()) {
                throw new \Exception("An error occurred on saving balance.");
            }
            $transaction->saveTransaction();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
