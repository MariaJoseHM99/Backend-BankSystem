<?php

namespace App\Models\V1;

use Auth;
use App\Enums\CardType;
use App\Enums\RoleType;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model {
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
        "createdAt" => "datetime",
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
     * Returns all this card transactions.
     *
     * @return array
     */
    public function transactions() {
        return Transaction::where("destinationCardId", $this->cardId)->get();
    }

    /**
     * Returns all this card transactions by month and year.
     *
     * @param integer $month
     * @param integer $year
     * @return array
     */
    public function transactionsByDate(int $month, int $year) {
        $lastMonthDay = date("t", strtotime("$year-$month-1"));
        return Transaction::where("destinationCardId", $this->cardId)
            ->where("createdAt", ">=", "$year-$month-1")
            ->where("createdAt", "<=", "$year-$month-$lastMonthDay")
            ->get();
    }

    /**
     * Returns debit or credit card instance.
     *
     * @param string $cardId
     * @throws Exception
     * @return mixed
     */
    public static function getCardById($cardId) {
        $card = DB::table("card")->select("type", "accountId")->where("cardId", $cardId)->get()->first();
        if ($card == null) {
            throw new \Exception("Card not found.");
        }
        if ($card->accountId != Auth::user()->accountId && Auth::user()->role == RoleType::CLIENT) {
            throw new \Exception("Not authorized.");
        }
        $debitCreditCard = null;
        if ($card->type == CardType::DEBIT) {
            $debitCreditCard = DebitCard::with("card")->find($cardId);
            if ($debitCreditCard == null) {
                throw new \Exception("Debit card not found.");
            }
        } elseif ($card->type == CardType::CREDIT) {
            $debitCreditCard = CreditCard::with("card")->find($cardId);
            if ($debitCreditCard == null) {
                throw new \Exception("Credit card not found.");
            }
        }
        return $debitCreditCard;
    }

    /**
     * Returns debit or credit card instance.
     *
     * @param string $cardNumber
     * @throws Exception
     * @return mixed
     */
    public static function getCardByNumber($cardNumber) {
        $card = DB::table("card")->select("cardId", "accountId", "type")
            ->where("cardNumber", $cardNumber)->get()->first();
        if ($card == null) {
            throw new \Exception("Card not found.");
        }
        if ($card->accountId != Auth::user()->accountId && Auth::user()->role == RoleType::CLIENT) {
            throw new \Exception("Not authorized.");
        }
        $debitCreditCard = null;
        if ($card->type == CardType::DEBIT) {
            $debitCreditCard = DebitCard::find($card->cardId);
            if ($debitCreditCard == null) {
                throw new \Exception("Debit card not found.");
            }
        } elseif ($card->type == CardType::CREDIT) {
            $debitCreditCard = CreditCard::find($card->cardId);
            if ($debitCreditCard == null) {
                throw new \Exception("Credit card not found.");
            }
        }
        return $debitCreditCard;
    }
}
