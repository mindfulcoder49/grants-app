<?php

namespace App\Jobs;

use App\Mail\SendAlertEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\GrantsController;

class SendSavedSearchEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (!$this->user->alerts_setting || $this->user->alerts_setting['frequency'] === 'disabled') {
            Log::info("Alerts disabled for user: {$this->user->email}");
            return;
        }

        try {
            // Initialize GrantsController
            $grantsController = new GrantsController();

            // Embed the company description
            $embedding = $grantsController->embedText($this->user->company_description);

            // Perform the search
            $results = $grantsController->performSearch(
                $embedding,
                'centroid',                // Default search type
                5,                       // Top 5 results
                'cosine',                // Similarity metric
                200,                       // Top centroids
                1,                       // Refine percentage
                -1,                      // Single centroid (optional)
                [['scope' => 'open']]    // Default scope
            );

            if ($results->isEmpty()) {
                Log::info("No results found for user: {$this->user->email}");
                return;
            }

            // Convert results to array
            $grants = $results->values()->toArray();

            // Send email
            Mail::to($this->user->email)->send(new SendAlertEmail($this->user, $grants));

            Log::info("Alert email sent to user: {$this->user->email}");
        } catch (\Exception $e) {
            Log::error("Error processing search or sending email for user {$this->user->email}: {$e->getMessage()}");
        }
    }
}
