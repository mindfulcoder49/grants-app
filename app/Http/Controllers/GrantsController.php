<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grant;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
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

        // Log the incoming search request
        Log::info('Search request received.', ['request' => $request->all()]);

        // Get the search term
        $searchTerm = $request->input('description');
        $results = [];

        // If the search term is empty when trimmed, default to "Artificial Intelligence"
        if (empty(trim($searchTerm))) {
            Log::info('Search term is empty. Defaulting to "Artificial Intelligence".');
            $searchTerm = 'Artificial Intelligence';
        }

        // Log the actual search term being used
        Log::info('Searching with term: ' . $searchTerm);

        try {
            // Perform vector search
            $results = $this->vectorSearch($searchTerm);
        } catch (\Exception $e) {
            // Log the exception if vector search fails
            Log::error('Vector search failed.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Search failed.'], 500);
        }

        // Log the number of results found
        Log::info('Search results found.', ['count' => $results->count()]);

        // Pass the results to the Inertia page along with the search term
        return Inertia::render('Home', [
            'grants' => $results->values()->toArray(),
            'searchTerm' => $searchTerm
        ]);
    }

    /**
     * Perform a vector-based search using the embedded search term and return grants with similarity scores.
     */
    public function vectorSearch($searchTerm)
    {
        try {
            // Step 1: Embed the search term into a vector
            Log::info('Embedding the search term into a vector.');
            $embeddingResponse = $this->vectorController->embedText(new Request(['texts' => [$searchTerm]]));
            $embedding = $embeddingResponse->getData()->embeddings[0];
            Log::info('Embedding successful.', ['embedding' => $embedding]);

            // Step 2: Search for similar vectors using the embedded search term
            Log::info('Searching for similar vectors.');
            $similarVectorsResponse = $this->vectorController->searchSimilarVectors(new Request([
                'vector' => $embedding,
                'topN' => 20
            ]));
            Log::info('Similar vector search successful.');
            $similarVectors = $similarVectorsResponse->getData()->similar_vectors;
            Log::info('Found similar vectors.', ['count' => count($similarVectors)]);
        } catch (\Exception $e) {
            // Log if embedding or vector search fails
            Log::error('Error in vector search.', ['error' => $e->getMessage()]);
            throw $e;
        }

        // Step 3: Extract vector IDs and similarities
        Log::info('Extracting vector IDs and similarities.');
        $vectorIds = array_column($similarVectors, 'id');
        $vectorSimilarityMap = array_combine(
            array_column($similarVectors, 'id'),
            array_column($similarVectors, 'similarity')
        );

        // Step 4: Fetch the grants related to similar vectors by matching on `opportunity_id`
        Log::info('Fetching grants related to similar vectors.', ['vector_ids' => $vectorIds]);
        $grants = Grant::join('grant_vector', 'grants.opportunity_id', '=', 'grant_vector.opportunity_id')
            ->whereIn('grant_vector.vector_id', $vectorIds)
            ->select('grants.*', 'grant_vector.vector_id')
            ->get();

        Log::info('Fetched grants.', ['count' => $grants->count()]);

        // Step 5: Add similarity to each grant and sort by similarity in descending order
        Log::info('Adding similarity scores to grants.');
        $grants = $grants->map(function ($grant) use ($vectorSimilarityMap) {
            $grant->similarity = $vectorSimilarityMap[$grant->vector_id] ?? 0;
            return $grant;
        })->sortByDesc('similarity');

        Log::info('Grants sorted by similarity.', ['sorted_count' => $grants->count()]);

        // Filter by open grants (if necessary)
        /*
        $grants = $grants->filter(function ($grant) {
            return $grant->close_date >= now()->toDateString();
        });
        */

        return $grants;
    }
}
