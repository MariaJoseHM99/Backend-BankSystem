<?php

namespace App\Models\V1;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Account extends Authenticatable {
    use HasFactory, Notifiable;

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
        "password",
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "roleId" => "integer",
        "createdAt" => "dateTime",
    ];

    /**
     * The attributes that must be casted to date type.
     *
     * @var array
     */
    protected $dates = [
        "birthdate"
    ];
}
