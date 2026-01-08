<?php

namespace App\Console\Commands;

use App\Jobs\SendDownAlert;
use App\Models\Website;
use App\Services\WebsiteMonitorService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CheckWebsiteStatus extends Command
{
    protected $signature = 'monitor:check-websites';
    protected $description = 'Check the status of all monitored websites';

    public function handle(WebsiteMonitorService $monitorService): int
    {
        $this->info('Starting website status checks...');
        
        // Get websites that need checking (checked more than 15 minutes ago or never)
        $websites = Website::where('status', '!=', 'inactive')
            ->where(function ($query) {
                $query->whereNull('last_checked_at')
                    ->orWhere('last_checked_at', '<', now()->subMinutes(14)); // 14 min for buffer
            })
            ->with('client')
            ->chunkById(50, function (Collection $websites) use ($monitorService) {
                $this->info("Checking batch of {$websites->count()} websites...");
                
                $results = $monitorService->checkMultipleWebsites($websites);
                
                // Dispatch alerts for websites that are down
                foreach ($results as $websiteId => $isUp) {
                    if (!$isUp) {
                        $website = $websites->firstWhere('id', $websiteId);
                        
                        // Only send alert if the website just went down
                        // (status was previously 'up' or it's the first check)
                        if ($website->status !== 'down' || $website->check_count <= 1) {
                            SendDownAlert::dispatch($website);
                            $this->warn("Alert sent for: {$website->url}");
                        }
                    }
                }
                
                $upCount = count(array_filter($results));
                $this->info("Batch complete: {$upCount} up, " . (count($results) - $upCount) . " down");
            });

        $this->info('Website checks completed.');
        
        return Command::SUCCESS;
    }
}