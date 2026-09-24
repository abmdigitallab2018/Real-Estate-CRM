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
    DollarSign,
    CreditCard,
    Plus,
    Search,
    Printer,
    Receipt,
    Calendar,
    User,
    CheckCircle2,
    FileSpreadsheet
} from 'lucide-vue-next';

const props = defineProps({
    payments: Object,
    stats: Object,
    customers: Array,
    bookings: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const selectedType = ref(props.filters?.type || 'all');
const selectedMethod = ref(props.filters?.method || 'all');

const handleFilter = () => {
    router.get(route('admin.payments.index'), {
        type: selectedType.value,
        method: selectedMethod.value,
        search: search.value,
    }, { preserveState: true });
};

// Record Payment Modal
const isCreateOpen = ref(false);
const form = useForm({
    customer_id: '',
    booking_id: '',
    amount: '',
    payment_type: 'installment',
    payment_method: 'bank_transfer',
    transaction_reference: '',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const openCreateModal = () => {
    form.reset();
    form.payment_date = new Date().toISOString().split('T')[0];
    isCreateOpen.value = true;
};

const submitCreate = () => {
    form.post(route('admin.payments.store'), {
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

const getTypeBadge = (type) => {
    switch (type) {
        case 'booking_token': return { variant: 'primary', label: 'Token Deposit' };
        case 'installment': return { variant: 'info', label: 'Installment' };
        case 'full_payment': return { variant: 'success', label: 'Full Payment' };
        case 'brokerage_fee': return { variant: 'warning', label: 'Brokerage Fee' };
        case 'refund': return { variant: 'danger', label: 'Refund' };
        default: return { variant: 'secondary', label: type };
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Payments & Collections Ledger" />

        <div class="space-y-6">
            <!-- Header -->
            <AdminPageHeader
                title="Payment Collections Ledger"
                description="Record client payments, booking tokens, milestone installments, and generate printable receipts."
            >
                <template #actions>
                    <AppButton variant="primary" @click="openCreateModal">
                        <Plus class="w-4 h-4 mr-2" />
                        Record Payment
                    </AppButton>
                </template>
            </AdminPageHeader>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <AppCard class="p-4 border-l-4 border-emerald-500">
                    <span class="text-xs text-slate-500 font-medium">Total Collected</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ formatCurrency(stats.total_collected) }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">All verified payments</p>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-primary-500">
                    <span class="text-xs text-slate-500 font-medium">Booking Tokens</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ formatCurrency(stats.booking_tokens) }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Initial reservation deposits</p>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-indigo-500">
                    <span class="text-xs text-slate-500 font-medium">Installments</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ formatCurrency(stats.installments) }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Milestone collections</p>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-amber-500">
                    <span class="text-xs text-slate-500 font-medium">Brokerage Fees</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ formatCurrency(stats.brokerage) }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Agency commission income</p>
                </AppCard>
            </div>

            <!-- Filters Bar -->
            <AppCard class="p-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model="selectedType"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Payment Types</option>
                            <option value="booking_token">Booking Token</option>
                            <option value="installment">Installment</option>
                            <option value="full_payment">Full Payment</option>
                            <option value="brokerage_fee">Brokerage Fee</option>
                            <option value="refund">Refund</option>
                        </select>

                        <select
                            v-model="selectedMethod"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Methods</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / Instant</option>
                        </select>
                    </div>

                    <div class="relative max-w-xs w-full">
                        <Search class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            @keyup.enter="handleFilter"
                            type="text"
                            placeholder="Search receipt #, client, ref..."
                            class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>
            </AppCard>

            <!-- Payments Table -->
            <AppCard>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] font-semibold uppercase text-slate-500 tracking-wider">
                                <th class="py-3 px-4">Receipt # & Date</th>
                                <th class="py-3 px-4">Customer</th>
                                <th class="py-3 px-4">Booking / Reference</th>
                                <th class="py-3 px-4">Type</th>
                                <th class="py-3 px-4">Method & Transaction Ref</th>
                                <th class="py-3 px-4">Amount</th>
                                <th class="py-3 px-4 text-right">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="pay in payments.data"
                                :key="pay.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="py-3 px-4">
                                    <span class="font-mono font-bold text-xs text-slate-900 block">{{ pay.receipt_number }}</span>
                                    <span class="text-xs text-slate-400">{{ formatDate(pay.payment_date) }}</span>
                                </td>

                                <td class="py-3 px-4">
                                    <div v-if="pay.customer">
                                        <span class="font-semibold text-slate-900 block">{{ pay.customer.name }}</span>
                                        <span class="text-xs text-slate-500">{{ pay.payment_reference }}</span>
                                    </div>
                                    <span v-else class="text-xs text-slate-400">N/A</span>
                                </td>

                                <td class="py-3 px-4">
                                    <div v-if="pay.booking">
                                        <Link :href="route('admin.bookings.show', pay.booking.id)" class="text-xs font-mono font-semibold text-primary-600 hover:text-primary-700 block">
                                            {{ pay.booking.booking_number }}
                                        </Link>
                                        <span v-if="pay.booking.property" class="text-xs text-slate-500 truncate max-w-xs block">
                                            {{ pay.booking.property.title }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-slate-400">Direct Payment</span>
                                </td>

                                <td class="py-3 px-4">
                                    <AppBadge :variant="getTypeBadge(pay.payment_type).variant" size="sm">
                                        {{ getTypeBadge(pay.payment_type).label }}
                                    </AppBadge>
                                </td>

                                <td class="py-3 px-4">
                                    <span class="text-xs capitalize font-medium text-slate-800 block">{{ pay.payment_method.replace('_', ' ') }}</span>
                                    <span v-if="pay.transaction_reference" class="text-[11px] font-mono text-slate-400">{{ pay.transaction_reference }}</span>
                                </td>

                                <td class="py-3 px-4">
                                    <span class="font-bold text-emerald-600 text-sm">
                                        {{ formatCurrency(pay.amount) }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <Link
                                        :href="route('admin.payments.receipt', pay.id)"
                                        class="inline-flex items-center text-xs font-semibold text-primary-600 hover:text-primary-700 bg-primary-50 px-2.5 py-1 rounded-md transition"
                                    >
                                        <Printer class="w-3.5 h-3.5 mr-1" />
                                        Print
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="payments.data.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                                    No payment records found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="payments.data.length > 0" class="p-4 border-t border-slate-100">
                    <DataTablePagination :pagination="payments" />
                </div>
            </AppCard>
        </div>

        <!-- Record Payment Modal -->
        <AppModal :show="isCreateOpen" @close="isCreateOpen = false" max-width="md">
            <template #title>
                <div class="flex items-center space-x-2">
                    <Receipt class="w-5 h-5 text-primary-600" />
                    <span>Record Payment Collection</span>
                </div>
            </template>

            <form @submit.prevent="submitCreate" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Customer / Payer *</label>
                    <select
                        v-model="form.customer_id"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option value="">Select Customer</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Link to Booking (Optional)</label>
                    <select
                        v-model="form.booking_id"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">No Booking (General Payment)</option>
                        <option v-for="b in bookings" :key="b.id" :value="b.id">
                            {{ b.booking_number }} (Bal: ${{ Number(b.balance_amount).toLocaleString() }})
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Amount ($) *</label>
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="1"
                            placeholder="10000"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Type *</label>
                        <select
                            v-model="form.payment_type"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="installment">Installment</option>
                            <option value="booking_token">Booking Token</option>
                            <option value="full_payment">Full Payment</option>
                            <option value="brokerage_fee">Brokerage Fee</option>
                            <option value="security_deposit">Security Deposit</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Method *</label>
                        <select
                            v-model="form.payment_method"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / Instant Pay</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Payment Date *</label>
                        <input
                            v-model="form.payment_date"
                            type="date"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Transaction Ref / Cheque No.</label>
                    <input
                        v-model="form.transaction_reference"
                        type="text"
                        placeholder="e.g. TXN-94810294"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Notes</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Internal collection remarks..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isCreateOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="form.processing">Record Payment</AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
