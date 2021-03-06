<?php

namespace App\Commands;

use App\Commands\Concerns\EnsureHasToken;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use OhDear\PhpSdk\OhDear;

class SitesListCommand extends Command
{
    use EnsureHasToken;

    /** @var string */
    protected $signature = 'sites:list';

    /** @var string */
    protected $description = 'Display a list of sites and their current status';

    public function handle(OhDear $ohDear)
    {
        if (! $this->ensureHasToken()) {
            return 1;
        }

        $siteData = Collection::make($ohDear->sites())->map(static function ($site) {
            return [
                $site->id,
                $site->url,
                $site->attributes['summarized_check_result'],
                $site->attributes['latest_run_date'],
            ];
        });

        $this->table(['ID', 'URL', 'Status Summary', 'Last Checked'], $siteData);
    }
}
