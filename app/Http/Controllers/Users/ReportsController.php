<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User\Report;

class ReportsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reportID' => 'required|string|unique:reports,reportID',
            'description' => 'required|string',
            'customer' => 'nullable|array',
            'cashier' => 'nullable|array',
            'branch' => 'nullable|string',
        ]);

        $reportData = [
            'reportID' => $request->input('reportID'),
            'description' => $request->input('description'),
            'customer' => $request->input('customer'),
            'cashier' => $request->input('cashier'),
            'branch' => $request->input('branch'),
            'date' => now()
        ];

        $report = Report::create($reportData);

        return response()->json($report, 201);
    }
}
