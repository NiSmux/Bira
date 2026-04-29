<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function featureRequests()
    {
        $requests = Feedback::where('type', 'feature_request')
            ->with('user')
            ->latest('created_at')
            ->get();

        return view('feedback.feature-requests', compact('requests'));
    }

    public function bugReport()
    {
        return view('feedback.bug-report');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'        => 'required|in:feature_request,bug_report',
            'title'       => 'required|string|max:160',
            'description' => 'nullable|string|max:5000',
        ]);

        Feedback::create([
            'user_id'     => Auth::user()->id,
            'type'        => $validated['type'],
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        $redirect = $validated['type'] === 'feature_request'
            ? route('feedback.feature-requests')
            : route('feedback.bug-report');

        return redirect($redirect)->with('success',
            $validated['type'] === 'feature_request'
                ? 'Feature request submitted!'
                : 'Bug report submitted. Thank you!'
        );
    }
}
