<?php

namespace App\Models\V1;

use App\Enums\TransactionStatus;
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
        $minAmount = 0;
        $currentMonth = date("m");
        $currentYear = date("Y");
        if ($amount <= 0) {
            throw new \Exception("Amount must be higher than zero.");
        }
        $originalAmount = $amount;
        $unpaidTransactions = $this->getUnpaidTransactions();
        
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
