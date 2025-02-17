<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function notifications()
    {
        auth()->user()->unreadNotifications->markAsRead();

        $notifications = auth()->user()->notifications()->paginate(10);
        return view('notifications.all-notifications', compact('notifications'));
    }
}
