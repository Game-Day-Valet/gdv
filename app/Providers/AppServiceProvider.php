<?php

namespace App\Providers;

use App\Repositories\BundleRepository;
use App\Repositories\BundleRepositoryInterface;
use App\Repositories\CouponRepository;
use App\Repositories\CouponRepositoryInterface;
use App\Repositories\FaqRepository;
use App\Repositories\FaqRepositoryInterface;
use App\Repositories\FavoriteRepository;
use App\Repositories\PrivacyPolicyRepository;
use App\Repositories\PrivacyPolicyRepositoryInterface;
use App\Repositories\TermsConditionRepository;
use App\Repositories\TermsConditionRepositoryInterface;
use App\Repositories\FavoriteRepositoryInterface;
use App\Repositories\ItemRepository;
use App\Repositories\ItemRepositoryInterface;
use App\Repositories\RentalRepository;
use App\Repositories\RentalRepositoryInterface;
use App\Repositories\SportRepository;
use App\Repositories\SportRepositoryInterface;
use App\Repositories\TournamentRepository;
use App\Repositories\TournamentRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(TournamentRepositoryInterface::class, TournamentRepository::class);
        $this->app->bind(SportRepositoryInterface::class, SportRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(BundleRepositoryInterface::class, BundleRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);
        $this->app->bind(RentalRepositoryInterface::class, RentalRepository::class);
        $this->app->bind(FavoriteRepositoryInterface::class, FavoriteRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(PrivacyPolicyRepositoryInterface::class, PrivacyPolicyRepository::class);
        $this->app->bind(TermsConditionRepositoryInterface::class, TermsConditionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('apple', \SocialiteProviders\Apple\Provider::class);
        });

        Queue::after(function (JobProcessed $event) {
            Log::channel('queue_success')->info('Job Processed Successfully', [
                'job' => $event->job->resolveName(),
                'id' => $event->job->getJobId(),
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
            ]);
        });

        Queue::failing(function (JobFailed $event) {
            Log::channel('queue_fail')->error('Job Failed', [
                'job' => $event->job->resolveName(),
                'id' => $event->job->getJobId(),
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'exception' => $event->exception->getMessage(),
            ]);
        });
    }
}
