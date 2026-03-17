<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\Room;
use App\Policies\RoomPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Room::class => RoomPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
