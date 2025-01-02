<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grant;
use App\Models\SavedGrant;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\VectorController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Centroid;
use App\Http\Controllers\CentroidController;
use App\Models\Vector;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'testMode'    => 'boolean|nullable',
            'topN'          => 'integer|nullable',
            'centroid_async' => 'boolean|nullable',
            'percentageToRefine' => 'numeric|nullable',
            'open_only'     => 'boolean|nullable',
            'advancedFields'    => 'array|nullable',
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

        // Step 1: Embed the search term into a vector
        $embedding = $this->embedText($searchTerm);

        $normalizedVector = Vector::normalize($embedding);

        $centroid_async = $request->input('centroid_async', false);
        $searchType = $request->input('search_type');
        $topN = $request->input('topN', 2000);
        $top_centroids = ($searchType === 'centroid') ? $request->input('top_centroids', 5) : 5;
        $useHamming = $request->input('hamming_mode', 'hybrid');
        $single_centroid = $request->input('single_centroid', -1);
        $percentageToRefine = $request->input('percentageToRefine', 1);

        $open_only = $request->input('open_only', false);
        $scopes = [];

        if ($open_only) {
            $scopes = [['scope' => 'open']];
        } else {
            $scopes = [['scope' => 'all']];
        }
        
        if ($request->input('advancedFields')) {
            //add the advanced fields to the scopes with a scope => keyword field along with the field/value fields from the request
            foreach ($request->input('advancedFields') as $field) {

                //compare to Grants::getSearchFields() to ensure the field is valid
                if (!in_array($field['field'], Grant::getSearchFields())) {
                    Log::error('Invalid search field.', ['field' => $field['field']]);
                    return Inertia::render('Home', [
                        'grants'     => [],
                        'searchTerm' => $searchTerm,
                        'error'      => 'Invalid search field: ' . $field['field']
                    ]);
                }

                //truncate value to 255 characters
                $field['value'] = substr($field['value'], 0, 255);
                $scopes[] = ['scope' => 'keyword', 'field' => $field['field'], 'value' => $field['value']];
            }
        } 
        
        Log::info('Search scopes in GrantsController::search ', ['scopes' => $scopes]);

        if (!$centroid_async) {

            try {
                $results = $this->performSearch($embedding, $searchType, $topN, $useHamming, $top_centroids, $percentageToRefine, $single_centroid, $scopes);
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

            //take topN results
            $results = $results->take($topN);

            if ($request->input('testMode')) {
                // If test mode is enabled, return the results as JSON the same way the API would
                return response()->json(['grants' => $results->values()->toArray()]);
            }

            // Pass the results to the Inertia page along with the search term
            return Inertia::render('Home', [
                'grants'     => $results->values()->toArray(),
                'searchTerm' => $searchTerm
            ]);
        } else {

            Log::info('Searching asynchronously with term: ' . $searchTerm);
            //get the closest centroid array
            $myCentroidController = new CentroidController();    

            Log::info('Finding closest centroids');

            $closestCentroids = $myCentroidController->findClosestCentroids($normalizedVector, $top_centroids);

            Log::info('Found closest centroids', ['count' => count($closestCentroids)]);

            return new StreamedResponse(function() use ($closestCentroids, $embedding, $searchType, $topN, $useHamming, $top_centroids, $percentageToRefine, $scopes) {
                $this->streamSearchResults($closestCentroids, $embedding, $searchType, $topN, $useHamming, $top_centroids, $percentageToRefine, $scopes);
            });



        }

    }

    private function streamSearchResults($closestCentroids, $embedding, $searchType, $topN, $useHamming, $top_centroids, $percentageToRefine, $scopes) {
        foreach ($closestCentroids as $centroid) {
            $centroidID = $centroid['id'];
            try {
                $centroidGrants = $this->performSearch($embedding, $searchType, $topN, $useHamming, $top_centroids, $percentageToRefine, $centroidID, $scopes);
                
                foreach ($centroidGrants as $grant) {
                    echo json_encode($grant) . "\n"; // Output each grant JSON object followed by a newline
                    ob_flush();
                    flush();
                }
            } catch (\Exception $e) {
                Log::error('Error in search.', ['error' => $e->getMessage()]);
            }
        }
    }
    
    

    /**
     * Perform a search (vector or centroid) using the embedded search term.
     */
    public function performSearch($embedding, $searchType = 'vector', $topN = 2000, $useHamming = 'hybrid', $top_centroids = 5, $percentageToRefine = 1, $single_centroid = -1, $scopes = [['scope' => 'all']])
    {
        try {


            // Step 2: Retrieve similar vectors
            $similarVectors = $this->retrieveSimilarVectors($embedding, $searchType, $topN, $useHamming, $top_centroids, $percentageToRefine, $single_centroid, $scopes);

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
    public function embedText($text)
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
    private function retrieveSimilarVectors($embedding, $searchType, $topN, $useHamming = 'hybrid', $top_centroids = 5, $percentageToRefine = 1, $single_centroid = -1, $scopes = [['scope' => 'all']])
    {
        if ($searchType === 'centroid') {
            // Instantiate the CentroidController
            $centroidController = new CentroidController();
    
            // Prepare the request data
            $requestData = [
                'vector'        => $embedding,
                'top_centroids' => $top_centroids,
                'topN'          => $topN,
                'single_centroid' => $single_centroid,
                'scopes'         => $scopes,
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
        $existingGrant = SavedGrant::where('email', $user->email)->first();

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

    public function getSearchFields ( Request $request ) {
        $fields = Grant::getSearchFields();
        return response()->json($fields);
    }
}
