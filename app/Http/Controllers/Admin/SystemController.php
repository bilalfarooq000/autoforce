<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Spatie\Activitylog\Models\Activity;

class SystemController extends Controller
{
    public function activity()
    {
        $activities = Activity::with('causer', 'subject')->latest()->paginate(30);

        return view('admin.activity', compact('activities'));
    }

    public function runServiceReminders()
    {
        Artisan::call('autoforce:send-service-reminders');

        return back()->with('status', 'Service reminder check run: '.trim(Artisan::output()));
    }
}
