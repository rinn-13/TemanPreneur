<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueReportRequest;
use App\Http\Resources\IssueReportResource;
use App\Models\IssueReport;
use Illuminate\Http\Request;

class IssueReportController extends Controller
{
    /**
     * Store a new issue report
     */
    public function store(StoreIssueReportRequest $request)
    {
        $validated = $request->validated();
        $attachments = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $attachments[] = $file->store('issue_reports/attachments', 'public');
                }
            }
        }

        $report = IssueReport::create([
            'order_id' => $validated['order_id'] ?? null,
            'buyer_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'type' => $validated['type'],
            'description' => $request->input('message', $validated['description'] ?? null),
            'attachments' => $attachments ?: null,
            // DB enum values are ['open','in_progress','closed'] — use 'open' as default
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Laporan berhasil dikirim',
            'data' => $report,
        ], 201);
    }

    /**
     * Get user's reports
     */
    public function index(Request $request)
    {
        $reports = IssueReport::where('buyer_id', $request->user()->id)
            ->with('order.product.business')
            ->with('buyer')
            ->latest()
            ->get();

        return response()->json([
            'data' => $reports,
        ]);
    }

    /**
     * Admin: Get all issue reports
     */
    public function adminIndex(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $reports = IssueReport::with(['buyer', 'order.product.business'])
            ->latest()
            ->get();

        return IssueReportResource::collection($reports);
    }

    /**
     * Admin: Update report status
     */
    public function adminUpdateStatus(Request $request, IssueReport $report)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            // allowed enum values
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $report->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status laporan berhasil diperbarui',
            'data' => $report,
        ], 200);
    }

    /**
     * Get specific report
     */
    public function show(IssueReport $report)
    {
        // Check ownership
        if ($report->buyer_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $report->load('order.product.business', 'buyer'),
        ]);
    }

    /**
     * Buyer: confirm that issue has been resolved
     */
    public function buyerResolve(IssueReport $report)
    {
        if ($report->buyer_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($report->status === 'closed') {
            return response()->json(['message' => 'Laporan sudah ditutup'], 400);
        }

        $report->update([
            'status' => 'closed',
            'resolved_by_buyer_at' => now(),
        ]);

        return new IssueReportResource($report->load('buyer', 'order.product.business'));
    }
}