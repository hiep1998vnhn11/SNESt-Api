<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Carbon;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class User extends Authenticatable implements JWTSubject, Searchable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    // use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'email_verified_at',
        'provider_oauth',
        'provider_oauth_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'online_status',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getSearchResult(): SearchResult
    {
        return new \Spatie\Searchable\SearchResult(
            $this,
            $this->name,
            'translate'
        );
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Post');
    }

    public function with_friends()
    {
        return $this->hasMany('App\Models\Friend', 'friend_id');
    }

    public function relationships()
    {
        return $this->hasMany('App\Models\Friend');
    }

    public function friends()
    {
        return $this->hasMany('App\Models\Friend')
            ->where('status', '1')
            ->with('user_friend');
    }

    public function rooms()
    {
        return $this->hasMany('App\Models\Room');
    }

    public function with_rooms()
    {
        return $this->hasMany('App\Models\Room', 'with_id');
    }

    public function pages()
    {
        return $this->hasMany('App\Models\Page');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function sub_comments()
    {
        return $this->hasMany('App\Models\SubComment');
    }

    public function info()
    {
        return $this->hasOne('App\Models\Info');
    }

    public function scopeUserWithUrl($query, $url)
    {
        return $query->where('url', $url);
    }

    public function participants()
    {
        return $this->hasMany('App\Models\Participant');
    }

    public function threshes()
    {
        return $this->belongsToMany('App\Models\Thresh', 'participants');
    }

    public function getOnlineStatusAttribute()
    {
        $expireTime = Redis::get('user:' . $this->id . ':onlineStatus');
        if (!$expireTime) return [
            'time' => $expireTime,
            'status' => false
        ];
        return [
            'time' => $expireTime,
            'status' => $expireTime >= Carbon::now()
        ];
    }

    public function likes()
    {
        return $this->morphMany('App\Models\Like', 'likeable');
    }

    public function follows()
    {
        return $this->hasMany('App\Models\Follow');
    }

    public function followeds()
    {
        return $this->hasMany('App\Models\Follow', 'followed_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification');
    }

    public function unReadNotifications()
    {
        return $this->hasMany('App\Models\Notification')->whereNull('read_at');
    }

    public function unseenNotifications()
    {
        return $this->hasMany('App\Models\Notification')->whereNull('seen_at');
    }
}
