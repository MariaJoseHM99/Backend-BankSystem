<?php

namespace App\Models\V1;

use App\Enums\TransactionStatus;
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

    public function getUnpaidTransactions() {
        return Transaction::where("status", TransactionStatus::PENDING)->orderBy("createdAt", "asc")->get();
    }

    public function createMonthlyPayment($amount, $reference, $concept) {
        $CUT_DAY = 15;
        $SURCHARGE_RATE = 0.05;
        $MIN_RATE = 0.015;
        $currentMonth = date("m");
        $currentYear = date("Y");
        if ($amount <= 0) {
            throw new \Exception("Amount must be higher than zero.");
        }
        $originalAmount = $amount;
        $transactions = Transaction::where("cardId", $this->cardId)->get();
        $transactionsArr = \array_reduce($transactions, function ($currArr, $transaction) {
            if ($transaction->createdAt->year < $currentYear || $transaction->createdAt->month < $currentMonth) {
                // $existentMonthlyPayment = Transaction::where("cardId", $this->cardId)
                //     ->where("type", TransactionType::MONTHLY_PAYMENT)
                //     ->where("createdAt", "")
                // if ()
                $currArr[] = [
                    "amountDebt" => $transaction->amount,
                    "surcharge" => $transaction->amount * $SURCHARGE_RATE,
                    "interestRate" => $interest
                ];
            }
        }, []);
        $amountNotPaid = DB::table("transaction")->select(
            "SELECT (sumAmount - amountPaid) AS amountNotPaid FROM (
                SELECT IFNULL(SUM(amount), 0) AS sumAmount FROM transaction
            ) pending_q, (
                SELECT IFNULL(SUM(amount), 0) AS amountPaid FROM transaction WHERE type = 4
            ) paid_q"
        )[0]->amountNotPaid;
        $amountNotPaid += $this->positiveBalance;
        if ($amountNotPaid <= 0) {
            throw new \Exception("There are no debt amount.");
        }
        $minAmount = $amountNotPaid * $MIN_RATE;
        if ($amount < $minAmount) {
            throw new \Exception("Amount must be higher or equal than minimum amount.");
        }
        $currentDay = date("d", time());
        try {
            DB::beginTransaction();
            // TODO: Finish this
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

        }
        
    }
}
