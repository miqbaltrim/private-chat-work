<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username', 'display_name', 'password', 'avatar', 'public_key', 'is_online', 'last_seen',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen' => 'datetime',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_members')->withPivot('role')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function ownedRooms()
    {
        return $this->hasMany(Room::class, 'created_by');
    }
}
