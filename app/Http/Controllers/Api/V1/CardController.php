<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\V1\Card;
use App\Models\V1\CreditCard;
use App\Models\V1\DebitCard;
use Auth;
use Illuminate\Http\Request;
use Validator;

class CardController extends Controller {

    public function registerDebitCard(Request $request, int $accountId) {
        $validator = Validator::make(["accountId" => $accountId], [
            "accountId" => "required|integer"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on registering the card.",
                "reason" => "Invalid account ID."
            ], 400);
        }
        try {
            if (Auth::user()->role !== RoleType::EXECUTIVE) {
                throw new \Exception("Unauthorized.");
            }
            $card = Card::createDebitCard($accountId);
            $data = array_merge($card->toArray(), $card->card->toArray());
            $data["card"]["expirationDate"] = $card->card->getExpirationDate();
            return response()->json([
                "status" => "success",
                "data" => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on creating the account.",
                "reason" => $e->getMessage()
            ], 500);
        }
    }

    public function registerCreditCard(Request $request, int $accountId) {
        $validator = Validator::make(["accountId" => $accountId], [
            "accountId" => "required|integer"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on registering the card.",
                "reason" => "Invalid account ID."
            ], 400);
        }
        try {
            if (Auth::user()->role !== RoleType::EXECUTIVE) {
                throw new \Exception("Unauthorized.");
            }
            $card = Card::createCreditCard($accountId);
            $data = array_merge($card->toArray(), $card->card->toArray());
            $data["card"]["expirationDate"] = $card->card->getExpirationDate();
            return response()->json([
                "status" => "success",
                "data" => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on creating the account.",
                "reason" => $e->getMessage()
            ], 500);
        }
    }
    
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
                "message" => "An error occurred on fetching the card.",
                "reason" => "Invalid card number."
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

    public function getCardDebt(Request $request, string $cardNumber) {
        $validator = Validator::make(["cardNumber" => $cardNumber], [
            "cardNumber" => "required|string|min:16|max:16"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on fetching the card.",
                "reason" => "Invalid card number."
            ], 400);
        }
        try {
            $card = Card::getCardByNumber($cardNumber);
            if ($card instanceof DebitCard) {
                return response()->json([
                    "status" => "failure",
                    "message" => "An error occurred on creating the transaction.",
                    "reason" => "Card is not a credit card."
                ], 400);
            }
            $debt = $card->getDebt();
            return response()->json([
                "status" => "success",
                "data" => $debt
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
