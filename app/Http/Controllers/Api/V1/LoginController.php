<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Account;
use App\Models\V1\Role;
use App\Enums\RoleType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Tttp\Token;

class LoginController extends Controller {

    public function signUp(Request $request) {
        $validation = $request->validate([
            'name' => 'required|string',
            'lastName' => 'required|string',
            'username' => 'required|string',
            'birthdate' => "required|date",
            'street' => 'required|string',
            'extNumber' => 'required|string',
            'intNumber' => 'nullable|string',
            'colony' => 'required|string',
            'zipCode' => 'required|string',
            'cellphoneNumber' => 'nullable|string',
            'homePhone' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        try {
            if (Auth::user()->role !== RoleType::EXECUTIVE) {
                throw new \Exception("Unauthorized.");
            }
            $existsAccount = Account::existsAccountByEmail($request->input("email"));
            if ($existsAccount) {
                throw new \Exception("There is already an existent account with this email.");
            }
            $existsAccount = Account::existsAccountByUsername($request->input("username"));
            if ($existsAccount) {
                throw new \Exception("There is already an existent account with this username.");
            }
            $account = new Account();
            $account->name = $request->input("name");
            $account->lastName = $request->input("lastName");
            $account->username = $request->input("username");
            $account->birthdate = $request->input("birthdate");
            $account->street = $request->input("street");
            $account->extNumber = $request->input("extNumber");
            $account->intNumber = $request->input("intNumber");
            $account->colony = $request->input("colony");
            $account->zipCode = $request->input("zipCode");
            $account->cellphoneNumber = $request->input("cellphoneNumber");
            $account->homePhone = $request->input("homePhone");
            $account->email = $request->input("email");
            $account->password = Hash::make($request->input("password"));
            $account->role = RoleType::CLIENT;
            $account->createdAt = date("Y-m-d H:i:s");

            $account->saveAccount();

            return response()->json([
                "status" => "success",
                "data" => $account
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on creating the account.",
                "reason" => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        try {
            $account = Account::getAccountByEmail($request->input("email"));
            if (!Hash::check($request->input("password"), $account->password)) {
                return response()->json([
                    "status" => "failure",
                    "message" => "An error occurred on fetching the account.",
                    "reason" => "Account not found."
                ], 401);
            }
            $tokenData = $account->generateToken();
            return response()->json([
                "status" => "success",
                "data" => [
                    "account" => $account,
                    "token" => $tokenData
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on fetching the account.",
                "reason" => $e->getMessage()
            ], 500);
        }   
    }

    public function logout(Request $request) {
        Auth::user()->token()->revoke();

        return response()->json([
            "status" => "success",
            "message" => "Logged out successfully"
        ]);
    }
}