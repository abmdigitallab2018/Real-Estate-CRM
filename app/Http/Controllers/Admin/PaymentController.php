<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request): Response
    {
        $query = Payment::with(['booking.property', 'customer', 'deal']);

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('payment_type', $request->input('type'));
        }

        if ($request->filled('method') && $request->input('method') !== 'all') {
            $query->where('payment_method', $request->input('method'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_reference', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhere('transaction_reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        $stats = [
            'total_collected' => (float) Payment::where('status', 'completed')->sum('amount'),
            'booking_tokens' => (float) Payment::where('status', 'completed')->where('payment_type', 'booking_token')->sum('amount'),
            'installments' => (float) Payment::where('status', 'completed')->where('payment_type', 'installment')->sum('amount'),
            'brokerage' => (float) Payment::where('status', 'completed')->where('payment_type', 'brokerage_fee')->sum('amount'),
        ];

        $customers = Customer::where('status', 'active')->select('id', 'name')->get();
        $bookings = Booking::whereIn('status', ['confirmed', 'converted', 'pending'])->select('id', 'booking_number', 'total_amount', 'balance_amount')->get();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'stats' => $stats,
            'customers' => $customers,
            'bookings' => $bookings,
            'filters' => $request->only(['type', 'method', 'search']),
        ]);
    }

    /**
     * Record a new payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'deal_id' => 'nullable|exists:deals,id',
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'required|in:booking_token,installment,full_payment,brokerage_fee,security_deposit,refund',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,card,upi,online',
            'transaction_reference' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $payCount = Payment::count() + 1;
            $payRef = 'PAY-' . date('Y') . '-' . str_pad((string) $payCount, 4, '0', STR_PAD_LEFT);
            $receiptNum = 'RCP-' . date('Y') . '-' . str_pad((string) $payCount, 4, '0', STR_PAD_LEFT);

            $payment = Payment::create([
                'tenant_id' => $request->user()->tenant_id,
                'customer_id' => $validated['customer_id'],
                'booking_id' => $validated['booking_id'] ?? null,
                'deal_id' => $validated['deal_id'] ?? null,
                'payment_reference' => $payRef,
                'receipt_number' => $receiptNum,
                'amount' => $validated['amount'],
                'payment_type' => $validated['payment_type'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'payment_date' => $validated['payment_date'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            // If attached to booking, update booking balance
            if (!empty($validated['booking_id'])) {
                $booking = Booking::lockForUpdate()->find($validated['booking_id']);
                if ($booking) {
                    $newPaid = (float) $booking->paid_amount + (float) $validated['amount'];
                    $newBalance = max(0, (float) $booking->total_amount - $newPaid);
                    $newStatus = $newPaid >= (float) $booking->total_amount ? 'paid' : 'partially_paid';

                    $booking->update([
                        'paid_amount' => $newPaid,
                        'balance_amount' => $newBalance,
                        'payment_status' => $newStatus,
                    ]);
                }
            }

            AuditLog::record("Payment {$payment->payment_reference} Recorded for \${$payment->amount}", $payment);

            return back()->with('success', "Payment {$payment->payment_reference} for \${$payment->amount} recorded successfully. Receipt #{$payment->receipt_number} generated.");
        });
    }

    /**
     * View/print payment receipt.
     */
    public function receipt(int $id): Response
    {
        $payment = Payment::with([
            'customer',
            'booking.property',
            'booking.agent',
            'deal',
            'tenant',
        ])->findOrFail($id);

        return Inertia::render('Admin/Payments/Receipt', [
            'payment' => $payment,
        ]);
    }
}
