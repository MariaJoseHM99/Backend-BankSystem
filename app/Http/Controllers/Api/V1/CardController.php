<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Models\V1\CreditCard;
use App\Models\V1\DebitCard;
use Illuminate\Http\Request;
use Validator;

class CardController extends Controller {
    
    /**
     * Returns debit or credit card data.
     *
     * @param Request $request
     * @return string
     */
    public function getCard(Request $request, string $cardNumber) {
        $validator = Validator::make(["cardNumber" => $cardNumber], [
            "cardNumber" => "required|string|min:16|max:16"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => "failure",
                "message" => "Invalid card number."
            ], 400);
        }
        try {
            $card = Card::getCardByNumber($cardNumber);
            return response()->json([
                "status" => "success",
                "data" => array_merge($card->toArray(), $card->card->toArray())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on fetching the card.",
                "reason" => $e->getMessage()
            ], 500);
        }
    }
}
