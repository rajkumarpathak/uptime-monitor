<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WebsiteFactory extends Factory
{
    public function definition(): array
    {
        $protocols = ['http://', 'https://'];
        $domains = ['example.com', 'test.org', 'demo.net', 'sample.dev', 'website.io'];
        
        return [
            'url' => $protocols[array_rand($protocols)] . $this->faker->word() . '.' . $domains[array_rand($domains)],
            'status' => 'checking',
            'last_checked_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'check_count' => $this->faker->numberBetween(1, 100),
            'failure_count' => $this->faker->numberBetween(0, 10),
        ];
    }
}