<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MHz\MysqlVector\VectorTable;
use MHz\MysqlVector\Nlp\Embedder;

class VectorController extends Controller
{
    protected $vectorTable;
    protected $embedder;

    public function __construct()
    {
        // Initialize MySQLi connection
        $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
        $tableName = 'my_vector_table';
        $dimension = 384;
        $engine = 'InnoDB';

        // Initialize VectorTable
        $this->vectorTable = new VectorTable($mysqli, $tableName, $dimension, $engine);

        // Initialize Embedder for text embedding
        $this->embedder = new Embedder();
    }

    // Insert a vector
    public function insertVector(Request $request)
    {
        $vector = $request->input('vector', []); // Expecting a 384-dimensional vector
        $vectorId = $this->vectorTable->upsert($vector);

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
        $vector = $request->input('vector', []);
        $topN = $request->input('topN', 5);

        $similarVectors = $this->vectorTable->search($vector, $topN);

        return response()->json(['similar_vectors' => $similarVectors]);
    }

    // Embed text and get vector representation
    public function embedText(Request $request)
    {
        $texts = $request->input('texts', []);

        $embeddings = $this->embedder->embed($texts);

        return response()->json(['embeddings' => $embeddings]);
    }
}

