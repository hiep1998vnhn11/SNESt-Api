<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'thresh_id'];

    public function user()
    {
        return $this->belongsTo('App\Models\User')
            ->select('id', 'url', 'profile_photo_path', 'name');
    }

    public function thresh()
    {
        return $this->belongsTo('App\Models\Thresh');
    }
}
