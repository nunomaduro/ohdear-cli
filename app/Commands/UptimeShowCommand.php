<?php

namespace App\Commands;

use App\Commands\Concerns\EnsureHasToken;
use Carbon\Carbon;
use LaravelZero\Framework\Commands\Command;
use OhDear\PhpSdk\OhDear;
use OhDear\PhpSdk\Resources\Uptime;

class UptimeShowCommand extends Command
{
    use EnsureHasToken;

    /** @var string */
    protected $signature = 'uptime:show {site-id : The id of the site to view uptime for}
                                        {start-date? : The date to start at}
                                        {end-date? : The date to end at}
                                        {--limit=10 : The number of uptime records to show}
                                        {--timeframe=hour : The timeframe to query data by}';

    /** @var string */
    protected $description = 'Display the recent uptime for a site';

    public function handle(OhDear $ohDear)
    {
        if (! $this->ensureHasToken()) {
            return 1;
        }

        if (! $startDate = $this->argument('start-date')) {
            $startDate = Carbon::yesterday()->format('YmdHis');
        }

        if (! $endDate = $this->argument('end-date')) {
            $endDate = now()->format('YmdHis');
        }

        $timeframe = in_array($this->option('timeframe'), ['hour', 'day', 'month']) ? $this->option('timeframe') : 'hour';

        $uptime = $ohDear->uptime($this->argument('site-id'), $startDate, $endDate, $timeframe);

        if (empty($uptime)) {
            $this->line('Unable to find any uptime periods for the specified site');

            return;
        }

        $this->output->listing(
            collect($uptime)->take((int) $this->option('limit'))->map(static function (Uptime $uptime) {
                return "{$uptime->datetime} ({$uptime->uptimePercentage}%)";
            })->toArray()
        );
    }
}
