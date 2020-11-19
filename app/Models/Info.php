<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->hasOne('App\Models\User');
    }

    public function jobs()
    {
        return $this->hasMany('App\Models\Job');
    }
    public function educates()
    {
        return $this->hasMany('App\Models\Educate');
    }
    public function stories()
    {
        return $this->hasMany('App\Models\Story');
    }
}
