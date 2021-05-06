<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model {
    use HasFactory;

    /**
     * Table in database.
     *
     * @var string
     */
    protected $table = "role";

    /**
     * Primary key in table.
     *
     * @var string
     */
    protected $primaryKey = "roleId";

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
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The attributes that must be casted to date type.
     *
     * @var array
     */
    protected $dates = [];
}
