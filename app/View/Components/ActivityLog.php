<?php

namespace App\View\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Illuminate\View\View;

class ActivityLog extends Component
{
    public $activities;

    public $model;

    /**
     * Create a new component instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        // Regra de Performance: Limite de 20 registros
        $this->activities = $model->activitiesAsSubject()
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.activity-log');
    }
}
