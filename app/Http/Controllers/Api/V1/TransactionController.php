<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransactionType;
use App\Models\V1\Card;
use App\Models\V1\CreditCard;
use App\Models\V1\DebitCard;
use App\Models\V1\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class TransactionController extends Controller {
    /**
     * Validates transaction input data.
     *
     * @param array $input
     * @return boolean
     */
    private function _hasValidData($input) {
        $validator = Validator::make($input, [
            "originCardId" => "nullable|integer",
            "amount" => "required|numeric",
            "reference" => "required|string|max:6",
            "concept" => "nullable|string|max:25"
        ]);
        return !$validator->fails();
    }

    /**
     * Returns all card transactions.
     *
     * @param Request $request
     * @param integer $cardId
     * @return string
     */
    public function getCardTransactions(Request $request, int $cardId) {
        try {
            $card = Card::getCardById($cardId);
            return response()->json([
                "status" => "success",
                "data" => $card->transactions()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Returns all card transactions by date.
     *
     * @param Request $request
     * @param integer $cardId
     * @param integer $year
     * @param integer $month
     * @return string
     */
    public function getCardTransactionsByDate(Request $request, int $cardId, int $year, int $month) {
        try {
            $card = Card::getCardById($cardId);
            return response()->json([
                "status" => "success",
                "data" => $card->transactionsByDate($month, $year)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Creates a deposit transaction.
     *
     * @param Request $request
     * @param int $cardId
     * @return string
     */
    public function createDepositTransaction(Request $request, int $cardId) {
        if (!$this->_hasValidData($request->all())) {
            return response()->json([
                "status" => "failure",
                "message" => "Not enough fields or invalid data."
            ], 400);
        }
        try {
            $card = Card::getCardById($cardId);
            if ($card instanceof CreditCard) {
                return response()->json([
                    "status" => "failure",
                    "message" => "Destination card is not a debit card."
                ], 400);
            }
            $card->deposit(
                $request->input("amount"),
                $request->input("reference"),
                $request->input("concept")
            );
            return response()->json([
                "status" => "success",
                "message" => "Transaction created."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Creates a withdrawal transaction.
     *
     * @param Request $request
     * @param int $cardId
     * @return string
     */
    public function createWithdrawalTransaction(Request $request, int $cardId) {
        if (!$this->_hasValidData($request->all())) {
            return response()->json([
                "status" => "failure",
                "message" => "Not enough fields or invalid data."
            ], 400);
        }
        try {
            $card = Card::getCardById($cardId);
            if ($card instanceof CreditCard) {
                return response()->json([
                    "status" => "failure",
                    "message" => "Destination card is not a debit card."
                ], 400);
            }
            $card->withdraw(
                $request->input("amount"),
                $request->input("reference"),
                $request->input("concept")
            );
            return response()->json([
                "status" => "success",
                "message" => "Transaction created."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Creates a montly payment transaction.
     *
     * @param Request $request
     * @return string
     */
    public function createMonthlyPaymentTransaction(Request $request) {
        if (!_hasValidData($request->all())) {
            return response()->json([
                "status" => "failure",
                "message" => "Not enough fields or invalid data."
            ], 400);
        }
        try {
            $card = Card::getCardById($request->input("destinationCardId"));
            if ($card instanceof DebitCard) {
                return response()->json([
                    "status" => "failure",
                    "message" => "Destination card is not a credit card."
                ], 400);
            }
            $card->createMonthlyPayment(
                $request->input("amount"),
                $request->input("reference"),
                $request->input("concept")
            );
            return response()->json([
                "status" => "success",
                "message" => "Transaction created."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on creating the transaction."
            ], 500);
        }
    }
}
