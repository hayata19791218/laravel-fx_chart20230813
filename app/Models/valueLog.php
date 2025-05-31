<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class valueLog extends Model
{
    protected $fillable = [
        'rate', 
        'fetched_at'
    ];
}
