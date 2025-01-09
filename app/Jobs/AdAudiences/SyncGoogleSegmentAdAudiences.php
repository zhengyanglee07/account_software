<?php

namespace App\Jobs\AdAudiences;

use App\AdAudience;
use App\Services\AdAudiences\GoogleCustomerMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncGoogleSegmentAdAudiences implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $GOOGLE_PROVIDER_ID = 1;

        $googleAdAudiences = AdAudience
            ::with(['segment', 'accountOauth'])
            ->whereHas('accountOauth', function ($q) use ($GOOGLE_PROVIDER_ID) {
                $q->where('social_media_provider_id', $GOOGLE_PROVIDER_ID);
            })
            ->get();

        foreach ($googleAdAudiences as $adAudience) {
            $segment = $adAudience->segment;

            $google = new GoogleCustomerMatch(
                $adAudience->accountOauth->refresh_token,
                $segment->account_id
            );
            $google->syncCrmBasedUserList($segment, $adAudience);
        }
    }
}
