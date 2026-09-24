<?php

use App\Http\Controllers\Admin\AgencySettingController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyMatchingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SiteVisitController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Portal\CustomerPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPropertyController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPropertyController::class, 'index'])->name('home');
Route::get('/properties', [PublicPropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{slug}', [PublicPropertyController::class, 'show'])->name('properties.show');
Route::post('/properties/inquiry', [PublicPropertyController::class, 'submitInquiry'])->name('properties.inquiry');
Route::post('/properties/schedule-visit', [PublicPropertyController::class, 'scheduleVisit'])->name('properties.schedule-visit');
Route::get('/pricing', [PublicPropertyController::class, 'pricing'])->name('pricing');

/*
|--------------------------------------------------------------------------
| Dashboard Central Dispatcher
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'super_admin' && empty($user->tenant_id)) {
        return redirect()->route('superadmin.dashboard');
    }
    if ($user->role === 'customer') {
        return redirect()->route('portal.dashboard');
    }
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Super Admin SaaS Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/tenants', [SuperAdminController::class, 'tenants'])->name('tenants');
    Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('tenants.store');
    Route::post('/tenants/{id}/toggle', [SuperAdminController::class, 'toggleStatus'])->name('tenants.toggle');
    Route::get('/plans', [SuperAdminController::class, 'plans'])->name('plans');
    Route::post('/plans', [SuperAdminController::class, 'storePlan'])->name('plans.store');
});

/*
|--------------------------------------------------------------------------
| Customer / Buyer Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Agency CRM Back-Office Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 1. Property Management
    Route::resource('properties', PropertyController::class);
    Route::post('properties/{id}/status', [PropertyController::class, 'updateStatus'])->name('properties.status');

    // 2. Leads Management
    Route::resource('leads', LeadController::class);
    Route::post('leads/{id}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('leads/{id}/convert', [LeadController::class, 'convert'])->name('leads.convert');

    // 3. Customers Management
    Route::resource('customers', CustomerController::class);

    // 4. Property Matching Engine
    Route::get('matching', [PropertyMatchingController::class, 'index'])->name('matching.index');
    Route::post('matching/shortlist', [PropertyMatchingController::class, 'shortlist'])->name('matching.shortlist');
    Route::post('matching/share', [PropertyMatchingController::class, 'share'])->name('matching.share');
    Route::post('matching/feedback', [PropertyMatchingController::class, 'recordFeedback'])->name('matching.feedback');

    // 5. Sales Pipeline & Deals
    Route::resource('deals', DealController::class)->except(['create', 'edit']);
    Route::post('deals/{id}/stage', [DealController::class, 'updateStage'])->name('deals.stage');

    // 6. Site Visits
    Route::get('site-visits', [SiteVisitController::class, 'index'])->name('site-visits.index');
    Route::post('site-visits', [SiteVisitController::class, 'store'])->name('site-visits.store');
    Route::post('site-visits/{id}/status', [SiteVisitController::class, 'updateStatus'])->name('site-visits.status');
    Route::post('site-visits/{id}/reschedule', [SiteVisitController::class, 'reschedule'])->name('site-visits.reschedule');
    Route::delete('site-visits/{id}', [SiteVisitController::class, 'destroy'])->name('site-visits.destroy');

    // 7. Tasks & Activities
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('tasks/{id}/toggle', [TaskController::class, 'toggleComplete'])->name('tasks.toggle');
    Route::delete('tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // 8. Bookings & Reservations
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('bookings/{id}/convert', [BookingController::class, 'convert'])->name('bookings.convert');

    // 9. Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{id}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // 10. Commissions
    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::post('commissions', [CommissionController::class, 'store'])->name('commissions.store');
    Route::post('commissions/{id}/approve', [CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('commissions/{id}/pay', [CommissionController::class, 'markPaid'])->name('commissions.pay');
    Route::post('commissions/rules', [CommissionController::class, 'storeRule'])->name('commissions.rules.store');

    // 11. Team & Agents
    Route::get('agents', [AgentController::class, 'index'])->name('agents.index');
    Route::post('agents', [AgentController::class, 'store'])->name('agents.store');
    Route::put('agents/{id}', [AgentController::class, 'update'])->name('agents.update');
    Route::delete('agents/{id}', [AgentController::class, 'destroy'])->name('agents.destroy');

    // 12. Branches
    Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
    Route::put('branches/{id}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('branches/{id}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // 13. Documents
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // 14. Reports & Analytics
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

    // 15. Agency Settings
    Route::get('settings', [AgencySettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AgencySettingController::class, 'update'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
