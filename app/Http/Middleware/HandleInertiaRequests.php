<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                    'specialization' => $user->specialization,
                    'commission_rate' => $user->commission_rate,
                    'target_amount' => $user->target_amount,
                    'tenant' => $user->tenant ? [
                        'id' => $user->tenant->id,
                        'name' => $user->tenant->name,
                        'slug' => $user->tenant->slug,
                        'currency' => $user->tenant->currency,
                        'currency_symbol' => $user->tenant->currency_symbol,
                        'status' => $user->tenant->status,
                        'settings' => $user->tenant->settings,
                    ] : null,
                    'branch' => $user->branch ? [
                        'id' => $user->branch->id,
                        'name' => $user->branch->name,
                        'code' => $user->branch->code,
                    ] : null,
                ] : null,
            ],
            'currentTenant' => fn () => $user && $user->tenant ? [
                'id' => $user->tenant->id,
                'name' => $user->tenant->name,
                'slug' => $user->tenant->slug,
                'currency' => $user->tenant->currency,
                'currency_symbol' => $user->tenant->currency_symbol,
                'phone' => $user->tenant->phone,
                'email' => $user->tenant->email,
                'address' => $user->tenant->address,
                'city' => $user->tenant->city,
                'status' => $user->tenant->status,
                'settings' => $user->tenant->settings,
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
