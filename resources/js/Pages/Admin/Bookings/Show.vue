<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import {
    ArrowLeft,
    Building2,
    User,
    DollarSign,
    CheckCircle2,
    XCircle,
    Calendar,
    CreditCard,
    FileText,
    Receipt,
    AlertTriangle,
    ShieldCheck,
    Phone,
    Mail,
    Printer
} from 'lucide-vue-next';

const props = defineProps({
    booking: Object,
});

// Cancel Modal
const isCancelOpen = ref(false);
const cancelForm = useForm({
    cancellation_reason: '',
    refund_amount: 0,
});

const openCancelModal = () => {
    cancelForm.cancellation_reason = '';
    cancelForm.refund_amount = 0;
    isCancelOpen.value = true;
};

const submitCancel = () => {
    cancelForm.post(route('admin.bookings.cancel', props.booking.id), {
        onSuccess: () => {
            isCancelOpen.value = false;
        }
    });
};

// Convert to Final Sale
const convertToSale = () => {
    if (confirm('Convert this booking to a completed sale/lease? This will mark the property as Sold/Rented.')) {
        router.post(route('admin.bookings.convert', props.booking.id));
    }
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

const progressPercentage = Math.min(100, Math.round(((props.booking.paid_amount || 0) / (props.booking.total_amount || 1)) * 100));
</script>

<template>
    <AdminLayout>
        <Head :title="`Booking - ${booking.booking_number}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('admin.bookings.index')"
                        class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition text-slate-600"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-mono font-bold text-primary-600 bg-primary-50 px-2.5 py-0.5 rounded">
                                {{ booking.booking_number }}
                            </span>
                            <AppBadge :variant="booking.status === 'confirmed' ? 'success' : (booking.status === 'converted' ? 'primary' : 'danger')" size="sm" class="capitalize">
                                {{ booking.status }}
                            </AppBadge>
                            <AppBadge :variant="booking.payment_status === 'paid' ? 'success' : 'warning'" size="sm" class="capitalize">
                                {{ booking.payment_status }}
                            </AppBadge>
                        </div>
                        <h1 class="text-xl font-bold text-slate-900 mt-1">Property Reservation Details</h1>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <AppButton
                        v-if="booking.status === 'confirmed'"
                        variant="danger"
                        size="sm"
                        @click="openCancelModal"
                    >
                        <XCircle class="w-4 h-4 mr-1.5" />
                        Cancel Reservation
                    </AppButton>

                    <AppButton
                        v-if="booking.status === 'confirmed'"
                        variant="primary"
                        size="sm"
                        @click="convertToSale"
                    >
                        <CheckCircle2 class="w-4 h-4 mr-1.5" />
                        Convert to Final Sale
                    </AppButton>
                </div>
            </div>

            <!-- Financial Progress Card -->
            <AppCard class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase">Total Transaction Value</span>
                        <div class="text-2xl font-bold text-slate-900 mt-1">{{ formatCurrency(booking.total_amount) }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase">Booking Deposit</span>
                        <div class="text-2xl font-bold text-slate-900 mt-1">{{ formatCurrency(booking.booking_amount) }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase">Total Collected</span>
                        <div class="text-2xl font-bold text-emerald-600 mt-1">{{ formatCurrency(booking.paid_amount) }}</div>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-semibold uppercase">Outstanding Balance</span>
                        <div class="text-2xl font-bold text-amber-600 mt-1">{{ formatCurrency(booking.balance_amount) }}</div>
                    </div>
                </div>

                <!-- Collection Progress Bar -->
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <span class="font-medium text-slate-700">Payment Collection Progress</span>
                        <span class="font-bold text-slate-900">{{ progressPercentage }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-300"
                            :class="progressPercentage >= 100 ? 'bg-emerald-500' : 'bg-primary-500'"
                            :style="{ width: `${progressPercentage}%` }"
                        ></div>
                    </div>
                </div>
            </AppCard>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Property and Customer (2 cols) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Property Details -->
                    <AppCard class="p-6">
                        <h3 class="text-sm font-semibold uppercase text-slate-500 tracking-wider mb-4">
                            Reserved Property
                        </h3>
                        <div v-if="booking.property" class="flex flex-col sm:flex-row gap-5">
                            <div class="w-full sm:w-48 h-32 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0 border border-slate-200">
                                <img
                                    v-if="booking.property.images?.length > 0"
                                    :src="booking.property.images[0].image_url"
                                    :alt="booking.property.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                    <Building2 class="w-8 h-8" />
                                </div>
                            </div>
                            <div class="space-y-2 flex-1">
                                <Link
                                    :href="route('admin.properties.show', booking.property.id)"
                                    class="text-base font-bold text-slate-900 hover:text-primary-600 block"
                                >
                                    {{ booking.property.title }}
                                </Link>
                                <p class="text-xs text-slate-500">{{ booking.property.address }}, {{ booking.property.city }}</p>
                                <div class="flex items-center space-x-3 text-xs pt-2">
                                    <span class="font-mono font-medium text-slate-500">{{ booking.property.property_code }}</span>
                                    <AppBadge size="sm" variant="primary" class="capitalize">{{ booking.property.property_type }}</AppBadge>
                                    <span class="font-bold text-slate-900">{{ formatCurrency(booking.property.price) }}</span>
                                </div>
                            </div>
                        </div>
                    </AppCard>

                    <!-- Payments Ledger -->
                    <AppCard class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold uppercase text-slate-500 tracking-wider">
                                Payments & Receipts Ledger
                            </h3>
                            <Link
                                :href="route('admin.payments.index')"
                                class="text-xs font-semibold text-primary-600 hover:text-primary-700"
                            >
                                + Record Payment
                            </Link>
                        </div>

                        <div v-if="booking.payments?.length > 0" class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 text-slate-400 uppercase font-semibold">
                                        <th class="py-2.5 px-3">Receipt #</th>
                                        <th class="py-2.5 px-3">Date</th>
                                        <th class="py-2.5 px-3">Type</th>
                                        <th class="py-2.5 px-3">Method</th>
                                        <th class="py-2.5 px-3">Amount</th>
                                        <th class="py-2.5 px-3 text-right">Receipt</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="pay in booking.payments" :key="pay.id">
                                        <td class="py-2.5 px-3 font-mono font-bold text-slate-800">{{ pay.receipt_number }}</td>
                                        <td class="py-2.5 px-3 text-slate-500">{{ formatDate(pay.payment_date) }}</td>
                                        <td class="py-2.5 px-3 capitalize">{{ pay.payment_type.replace('_', ' ') }}</td>
                                        <td class="py-2.5 px-3 capitalize">{{ pay.payment_method.replace('_', ' ') }}</td>
                                        <td class="py-2.5 px-3 font-bold text-emerald-600">{{ formatCurrency(pay.amount) }}</td>
                                        <td class="py-2.5 px-3 text-right">
                                            <Link
                                                :href="route('admin.payments.receipt', pay.id)"
                                                class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium"
                                            >
                                                <Printer class="w-3.5 h-3.5 mr-1" />
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-xs text-slate-400 text-center py-6">
                            No payments recorded against this reservation yet.
                        </div>
                    </AppCard>

                    <!-- Notes & Terms -->
                    <AppCard class="p-6">
                        <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">Booking Agreement Notes</h4>
                        <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100 whitespace-pre-wrap">
                            {{ booking.notes || 'No terms or special conditions recorded.' }}
                        </p>
                    </AppCard>
                </div>

                <!-- Right Column: Buyer, Agent & Expiry info -->
                <div class="space-y-6">
                    <!-- Customer Profile -->
                    <AppCard class="p-5">
                        <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-3">Customer / Buyer</h4>
                        <div v-if="booking.customer" class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                                    {{ booking.customer.name.substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <Link :href="route('admin.customers.show', booking.customer.id)" class="font-bold text-slate-900 text-sm hover:text-primary-600">
                                        {{ booking.customer.name }}
                                    </Link>
                                    <p class="text-xs text-slate-500 capitalize">{{ booking.customer.customer_type }}</p>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                                <div class="flex items-center space-x-2">
                                    <Phone class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ booking.customer.phone || 'N/A' }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Mail class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ booking.customer.email || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </AppCard>

                    <!-- Assigned Agent -->
                    <AppCard class="p-5">
                        <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-3">Closing Agent</h4>
                        <div v-if="booking.agent" class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">
                                {{ booking.agent.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ booking.agent.name }}</p>
                                <p class="text-xs text-slate-500">{{ booking.agent.email }}</p>
                            </div>
                        </div>
                    </AppCard>

                    <!-- Timeline & Expiry -->
                    <AppCard class="p-5 space-y-3">
                        <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">Dates & Timeline</h4>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Booking Date</span>
                            <span class="font-bold text-slate-800">{{ formatDate(booking.booking_date) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Reservation Expiry</span>
                            <span class="font-bold text-slate-800">{{ formatDate(booking.expiry_date) }}</span>
                        </div>
                    </AppCard>

                    <!-- Cancelled details if applicable -->
                    <AppCard v-if="booking.status === 'cancelled'" class="p-5 bg-red-50 border-red-200">
                        <span class="text-xs font-bold uppercase text-red-800 block mb-1">Reservation Cancelled</span>
                        <p class="text-xs text-red-700">{{ booking.cancellation_reason }}</p>
                        <p v-if="booking.refund_amount > 0" class="text-xs text-red-800 font-bold mt-2">
                            Refund Issued: {{ formatCurrency(booking.refund_amount) }}
                        </p>
                    </AppCard>
                </div>
            </div>
        </div>

        <!-- Cancel Booking Modal -->
        <AppModal :show="isCancelOpen" @close="isCancelOpen = false" max-width="md">
            <template #title>
                <div class="flex items-center space-x-2 text-red-600">
                    <AlertTriangle class="w-5 h-5" />
                    <span>Cancel Property Reservation</span>
                </div>
            </template>

            <form @submit.prevent="submitCancel" class="space-y-4">
                <p class="text-xs text-slate-600">
                    Cancelling this booking will release property <strong class="text-slate-900">{{ booking.property?.title }}</strong> back to <em>Available</em> status so other clients can book it.
                </p>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Cancellation Reason *</label>
                    <textarea
                        v-model="cancelForm.cancellation_reason"
                        rows="3"
                        placeholder="Why is the client cancelling? (e.g. Loan rejection, decided against property)..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-red-500 focus:ring-red-500"
                        required
                    ></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Refund Amount ($)</label>
                    <input
                        v-model="cancelForm.refund_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-red-500 focus:ring-red-500"
                    />
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isCancelOpen = false">Dismiss</AppButton>
                    <AppButton variant="danger" type="submit" :loading="cancelForm.processing">Confirm Cancellation</AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
