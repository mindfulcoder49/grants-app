<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Vector; // Use the new Vector model
use App\Http\Controllers\VectorController;

class GrantVectorSeeder extends Seeder
{
    protected $embedder;

    public function __construct()
    {
        $this->embedder = new VectorController();
    }

    public function run()
    {
        Log::info('Starting the GrantVectorSeeder.');

        // Process grants in chunks to avoid loading all data into memory at once
        DB::table('grants')->orderBy('id')->chunk(100, function ($grants) {
            foreach ($grants as $grant) {
                $this->processGrant($grant);
            }

            // Trigger garbage collection after processing each chunk
            gc_collect_cycles();
        });

        Log::info('Finished processing grants for vector embeddings.');
    }

    private function processGrant($grant)
    {
        Log::info("Processing grant: Opportunity ID - " . $grant->opportunity_id);

        // Check if a vector entry exists for this grant
        $existingVectorEntry = DB::table('grant_vector')
            ->where('grant_id', $grant->id)
            ->where('opportunity_id', $grant->opportunity_id)
            ->exists();

        if (!$existingVectorEntry) {
            Log::info("No vector found for grant: Opportunity ID - " . $grant->opportunity_id);

            // Prepare the text for embedding
            $textToEmbed = $this->prepareTextForEmbedding($grant);

            try {
                // Generate the embedding
                $embedding = $this->embedder->embed($textToEmbed);
            } catch (\Exception $e) {
                Log::error("Failed to generate embedding for grant: Opportunity ID - " . $grant->opportunity_id);
                return;
            }

            // Normalize the vector and insert it into the database
            $this->storeVector($embedding[0], $grant);
        } else {
            Log::info("Vector already exists for grant: Opportunity ID - " . $grant->opportunity_id);
            // Optionally update the vector if it's outdated (you can add this logic here)
        }
    }

    private function prepareTextForEmbedding($grant)
    {
        return "Title: " . $grant->opportunity_title . ' Description: ' . $grant->description
            . ' Category: ' . $grant->opportunity_category . ' Funding Instrument: ' . $grant->funding_instrument_type
            . ' Eligible Applicants: ' . $grant->eligible_applicants . ' Agency: ' . $grant->agency_name;
    }

    private function storeVector($embedding, $grant)
    {
        // Normalize and insert the vector
        $normalizedVector = Vector::normalize($embedding);
        $magnitude = Vector::getMagnitude($normalizedVector);
        $binaryCode = Vector::vectorToBinary($normalizedVector);

        // Create the new vector entry
        $vectorModel = Vector::create([
            'vector' => $embedding,
            'normalized_vector' => $normalizedVector,
            'magnitude' => $magnitude,
            'binary_code' => $binaryCode,
        ]);

        // Insert into the pivot table
        DB::table('grant_vector')->insert([
            'grant_id' => $grant->id,
            'vector_id' => $vectorModel->id,
            'opportunity_id' => $grant->opportunity_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("Inserted vector for grant: Opportunity ID - " . $grant->opportunity_id);

        // Unset large variables to free memory
        unset($embedding, $normalizedVector, $vectorModel);
    }
}
