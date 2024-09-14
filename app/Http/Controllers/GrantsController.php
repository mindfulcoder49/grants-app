<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grant;
use Inertia\Inertia;
use App\Http\Controllers\VectorController;

class GrantsController extends Controller
{
    protected $vectorController;

    public function __construct()
    {
        $this->vectorController = new VectorController();
    }

    /**
     * Main search method that checks for either text search or vector search.
     */
    public function search(Request $request) 
    {
        // Validate the input
        $request->validate([
            'description' => 'string|nullable',
        ]);

        // Get the search term
        $searchTerm = $request->input('description');
        $results = [];

        // If the search term is empty when trimmed search Artificial Intelligence
        if (empty(trim($searchTerm))) {
            $searchTerm = 'Artificial Intelligence';
        }

        $results = $this->vectorSearch($searchTerm);

        // Pass the results to the Inertia page along with the search term
        return Inertia::render('Home', [
            'grants' => $results->values()->toArray(),
            'searchTerm' => $searchTerm
        ]);
    }

    /**
     * Perform a text-based search across the grants table.
     */
    public function textSearch($searchTerm)
    {
        return Grant::when($searchTerm, function ($query, $searchTerm) {
            return $query->where('description', 'LIKE', "%{$searchTerm}%")
                ->orWhere('opportunity_title', 'LIKE', "%{$searchTerm}%")
                ->orWhere('opportunity_id', 'LIKE', "%{$searchTerm}%")
                ->orWhere('opportunity_number', 'LIKE', "%{$searchTerm}%")
                ->orWhere('opportunity_category', 'LIKE', "%{$searchTerm}%")
                ->orWhere('category_explanation', 'LIKE', "%{$searchTerm}%")
                ->orWhere('cfda_number', 'LIKE', "%{$searchTerm}%")
                ->orWhere('eligible_applicants', 'LIKE', "%{$searchTerm}%")
                ->orWhere('additional_information_on_eligibility', 'LIKE', "%{$searchTerm}%")
                ->orWhere('agency_code', 'LIKE', "%{$searchTerm}%")
                ->orWhere('agency_name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('post_date', 'LIKE', "%{$searchTerm}%")
                ->orWhere('close_date', 'LIKE', "%{$searchTerm}%")
                ->orWhere('last_updated_or_created_date', 'LIKE', "%{$searchTerm}%")
                ->orWhere('award_ceiling', 'LIKE', "%{$searchTerm}%")
                ->orWhere('award_floor', 'LIKE', "%{$searchTerm}%")
                ->orWhere('estimated_total_program_funding', 'LIKE', "%{$searchTerm}%")
                ->orWhere('expected_number_of_awards', 'LIKE', "%{$searchTerm}%")
                ->orWhere('grantor_contact_text', 'LIKE', "%{$searchTerm}%")
                ->orWhere('grantor_contact_email', 'LIKE', "%{$searchTerm}%")
                ->orWhere('grantor_contact_phone_number', 'LIKE', "%{$searchTerm}%")
                ->orWhere('version', 'LIKE', "%{$searchTerm}%");
        })->get();
    }

    /**
     * Perform a vector-based search using the embedded search term and return grants with similarity scores.
     */
    public function vectorSearch($searchTerm)
    {
        // Step 1: Embed the search term into a vector
        $embeddingResponse = $this->vectorController->embedText(new Request(['texts' => [$searchTerm]]));
        $embedding = $embeddingResponse->getData()->embeddings[0];

        // Step 2: Search for similar vectors using the embedded search term
        $similarVectorsResponse = $this->vectorController->searchSimilarVectors(new Request([
            'vector' => $embedding,
            'topN' => 200
        ]));

        $similarVectors = $similarVectorsResponse->getData()->similar_vectors;

        // Step 3: Extract vector IDs and similarities
        $vectorIds = array_column($similarVectors, 'id');
        $vectorSimilarityMap = array_combine(
            array_column($similarVectors, 'id'),
            array_column($similarVectors, 'similarity')
        );

        // Step 4: Fetch the grants related to similar vectors by matching on `opportunity_id`
        $grants = Grant::join('grant_vector', 'grants.opportunity_id', '=', 'grant_vector.opportunity_id')
            ->whereIn('grant_vector.vector_id', $vectorIds)
            ->select('grants.*', 'grant_vector.vector_id')
            ->get();

        // Step 5: Add similarity to each grant and sort by similarity in descending order
        $grants = $grants->map(function ($grant) use ($vectorSimilarityMap) {
            $grant->similarity = $vectorSimilarityMap[$grant->vector_id] ?? 0;
            return $grant;
        })->sortByDesc('similarity');

        /* limit to open grants
        $grants = $grants->filter(function ($grant) {
            return $grant->close_date >= now()->toDateString();
        });
        */

        return $grants;
    }

    
}
