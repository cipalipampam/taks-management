<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\User;
use App\Observers\TaskObserver;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureObservability();
        $this->registerPolicies();
        Task::observe(TaskObserver::class);
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Task::class, \App\Policies\TaskPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function configureObservability(): void
    {
        Event::listen(Failed::class, function (Failed $event): void {
            $request = request();
            $usernameField = config('fortify.username', 'email');
            $identifier = $event->credentials[$usernameField] ?? $event->credentials['email'] ?? null;

            Log::warning('auth.login_failed', [
                'guard' => $event->guard,
                'identifier' => $identifier,
                'ip' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        });
    }
}
