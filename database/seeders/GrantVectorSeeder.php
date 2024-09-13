<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MHz\MysqlVector\VectorTable;
use MHz\MysqlVector\Nlp\Embedder;

class GrantVectorSeeder extends Seeder
{
    protected $vectorTable;
    protected $embedder;

    public function __construct()
    {
        // Initialize MySQLi connection for VectorTable
        $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
        $tableName = 'my_vector_table';
        $dimension = 1536;
        $engine = 'InnoDB';

        // Initialize VectorTable
        $this->vectorTable = new VectorTable($mysqli, $tableName, $dimension, $engine);

        // Initialize Embedder for text embedding
        $this->embedder = new Embedder();
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
                
                
                /* options: 
                    'opportunity_title',
                    'opportunity_id',
                    'opportunity_number',
                    'opportunity_category',
                    'opportunity_category_explanation',
                    'funding_instrument_type',
                    'category_of_funding_activity',
                    'cfda_number',
                    'eligible_applicants',
                    'additional_information_on_eligibility',
                    'agency_code',
                    'agency_name',
                    'post_date',
                    'close_date',
                    'last_updated_or_created_date',
                    'award_ceiling',
                    'award_floor',
                    'estimated_total_program_funding',
                    'expected_number_of_awards',
                    'description',
                    'cost_sharing_requirement',
                    'additional_information_url',
                    'grantor_contact_email',
                    'grantor_contact_email_description',
                    'grantor_contact_text',
                    'version',
                    'created_at',
                    'updated_at'
                */

                $textToEmbed = "Title: " . $grant->opportunity_title . ' Description: ' . $grant->description;
                $textToEmbed .= ' Category: ' . $grant->opportunity_category . ' Funding Instrument: ' . $grant->funding_instrument_type;
                $textToEmbed .= ' Eligible Applicants: ' . $grant->eligible_applicants . ' Agency: ' . $grant->agency_name;
                    

                // Create embeddings using the embedText function of the Embedder class
                $embedding = $this->embedder->embed([$textToEmbed]);

                // Ensure we have a valid embedding
                if (empty($embedding) || !is_array($embedding[0])) {
                    Log::error("Failed to generate embedding for grant: Opportunity ID - " . $grant->opportunity_id);
                    continue;
                }

                // Insert the new vector into the vector table using the VectorTable class
                $vectorId = $this->vectorTable->upsert($embedding[0]);

                // Insert the pivot table entry with the grant ID, vector ID, and opportunity ID
                DB::table('grant_vector')->insert([
                    'grant_id' => $grant->id,
                    'vector_id' => $vectorId,
                    'opportunity_id' => $grant->opportunity_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Inserted vector for grant: Opportunity ID - " . $grant->opportunity_id);
            } else {
                Log::info("Vector already exists for grant: Opportunity ID - " . $grant->opportunity_id);
            }
        }

        Log::info("Finished processing grants for vector embeddings.");
    }
}
