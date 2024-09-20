<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function store(Request $request)
    {
        // Validate the incoming feedback data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'feedback' => 'required|string|max:1000',
        ]);

        // Send the feedback email
        Mail::raw("Feedback from {$validated['name']} ({$validated['email']}):\n\n{$validated['feedback']}", function ($message) use ($validated) {
            $message->to('alex.g.alcivar49@gmail.com') // Change to your desired recipient
                    ->subject('New Feedback Submission');
        });

        // Return a response to the Inertia.js frontend
        return redirect()->back()->with('success', 'Thank you for your feedback!');
    }
}
