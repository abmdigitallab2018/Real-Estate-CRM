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
    Kanban,
    Plus,
    Search,
    DollarSign,
    TrendingUp,
    Calendar,
    User,
    Building2,
    CheckCircle2,
    XCircle,
    ArrowRight,
    Filter,
    Clock,
    Briefcase
} from 'lucide-vue-next';

const props = defineProps({
    currentPipeline: Object,
    pipelines: Array,
    stagesWithDeals: Array,
    totalPipelineValue: Number,
    weightedPipelineValue: Number,
    agents: Array,
    properties: Array,
    customers: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const selectedAgent = ref(props.filters?.agent_id || 'all');
const selectedPipeline = ref(props.currentPipeline?.id || '');

const handleFilter = () => {
    router.get(route('admin.deals.index'), {
        pipeline_id: selectedPipeline.value,
        agent_id: selectedAgent.value,
        search: search.value,
    }, { preserveState: true });
};

// Create Deal Modal
const isCreateOpen = ref(false);
const form = useForm({
    pipeline_id: props.currentPipeline?.id || '',
    stage_id: props.currentPipeline?.stages?.[0]?.id || '',
    customer_id: '',
    property_id: '',
    assigned_agent_id: '',
    title: '',
    deal_type: 'sale',
    expected_value: '',
    probability: 50,
    expected_close_date: '',
    notes: '',
});

const openCreateModal = () => {
    form.reset();
    form.pipeline_id = props.currentPipeline?.id || '';
    form.stage_id = props.currentPipeline?.stages?.[0]?.id || '';
    isCreateOpen.value = true;
};

const submitCreate = () => {
    form.post(route('admin.deals.store'), {
        onSuccess: () => {
            isCreateOpen.value = false;
            form.reset();
        }
    });
};

// Move Stage Modal
const isMoveOpen = ref(false);
const selectedDeal = ref(null);
const stageForm = useForm({
    stage_id: '',
    actual_value: '',
    lost_reason: '',
});

const openMoveModal = (deal) => {
    selectedDeal.value = deal;
    stageForm.stage_id = deal.stage_id;
    stageForm.actual_value = deal.expected_value;
    stageForm.lost_reason = '';
    isMoveOpen.value = true;
};

const submitMoveStage = () => {
    if (!selectedDeal.value) return;
    stageForm.post(route('admin.deals.stage', selectedDeal.value.id), {
        onSuccess: () => {
            isMoveOpen.value = false;
            selectedDeal.value = null;
        }
    });
};

const selectedTargetStage = computed(() => {
    if (!props.currentPipeline?.stages) return null;
    return props.currentPipeline.stages.find(s => s.id === stageForm.stage_id);
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

const totalDealsCount = computed(() => {
    if (!props.stagesWithDeals) return 0;
    return props.stagesWithDeals.reduce((acc, stage) => acc + (stage.deals_count || 0), 0);
});
</script>

<template>
    <AdminLayout>
        <Head title="Deals & Sales Pipeline" />

        <div class="space-y-6">
            <!-- Header -->
            <AdminPageHeader
                title="Sales Pipeline & Deals"
                description="Track and manage active transactions, negotiations, and closing stages."
            >
                <template #actions>
                    <AppButton variant="primary" @click="openCreateModal">
                        <Plus class="w-4 h-4 mr-2" />
                        New Deal
                    </AppButton>
                </template>
            </AdminPageHeader>

            <!-- Metrics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <AppCard class="p-5 flex items-center justify-between border-l-4 border-primary-500">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Active Deals</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ totalDealsCount }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Across {{ stagesWithDeals?.length || 0 }} pipeline stages</p>
                    </div>
                    <div class="p-3 bg-primary-50 text-primary-600 rounded-xl">
                        <Briefcase class="w-6 h-6" />
                    </div>
                </AppCard>

                <AppCard class="p-5 flex items-center justify-between border-l-4 border-emerald-500">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Pipeline Value</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ formatCurrency(totalPipelineValue) }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Cumulative potential transaction volume</p>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <DollarSign class="w-6 h-6" />
                    </div>
                </AppCard>

                <AppCard class="p-5 flex items-center justify-between border-l-4 border-indigo-500">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Weighted Pipeline Value</p>
                        <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ formatCurrency(weightedPipelineValue) }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Probability-adjusted expected revenue</p>
                    </div>
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                        <TrendingUp class="w-6 h-6" />
                    </div>
                </AppCard>
            </div>

            <!-- Pipeline Filter & Controls Bar -->
            <AppCard class="p-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Pipeline Selector -->
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pipeline:</span>
                            <select
                                v-model="selectedPipeline"
                                @change="handleFilter"
                                class="rounded-lg border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500 font-medium py-1.5"
                            >
                                <option v-for="p in pipelines" :key="p.id" :value="p.id">
                                    {{ p.name }} {{ p.is_default ? '(Default)' : '' }}
                                </option>
                            </select>
                        </div>

                        <!-- Agent Filter -->
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Agent:</span>
                            <select
                                v-model="selectedAgent"
                                @change="handleFilter"
                                class="rounded-lg border-slate-300 text-sm focus:border-primary-500 focus:ring-primary-500 py-1.5"
                            >
                                <option value="all">All Agents</option>
                                <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative max-w-xs w-full">
                        <Search class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            @keyup.enter="handleFilter"
                            type="text"
                            placeholder="Search deal, client, property..."
                            class="w-full pl-9 pr-4 py-1.5 text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>
            </AppCard>

            <!-- Kanban Board Columns -->
            <div class="flex gap-5 overflow-x-auto pb-6 items-start min-h-[600px]">
                <div
                    v-for="stage in stagesWithDeals"
                    :key="stage.id"
                    class="w-80 flex-shrink-0 bg-slate-100/80 rounded-2xl p-3 border border-slate-200/80 flex flex-col max-h-[750px]"
                >
                    <!-- Stage Header -->
                    <div class="flex items-center justify-between pb-3 px-1 border-b border-slate-200/80 mb-3">
                        <div class="flex items-center space-x-2">
                            <span
                                class="w-3 h-3 rounded-full"
                                :style="{ backgroundColor: stage.color || '#3B82F6' }"
                            ></span>
                            <h4 class="font-semibold text-slate-800 text-sm">{{ stage.name }}</h4>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold">
                                {{ stage.deals_count }}
                            </span>
                        </div>
                        <div class="text-xs font-medium text-slate-500">
                            {{ formatCurrency(stage.total_value) }}
                        </div>
                    </div>

                    <!-- Deals Cards List -->
                    <div class="space-y-3 overflow-y-auto flex-1 pr-1">
                        <div
                            v-for="deal in stage.deals"
                            :key="deal.id"
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition duration-150 space-y-3 group"
                        >
                            <!-- Card Header: Code & Deal Type -->
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-medium text-slate-400">
                                    {{ deal.deal_code }}
                                </span>
                                <div class="flex items-center space-x-1.5">
                                    <AppBadge
                                        :variant="deal.deal_type === 'sale' ? 'primary' : 'warning'"
                                        size="sm"
                                        class="capitalize"
                                    >
                                        {{ deal.deal_type }}
                                    </AppBadge>
                                </div>
                            </div>

                            <!-- Deal Title -->
                            <Link
                                :href="route('admin.deals.show', deal.id)"
                                class="block font-semibold text-slate-900 text-sm group-hover:text-primary-600 transition"
                            >
                                {{ deal.title }}
                            </Link>

                            <!-- Linked Property -->
                            <div v-if="deal.property" class="flex items-center text-xs text-slate-600 space-x-1.5 truncate">
                                <Building2 class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />
                                <span class="truncate">{{ deal.property.title }}</span>
                            </div>

                            <!-- Customer Name -->
                            <div class="flex items-center text-xs text-slate-600 space-x-1.5 truncate">
                                <User class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />
                                <span class="truncate font-medium">{{ deal.customer?.name || 'No Client' }}</span>
                            </div>

                            <!-- Value & Probability -->
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-400">Value: </span>
                                    <span class="font-bold text-slate-900">{{ formatCurrency(deal.expected_value) }}</span>
                                </div>
                                <div class="flex items-center space-x-1 text-slate-500">
                                    <Clock class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ formatDate(deal.expected_close_date) }}</span>
                                </div>
                            </div>

                            <!-- Probability Bar -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px] text-slate-400 font-medium">
                                    <span>Probability</span>
                                    <span>{{ deal.probability }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full"
                                        :class="deal.probability >= 70 ? 'bg-emerald-500' : (deal.probability >= 40 ? 'bg-primary-500' : 'bg-amber-500')"
                                        :style="{ width: `${deal.probability}%` }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Action Bar: Move stage & details -->
                            <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                                <span class="text-[11px] text-slate-400">
                                    {{ deal.assigned_agent?.name || 'Unassigned' }}
                                </span>
                                <button
                                    @click="openMoveModal(deal)"
                                    type="button"
                                    class="inline-flex items-center text-xs font-medium text-primary-600 hover:text-primary-700 bg-primary-50 px-2 py-1 rounded-md transition"
                                >
                                    Change Stage
                                    <ArrowRight class="w-3 h-3 ml-1" />
                                </button>
                            </div>
                        </div>

                        <!-- Empty Column State -->
                        <div
                            v-if="stage.deals.length === 0"
                            class="p-6 text-center text-xs text-slate-400 border border-dashed border-slate-300 rounded-xl"
                        >
                            No deals in this stage
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Deal Modal -->
        <AppModal :show="isCreateOpen" @close="isCreateOpen = false" max-width="lg">
            <template #title>
                <div class="flex items-center space-x-2">
                    <Plus class="w-5 h-5 text-primary-600" />
                    <span>Create New Deal</span>
                </div>
            </template>

            <form @submit.prevent="submitCreate" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Deal Title *</label>
                    <input
                        v-model="form.title"
                        type="text"
                        placeholder="e.g. Skyline Penthouse Acquisition"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Assigned Agent *</label>
                        <select
                            v-model="form.assigned_agent_id"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="">Select Agent</option>
                            <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Linked Property (Optional)</label>
                        <select
                            v-model="form.property_id"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">No Property Attached</option>
                            <option v-for="p in properties" :key="p.id" :value="p.id">
                                {{ p.property_code }} - {{ p.title }} (${{ Number(p.price).toLocaleString() }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Deal Type *</label>
                        <select
                            v-model="form.deal_type"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="sale">Sale</option>
                            <option value="rent">Rent</option>
                            <option value="lease">Lease</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Initial Stage *</label>
                        <select
                            v-model="form.stage_id"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option v-for="s in currentPipeline?.stages" :key="s.id" :value="s.id">
                                {{ s.name }} ({{ s.probability }}%)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Expected Value ($) *</label>
                        <input
                            v-model="form.expected_value"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="500000"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Expected Close Date</label>
                        <input
                            v-model="form.expected_close_date"
                            type="date"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Probability (%)</label>
                        <input
                            v-model="form.probability"
                            type="number"
                            min="0"
                            max="100"
                            placeholder="50"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Notes / Terms</label>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Negotiation terms, client preferences..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isCreateOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="form.processing">Create Deal</AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Change Stage Modal -->
        <AppModal :show="isMoveOpen" @close="isMoveOpen = false" max-width="md">
            <template #title>
                <div class="flex items-center space-x-2">
                    <ArrowRight class="w-5 h-5 text-primary-600" />
                    <span>Advance Deal Stage</span>
                </div>
            </template>

            <form v-if="selectedDeal" @submit.prevent="submitMoveStage" class="space-y-4">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="font-semibold text-slate-800 text-sm">{{ selectedDeal.title }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">Current Value: {{ formatCurrency(selectedDeal.expected_value) }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Select New Stage *</label>
                    <select
                        v-model="stageForm.stage_id"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option v-for="s in currentPipeline?.stages" :key="s.id" :value="s.id">
                            {{ s.name }} ({{ s.probability }}% win probability)
                        </option>
                    </select>
                </div>

                <!-- If Closed Won Selected -->
                <div v-if="selectedTargetStage?.is_won" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl space-y-2">
                    <div class="flex items-center text-emerald-800 text-xs font-semibold space-x-1.5">
                        <CheckCircle2 class="w-4 h-4 text-emerald-600" />
                        <span>Closing Deal as WON!</span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-emerald-900 mb-1">Final Actual Value ($)</label>
                        <input
                            v-model="stageForm.actual_value"
                            type="number"
                            step="0.01"
                            class="w-full text-sm rounded-lg border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500"
                            required
                        />
                    </div>
                    <p class="text-[11px] text-emerald-700">The attached property will automatically be marked as Sold/Rented.</p>
                </div>

                <!-- If Closed Lost Selected -->
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
                            placeholder="e.g. Budget issues, bought from competitor..."
                            class="w-full text-sm rounded-lg border-red-300 focus:border-red-500 focus:ring-red-500"
                            required
                        />
                    </div>
                    <p class="text-[11px] text-red-700">Any property under negotiation will be released back to Available.</p>
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isMoveOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="stageForm.processing">Update Stage</AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
