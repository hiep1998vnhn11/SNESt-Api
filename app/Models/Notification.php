<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $table = 'notifications';

    protected $fillable = [
        'id',
        'type',
        'object_type',
        'object_id',
        'title',
        'user_id',
        'target_user_id',
        'read_at',
        'object_url',
        'seen_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')
            ->select(['id', 'full_name', 'profile_photo_path', 'url']);
    }

    public function targetUser()
    {
        return $this->belongsTo('App\Models\User', 'target_user_id', 'id')
            ->select(['id', 'full_name', 'profile_photo_path', 'url']);
    }

    public function getAll()
    {
        return $this->query()
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
