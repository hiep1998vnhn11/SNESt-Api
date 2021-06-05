<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thresh extends Model
{
    use HasFactory;
    protected $fillable = ['type'];

    public function participants()
    {
        return $this->hasMany('App\Models\Participant')->with('user');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'participants');
    }

    public function messages()
    {
        return $this->hasMany('App\Models\Message');
    }

    public function represent()
    {
        return $this->hasOne('App\Models\Participant')
            ->where('user_id', '!=', auth()->user()->id)
            ->with('user');
    }

    public function lastMessage()
    {
        return $this->hasOne('App\Models\Message')
            ->latest('created_at')
            ->with('user');
    }
}
