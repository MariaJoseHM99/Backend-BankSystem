<?php

namespace App\Models\V1;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Tttp\Token;
use Carbon\Carbon;

class Account extends Authenticatable {
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * Table in database.
     *
     * @var string
     */
    protected $table = "account";

    /**
     * Primary key in table.
     *
     * @var string
     */
    protected $primaryKey = "accountId";

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
        "password"
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "role" => "integer",
        "createdAt" => "datetime"
    ];

    /**
     * The attributes that must be casted to date type.
     *
     * @var array
     */
    protected $dates = [
        "birthdate"
    ];

    public function saveAccount() {
        if (!$this->save()) {
            throw new \Exception("An error occurred on saving account.");
        }
    }

    public static function getAccountByEmail(string $email) {
        $account = Account::where("email", $email)->get()->first();
        if ($account == null) {
            throw new \Exception("Account not found.");
        }
        return $account;
    }

    public function generateToken() {
        $tokenResult = $this->createToken('Personal Access Token');
        $token = $tokenResult->token;  
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        return [
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
        ];
    }
}
