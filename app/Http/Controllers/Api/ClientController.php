<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Http\Resources\WebsiteResource;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::where('is_active', true)
            ->withCount('websites')
            ->orderBy('email')
            ->get();
            
        return ClientResource::collection($clients);
    }

    public function websites(Client $client)
    {
        if (!$client->is_active) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $websites = $client->websites()
            ->orderBy('status')
            ->orderBy('url')
            ->get();

        return response()->json([
            'client' => new ClientResource($client),
            'websites' => WebsiteResource::collection($websites),
        ]);
    }

    private function getStatusColor($status): string
    {
        return match($status) {
            'up' => 'green',
            'down' => 'red',
            default => 'gray',
        };
    }
}