<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Tttp\Token;
use App\Models\V1\Account;
use App\Models\V1\Role;


class LoginController extends Controller
{ 
    public function signUp(Request $request)
    {
        $request->validate([
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
        $account->roleId = $role->roleId;
        $account->createdAt = date("Y-m-d H:i:s");
        $account->save();
        


        return response()->json([
            'message' => 'Successfully created account!'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $account = Account::where("email", $request->input("email"))->get()->first();
        if ($account == null) {
            return response()->json([
                'message' => 'Unauthorized Email'
            ], 401);
        }
        if (!Hash::check($request->input("password"), $account->password)) {
            return response()->json([
                'message' => 'Unauthorized Password'
            ], 401);
        }
        
        $tokenResult = $account->createToken('Personal Access Token');

        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
        ]);
    }

    public function logout(Request $request)
    {
        $request->account()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function account(Request $request)
    {
        return response()->json($request->account());
    }
}