<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;

class GrantsTableSeeder extends Seeder
{
    public function run()
    {
        Log::debug('Starting to seed the grants table.');

        // Get the latest XML file from the storage directory
        $directory = storage_path('app/grants/');
        $files = glob($directory . 'GrantsDBExtract*v2.xml');

        // Ensure we have at least one file
        if (!$files) {
            Log::error('No XML files found in the grants directory.');
            return;
        }

        // Sort the files by modification time (newest first)
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Get the latest file
        $latestXMLFile = $files[0];

        // Load the XML file
        $xml = simplexml_load_file($latestXMLFile);

        Log::debug('Loaded the XML file: ' . $latestXMLFile);

        // Ensure the XML is loaded correctly
        if ($xml === false) {
            Log::error('Failed to load the XML file. Exiting seeder.');
            return;
        }

        // Set the correct namespace for the XML
        $namespace = 'http://apply.grants.gov/system/OpportunityDetail-V1.0';

        // Find all OpportunitySynopsisDetail_1_0 elements
        $grants = $xml->children($namespace)->OpportunitySynopsisDetail_1_0;

        // Ensure grants are found
        if (count($grants) == 0) {
            Log::debug('No grants found in the XML file.');
            return;
        }

        Log::info("Found " . count($grants) . " grants to seed.");

        // Convert the grants to an array and reverse it
        $grantsArray = [];
        foreach ($grants as $grant) {
            $grantsArray[] = $grant;
        }
        $reversedGrants = array_reverse($grantsArray);

        $counter = 0;
        $batchSize = 500;  // Process in batches of 50
        $batch = [];

        foreach ($reversedGrants as $grant) {
            $counter++;
            $batch[] = $this->prepareGrantData($grant);  // Prepare grant data for batch upsert

            // Process when batch reaches the defined size
            if (count($batch) == $batchSize) {
                $this->upsertBatch($batch);
                $batch = []; // Reset batch array

                // Trigger garbage collection
                gc_collect_cycles();
            }
        }

        // Process any remaining grants in the last batch
        if (count($batch) > 0) {
            $this->upsertBatch($batch);
        }

        Log::info("Finished processing $counter grants.");
        $this->command->info("Finished processing $counter grants.");
    }

    /**
     * Prepares grant data for batch upsert.
     */
    private function prepareGrantData($grant)
    {
        // Convert dates from MMDDYYYY to Y-m-d
        $post_date = DateTime::createFromFormat('mdY', (string)$grant->PostDate)->format('Y-m-d');
        $close_date = (string)$grant->CloseDate ? DateTime::createFromFormat('mdY', (string)$grant->CloseDate)->format('Y-m-d') : null;
        $last_updated_or_created_date = DateTime::createFromFormat('mdY', (string)$grant->LastUpdatedDate)->format('Y-m-d');

        // Prepare the data array for this grant
        return [
            'opportunity_title' => html_entity_decode((string)$grant->OpportunityTitle, ENT_QUOTES | ENT_HTML5),
            'opportunity_id' => html_entity_decode((string)$grant->OpportunityID, ENT_QUOTES | ENT_HTML5),
            'opportunity_number' => html_entity_decode((string)$grant->OpportunityNumber, ENT_QUOTES | ENT_HTML5),
            'opportunity_category' => html_entity_decode((string)$grant->OpportunityCategory, ENT_QUOTES | ENT_HTML5),
            'opportunity_category_explanation' => html_entity_decode((string)$grant->CategoryExplanation ?? null, ENT_QUOTES | ENT_HTML5),
            'funding_instrument_type' => html_entity_decode((string)$grant->FundingInstrumentType, ENT_QUOTES | ENT_HTML5),
            'category_of_funding_activity' => html_entity_decode((string)$grant->CategoryOfFundingActivity, ENT_QUOTES | ENT_HTML5),
            'cfda_number' => html_entity_decode((string)$grant->CFDANumbers ?? null, ENT_QUOTES | ENT_HTML5),
            'eligible_applicants' => html_entity_decode((string)$grant->EligibleApplicants, ENT_QUOTES | ENT_HTML5),
            'additional_information_on_eligibility' => html_entity_decode((string)$grant->AdditionalInformationOnEligibility ?? null, ENT_QUOTES | ENT_HTML5),
            'agency_code' => html_entity_decode((string)$grant->AgencyCode ?? null, ENT_QUOTES | ENT_HTML5),
            'agency_name' => html_entity_decode((string)$grant->AgencyName ?? null, ENT_QUOTES | ENT_HTML5),
            'post_date' => $post_date,
            'close_date' => $close_date,
            'last_updated_or_created_date' => $last_updated_or_created_date,
            'award_ceiling' => (float)$grant->AwardCeiling ?? null,
            'award_floor' => (float)$grant->AwardFloor ?? null,
            'estimated_total_program_funding' => (float)$grant->EstimatedTotalProgramFunding ?? null,
            'expected_number_of_awards' => (int)$grant->ExpectedNumberOfAwards ?? null,
            'description' => html_entity_decode((string)$grant->Description ?? null, ENT_QUOTES | ENT_HTML5),
            'cost_sharing_requirement' => (string)$grant->CostSharingOrMatchingRequirement === 'Yes',
            'additional_information_url' => html_entity_decode((string)$grant->AdditionalInformationURL ?? null, ENT_QUOTES | ENT_HTML5),
            'grantor_contact_email' => html_entity_decode((string)$grant->GrantorContactEmail ?? null, ENT_QUOTES | ENT_HTML5),
            'grantor_contact_email_description' => html_entity_decode((string)$grant->GrantorContactEmailDescription ?? null, ENT_QUOTES | ENT_HTML5),
            'grantor_contact_text' => html_entity_decode((string)$grant->GrantorContactText ?? null, ENT_QUOTES | ENT_HTML5),
            'version' => html_entity_decode((string)$grant->Version, ENT_QUOTES | ENT_HTML5),
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    /**
     * Upserts a batch of grants into the database.
     */
    private function upsertBatch(array $batch)
    {
        // Perform a batch upsert into the 'grants' table
        DB::table('grants')->upsert($batch, ['opportunity_id'], [
            'opportunity_title', 'opportunity_number', 'opportunity_category', 'opportunity_category_explanation',
            'funding_instrument_type', 'category_of_funding_activity', 'cfda_number', 'eligible_applicants',
            'additional_information_on_eligibility', 'agency_code', 'agency_name', 'post_date', 'close_date',
            'last_updated_or_created_date', 'award_ceiling', 'award_floor', 'estimated_total_program_funding',
            'expected_number_of_awards', 'description', 'cost_sharing_requirement', 'additional_information_url',
            'grantor_contact_email', 'grantor_contact_email_description', 'grantor_contact_text', 'version', 'updated_at'
        ]);

        Log::info("Upserted a batch of " . count($batch) . " grants.");
    }
}
