<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Document;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    /**
     * Display listing of documents.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Document::with(['uploader', 'documentable']);

        // Sensitive documents check: normal agents can only see public documents or their own
        if ($user->isAgent()) {
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                  ->orWhere('uploaded_by', $user->id);
            });
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('document_type', $request->input('type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        $documents = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => Document::count(),
            'agreements' => Document::where('document_type', 'agreement')->count(),
            'title_deeds' => Document::where('document_type', 'title_deed')->count(),
            'ownership_proofs' => Document::where('document_type', 'ownership_proof')->count(),
            'brochures' => Document::where('document_type', 'brochure')->count(),
        ];

        $properties = Property::select('id', 'title', 'property_code')->get();
        $customers = Customer::where('status', 'active')->select('id', 'name')->get();
        $bookings = Booking::select('id', 'booking_number')->get();
        $deals = Deal::select('id', 'title', 'deal_code')->get();

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents,
            'stats' => $stats,
            'properties' => $properties,
            'customers' => $customers,
            'bookings' => $bookings,
            'deals' => $deals,
            'filters' => $request->only(['type', 'search']),
        ]);
    }

    /**
     * Store uploaded document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|in:ownership_proof,title_deed,agreement,identity_proof,floor_plan,payment_receipt,brochure,other',
            'documentable_type' => 'required|in:Property,Customer,Booking,Deal',
            'documentable_id' => 'required|integer',
            'file' => 'nullable|file|max:20480', // max 20MB
            'file_url' => 'nullable|string',
            'is_private' => 'boolean',
        ]);

        $filePath = '';
        $fileSize = null;
        $fileExt = 'pdf';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('documents', 'public');
            $fileSize = $file->getSize();
            $fileExt = $file->getClientOriginalExtension();
        } elseif (!empty($validated['file_url'])) {
            $filePath = $validated['file_url'];
            $fileExt = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'pdf';
        } else {
            return back()->with('error', 'Please provide a file to upload or valid file URL.');
        }

        $modelClass = match ($validated['documentable_type']) {
            'Property' => Property::class,
            'Customer' => Customer::class,
            'Booking' => Booking::class,
            'Deal' => Deal::class,
            default => Property::class,
        };

        $document = Document::create([
            'tenant_id' => $request->user()->tenant_id,
            'documentable_type' => $modelClass,
            'documentable_id' => $validated['documentable_id'],
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'file_extension' => strtolower($fileExt),
            'is_private' => $validated['is_private'] ?? false,
            'uploaded_by' => $request->user()->id,
        ]);

        AuditLog::record("Document '{$document->title}' Uploaded", $document);

        return back()->with('success', "Document '{$document->title}' uploaded successfully.");
    }

    /**
     * Delete document.
     */
    public function destroy(int $id)
    {
        $doc = Document::findOrFail($id);
        if (Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();

        return back()->with('success', 'Document removed.');
    }
}
