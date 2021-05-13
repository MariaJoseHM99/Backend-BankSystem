<?php

namespace App\Models\V1;

use App\Enums\TransactionType;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitCard extends Card {
    use HasFactory;

    /**
     * Table in database.
     *
     * @var string
     */
    protected $table = "debit_card";

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
        "balance" => "float",
    ];

    /**
     * The attributes that must be casted to date type.
     *
     * @var array
     */
    protected $dates = [];

    public function card() {
        return $this->hasOne(Card::class, "cardId", "cardId");
    }

    /**
     * Deposits an amount to the balance.
     *
     * @param float $amount
     * @param string $reference
     * @param string $concept
     * @throws Exception
     * @return void
     */
    public function deposit($amount, $reference, $concept) {
        if ($amount <= 0) {
            throw new \Exception("Amount must be higher than zero.");
        }
        $this->balance += $amount;
        $transaction = new Transaction();
        $transaction->destinationCardId = $this->cardId;
        $transaction->type = TransactionType::DEPOSIT;
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

    /**
     * Withdraws an amount from the balance.
     *
     * @param float $amount
     * @param string $reference
     * @param string $concept
     * @throws Exception
     * @return void
     */
    public function withdraw($amount, $reference, $concept) {
        if ($amount <= 0) {
            throw new \Exception("Amount must be higher than zero.");
        }
        if ($this->balance < $amount) {
            throw new \Exception("Amount must be lower or equal than current balance.");
        }
        $this->balance -= $amount;
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
