<?php

namespace App\Models\V1;

use App\Enums\CardType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class Card extends Model {
    use HasFactory;

    /**
     * Table in database.
     *
     * @var string
     */
    protected $table = "card";

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
    protected $hidden = [
        "cvv",
        "pin",
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "cvv" => "integer",
        "pin" => "integer",
        "createdAt" => "dateTime",
        "type" => "integer",
        "status" => "integer",
    ];

    /**
     * The attributes that must be casted to date type.
     *
     * @var array
     */
    protected $dates = [
        "expirationDate"
    ];

    /**
     * Returns debit or credit card instance.
     *
     * @param int $cardId
     * @throws Exception
     * @return mixed
     */
    public static function getCardById($cardId) {
        $card = static::find($cardId);
        if ($card == null) {
            throw new \Exception("Card not found.");
        }
        $debitCreditCard = null;
        if ($card->type == CardType::DEBIT) {
            $debitCreditCard = DebitCard::find($cardId);
            if ($debitCreditCard == null) {
                throw new \Exception("Debit card not found.");
            }
        } elseif ($card->type == CardType::CREDIT) {
            $debitCreditCard = CreditCard::find($cardId);
            if ($debitCreditCard == null) {
                throw new \Exception("Credit card not found.");
            }
        }
        return $debitCreditCard;
    }
}
