<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitCard extends Model {
    use HasFactory;

    /**
     * Table in database.
     *
     * @var string
     */
    protected $table = "debitCard";

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
        "balance" => "number",
    ];

    /**
     * The attributes that must be casted to date type.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Adds an amount to the balance.
     *
     * @param float $amount
     * @throws Exception
     * @return void
     */
    public function deposit($amount) {
        if ($amount < 0) {
            throw new \Exception("Amount must be higher than zero.");
        }
        $this->balance += $amount;
        if (!$this->save()) {
            throw new \Exception("An error occurred on saving balance.");
        }
    }

    /**
     * Withdraws an amount from the balance.
     *
     * @param float $amount
     * @throws Exception
     * @return void
     */
    public function withdraw($amount) {
        if ($amount < 0) {
            throw new \Exception("Amount must be higher than zero.");
        }
        if ($this->balance < $amount) {
            throw new \Exception("Amount must be lower or equal than current balance.");
        }
        $this->balance -= $amount;
        if (!$this->save()) {
            throw new \Exception("An error occurred on saving balance.");
        }
    }
}
