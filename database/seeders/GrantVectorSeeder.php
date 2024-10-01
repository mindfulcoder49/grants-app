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

        // Fetch all grants from the database
        $grants = DB::table('grants')->get();

        foreach ($grants as $grant) {
            Log::info("Processing grant: Opportunity ID - " . $grant->opportunity_id);

            // Check if a vector entry exists in the pivot table for this grant and opportunity ID
            $existingVectorEntry = DB::table('grant_vector')
                ->where('grant_id', $grant->id)
                ->where('opportunity_id', $grant->opportunity_id)
                ->exists();

            if (!$existingVectorEntry) {
                Log::info("No vector found for grant: Opportunity ID - " . $grant->opportunity_id);

                // Prepare the text to be embedded (you can use relevant fields such as title, description, etc.)
                $textToEmbed = "Title: " . $grant->opportunity_title . ' Description: ' . $grant->description;
                $textToEmbed .= ' Category: ' . $grant->opportunity_category . ' Funding Instrument: ' . $grant->funding_instrument_type;
                $textToEmbed .= ' Eligible Applicants: ' . $grant->eligible_applicants . ' Agency: ' . $grant->agency_name;

                try {
                    // Create embeddings using the embedText function of the Embedder class
                    $embedding = $this->embedder->embed($textToEmbed);
                } catch (\Exception $e) {
                    Log::error("Failed to generate embedding for grant: Opportunity ID - " . $grant->opportunity_id);
                    continue;
                }

                // Normalize and insert the vector using the Vector model
                $vectorData = $embedding[0];
                $normalizedVector = Vector::normalize($vectorData);
                $magnitude = Vector::getMagnitude($normalizedVector);
                $binaryCode = Vector::vectorToBinary($normalizedVector);

                // Create a new vector entry in the database
                $vectorModel = Vector::create([
                    'vector' => $vectorData,
                    'normalized_vector' => $normalizedVector,
                    'magnitude' => $magnitude,
                    'binary_code' => $binaryCode,
                ]);

                // Insert the pivot table entry with the grant ID, vector ID, and opportunity ID
                DB::table('grant_vector')->insert([
                    'grant_id' => $grant->id,
                    'vector_id' => $vectorModel->id,
                    'opportunity_id' => $grant->opportunity_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Inserted vector for grant: Opportunity ID - " . $grant->opportunity_id);
            } else {
                Log::info("Vector already exists for grant: Opportunity ID - " . $grant->opportunity_id);
                //Check if the vector is older than the grant based on the updated_at field
                //If so, update the vector with the latest embedding
                //You can use the Vector model to update the vector

                // Fetch the existing vector entry for this grant
                $existingVectorEntry = DB::table('grant_vector')
                ->where('grant_id', $grant->id)
                ->where('opportunity_id', $grant->opportunity_id)
                ->first();

                $vectorModel = Vector::find($existingVectorEntry->vector_id);

                if ($vectorModel) {
                    // Compare the updated_at fields of the vector and the grant
                    if ($vectorModel->updated_at < $grant->updated_at) {
                        Log::info("Vector is older than the grant. Updating vector for grant: Opportunity ID - " . $grant->opportunity_id);
            
                        // Prepare the text to be embedded
                        $textToEmbed = "Title: " . $grant->opportunity_title . ' Description: ' . $grant->description;
                        $textToEmbed .= ' Category: ' . $grant->opportunity_category . ' Funding Instrument: ' . $grant->funding_instrument_type;
                        $textToEmbed .= ' Eligible Applicants: ' . $grant->eligible_applicants . ' Agency: ' . $grant->agency_name;
            
                        // Create embeddings using the embedText function of the Embedder class
                        $embedding = $this->embedder->embed([$textToEmbed]);
            
                        // Ensure we have a valid embedding
                        if (empty($embedding) || !is_array($embedding[0])) {
                            Log::error("Failed to generate embedding for grant: Opportunity ID - " . $grant->opportunity_id);
                            return;
                        }
            
                        // Update the existing vector model with new embedding data
                        $vectorModel->update([
                            'vector' => $embedding[0],
                            'normalized_vector' => Vector::normalize($embedding[0]),
                            'magnitude' => Vector::getMagnitude(Vector::normalize($embedding[0])),
                            'binary_code' => Vector::vectorToBinary(Vector::normalize($embedding[0])),
                            'updated_at' => now(), // Update the timestamp
                        ]);
            
                        Log::info("Updated vector for grant: Opportunity ID - " . $grant->opportunity_id);
                    } else {
                        Log::info("Vector is up to date for grant: Opportunity ID - " . $grant->opportunity_id);
                    }
                } else {
                    Log::error("Vector entry not found for grant: Opportunity ID - " . $grant->opportunity_id);
                }
            }
        }

        Log::info("Finished processing grants for vector embeddings.");
    }
}
