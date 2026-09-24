<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';
import {
    BookmarkCheck,
    Plus,
    Search,
    ShieldCheck,
    DollarSign,
    CheckCircle2,
    XCircle,
    Calendar,
    Building2,
    User,
    Clock,
    AlertCircle,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    bookings: Object,
    stats: Object,
    agents: Array,
    availableProperties: Array,
    customers: Array,
    deals: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || 'all');
const selectedPaymentStatus = ref(props.filters?.payment_status || 'all');

const handleFilter = () => {
    router.get(route('admin.bookings.index'), {
        status: selectedStatus.value,
        payment_status: selectedPaymentStatus.value,
        search: search.value,
    }, { preserveState: true });
};

// Reserve Property Modal
const isCreateOpen = ref(false);
const form = useForm({
    property_id: '',
    customer_id: '',
    agent_id: '',
    deal_id: '',
    booking_date: new Date().toISOString().split('T')[0],
    expiry_date: '',
    total_amount: '',
    booking_amount: '',
    record_initial_payment: true,
    payment_method: 'bank_transfer',
    transaction_reference: '',
    notes: '',
});

const onPropertySelected = () => {
    const prop = props.availableProperties.find(p => p.id === form.property_id);
    if (prop) {
        form.total_amount = prop.price;
        // default 10% token
        form.booking_amount = Math.round(prop.price * 0.1);
    }
};

const openCreateModal = () => {
    form.reset();
    form.booking_date = new Date().toISOString().split('T')[0];
    isCreateOpen.value = true;
};

const submitCreate = () => {
    form.post(route('admin.bookings.store'), {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        }
    });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'confirmed': return { variant: 'success', label: 'Confirmed' };
        case 'pending': return { variant: 'warning', label: 'Pending' };
        case 'converted': return { variant: 'primary', label: 'Converted (Sold)' };
        case 'cancelled': return { variant: 'danger', label: 'Cancelled' };
        default: return { variant: 'secondary', label: status };
    }
};

const getPaymentBadge = (status) => {
    switch (status) {
        case 'paid': return { variant: 'success', label: 'Paid in Full' };
        case 'partially_paid': return { variant: 'warning', label: 'Partially Paid' };
        case 'unpaid': return { variant: 'danger', label: 'Unpaid' };
        default: return { variant: 'secondary', label: status };
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Bookings & Reservations" />

        <div class="space-y-6">
            <!-- Header -->
            <AdminPageHeader
                title="Bookings & Reservations"
                description="Manage reserved properties, booking token payments, and purchase agreements."
            >
                <template #actions>
                    <AppButton variant="primary" @click="openCreateModal">
                        <Plus class="w-4 h-4 mr-2" />
                        Reserve Property
                    </AppButton>
                </template>
            </AdminPageHeader>

            <!-- Metrics Overview -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <AppCard class="p-4 border-l-4 border-primary-500">
                    <span class="text-xs text-slate-500 font-medium">Total Bookings</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.total }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ stats.confirmed }} active reservations</p>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-emerald-500">
                    <span class="text-xs text-slate-500 font-medium">Total Booking Volume</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ formatCurrency(stats.total_volume) }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Value of confirmed bookings</p>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-indigo-500">
                    <span class="text-xs text-slate-500 font-medium">Total Collected</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ formatCurrency(stats.total_collected) }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Token and installment payments</p>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-purple-500">
                    <span class="text-xs text-slate-500 font-medium">Converted to Closed</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.converted }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Final deed executions</p>
                </AppCard>
            </div>

            <!-- Double-Booking Prevention Banner -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-100 text-blue-700 rounded-lg">
                        <ShieldCheck class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Anti-Conflict Protection Active</h4>
                        <p class="text-xs text-blue-700">Atomic database transaction locking prevents duplicate reservations for the same property simultaneously.</p>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <AppCard class="p-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model="selectedStatus"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Reservation Statuses</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="converted">Converted (Sold)</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

                        <select
                            v-model="selectedPaymentStatus"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Payment Statuses</option>
                            <option value="paid">Fully Paid</option>
                            <option value="partially_paid">Partially Paid</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>

                    <div class="relative max-w-xs w-full">
                        <Search class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            @keyup.enter="handleFilter"
                            type="text"
                            placeholder="Search booking #, client, property..."
                            class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>
            </AppCard>

            <!-- Bookings Table -->
            <AppCard>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] font-semibold uppercase text-slate-500 tracking-wider">
                                <th class="py-3 px-4">Booking Number</th>
                                <th class="py-3 px-4">Property</th>
                                <th class="py-3 px-4">Customer</th>
                                <th class="py-3 px-4">Financials</th>
                                <th class="py-3 px-4">Reservation Status</th>
                                <th class="py-3 px-4">Payment Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="b in bookings.data"
                                :key="b.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="py-3 px-4">
                                    <Link :href="route('admin.bookings.show', b.id)" class="font-mono font-bold text-xs text-primary-600 hover:text-primary-700 block">
                                        {{ b.booking_number }}
                                    </Link>
                                    <span class="text-xs text-slate-400">{{ formatDate(b.booking_date) }}</span>
                                </td>

                                <td class="py-3 px-4">
                                    <div v-if="b.property" class="space-y-0.5 max-w-xs">
                                        <Link :href="route('admin.properties.show', b.property.id)" class="font-semibold text-slate-900 hover:text-primary-600 truncate block">
                                            {{ b.property.title }}
                                        </Link>
                                        <span class="text-xs font-mono text-slate-400">{{ b.property.property_code }}</span>
                                    </div>
                                </td>

                                <td class="py-3 px-4">
                                    <div v-if="b.customer">
                                        <Link :href="route('admin.customers.show', b.customer.id)" class="font-semibold text-slate-900 hover:text-primary-600 block">
                                            {{ b.customer.name }}
                                        </Link>
                                        <span class="text-xs text-slate-500">{{ b.customer.phone }}</span>
                                    </div>
                                </td>

                                <td class="py-3 px-4">
                                    <div class="space-y-0.5 text-xs">
                                        <div class="font-bold text-slate-900">Total: {{ formatCurrency(b.total_amount) }}</div>
                                        <div class="text-emerald-600">Paid: {{ formatCurrency(b.paid_amount) }}</div>
                                        <div v-if="b.balance_amount > 0" class="text-slate-500">Due: {{ formatCurrency(b.balance_amount) }}</div>
                                    </div>
                                </td>

                                <td class="py-3 px-4">
                                    <AppBadge :variant="getStatusBadge(b.status).variant" size="sm">
                                        {{ getStatusBadge(b.status).label }}
                                    </AppBadge>
                                </td>

                                <td class="py-3 px-4">
                                    <AppBadge :variant="getPaymentBadge(b.payment_status).variant" size="sm">
                                        {{ getPaymentBadge(b.payment_status).label }}
                                    </AppBadge>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <Link
                                        :href="route('admin.bookings.show', b.id)"
                                        class="inline-flex items-center text-xs font-semibold text-primary-600 hover:text-primary-700 bg-primary-50 px-2.5 py-1 rounded-md transition"
                                    >
                                        Details
                                        <ArrowRight class="w-3.5 h-3.5 ml-1" />
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="bookings.data.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                                    No bookings found matching filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="bookings.data.length > 0" class="p-4 border-t border-slate-100">
                    <DataTablePagination :pagination="bookings" />
                </div>
            </AppCard>
        </div>

        <!-- Reserve Property Modal -->
        <AppModal :show="isCreateOpen" @close="isCreateOpen = false" max-width="lg">
            <template #title>
                <div class="flex items-center space-x-2">
                    <BookmarkCheck class="w-5 h-5 text-primary-600" />
                    <span>Create Property Reservation</span>
                </div>
            </template>

            <form @submit.prevent="submitCreate" class="space-y-4">
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                    <strong class="font-bold">Reservation Lock:</strong> Saving this reservation will immediately change the property status to <span class="font-bold text-amber-900">Reserved</span> and lock it against other reservations.
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Select Property to Reserve *</label>
                    <select
                        v-model="form.property_id"
                        @change="onPropertySelected"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option value="">Choose Available Property</option>
                        <option v-for="p in availableProperties" :key="p.id" :value="p.id">
                            {{ p.property_code }} - {{ p.title }} (${{ Number(p.price).toLocaleString() }})
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Customer / Buyer *</label>
                        <select
                            v-model="form.customer_id"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="">Select Customer</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Closing Agent *</label>
                        <select
                            v-model="form.agent_id"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="">Select Agent</option>
                            <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Total Agreed Value ($) *</label>
                        <input
                            v-model="form.total_amount"
                            type="number"
                            step="0.01"
                            placeholder="500000"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Booking Token Amount ($) *</label>
                        <input
                            v-model="form.booking_amount"
                            type="number"
                            step="0.01"
                            placeholder="50000"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Booking Date *</label>
                        <input
                            v-model="form.booking_date"
                            type="date"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Reservation Expiry Date</label>
                        <input
                            v-model="form.expiry_date"
                            type="date"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>

                <!-- Instant token payment check -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                    <label class="flex items-center space-x-2 text-xs font-bold text-slate-800 cursor-pointer">
                        <input
                            v-model="form.record_initial_payment"
                            type="checkbox"
                            class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                        />
                        <span>Record initial token payment immediately & generate receipt</span>
                    </label>

                    <div v-if="form.record_initial_payment" class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Payment Method</label>
                            <select
                                v-model="form.payment_method"
                                class="w-full text-xs rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="bank_transfer">Bank Transfer / Wire</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI / Instant Pay</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase text-slate-500 mb-1">Transaction Ref / Cheque #</label>
                            <input
                                v-model="form.transaction_reference"
                                type="text"
                                placeholder="Ref #..."
                                class="w-full text-xs rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Notes / Terms</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Payment milestones, handover timeline..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isCreateOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="form.processing">Confirm Reservation</AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
