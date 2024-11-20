<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\ApiBaseController;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportsController extends ApiBaseController
{
    public function index(Request $request)
    {
        $reports = Report::query();

        if ($request->has('user_id')) {
            $reports->where('user_id', $request->user_id);
        }

        if ($request->has('reportable_type')) {
            $reports->where('reportable_type', $request->reportable_type);
        }

        $paginatedReports = $reports->paginate($request->get('per_page', 15));
        return $this->sendSuccess($paginatedReports, 'Reports retrieved successfully');
    }

    public function store(Request $request)
    {
        $validTypes = [
            'post' => 'App\Models\Post',
            'artwork' => 'App\Models\Artwork',
        ];

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reportable_id' => 'required|integer',
            'reportable_type' => 'required|string|in:' . implode(',', array_keys($validTypes)),
            'reason' => 'nullable|string|max:255',
        ]);

        $reportableType = $validTypes[$validated['reportable_type']];

        $report = Report::create([
            'user_id' => $validated['user_id'],
            'reportable_id' => $validated['reportable_id'],
            'reportable_type' => $reportableType,
            'reason' => $validated['reason'],
        ]);

        return $this->sendSuccess($report, 'Report created successfully', 201);
    }


    public function show($id)
    {
        $report = Report::findOrFail($id);
        return $this->sendSuccess($report, 'Report retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $report->update($request->all());
        return $this->sendSuccess($report, 'Report updated successfully');
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();
        return $this->sendSuccess(null, 'Report deleted successfully', 204);
    }

    public function filterByUser($userId, Request $request)
    {
        $reports = Report::where('user_id', $userId)->paginate($request->get('per_page', 15));
        return $this->sendSuccess($reports, 'Reports filtered by user retrieved successfully');
    }

    public function filterByType($type, Request $request)
    {
        $reports = Report::where('reportable_type', $type)->paginate($request->get('per_page', 15));
        return $this->sendSuccess($reports, 'Reports filtered by type retrieved successfully');
    }

    public function search(Request $request)
    {
        $query = Report::query();

        if ($request->has('reason')) {
            $query->where('reason', 'LIKE', '%' . $request->reason . '%');
        }

        $reports = $query->paginate($request->get('per_page', 15));
        return $this->sendSuccess($reports, 'Reports search results retrieved successfully');
    }
}
