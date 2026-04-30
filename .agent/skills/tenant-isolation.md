# Skill: Multi-Tenant Data Isolation

Use this skill whenever creating models, controllers, policies, or queries that touch tenant-owned data.

---

## The Rule

Every tenant-owned record has `workspace_id`. Every query on tenant data MUST be scoped to the current workspace.

**Never do:**
```php
Ticket::find($id);
Ticket::all();
Ticket::where('status', 'new')->get();
```

**Always do:**
```php
$workspace->tickets()->findOrFail($id);
$workspace->tickets()->where('status', 'new')->get();
```

---

## Workspace Resolution Middleware

Create `app/Http/Middleware/SetCurrentWorkspace.php`:

```php
public function handle(Request $request, Closure $next): Response
{
    $workspace = Workspace::where('id', $request->route('workspace'))
        ->whereHas('members', fn ($q) => $q->where('user_id', $request->user()->id))
        ->firstOrFail();

    $request->merge(['currentWorkspace' => $workspace]);

    return $next($request);
}
```

Register in `routes/api.php`:
```php
Route::middleware(['auth:sanctum', 'workspace'])
    ->prefix('workspaces/{workspace}')
    ->group(function () {
        Route::apiResource('tickets', TicketController::class);
        Route::apiResource('policies', PolicyController::class);
        // ...
    });
```

---

## Migration Pattern

```php
Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
    // ... other fields
    $table->timestamps();

    $table->index('workspace_id');                   // Always index workspace_id
    $table->index(['workspace_id', 'status']);       // Compound index for common filters
});
```

---

## Model Pattern

```php
class Ticket extends Model
{
    protected $fillable = ['workspace_id', 'subject', 'body', 'status', /* ... */];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function aiRuns(): HasMany
    {
        return $this->hasMany(AiRun::class);
    }
}
```

---

## Policy Pattern

```php
class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->workspaces()->where('id', $ticket->workspace_id)->exists();
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket)
            && $user->roleInWorkspace($ticket->workspace_id) !== 'viewer';
    }
}
```

---

## Controller Pattern

```php
class TicketController extends Controller
{
    public function index(Request $request, Workspace $workspace): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Ticket::class, $workspace]);

        $tickets = $workspace->tickets()
            ->with('aiOutput')
            ->filter($request->only(['status', 'category', 'urgency']))
            ->latest()
            ->paginate(25);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request, Workspace $workspace): TicketResource
    {
        $this->authorize('create', [Ticket::class, $workspace]);

        $ticket = $workspace->tickets()->create($request->validated());

        ProcessTicketAiPipelineJob::dispatch($ticket)->onQueue('ai');

        return new TicketResource($ticket);
    }
}
```

---

## Checklist for Every New Tenant Model

- [ ] Migration has `workspace_id` as a non-nullable `foreignId` with `constrained()`
- [ ] Migration has `index('workspace_id')` or compound index
- [ ] Model has `workspace(): BelongsTo` relationship
- [ ] Model is always queried through `$workspace->relation()`, never globally
- [ ] A Policy exists and is registered in `AuthServiceProvider`
- [ ] All Controller methods call `$this->authorize()`
