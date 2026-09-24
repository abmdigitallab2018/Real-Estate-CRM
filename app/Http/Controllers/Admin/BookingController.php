<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings & reservations.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Booking::with(['property', 'customer', 'agent', 'deal', 'payments']);

        if ($user->isAgent()) {
            $query->where('agent_id', $user->id);
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status') && $request->input('payment_status') !== 'all') {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('property', fn ($pq) => $pq->where('title', 'like', "%{$search}%"));
            });
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'converted' => Booking::where('status', 'converted')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'total_volume' => (float) Booking::whereIn('status', ['confirmed', 'converted'])->sum('total_amount'),
            'total_collected' => (float) Booking::whereIn('status', ['confirmed', 'converted'])->sum('paid_amount'),
        ];

        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        // Allow choosing available properties
        $availableProperties = Property::whereIn('status', ['available', 'under_negotiation'])->select('id', 'title', 'property_code', 'price')->get();
        $customers = Customer::where('status', 'active')->select('id', 'name', 'phone')->get();
        $deals = Deal::whereNull('closed_at')->select('id', 'title', 'deal_code')->get();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'stats' => $stats,
            'agents' => $agents,
            'availableProperties' => $availableProperties,
            'customers' => $customers,
            'deals' => $deals,
            'filters' => $request->only(['status', 'payment_status', 'search']),
        ]);
    }

    /**
     * Store a new property reservation/booking.
     * Uses database transaction and checks for active reservations to prevent conflicts.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'customer_id' => 'required|exists:customers,id',
            'agent_id' => 'required|exists:users,id',
            'deal_id' => 'nullable|exists:deals,id',
            'booking_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:booking_date',
            'total_amount' => 'required|numeric|min:1',
            'booking_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            // Token payment optional immediately
            'record_initial_payment' => 'boolean',
            'payment_method' => 'nullable|in:cash,bank_transfer,cheque,card,upi,online',
            'transaction_reference' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $propertyId = $validated['property_id'];

            // 1. Conflict Prevention: Check if property has active reservation
            if (Booking::hasActiveReservation($propertyId)) {
                return back()->with('error', 'Conflict Alert: This property already has an active reservation or booking. You cannot create a duplicate reservation.');
            }

            // 2. Lock property row and verify status
            $property = Property::lockForUpdate()->findOrFail($propertyId);
            if (in_array($property->status, ['sold', 'rented', 'reserved'])) {
                return back()->with('error', "Property is currently in '{$property->status}' status and cannot be reserved.");
            }

            $count = Booking::count() + 1;
            $bookingNumber = 'BKG-' . date('Y') . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);

            $initialPaid = (!empty($validated['record_initial_payment']) && $validated['booking_amount'] > 0)
                ? (float) $validated['booking_amount']
                : 0.00;

            $balance = (float) $validated['total_amount'] - $initialPaid;
            $paymentStatus = $initialPaid >= (float) $validated['total_amount']
                ? 'paid'
                : ($initialPaid > 0 ? 'partially_paid' : 'unpaid');

            // 3. Create Booking Record
            $booking = Booking::create([
                'tenant_id' => $request->user()->tenant_id,
                'property_id' => $property->id,
                'customer_id' => $validated['customer_id'],
                'agent_id' => $validated['agent_id'],
                'deal_id' => $validated['deal_id'] ?? null,
                'booking_number' => $bookingNumber,
                'booking_date' => $validated['booking_date'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'total_amount' => $validated['total_amount'],
                'booking_amount' => $validated['booking_amount'],
                'paid_amount' => $initialPaid,
                'balance_amount' => $balance,
                'status' => 'confirmed',
                'payment_status' => $paymentStatus,
                'notes' => $validated['notes'] ?? null,
            ]);

            // 4. Update property status to 'reserved'
            $property->update(['status' => 'reserved']);

            // 5. If initial payment provided, record Payment
            if ($initialPaid > 0) {
                $payCount = Payment::count() + 1;
                $payRef = 'PAY-' . date('Y') . '-' . str_pad((string) $payCount, 4, '0', STR_PAD_LEFT);

                Payment::create([
                    'tenant_id' => $request->user()->tenant_id,
                    'booking_id' => $booking->id,
                    'deal_id' => $booking->deal_id,
                    'customer_id' => $booking->customer_id,
                    'payment_reference' => $payRef,
                    'amount' => $initialPaid,
                    'payment_type' => 'booking_token',
                    'payment_method' => $validated['payment_method'] ?? 'bank_transfer',
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                    'payment_date' => $validated['booking_date'],
                    'receipt_number' => 'RCP-' . date('Y') . '-' . str_pad((string) $payCount, 4, '0', STR_PAD_LEFT),
                    'status' => 'completed',
                    'notes' => 'Initial booking token payment recorded during reservation.',
                ]);
            }

            AuditLog::record("Booking {$booking->booking_number} Created & Property '{$property->property_code}' Reserved", $booking);

            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', "Property '{$property->title}' reserved successfully! Booking #{$booking->booking_number} confirmed.");
        });
    }

    /**
     * Show booking details.
     */
    public function show(int $id): Response
    {
        $booking = Booking::with([
            'property.images',
            'customer',
            'agent',
            'deal.stage',
            'payments',
            'commissions.agent',
        ])->findOrFail($id);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Cancel a booking and release property back to available.
     */
    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $id) {
            $booking = Booking::lockForUpdate()->findOrFail($id);

            if ($booking->status === 'cancelled') {
                return back()->with('error', 'Booking is already cancelled.');
            }

            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['cancellation_reason'],
                'refund_amount' => $validated['refund_amount'] ?? 0,
            ]);

            // Release property back to available
            $property = Property::find($booking->property_id);
            if ($property && $property->status === 'reserved') {
                $property->update(['status' => 'available']);
            }

            AuditLog::record("Booking {$booking->booking_number} Cancelled & Property Released", $booking);

            return back()->with('success', "Booking {$booking->booking_number} cancelled. Property is now available again.");
        });
    }

    /**
     * Convert booking to completed deal/sale.
     */
    public function convert(Request $request, int $id)
    {
        return DB::transaction(function () use ($id) {
            $booking = Booking::lockForUpdate()->findOrFail($id);

            $booking->update([
                'status' => 'converted',
                'payment_status' => 'paid',
                'balance_amount' => 0,
            ]);

            // Mark property as sold or rented
            $property = Property::find($booking->property_id);
            if ($property) {
                $property->update([
                    'status' => in_array($property->listing_purpose, ['rent', 'lease']) ? 'rented' : 'sold'
                ]);
            }

            // Close associated deal if present
            if ($booking->deal_id) {
                $deal = Deal::find($booking->deal_id);
                if ($deal) {
                    $wonStage = \App\Models\PipelineStage::where('pipeline_id', $deal->pipeline_id)->where('is_won', true)->first();
                    if ($wonStage) {
                        $deal->update([
                            'stage_id' => $wonStage->id,
                            'closed_at' => now(),
                            'actual_value' => $booking->total_amount,
                        ]);
                    }
                }
            }

            AuditLog::record("Booking {$booking->booking_number} Converted to Completed Transaction", $booking);

            return back()->with('success', "Booking {$booking->booking_number} converted! Property marked as {$property->status}.");
        });
    }
}
