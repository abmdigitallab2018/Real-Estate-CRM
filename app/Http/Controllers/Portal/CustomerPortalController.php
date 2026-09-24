<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\PropertyMatch;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerPortalController extends Controller
{
    /**
     * Customer portal dashboard overview.
     */
    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        $customer = Customer::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$customer) {
            // create linked customer record if none exists
            $customer = Customer::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?: 'N/A',
                'customer_type' => 'buyer',
                'status' => 'active',
            ]);
        }

        // Shortlisted Properties
        $shortlisted = PropertyMatch::with('property.images')
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['shortlisted', 'suggested'])
            ->get();

        // Scheduled Site Visits
        $visits = SiteVisit::with('property.images', 'assignedAgent')
            ->where('customer_id', $customer->id)
            ->orderBy('scheduled_date', 'desc')
            ->get();

        // Active Bookings & Reservations
        $bookings = Booking::with('property.images', 'payments')
            ->where('customer_id', $customer->id)
            ->get();

        // Invoices & Payments
        $payments = Payment::with('booking.property')
            ->where('customer_id', $customer->id)
            ->latest('payment_date')
            ->get();

        // Documents available to customer (only non-private documents)
        $documents = Document::where('documentable_type', \App\Models\Customer::class)
            ->where('documentable_id', $customer->id)
            ->where('is_private', false)
            ->get();

        return Inertia::render('Portal/Dashboard', [
            'customer' => $customer,
            'shortlisted' => $shortlisted,
            'visits' => $visits,
            'bookings' => $bookings,
            'payments' => $payments,
            'documents' => $documents,
        ]);
    }
}
