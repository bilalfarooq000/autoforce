<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Spatie\Activitylog\Models\Activity;

class SystemController extends Controller
{
    public function activity(Request $request)
    {
        $activities = Activity::with('causer', 'subject')
            ->when($request->user_id, fn ($q, $id) => $q->where('causer_id', $id))
            ->when($request->subject_type, fn ($q, $t) => $q->where('subject_type', $t))
            ->when($request->event, fn ($q, $e) => $q->where('event', $e))
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $causerIds = Activity::whereNotNull('causer_id')->distinct()->pluck('causer_id');
        $causers = User::whereIn('id', $causerIds)->orderBy('name')->get(['id', 'name']);
        $subjectTypes = Activity::whereNotNull('subject_type')->distinct()->pluck('subject_type')->sort()->values();

        return view('admin.activity', compact('activities', 'causers', 'subjectTypes'));
    }

    public function runServiceReminders()
    {
        Artisan::call('autoforce:send-service-reminders');

        return back()->with('status', 'Service reminder check run: '.trim(Artisan::output()));
    }
}
