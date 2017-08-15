<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'brand','active'
    ];

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

}
