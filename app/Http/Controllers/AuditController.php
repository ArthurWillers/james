<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(): View
    {
        $activities = Activity::with('causer')->latest()->paginate(15);

        return view('audit.index', compact('activities'));
    }

    /**
     * Display the specified audit log.
     */
    public function show(Activity $activity): View
    {
        $activity->load('causer');

        return view('audit.show', compact('activity'));
    }
}
