<?php
// app/Models/Room.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_private', 'password', 'created_by'];

    protected $casts = ['is_private' => 'boolean'];

    public function members()
    {
        return $this->belongsToMany(User::class, 'room_members')->withPivot('role')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function onlineMembers()
    {
        return $this->members()->where('is_online', true);
    }
}
