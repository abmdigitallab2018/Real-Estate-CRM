<script setup>
import { ref, computed } from 'vue';
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
    Calendar,
    DollarSign,
    CheckCircle2,
    XCircle,
    Clock,
    FileText,
    Percent,
    CreditCard,
    MapPin,
    Phone,
    Mail,
    ChevronRight,
    Briefcase,
    TrendingUp,
    Bookmark
} from 'lucide-vue-next';

const props = defineProps({
    deal: Object,
});

// Stage advancement modal
const isStageModalOpen = ref(false);
const stageForm = useForm({
    stage_id: props.deal.stage_id,
    actual_value: props.deal.actual_value || props.deal.expected_value,
    lost_reason: props.deal.lost_reason || '',
});

const openStageModal = (stageId) => {
    stageForm.stage_id = stageId || props.deal.stage_id;
    isStageModalOpen.value = true;
};

const submitStageChange = () => {
    stageForm.post(route('admin.deals.stage', props.deal.id), {
        onSuccess: () => {
            isStageModalOpen.value = false;
        }
    });
};

const selectedTargetStage = computed(() => {
    return props.deal.pipeline?.stages?.find(s => s.id === stageForm.stage_id);
});

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
</script>

<template>
    <AdminLayout>
        <Head :title="`Deal - ${deal.title}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('admin.deals.index')"
                        class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition text-slate-600"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-mono font-bold text-primary-600 bg-primary-50 px-2 py-0.5 rounded">
                                {{ deal.deal_code }}
                            </span>
                            <AppBadge :variant="deal.deal_type === 'sale' ? 'primary' : 'warning'" size="sm" class="capitalize">
                                {{ deal.deal_type }}
                            </AppBadge>
                            <span
                                v-if="deal.stage?.is_won"
                                class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800"
                            >
                                <CheckCircle2 class="w-3.5 h-3.5 mr-1 text-emerald-600" /> Won
                            </span>
                            <span
                                v-else-if="deal.stage?.is_lost"
                                class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-800"
                            >
                                <XCircle class="w-3.5 h-3.5 mr-1 text-red-600" /> Lost
                            </span>
                        </div>
                        <h1 class="text-xl font-bold text-slate-900 mt-1">{{ deal.title }}</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <AppButton variant="secondary" @click="openStageModal(null)">
                        Change Stage
                    </AppButton>
                    <Link
                        v-if="deal.property_id && !deal.stage?.is_won && !deal.stage?.is_lost"
                        :href="route('admin.bookings.index')"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition"
                    >
                        <Bookmark class="w-4 h-4 mr-2" />
                        Create Booking
                    </Link>
                </div>
            </div>

            <!-- Pipeline Visual Stepper -->
            <AppCard class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-semibold uppercase text-slate-400">
                        Pipeline Progression: {{ deal.pipeline?.name }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">
                        Current Stage: <strong class="text-slate-800">{{ deal.stage?.name }}</strong>
                    </span>
                </div>

                <div class="flex items-center space-x-2 overflow-x-auto pb-2">
                    <div
                        v-for="(st, idx) in deal.pipeline?.stages"
                        :key="st.id"
                        @click="openStageModal(st.id)"
                        class="flex-1 min-w-[130px] p-2.5 rounded-xl border text-center cursor-pointer transition relative"
                        :class="[
                            st.id === deal.stage_id
                                ? 'bg-primary-50 border-primary-500 ring-2 ring-primary-500/20'
                                : (st.order < (deal.stage?.order || 0)
                                    ? 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                                    : 'bg-white border-slate-200 text-slate-400 hover:border-slate-300')
                        ]"
                    >
                        <div class="text-xs font-bold truncate">{{ st.name }}</div>
                        <div class="text-[11px] font-medium mt-0.5" :class="st.id === deal.stage_id ? 'text-primary-600' : 'text-slate-400'">
                            {{ st.probability }}%
                        </div>
                    </div>
                </div>
            </AppCard>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Financial & Negotiation Details (2 cols) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Deal Financial Snapshot -->
                    <AppCard class="p-6">
                        <h3 class="text-sm font-semibold uppercase text-slate-500 tracking-wider mb-4">
                            Financial Summary
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-xs text-slate-500">Expected Value</span>
                                <div class="text-xl font-bold text-slate-900 mt-1">
                                    {{ formatCurrency(deal.expected_value) }}
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-xs text-slate-500">Actual Closed Value</span>
                                <div class="text-xl font-bold text-slate-900 mt-1">
                                    {{ deal.actual_value ? formatCurrency(deal.actual_value) : 'Pending Close' }}
                                </div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="text-xs text-slate-500">Target Close Date</span>
                                <div class="text-xl font-bold text-slate-900 mt-1">
                                    {{ formatDate(deal.expected_close_date) }}
                                </div>
                            </div>
                        </div>

                        <!-- Lost Reason Alert if lost -->
                        <div v-if="deal.stage?.is_lost" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                            <span class="text-xs font-bold uppercase text-red-800">Deal Closed as Lost:</span>
                            <p class="text-sm text-red-700 mt-1">{{ deal.lost_reason || 'No specific reason provided.' }}</p>
                        </div>

                        <!-- Notes / Negotiation log -->
                        <div class="mt-5">
                            <h4 class="text-xs font-semibold uppercase text-slate-400 mb-2">Deal Notes & Requirements</h4>
                            <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100 whitespace-pre-wrap">
                                {{ deal.notes || 'No specific notes recorded for this deal.' }}
                            </p>
                        </div>
                    </AppCard>

                    <!-- Associated Bookings & Payments -->
                    <AppCard class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold uppercase text-slate-500 tracking-wider">
                                Linked Bookings & Reservations
                            </h3>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                {{ deal.bookings?.length || 0 }}
                            </span>
                        </div>

                        <div v-if="deal.bookings?.length > 0" class="space-y-3">
                            <div
                                v-for="b in deal.bookings"
                                :key="b.id"
                                class="p-4 rounded-xl border border-slate-200 flex items-center justify-between bg-white hover:bg-slate-50 transition"
                            >
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-mono text-xs font-bold text-primary-600">{{ b.booking_number }}</span>
                                        <AppBadge :variant="b.status === 'confirmed' ? 'success' : (b.status === 'cancelled' ? 'danger' : 'primary')" size="sm">
                                            {{ b.status }}
                                        </AppBadge>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Booked on {{ formatDate(b.booking_date) }} &bull; Total: {{ formatCurrency(b.total_amount) }}
                                    </p>
                                </div>
                                <Link
                                    :href="route('admin.bookings.show', b.id)"
                                    class="text-xs font-semibold text-primary-600 hover:text-primary-700"
                                >
                                    View Booking &rarr;
                                </Link>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-xs text-slate-400">
                            No reservations or bookings created for this deal yet.
                        </div>
                    </AppCard>

                    <!-- Associated Site Visits -->
                    <AppCard class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold uppercase text-slate-500 tracking-wider">
                                Site Visit History
                            </h3>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                {{ deal.site_visits?.length || 0 }}
                            </span>
                        </div>

                        <div v-if="deal.site_visits?.length > 0" class="space-y-3">
                            <div
                                v-for="v in deal.site_visits"
                                :key="v.id"
                                class="p-3 rounded-xl border border-slate-200 flex items-center justify-between text-xs"
                            >
                                <div class="space-y-1">
                                    <span class="font-mono font-bold text-slate-700">{{ v.visit_code }}</span>
                                    <p class="text-slate-500">{{ formatDate(v.scheduled_date) }} at {{ v.scheduled_time }}</p>
                                </div>
                                <AppBadge :variant="v.status === 'completed' ? 'success' : 'primary'" size="sm">
                                    {{ v.status }}
                                </AppBadge>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-xs text-slate-400">
                            No site visits scheduled for this deal.
                        </div>
                    </AppCard>
                </div>

                <!-- Right Column: Customer, Property, and Agent info (1 col) -->
                <div class="space-y-6">
                    <!-- Customer Card -->
                    <AppCard class="p-5">
                        <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-3">Customer / Buyer</h4>
                        <div v-if="deal.customer" class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                                    {{ deal.customer.name.substring(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <Link :href="route('admin.customers.show', deal.customer.id)" class="font-bold text-slate-900 text-sm hover:text-primary-600">
                                        {{ deal.customer.name }}
                                    </Link>
                                    <p class="text-xs text-slate-500 capitalize">{{ deal.customer.customer_type }}</p>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 space-y-2 text-xs text-slate-600">
                                <div class="flex items-center space-x-2">
                                    <Phone class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ deal.customer.phone || 'N/A' }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Mail class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ deal.customer.email || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-xs text-slate-400">No linked customer profile.</div>
                    </AppCard>

                    <!-- Property Card -->
                    <AppCard class="p-5">
                        <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-3">Linked Property</h4>
                        <div v-if="deal.property" class="space-y-3">
                            <div class="aspect-video w-full rounded-lg overflow-hidden bg-slate-100 border border-slate-200 relative">
                                <img
                                    v-if="deal.property.images?.length > 0"
                                    :src="deal.property.images[0].image_url"
                                    :alt="deal.property.title"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                    <Building2 class="w-8 h-8" />
                                </div>
                            </div>

                            <div>
                                <Link
                                    :href="route('admin.properties.show', deal.property.id)"
                                    class="font-bold text-slate-900 text-sm hover:text-primary-600 block truncate"
                                >
                                    {{ deal.property.title }}
                                </Link>
                                <div class="flex items-center justify-between mt-1 text-xs">
                                    <span class="font-mono text-slate-500">{{ deal.property.property_code }}</span>
                                    <span class="font-bold text-slate-900">{{ formatCurrency(deal.property.price) }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-xs text-slate-400">
                            No property specifically linked to this inquiry deal yet.
                        </div>
                    </AppCard>

                    <!-- Assigned Agent Card -->
                    <AppCard class="p-5">
                        <h4 class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-3">Assigned Sales Agent</h4>
                        <div v-if="deal.assigned_agent" class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">
                                {{ deal.assigned_agent.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ deal.assigned_agent.name }}</p>
                                <p class="text-xs text-slate-500">{{ deal.assigned_agent.email }}</p>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>

        <!-- Change Stage Modal -->
        <AppModal :show="isStageModalOpen" @close="isStageModalOpen = false" max-width="md">
            <template #title>
                <div class="flex items-center space-x-2">
                    <TrendingUp class="w-5 h-5 text-primary-600" />
                    <span>Update Pipeline Stage</span>
                </div>
            </template>

            <form @submit.prevent="submitStageChange" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Select Stage *</label>
                    <select
                        v-model="stageForm.stage_id"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option v-for="s in deal.pipeline?.stages" :key="s.id" :value="s.id">
                            {{ s.name }} ({{ s.probability }}% win probability)
                        </option>
                    </select>
                </div>

                <div v-if="selectedTargetStage?.is_won" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl space-y-2">
                    <div class="flex items-center text-emerald-800 text-xs font-semibold space-x-1.5">
                        <CheckCircle2 class="w-4 h-4 text-emerald-600" />
                        <span>Closing Deal as WON!</span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-emerald-900 mb-1">Final Transaction Value ($)</label>
                        <input
                            v-model="stageForm.actual_value"
                            type="number"
                            step="0.01"
                            class="w-full text-sm rounded-lg border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500"
                            required
                        />
                    </div>
                </div>

                <div v-if="selectedTargetStage?.is_lost" class="p-3 bg-red-50 border border-red-200 rounded-xl space-y-2">
                    <div class="flex items-center text-red-800 text-xs font-semibold space-x-1.5">
                        <XCircle class="w-4 h-4 text-red-600" />
                        <span>Closing Deal as LOST</span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-red-900 mb-1">Reason for Loss *</label>
                        <input
                            v-model="stageForm.lost_reason"
                            type="text"
                            placeholder="e.g. Price too high, lost to competing agency..."
                            class="w-full text-sm rounded-lg border-red-300 focus:border-red-500 focus:ring-red-500"
                            required
                        />
                    </div>
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isStageModalOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="stageForm.processing">Save Stage</AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
