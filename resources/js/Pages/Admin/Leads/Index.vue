<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';
import {
    Plus,
    Search,
    Phone,
    Mail,
    UserCheck,
    ArrowRightLeft,
    CheckCircle,
    Eye,
    Edit,
    Trash2,
    SlidersHorizontal,
    Flame
} from 'lucide-vue-next';

const props = defineProps({
    leads: Object,
    filters: Object,
    stats: Object,
    agents: Array,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const source = ref(props.filters.source || 'all');
const priority = ref(props.filters.priority || 'all');
const agentId = ref(props.filters.agent_id || 'all');

const applyFilters = () => {
    router.get(
        route('admin.leads.index'),
        {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
            source: source.value !== 'all' ? source.value : undefined,
            priority: priority.value !== 'all' ? priority.value : undefined,
            agent_id: agentId.value !== 'all' ? agentId.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

// Assign Modal
const assignModalOpen = ref(false);
const leadToAssign = ref(null);
const assignForm = useForm({
    assigned_agent_id: '',
});

const openAssignModal = (lead) => {
    leadToAssign.value = lead;
    assignForm.assigned_agent_id = lead.assigned_agent_id || '';
    assignModalOpen.value = true;
};

const submitAssign = () => {
    if (!leadToAssign.value) return;
    assignForm.post(route('admin.leads.assign', leadToAssign.value.id), {
        onSuccess: () => {
            assignModalOpen.value = false;
            leadToAssign.value = null;
        }
    });
};

// Convert Modal
const convertModalOpen = ref(false);
const leadToConvert = ref(null);
const convertForm = useForm({
    create_deal: true,
    deal_title: '',
    expected_value: '',
});

const openConvertModal = (lead) => {
    leadToConvert.value = lead;
    convertForm.deal_title = `${lead.name} - Deal`;
    convertForm.expected_value = lead.budget_max || 500000;
    convertModalOpen.value = true;
};

const submitConvert = () => {
    if (!leadToConvert.value) return;
    convertForm.post(route('admin.leads.convert', leadToConvert.value.id), {
        onSuccess: () => {
            convertModalOpen.value = false;
            leadToConvert.value = null;
        }
    });
};

// Delete Confirmation
const deleteModalOpen = ref(false);
const leadToDelete = ref(null);

const confirmDelete = (lead) => {
    leadToDelete.value = lead;
    deleteModalOpen.value = true;
};

const doDelete = () => {
    if (!leadToDelete.value) return;
    router.delete(route('admin.leads.destroy', leadToDelete.value.id), {
        onSuccess: () => {
            deleteModalOpen.value = false;
            leadToDelete.value = null;
        }
    });
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'new': return { variant: 'indigo', label: 'New Inquiry' };
        case 'contacted': return { variant: 'slate', label: 'Contacted' };
        case 'qualified': return { variant: 'emerald', label: 'Qualified Buyer' };
        case 'site_visit_scheduled': return { variant: 'purple', label: 'Visit Scheduled' };
        case 'negotiation': return { variant: 'amber', label: 'Negotiation' };
        case 'converted': return { variant: 'emerald', label: 'Converted' };
        case 'lost': return { variant: 'rose', label: 'Lost' };
        default: return { variant: 'slate', label: st };
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Buyer & Seller Leads - Real Estate CRM" />

        <AdminPageHeader
            title="Buyer & Seller Leads Management"
            description="Track inquiries, qualify buyers, schedule showings, and convert leads into closed deals."
        >
            <template #actions>
                <Link :href="route('admin.leads.create')">
                    <AppButton size="sm" variant="primary">
                        <Plus class="w-4 h-4 mr-1" />
                        <span>Add New Lead</span>
                    </AppButton>
                </Link>
            </template>
        </AdminPageHeader>

        <!-- Status Counters Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 mb-6">
            <button
                type="button"
                @click="status = 'all'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'all' ? 'bg-white border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-slate-400 uppercase">All</span>
                <span class="block text-xl font-black text-slate-900 mt-1">{{ stats.total }}</span>
            </button>

            <button
                type="button"
                @click="status = 'new'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'new' ? 'bg-blue-50/40 border-blue-500 ring-2 ring-blue-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-blue-600 uppercase">New</span>
                <span class="block text-xl font-black text-blue-700 mt-1">{{ stats.new }}</span>
            </button>

            <button
                type="button"
                @click="status = 'contacted'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'contacted' ? 'bg-slate-100 border-slate-500 ring-2 ring-slate-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-slate-500 uppercase">Contacted</span>
                <span class="block text-xl font-black text-slate-700 mt-1">{{ stats.contacted }}</span>
            </button>

            <button
                type="button"
                @click="status = 'qualified'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'qualified' ? 'bg-emerald-50/40 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-emerald-600 uppercase">Qualified</span>
                <span class="block text-xl font-black text-emerald-700 mt-1">{{ stats.qualified }}</span>
            </button>

            <button
                type="button"
                @click="status = 'negotiation'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'negotiation' ? 'bg-amber-50/40 border-amber-500 ring-2 ring-amber-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-amber-600 uppercase">Negotiation</span>
                <span class="block text-xl font-black text-amber-700 mt-1">{{ stats.negotiation }}</span>
            </button>

            <button
                type="button"
                @click="status = 'converted'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'converted' ? 'bg-emerald-50/40 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-emerald-600 uppercase">Converted</span>
                <span class="block text-xl font-black text-emerald-700 mt-1">{{ stats.converted }}</span>
            </button>

            <button
                type="button"
                @click="status = 'lost'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'lost' ? 'bg-rose-50/40 border-rose-500 ring-2 ring-rose-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-rose-600 uppercase">Lost</span>
                <span class="block text-xl font-black text-rose-700 mt-1">{{ stats.lost }}</span>
            </button>
        </div>

        <!-- Search & Filter Controls -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="relative lg:col-span-2">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Search lead name, phone, email, preferred location..."
                        class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                    />
                </div>

                <select
                    v-model="source"
                    @change="applyFilters"
                    class="bg-slate-50 border border-slate-200 text-xs rounded-xl px-3 py-2 text-slate-700 focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="all">All Sources</option>
                    <option value="website">Website</option>
                    <option value="social_media">Social Media</option>
                    <option value="referrals">Referrals</option>
                    <option value="property_portals">Property Portals</option>
                    <option value="advertisements">Advertisements</option>
                    <option value="calls">Phone Call</option>
                    <option value="walk_ins">Walk-in</option>
                </select>

                <select
                    v-model="priority"
                    @change="applyFilters"
                    class="bg-slate-50 border border-slate-200 text-xs rounded-xl px-3 py-2 text-slate-700 focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="all">All Priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>

                <select
                    v-model="agentId"
                    @change="applyFilters"
                    class="bg-slate-50 border border-slate-200 text-xs rounded-xl px-3 py-2 text-slate-700 focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="all">All Assigned Agents</option>
                    <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                        {{ agent.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Lead Contact</th>
                            <th class="py-3 px-4">Type & Source</th>
                            <th class="py-3 px-4">Requirements & Budget</th>
                            <th class="py-3 px-4 text-center">Score / Priority</th>
                            <th class="py-3 px-4">Assigned Agent</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4">
                                <Link :href="route('admin.leads.show', lead.id)" class="font-bold text-slate-900 hover:text-indigo-600 transition block">
                                    {{ lead.name }}
                                </Link>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                    <span class="flex items-center gap-1"><Phone class="w-3 h-3 text-slate-400" />{{ lead.phone }}</span>
                                    <span v-if="lead.email" class="hidden sm:inline text-slate-300">&bull;</span>
                                    <span v-if="lead.email" class="hidden sm:inline truncate max-w-[140px]">{{ lead.email }}</span>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <span class="font-bold capitalize text-slate-900 block">{{ lead.lead_type }}</span>
                                <span class="text-[10px] uppercase font-semibold text-slate-400 capitalize">{{ lead.source.replace('_', ' ') }}</span>
                            </td>

                            <td class="py-3 px-4">
                                <div v-if="lead.budget_max" class="font-extrabold text-slate-900">
                                    ${{ Number(lead.budget_min || 0).toLocaleString() }} - ${{ Number(lead.budget_max).toLocaleString() }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    {{ lead.property_type ? lead.property_type.replace('_', ' ') : 'Any' }} &bull; {{ lead.preferred_location || 'Any Area' }}
                                </div>
                            </td>

                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black" :class="lead.score >= 75 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700'">
                                    <Flame v-if="lead.score >= 75" class="w-3 h-3 text-amber-500" />
                                    <span>{{ lead.score }}/100</span>
                                </div>
                                <div class="text-[10px] font-bold uppercase mt-0.5 text-slate-400">
                                    {{ lead.priority }}
                                </div>
                            </td>

                            <td class="py-3 px-4 text-slate-700 font-semibold">
                                <span v-if="lead.assigned_agent">{{ lead.assigned_agent.name }}</span>
                                <button
                                    v-else
                                    type="button"
                                    @click="openAssignModal(lead)"
                                    class="text-[11px] font-bold text-indigo-600 hover:underline"
                                >
                                    + Assign Agent
                                </button>
                            </td>

                            <td class="py-3 px-4 text-center">
                                <AppBadge :variant="getStatusBadge(lead.status).variant">
                                    {{ getStatusBadge(lead.status).label }}
                                </AppBadge>
                            </td>

                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        v-if="lead.status !== 'converted'"
                                        type="button"
                                        @click="openConvertModal(lead)"
                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition border border-emerald-200 flex items-center gap-1"
                                        title="Convert to Customer & Deal"
                                    >
                                        <CheckCircle class="w-3 h-3" />
                                        <span>Convert</span>
                                    </button>

                                    <Link :href="route('admin.leads.show', lead.id)" class="p-1 rounded-md text-slate-500 hover:text-indigo-600" title="View details">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="route('admin.leads.edit', lead.id)" class="p-1 rounded-md text-slate-500 hover:text-amber-600" title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(lead)" class="p-1 rounded-md text-slate-500 hover:text-rose-600" title="Archive">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200">
            <span class="text-xs text-slate-500">
                Showing {{ leads.from || 0 }} to {{ leads.to || 0 }} of {{ leads.total }} leads
            </span>
            <DataTablePagination :pagination="leads" @page="applyFilters" />
        </div>

        <!-- Assign Agent Modal -->
        <AppModal
            :show="assignModalOpen"
            title="Assign Lead to Agent"
            @close="assignModalOpen = false"
        >
            <form @submit.prevent="submitAssign" class="space-y-4">
                <p class="text-xs text-slate-500">
                    Assigning lead <strong class="text-slate-800">{{ leadToAssign?.name }}</strong> to a real estate agent for immediate follow-up:
                </p>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Agent *</label>
                    <select
                        v-model="assignForm.assigned_agent_id"
                        required
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Choose Agent</option>
                        <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <AppButton size="sm" variant="secondary" type="button" @click="assignModalOpen = false">Cancel</AppButton>
                    <AppButton size="sm" variant="primary" type="submit" :disabled="assignForm.processing">Confirm Assignment</AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Convert Lead Modal -->
        <AppModal
            :show="convertModalOpen"
            title="Convert Lead to Customer & Opportunity"
            @close="convertModalOpen = false"
        >
            <form @submit.prevent="submitConvert" class="space-y-4">
                <p class="text-xs text-slate-600 leading-relaxed">
                    This will graduate <strong class="text-slate-900">{{ leadToConvert?.name }}</strong> into a permanent Customer account, record buyer preferences, and optionally create an active deal in the pipeline.
                </p>

                <div class="p-3 bg-slate-50 rounded-xl space-y-3 border border-slate-200">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800">
                        <input v-model="convertForm.create_deal" type="checkbox" class="rounded text-indigo-600" />
                        <span>Create Opportunity / Deal in Sales Pipeline</span>
                    </label>

                    <div v-if="convertForm.create_deal" class="space-y-2 pt-1">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Deal Name</label>
                            <input v-model="convertForm.deal_title" type="text" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Expected Deal Value ($)</label>
                            <input v-model="convertForm.expected_value" type="number" step="0.01" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <AppButton size="sm" variant="secondary" type="button" @click="convertModalOpen = false">Cancel</AppButton>
                    <AppButton size="sm" variant="primary" type="submit" :disabled="convertForm.processing">
                        Confirm & Convert
                    </AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Delete Dialog -->
        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Archive Lead?"
            :message="`Are you sure you want to archive lead '${leadToDelete?.name}'?`"
            confirm-text="Archive Lead"
            confirm-variant="danger"
            @confirm="doDelete"
            @close="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
