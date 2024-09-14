<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MHz\MysqlVector\VectorTable;
use MHz\MysqlVector\Nlp\Embedder;
use Illuminate\Support\Facades\Log;

class VectorController extends Controller
{
    protected $vectorTable;
    protected $embedder;

    public function __construct()
    {
        // Initialize MySQLi connection
        Log::info('Initializing MySQLi connection.');
        $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
        $tableName = 'my_vector_table';
        $dimension = 1536;
        $engine = 'InnoDB';

        // Initialize VectorTable
        Log::info('Initializing VectorTable.');
        $this->vectorTable = new VectorTable($mysqli, $tableName, $dimension, $engine);

        // Initialize Embedder for text embedding
        Log::info('Initializing Embedder.');
        $this->embedder = new Embedder();
    }

    // Insert a vector
    public function insertVector(Request $request)
    {
        Log::debug('Inserting a new vector.');
        $vector = $request->input('vector', []); // Expecting a 384-dimensional vector
        $vectorId = $this->vectorTable->upsert($vector);

        Log::debug('Vector inserted successfully.', ['vector_id' => $vectorId]);
        return response()->json(['vector_id' => $vectorId]);
    }

    // Update an existing vector
    public function updateVector(Request $request, $id)
    {
        $vector = $request->input('vector', []);
        $this->vectorTable->upsert($vector, $id);

        return response()->json(['message' => 'Vector updated successfully.']);
    }

    // Delete a vector
    public function deleteVector($id)
    {
        $this->vectorTable->delete($id);

        return response()->json(['message' => 'Vector deleted successfully.']);
    }

    // Calculate cosine similarity between two vectors
    public function calculateCosineSimilarity(Request $request)
    {
        $vector1 = $request->input('vector1', []);
        $vector2 = $request->input('vector2', []);

        $similarity = $this->vectorTable->cosim($vector1, $vector2);

        return response()->json(['cosine_similarity' => $similarity]);
    }

    // Search for similar vectors
    public function searchSimilarVectors(Request $request)
    {
        //put function name in logging 
        Log::info('searchSimilarVectors: Searching for similar vectors.');
        $vector = $request->input('vector', []);
        $topN = $request->input('topN', 5);

        Log::info('searchSimilarVectors: Vector received.', ['vector' => $vector]);
        Log::info('searchSimilarVectors: Top N value.', ['topN' => $topN]);

        $similarVectors = $this->vectorTable->search($vector, $topN);

        Log::info('searchSimilarVectors: Found similar vectors.', ['count' => count($similarVectors)]);
        return response()->json(['similar_vectors' => $similarVectors]);
    }

    // Embed text and get vector representation
    public function embedText(Request $request)
    {
        $texts = $request->input('texts', []);

        $embeddings = $this->embedder->embed($texts);

        return response()->json(['embeddings' => $embeddings]);
    }

    public function listVectors(Request $request)
    {
        // Fetch vectors from the database
        $vectors = $this->vectorTable->selectAll();
    
        // Ensure all data is properly UTF-8 encoded
        array_walk_recursive($vectors, function (&$item) {
            if (is_string($item)) {
                $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
            }
        });
    
        return response()->json(['vectors' => $vectors]);
    }
    
    
}

