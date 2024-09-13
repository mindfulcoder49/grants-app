<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrantsTableSeeder extends Seeder
{
    public function run()
    {
        Log::info('Starting to seed the grants table.');

    // Get the latest XML file from the storage directory
    $directory = storage_path('app/grants/');
    $files = glob($directory . 'GrantsDBExtract*v2.xml');

    // Ensure we have at least one file
    if (!$files) {
        Log::error('No XML files found in the grants directory.');
        return;
    }

    // Sort the files by modification time (newest first)
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    // Get the latest file
    $latestXMLFile = $files[0];

    // Load the XML file
    $xml = simplexml_load_file($latestXMLFile);

    Log::info('Loaded the XML file: ' . $latestXMLFile);

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
            Log::info('No grants found in the XML file.');
            return;
        }

        Log::info("Found " . count($grants) . " grants to seed.");

        $counter = 0;
        foreach ($grants as $grant) {
            $counter++;
            Log::info("Processing grant #$counter: Opportunity ID - " . (string)$grant->OpportunityID);

            // Convert dates to the correct format
            $post_date = date('Y-m-d', strtotime((string)$grant->PostDate));
            $close_date = (string)$grant->CloseDate ? date('Y-m-d', strtotime((string)$grant->CloseDate)) : null;
            $last_updated_or_created_date = date('Y-m-d', strtotime((string)$grant->LastUpdatedDate));

            // Data array for the current grant
            $grantData = [
                'opportunity_title' => (string)$grant->OpportunityTitle,
                'opportunity_id' => (string)$grant->OpportunityID,
                'opportunity_number' => (string)$grant->OpportunityNumber,
                'opportunity_category' => (string)$grant->OpportunityCategory,
                'opportunity_category_explanation' => (string)$grant->CategoryExplanation ?? null,
                'funding_instrument_type' => (string)$grant->FundingInstrumentType,
                'category_of_funding_activity' => (string)$grant->CategoryOfFundingActivity,
                'cfda_number' => (string)$grant->CFDANumbers ?? null,
                'eligible_applicants' => (string)$grant->EligibleApplicants,
                'additional_information_on_eligibility' => (string)$grant->AdditionalInformationOnEligibility ?? null,
                'agency_code' => (string)$grant->AgencyCode ?? null,
                'agency_name' => (string)$grant->AgencyName ?? null,
                'post_date' => $post_date,
                'close_date' => $close_date,
                'last_updated_or_created_date' => $last_updated_or_created_date,
                'award_ceiling' => (float)$grant->AwardCeiling ?? null,
                'award_floor' => (float)$grant->AwardFloor ?? null,
                'estimated_total_program_funding' => (float)$grant->EstimatedTotalProgramFunding ?? null,
                'expected_number_of_awards' => (int)$grant->ExpectedNumberOfAwards ?? null,
                'description' => (string)$grant->Description ?? null,
                'cost_sharing_requirement' => (string)$grant->CostSharingOrMatchingRequirement === 'Yes',
                'additional_information_url' => (string)$grant->AdditionalInformationURL ?? null,
                'grantor_contact_email' => (string)$grant->GrantorContactEmail ?? null,
                'grantor_contact_email_description' => (string)$grant->GrantorContactEmailDescription ?? null,
                'grantor_contact_text' => (string)$grant->GrantorContactText ?? null,
                'version' => (string)$grant->Version,
                'updated_at' => now(),
            ];

            // Check if the grant exists based on the unique Opportunity ID
            $existingGrant = DB::table('grants')->where('opportunity_id', (string)$grant->OpportunityID)->first();

            if ($existingGrant) {
                // Compare the existing record with the new data
                $hasChanged = false;
                foreach ($grantData as $key => $value) {
                    if ($existingGrant->$key != $value) {
                        $hasChanged = true;
                        break;
                    }
                }

                if ($hasChanged) {
                    // Update the existing record since the data has changed
                    DB::table('grants')
                        ->where('id', $existingGrant->id)
                        ->update(array_merge($grantData, ['created_at' => $existingGrant->created_at]));  // Preserve created_at
                    Log::info("Updated grant #$counter: Opportunity ID - " . (string)$grant->OpportunityID);
                    $this->command->info("Updated grant #$counter: Opportunity ID - " . (string)$grant->OpportunityID);
                } else {
                    Log::info("No changes for grant #$counter: Opportunity ID - " . (string)$grant->OpportunityID);
                }
            } else {
                // Insert the new grant record
                DB::table('grants')->insert(array_merge($grantData, ['created_at' => now()]));
                Log::info("Inserted new grant #$counter: Opportunity ID - " . (string)$grant->OpportunityID);
                $this->command->info("Inserted new grant #$counter: Opportunity ID - " . (string)$grant->OpportunityID);
            }
        }

        Log::info("Finished processing $counter grants.");
        $this->command->info("Finished processing $counter grants.");
    }
}
