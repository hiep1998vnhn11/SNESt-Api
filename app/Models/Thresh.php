<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thresh extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function participants()
    {
        return $this->hasMany('App\Models\Participant');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'participants');
    }
}
