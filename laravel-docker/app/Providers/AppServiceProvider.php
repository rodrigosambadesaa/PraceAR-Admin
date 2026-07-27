<?php

namespace App\Providers;

use App\Console\Commands\BootstrapPracearCommand;
use App\Console\Commands\SecurityCheckCommand;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', static fn (Request $request): Limit => Limit::perMinute(20)
            ->by('login:' . $request->ip()));

        RateLimiter::for('admin', static fn (Request $request): Limit => Limit::perMinute(120)
            ->by('admin:' . ($request->session()->get('pracear.user_id') ?: $request->ip())));

        RateLimiter::for('sensitive', static fn (Request $request): Limit => Limit::perMinute(10)
            ->by('sensitive:' . ($request->session()->get('pracear.user_id') ?: $request->ip())));

        RateLimiter::for('unity', static fn (Request $request): Limit => Limit::perMinute(120)
            ->by('unity:' . $request->ip()));

        $this->commands([
            BootstrapPracearCommand::class,
            SecurityCheckCommand::class,
        ]);
    }
}
