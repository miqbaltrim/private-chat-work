<?php
// app/Policies/RoomPolicy.php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function view(User $user, Room $room): bool
    {
        return $room->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Room $room): bool
    {
        return $room->created_by === $user->id;
    }

    public function delete(User $user, Room $room): bool
    {
        return $room->created_by === $user->id;
    }
}
