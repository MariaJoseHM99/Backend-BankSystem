<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Models\V1\CreditCard;
use App\Models\V1\DebitCard;
use Illuminate\Http\Request;

class CardController extends Controller {
    
    /**
     * Returns debit or credit card data.
     *
     * @param Request $request
     * @return string
     */
    public function getCard(Request $request) {
        $validator = Validator::make($request->all(), [
            "id" => "required|integer"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => "success",
                "message" => "ID not submitted."
            ], 400);
        }
        try {
            $card = Card::getCardById($request->input("id"));
            return response()->json([
                "status" => "success",
                "data" => $card
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on fetching the card."
            ], 500);
        }
    }
}
