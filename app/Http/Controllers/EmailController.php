<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Email;

class EmailController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Log the incoming request data for debugging purposes
            Log::info('Feedback form submission received', $request->all());

            // Validate the incoming feedback data, including an optional image file
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'feedback' => 'required|string|max:1000',
                'image' => 'nullable|image|max:10000', // Optional image, max 10MB
            ]);

            Log::info('Feedback form validation successful', $validated);

            // Define email recipients
            $recipients = [
                'alex.g.alcivar49@gmail.com' => 'Alex Alcivar',
                'alexander.richshea@gmail.com' => 'Second Recipient'
            ];

            // Define email subject and message content
            $subject = 'New Feedback Submission';
            $textBody = new TextPart("Feedback from {$validated['name']} ({$validated['email']}):\n\n{$validated['feedback']}");

            // Create a Symfony Email object to add attachments properly
            $email = (new Email())
                ->subject($subject)
                ->to(...array_keys($recipients))
                ->text("Feedback from {$validated['name']} ({$validated['email']}):\n\n{$validated['feedback']}");

            // Log that we're preparing the email body
            Log::info('Preparing email body', ['body' => $textBody]);

            // Check if an image was uploaded and attach it
            if (isset($validated['image'])) {
                Log::info('Image found, attaching to email', [
                    'image_name' => $validated['image']->getClientOriginalName(),
                    'image_size' => $validated['image']->getSize()
                ]);

                $email->attachFromPath($validated['image']->getRealPath(), $validated['image']->getClientOriginalName(), $validated['image']->getMimeType());
            }

            // Send the email
            Mail::send([], [], function ($message) use ($email) {
                $message->setSymfonyMessage($email);
            });

            Log::info('Email sent successfully');
        
            // Return a response to the Inertia.js frontend
            return redirect()->back()->with('success', 'Thank you for your feedback!');

        } catch (\Exception $e) {
            // Log the error details
            Log::error('Error occurred during feedback submission', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            // Optionally, return an error response to the frontend
            return redirect()->back()->with('error', 'An error occurred while submitting your feedback. Please try again.');
        }
    }
}
