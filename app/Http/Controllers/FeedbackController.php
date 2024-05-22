<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $openFeedbacks = Feedback::where('status', 'open')->where('user_id', auth()->user()->id)->get();
        $closedFeedbacks = Feedback::where('status', 'closed')->where('user_id', auth()->user()->id)->get();

        return response()->json([
            'open' => $openFeedbacks,
            'closed' => $closedFeedbacks,
        ]);
    }

    public function search(Request $request)
    {
        $openFeedbacks = Feedback::where('status', 'open')->where('user_id', auth()->user()->id)->where('title', 'like', '%' . $request->search . '%')->get();
        $closedFeedbacks = Feedback::where('status', 'closed')->where('user_id', auth()->user()->id)->where('title', 'like', '%' . $request->search . '%')->get();

        return response()->json([
            'open' => $openFeedbacks,
            'closed' => $closedFeedbacks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $feedback = Feedback::create([
            'user_id' => auth()->user()->id,
            'title' => $request->title,
            'content' => $request->content,
            'severity_level' => $request->severity_level,
            'type' => $request->type,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Feedback created successfully',
            'feedback' => $feedback,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        // check if the user is the owner of the feedback or an admin
        if ($feedback->user_id !== auth()->user()->id || !auth()->user()->role === 'admin') {
            return response()->json([
                'message' => 'You are not authorized to view this feedback',
            ], 403);
        }

        return response()->json([
            'feedback' => $feedback,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Feedback $feedback)
    {
        // check if the user is the owner of the feedback or an admin
        if ($feedback->user_id !== auth()->user()->id || !auth()->user()->role === 'admin') {
            return response()->json([
                'message' => 'You are not authorized to update this feedback',
            ], 403);
        }

        $feedback->update([
            'title' => $request->title,
            'content' => $request->content,
            'severity_level' => $request->severity_level,
            'type' => $request->type,
        ]);

        return response()->json([
            'message' => 'Feedback updated successfully',
            'feedback' => $feedback,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback)
    {
        // check if the user is the owner of the feedback or an admin
        if ($feedback->user_id !== auth()->user()->id || !auth()->user()->role === 'admin') {
            return response()->json([
                'message' => 'You are not authorized to delete this feedback',
            ], 403);
        }

        $feedback->delete();

        return response()->json([
            'message' => 'Feedback deleted successfully',
        ]);
    }
}
