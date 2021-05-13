<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    use HasFactory;

    /**
     * Table in database.
     *
     * @var string
     */
    protected $table = "transaction";

    /**
     * Primary key in table.
     *
     * @var string
     */
    protected $primaryKey = "transactionId";

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
        "destinationCardId" => "integer",
        "originCardId" => "integer",
        "type" => "integer",
        "createdAt" => "datetime",
        "amount" => "float",
        "interestRate" => "float",
        "surchargeRate" => "float",
        "status" => "integer",
    ];

    /**
     * The attributes that must be casted to date type.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * Saves or updates transaction in database.
     *
     * @throws Exception
     * @return void
     */
    public function saveTransaction() {
        if (!$this->save()) {
            throw new \Exception("An error occurred on saving transaction.");
        }
    }
}
