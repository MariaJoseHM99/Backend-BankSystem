<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Account;
use App\Models\V1\Role;
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
            'birthdate' => 'required|date',
            'street' => 'required|string',
            'extNumber' => 'required|string',
            'intNumber' => 'nullable|string',
            'colony' => 'required|string',
            'zipCode' => 'required|string',
            'cellphoneNumber' => 'nullable|string',
            'homePhone' => 'required|string',
            'email' => 'required|string|email|unique:account',
            'password' => 'required|string'
        ]);
        try {
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
            $account->roleId = Role::getRoleByName("Cliente")->roleId;
            $account->createdAt = date("Y-m-d H:i:s");

            $account->saveAccount();

            return response()->json([
                "status" => "success",
                'message' => 'Account created successfully!'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        try{
            $account = Account::getAccountByEmail($request->input("email"));
            if (!Hash::check($request->input("password"), $account->password)) {
                return response()->json([
                    "status" => "failure",
                    'message' => 'Unauthorized Password'
                ], 401);
            }
            
            $tokenResult = $account->createToken('Personal Access Token');
            $token = $tokenResult->token;  
            $token->expires_at = Carbon::now()->addWeeks(1);
            $account->saveToken();
            return array(
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
            );

        }catch (\Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => $e->getMessage()
            ], 500);
        }   
    }

    public function logout(Request $request) {
        $request->account()->token()->revoke();

        return response()->json([
            "status" => "success",
            'message' => 'Logged out successfully'
        ]);
    }
}