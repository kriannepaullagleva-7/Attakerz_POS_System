<?php

namespace App\Http\Controllers;

use App\Models\Sale;

class ReportController extends Controller
{
    public function index()
    {
        $daily = Sale::selectRaw('DATE(date) as d, SUM(total_amount) as total')
            ->groupBy('d')->get();

        $monthly = Sale::selectRaw('MONTH(date) as m, SUM(total_amount) as total')
            ->groupBy('m')->get();

        return view('reports.index', compact('daily','monthly'));
    }
}
