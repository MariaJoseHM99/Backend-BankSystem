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
            'lastname' => 'required|string',
            'username' => 'required|string',
            'birthdate' => 'required|date',
            'street' => 'required|string',
            'extNumber' => 'required|string',
            'intNumber' => 'required|string',
            'colony' => 'required|string',
            'zipCode' => 'required|string',
            'cellphoneNumber' => 'string',
            'homePhone' => 'required|string',
            'email' => 'required|string|email|unique:account',
            'password' => 'required|string'
        ]);

        $account = new Account();
        $account->name = $request->input("name");
        $account->lastname = $request->input("lastname");
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
        $role = Role::where("name", "Cliente")->select("roleId")->get()->first();
        if ($role == null) {
            return response()->json([
                "status" => "failure",
                "message" => "Role Client not found."
            ], 500);
        }
        $account->roleId = $role->roleId;
        $account->createdAt = date("Y-m-d H:i:s");
        if (!$account->save()) {
            return response()->json([
                "status" => "failure",
                "message" => "An error occurred on saving account."
            ], 500);
        }

        return response()->json([
            "status" => "success",
            'message' => 'Account created successfully!'
        ], 201);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $account = Account::where("email", $request->input("email"))->get()->first();
        if ($account == null) {
            return response()->json([
                "status" => "failure",
                'message' => 'Unauthorized Email'
            ], 401);
        }
        if (!Hash::check($request->input("password"), $account->password)) {
            return response()->json([
                "status" => "failure",
                'message' => 'Unauthorized Password'
            ], 401);
        }
        
        $tokenResult = $account->createToken('Personal Access Token');

        $token = $tokenResult->token;
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
        ]);
    }

    public function logout(Request $request) {
        $request->account()->token()->revoke();

        return response()->json([
            "status" => "success",
            'message' => 'Logged out successfully'
        ]);
    }

    public function account(Request $request) {
        return response()->json($request->account());
    }
}