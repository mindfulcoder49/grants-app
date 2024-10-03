<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grant;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\VectorController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Centroid;
use App\Http\Controllers\CentroidController;
use App\Models\Vector;
use Illuminate\Support\Facades\DB;

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
            'description'   => 'string|nullable',
            'search_type'   => 'string|required',
            'top_centroids' => 'integer|nullable',
            'hamming_mode'  => 'string|nullable',
        ]);

        // Log the incoming search request
        Log::info('Search request received.', ['request' => $request->all()]);

        // Get the search term
        $searchTerm = $request->input('description');

        // If the search term is empty when trimmed, default to "Artificial Intelligence"
        if (empty(trim($searchTerm))) {
            Log::info('Search term is empty. Defaulting to "Artificial Intelligence".');
            $searchTerm = 'Artificial Intelligence';
        }

        // Log the actual search term being used
        Log::info('Searching with term: ' . $searchTerm);

        try {
            $searchType = $request->input('search_type');
            $topN = ($searchType === 'centroid') ? $request->input('top_centroids', 5) : 2000;
            $useHamming = $request->input('hamming_mode', 'hybrid');
            $results = $this->performSearch($searchTerm, $searchType, $topN, $useHamming);
        } catch (\Exception $e) {
            // Log the exception if search fails
            Log::error('Search failed.', ['error' => $e->getMessage()]);
            return Inertia::render('Home', [
                'grants'     => [],
                'searchTerm' => $searchTerm,
                'error'      => $e->getMessage()
            ]);
        }

        // Log the number of results found
        Log::info('Search results found.', ['count' => $results->count()]);

        // Pass the results to the Inertia page along with the search term
        return Inertia::render('Home', [
            'grants'     => $results->values()->toArray(),
            'searchTerm' => $searchTerm
        ]);
    }

    /**
     * Perform a search (vector or centroid) using the embedded search term.
     */
    public function performSearch($searchTerm, $searchType = 'vector', $topN = 2000, $useHamming = 'hybrid')
    {
        try {
            // Step 1: Embed the search term into a vector
            $embedding = $this->embedText($searchTerm);

            // Step 2: Retrieve similar vectors
            $similarVectors = $this->retrieveSimilarVectors($embedding, $searchType, $topN, $useHamming);

            if (empty($similarVectors)) {
                throw new \Exception("No similar vectors found.");
            }

            // Step 3: Extract vector IDs and similarities
            $vectorIds = array_column($similarVectors, 'id');
            $vectorSimilarityMap = array_combine(
                array_column($similarVectors, 'id'),
                array_column($similarVectors, 'similarity')
            );

            // Step 4: Fetch grants and assign similarities
            $grants = $this->fetchGrantsByVectorIds($vectorIds, $vectorSimilarityMap);

            return $grants;
        } catch (\Exception $e) {
            // Log the exception if anything goes wrong
            Log::error('Error in search.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Embed the given text into a vector.
     */
    private function embedText($text)
    {
        // Embedding the search term into a vector
        Log::info('Embedding text into a vector.');
        $embeddingResponse = $this->vectorController->embedText(new Request(['texts' => [$text]]));
        $embedding = $embeddingResponse->getData()->embeddings[0];
        Log::info('Embedding successful.');
        return $embedding;
    }

    /**
     * Retrieve similar vectors based on the search type.
     */
    private function retrieveSimilarVectors($embedding, $searchType, $topN, $useHamming = 'hybrid', $top_centroids = 5, $percentageToRefine = 1)
    {
        if ($searchType === 'centroid') {
            // Instantiate the CentroidController
            $centroidController = new CentroidController();
    
            // Prepare the request data
            $requestData = [
                'vector'        => $embedding,
                'top_centroids' => $top_centroids,
                'topN'          => $topN,
            ];
    
            // Add percentageToRefine if using hybrid search
            if ($useHamming === 'hybrid') {
                $requestData['percentageToRefine'] = $percentageToRefine;
            }
    
            // Create a new Request instance
            $request = new Request($requestData);
    
            // Call the appropriate search method based on $useHamming
            switch ($useHamming) {
                case 'cosine':
                    $similarVectorsResponse = $centroidController->searchByCosineSimilarity($request);
                    break;
                case 'hamming':
                    $similarVectorsResponse = $centroidController->searchByHammingDistance($request);
                    break;
                case 'hybrid':
                    $similarVectorsResponse = $centroidController->searchHybrid($request);
                    break;
                default:
                    // Default to hybrid search if $useHamming is not recognized
                    $similarVectorsResponse = $centroidController->searchHybrid($request);
            }
    
            // Extract similar vectors from the response
            $similarVectors = $similarVectorsResponse->getData()->similar_vectors;
    
            Log::info('Found similar vectors using centroid search.', ['count' => count($similarVectors)]);
            return $similarVectors;
    
        } else {
            // Direct vector search
            Log::info('Searching for similar vectors.');
            $similarVectorsResponse = $this->vectorController->searchSimilarVectors(new Request([
                'vector'     => $embedding,
                'topN'       => $topN,
                'useHamming' => $useHamming,
            ]));
            $similarVectors = $similarVectorsResponse->getData()->similar_vectors;
            Log::info('Found similar vectors.', ['count' => count($similarVectors)]);
            return $similarVectors;
        }
    }
    



    /**
     * Fetch grants by vector IDs and assign similarity scores.
     */
    private function fetchGrantsByVectorIds($vectorIds, $vectorSimilarityMap)
    {
        // Fetch the grants related to similar vectors by matching on `opportunity_id`
        Log::info('Fetching grants related to similar vectors.', ['count' => count($vectorIds)]);
        $grants = Grant::join('grant_vector', 'grants.opportunity_id', '=', 'grant_vector.opportunity_id')
            ->whereIn('grant_vector.vector_id', $vectorIds)
            ->select('grants.*', 'grant_vector.vector_id')
            ->get();

        Log::info('Fetched grants.', ['count' => $grants->count()]);

        // Add similarity to each grant
        Log::info('Adding similarity scores to grants.');
        $grants = $grants->map(function ($grant) use ($vectorSimilarityMap) {
            $grant->similarity = $vectorSimilarityMap[$grant->vector_id] ?? 0;
            return $grant;
        });

        // Sort grants by similarity descending
        $grants = $grants->sortByDesc('similarity');

        Log::info('Grants sorted by similarity.', ['count' => $grants->count()]);

        return $grants;
    }

    

    /**
     * Store the grant information for the logged-in user.
     */
    public function storeGrant(Request $request)
    {
        // Validate the incoming request for grant information
        $request->validate([
            'grant' => 'required|json', // Ensures the grant field is present and in JSON format
        ]);

        // Get the authenticated user's email
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }

        // Check if the email exists in the database
        $existingGrant = Grant::where('email', $user->email)->first();

        if ($existingGrant) {
            // If the user's grant info exists, update it
            $existingGrant->grant_info = $request->input('grant');
            $existingGrant->save();
        } else {
            // If no record exists, create a new one
            Grant::create([
                'email'      => $user->email,
                'grant_info' => $request->input('grant'),
            ]);
        }

        // Log the grant information save event
        Log::info('Grant information stored for user: ' . $user->email);

        return response()->json(['message' => 'Grant information saved successfully.']);
    }
}
