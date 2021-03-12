<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    use HasFactory;
    protected $table = 'relationships';
    protected $fillable = ['requester_id', 'addressee_id', 'action_id'];
    public function requester_user()
    {
        return $this->belongsTo('App\Models\User', 'requester_id')->select(['id', 'name', 'profile_photo_path', 'url']);
    }
    public function addressee_user()
    {
        return $this->belongsTo('App\Models\User', 'addressee_id')->select(['id', 'name', 'profile_photo_path', 'url']);
    }
}
