<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\University;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fetching universities from Hipo API...');

        // We are filtering by 'United Kingdom' to keep the database lightweight.
        // If you want every university in the world, remove '?country=United+Kingdom'
        $response = Http::get('http://universities.hipolabs.com/search?country=United+Kingdom');

        if ($response->successful()) {
            $universities = $response->json();
            $count = 0;

            foreach ($universities as $uni) {
                // The Hipo API returns domains as an array. We grab the first one.
                $domain = $uni['domains'][0] ?? null;

                if ($domain) {
                    // updateOrCreate checks if the domain exists. 
                    // If yes, it updates the name. If no, it creates a new row.
                    University::updateOrCreate(
                        ['email_domain' => $domain], // Search by domain
                        ['name' => $uni['name']]     // Update/Insert the name
                    );
                    $count++;
                }
            }

            $this->command->info("Success! {$count} universities imported into the database.");
        } else {
            $this->command->error('Failed to connect to the Hipo API.');
        }
    }
}
