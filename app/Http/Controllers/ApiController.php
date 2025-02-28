<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function fetch(Request $request)
    {
        $apiUrl = $request->input('url');

        // Fetch the data from the API
        try {
            $response = Http::get($apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data);
            } else {
                return response()->json(['error' => 'Failed to fetch data from API.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching data.'], 500);
        }
    }
}