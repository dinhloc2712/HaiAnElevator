<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_user');

        $query = Activity::with(['causer', 'subject'])->latest();

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        $activities = $query->paginate(20)->withQueryString();

        $subjectTypes = Activity::select('subject_type')->distinct()->pluck('subject_type');
        $events = Activity::select('event')->distinct()->pluck('event');
        $users = User::all();

        return view('admin.activity_logs.index', compact('activities', 'subjectTypes', 'events', 'users'));
    }
}
