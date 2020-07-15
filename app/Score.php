<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = ['name', 'user_score', 'system_score', 'status'];
}
