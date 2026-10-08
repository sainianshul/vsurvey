<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Authorize the custom chat channel for the user
Broadcast::channel('vyaparmitra_user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
