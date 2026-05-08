<?php

namespace App\Providers;

use App\Domain\Identity\Contracts\UserRepository;
use App\Domain\Identity\Repositories\EloquentUserRepository;
use App\Models\PolicyDocument;
use App\Domain\AiProcessing\Contracts\AiProvider;
use App\Domain\AiProcessing\Providers\MistralAiProvider;
use App\Domain\AiProcessing\Providers\MockAiProvider;
use App\Domain\Ticketing\Contracts\TicketRepository;
use App\Domain\Ticketing\Repositories\EloquentTicketRepository;
use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use App\Domain\Workspaces\Repositories\EloquentWorkspaceRepository;
use App\Models\Ticket;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Policies\PolicyDocumentPolicy;
use App\Policies\TicketPolicy;
use App\Policies\WorkspaceInvitationPolicy;
use App\Policies\WorkspaceMemberPolicy;
use App\Policies\WorkspacePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(WorkspaceRepository::class, EloquentWorkspaceRepository::class);
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);
        $this->app->bind(AiProvider::class, function ($app): AiProvider {
            $provider = strtolower((string) config('ai.provider', 'mock'));

            return match ($provider) {
                'mock' => $app->make(MockAiProvider::class),
                'mistral' => $app->make(MistralAiProvider::class),
                default => throw new InvalidArgumentException("Unsupported AI provider [{$provider}]."),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Workspace::class, WorkspacePolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(PolicyDocument::class, PolicyDocumentPolicy::class);
        Gate::policy(WorkspaceInvitation::class, WorkspaceInvitationPolicy::class);
        Gate::policy(WorkspaceMember::class, WorkspaceMemberPolicy::class);

        RateLimiter::for('auth-login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email', ''));

            return Limit::perMinute(5)->by('auth-login:'.$email.'|'.$request->ip());
        });

        RateLimiter::for('auth-register-owner', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email', ''));

            return Limit::perMinute(3)->by('auth-register-owner:'.$email.'|'.$request->ip());
        });
    }
}
