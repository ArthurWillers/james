<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request): View
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('module')) {
            $query->where('subject_type', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('description', $request->action);
        }

        if ($request->filled('user')) {
            $query->where('causer_id', $request->user);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        if ($request->filled('sort')) {
            if ($request->sort === 'oldest') {
                $query->reorder()->oldest();
            }
        }

        $activities = $query->paginate(100)->withQueryString();

        $modules = Activity::select('subject_type')->distinct()->pluck('subject_type');
        $actions = Activity::select('description')->distinct()->pluck('description');
        $users = User::whereIn('id', Activity::select('causer_id')->whereNotNull('causer_id')->distinct())->get();

        return view('audit.index', compact('activities', 'modules', 'actions', 'users'));
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
