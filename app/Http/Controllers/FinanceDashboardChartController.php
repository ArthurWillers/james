<?php

namespace App\Http\Controllers;

use App\Services\FinanceDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceDashboardChartController extends Controller
{
    public function __construct(private FinanceDashboardService $dashboardService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $period = $request->query('period', '1m');
        
        $data = $this->dashboardService->getNetWorthChartData($period);
        
        return response()->json($data);
    }
}
