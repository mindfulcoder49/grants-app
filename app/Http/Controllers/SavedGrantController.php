<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SavedGrant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SavedGrantController extends Controller
{
    /**
     * Store a new saved grant for the logged-in user.
     */
    public function store(Request $request)
    {
        // Validate the grant info
        $request->validate([
            'grant' => 'required|json', // Ensures the grant field is a valid JSON string
        ]);

        // Get the logged-in user's email
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }

        // Create new grant
        SavedGrant::create([
            'email' => $user->email,
            'grant_info' => $request->input('grant'),
        ]);

        Log::info('Grant information saved for user: ' . $user->email);

        return response()->json(['message' => 'Grant information saved successfully.']);
    }

    /**
     * Display a listing of saved grants.
     */
    public function index()
    {
        $savedGrants = SavedGrant::all();
        return response()->json($savedGrants);
    }

    /**
     * Delete a saved grant by ID.
     */
    public function destroy($id)
    {
        $savedGrant = SavedGrant::find($id);

        if (!$savedGrant) {
            return response()->json(['error' => 'Grant not found.'], 404);
        }

        $savedGrant->delete();

        return response()->json(['message' => 'Grant deleted successfully.']);
    }
}
