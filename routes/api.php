<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CardController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix("v1")->group(function () {
    Route::post('/account/login', [LoginController::class, 'login']);
    Route::group(['middleware' => 'auth:api'], function () {
        // LOGIN CONTROLLER
        Route::post('/account/signUp', [LoginController::class, 'signUp']);
        Route::post('/account/logout', [LoginController::class, 'logout']);
        // CARD CONTROLLER
        Route::post('/account/{accountId}/card/debit/register', [CardController::class, 'registerDebitCard']);
        Route::post('/account/{accountId}/card/credit/register', [CardController::class, 'registerCreditCard']);
        Route::get('/card/{cardNumber}/get', [CardController::class, 'getCard']);
        Route::get('/card/{cardNumber}/getDebt', [CardController::class, 'getCardDebt']);
        // TRANSACTION CONTROLLER
        Route::get('/card/{cardId}/transaction/get', [TransactionController::class, 'getCardTransactions']);
        Route::get('/card/{cardId}/transaction/date/{year}/{month}/get', [TransactionController::class, 'getCardTransactionsByDate']);
        Route::post('/card/{cardId}/transaction/deposit', [TransactionController::class, 'createDepositTransaction']);
        Route::post('/card/{cardId}/transaction/withdraw', [TransactionController::class, 'createWithdrawalTransaction']);
        Route::post('/card/{cardId}/transaction/monthlyPayment', [TransactionController::class, 'createMonthlyPaymentTransaction']);
    });
});
