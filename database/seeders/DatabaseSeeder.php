<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Website;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample clients
        $clients = Client::factory()->count(5)->create();
        
        // For each client, create 3-8 websites
        $clients->each(function ($client) {
            $websiteCount = rand(3, 8);
            Website::factory()->count($websiteCount)->create([
                'client_id' => $client->id,
                'status' => ['up', 'down', 'checking'][rand(0, 2)],
            ]);
        });
    }
}