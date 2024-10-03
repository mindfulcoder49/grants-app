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
            $results = $this->performSearch($searchTerm, $searchType, $topN);
        } catch (\Exception $e) {
            // Log the exception if search fails
            Log::error('Search failed.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Search failed.'], 500);
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
    public function performSearch($searchTerm, $searchType = 'vector', $topN = 2000)
    {
        try {
            // Step 1: Embed the search term into a vector
            $embedding = $this->embedText($searchTerm);

            // Step 2: Retrieve similar vectors
            $similarVectors = $this->retrieveSimilarVectors($embedding, $searchType, $topN);

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
    private function retrieveSimilarVectors($embedding, $searchType, $topN)
    {
        if ($searchType === 'centroid') {
            return $this->retrieveVectorsFromCentroids($embedding, $topN);
        } else {
            // Direct vector search
            Log::info('Searching for similar vectors.');
            $similarVectorsResponse = $this->vectorController->searchSimilarVectors(new Request([
                'vector' => $embedding,
                'topN'   => $topN,
            ]));
            $similarVectors = $similarVectorsResponse->getData()->similar_vectors;
            Log::info('Found similar vectors.', ['count' => count($similarVectors)]);
            return $similarVectors;
        }
    }

    /**
     * Retrieve vectors from centroids and calculate similarities.
     */
    private function retrieveVectorsFromCentroids($embedding, $topN)
    {
        // Step 1: Find the top N closest centroids to the embedded vector
        Log::info("Finding the top {$topN} closest centroids.");
        $closestCentroids = $this->findClosestCentroids($embedding, $topN);

        if (empty($closestCentroids)) {
            throw new \Exception("No centroids found for the vector.");
        }

        Log::info("Top {$topN} closest centroids found: " . implode(', ', array_column($closestCentroids, 'id')));

        // Step 2: Retrieve vectors assigned to these centroids
        Log::info("Fetching vectors associated with top {$topN} centroids.");
        $vectorsInCentroids = DB::table('grant_vector')
            ->join('vectors', 'grant_vector.vector_id', '=', 'vectors.id')
            ->whereIn('grant_vector.centroid_id', array_column($closestCentroids, 'id'))
            ->select('vectors.*', 'grant_vector.grant_id')
            ->get();

        if ($vectorsInCentroids->isEmpty()) {
            Log::warning("No vectors found for the top {$topN} centroids.");
            return [];
        }
        Log::info('Found vectors in the top centroids.', ['count' => $vectorsInCentroids->count()]);

        // Step 3: Perform cosine similarity between the embedded vector and the centroid's vectors
        Log::info('Performing similarity search within the top centroids.');
        $similarVectors = $this->calculateSimilarities($embedding, $vectorsInCentroids);

        return $similarVectors;
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
     * Find the top N closest centroids to the given vector.
     */
    private function findClosestCentroids($vector, $topN)
    {
        $centroids = Centroid::all();
        $centroidDistances = [];

        foreach ($centroids as $centroid) {
            $distance = Vector::cosineSimilarity($vector, $centroid->vector);
            $centroidDistances[] = [
                'id'       => $centroid->id,
                'distance' => $distance
            ];
        }

        // Sort centroids by distance (higher similarity is closer)
        usort($centroidDistances, fn($a, $b) => $b['distance'] <=> $a['distance']);

        // Return the top N closest centroids
        return array_slice($centroidDistances, 0, $topN);
    }

    /**
     * Calculate similarities between the query vector and the vectors in the centroid.
     */
    private function calculateSimilarities($queryVector, $vectorsInCentroid)
    {
        $similarVectors = [];

        foreach ($vectorsInCentroid as $vectorRecord) {
            $vector = json_decode($vectorRecord->vector, true); // Convert JSON string to array
            $similarity = Vector::cosineSimilarity($queryVector, $vector);

            $similarVectors[] = [
                'id'         => $vectorRecord->id,
                'similarity' => $similarity
            ];
        }

        // Sort by similarity (higher is more similar)
        usort($similarVectors, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return $similarVectors;
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
