<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use App\Models\SyllabusComment;
use App\Models\SyllabusSubmission;
use App\Models\Syllabus;
use Illuminate\Http\Request;

class SyllabusCommentController extends Controller
{
    /**
     * Compute the current review batch number by counting submissions to pending_review.
     */
    protected function getCurrentBatch(int $syllabusId): int
    {
        $count = SyllabusSubmission::where('syllabus_id', $syllabusId)
            ->where('to_status', 'pending_review')
            ->count();
        return max(1, $count); // first submission cycle is batch 1
    }

    /**
     * List comments grouped by batch for a syllabus; include currentBatch.
     */
    public function index(Request $request, Syllabus $syllabus)
    {
        $comments = SyllabusComment::where('syllabus_id', $syllabus->id)
            ->orderBy('batch')
            ->orderBy('id')
            ->get()
            ->groupBy('batch')
            ->map(function ($group) {
                return $group->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'partial_key' => $c->partial_key,
                        'title' => $c->title,
                        'body' => $c->body,
                        'status' => $c->status,
                        'created_by' => $c->created_by,
                        'updated_by' => $c->updated_by,
                        'created_at' => $c->created_at?->toDateTimeString(),
                        'updated_at' => $c->updated_at?->toDateTimeString(),
                    ];
                })->values();
            });

        return response()->json([
            'success' => true,
            'currentBatch' => $this->getCurrentBatch($syllabus->id),
            'commentsByBatch' => $comments,
        ]);
    }

    /**
     * Create/update a comment for the current review batch. Enforce one per partial per batch.
     */
    public function store(Request $request, Syllabus $syllabus)
    {
        $user = auth()->user();
        $request->validate([
            'partial_key' => 'required|string|max:64',
            'title' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'status' => 'nullable|string|max:24',
        ]);

        $batch = $this->getCurrentBatch($syllabus->id);

        $existing = SyllabusComment::where('syllabus_id', $syllabus->id)
            ->where('partial_key', $request->input('partial_key'))
            ->where('batch', $batch)
            ->first();

        $payload = [
            'syllabus_id' => $syllabus->id,
            'partial_key' => $request->input('partial_key'),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'status' => $request->input('status', 'draft'),
            'batch' => $batch,
            'updated_by' => $user?->id,
        ];

        if ($existing) {
            $existing->fill($payload);
            $existing->save();
            $comment = $existing;
        } else {
            $payload['created_by'] = $user?->id;
            $comment = SyllabusComment::create($payload);
        }

        return response()->json([
            'success' => true,
            'comment' => $comment,
            'batch' => $batch,
        ]);
    }
}
