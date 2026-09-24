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
    Calendar,
    Clock,
    Plus,
    Building2,
    User,
    CheckCircle2,
    XCircle,
    AlertCircle,
    CalendarCheck,
    MessageSquare,
    Edit3,
    Trash2,
    Phone,
    MapPin
} from 'lucide-vue-next';

const props = defineProps({
    visits: Object,
    stats: Object,
    agents: Array,
    properties: Array,
    customers: Array,
    filters: Object,
});

const currentView = ref(props.filters?.view || 'all');
const selectedStatus = ref(props.filters?.status || 'all');
const selectedAgent = ref(props.filters?.agent_id || 'all');
const selectedDate = ref(props.filters?.date || '');

const handleFilter = () => {
    router.get(route('admin.site-visits.index'), {
        view: currentView.value,
        status: selectedStatus.value,
        agent_id: selectedAgent.value,
        date: selectedDate.value,
    }, { preserveState: true });
};

const setView = (v) => {
    currentView.value = v;
    handleFilter();
};

// Schedule Visit Modal
const isScheduleOpen = ref(false);
const scheduleForm = useForm({
    property_id: '',
    customer_id: '',
    assigned_agent_id: '',
    scheduled_date: '',
    scheduled_time: '10:00',
    agent_notes: '',
});

const openScheduleModal = () => {
    scheduleForm.reset();
    isScheduleOpen.value = true;
};

const submitSchedule = () => {
    scheduleForm.post(route('admin.site-visits.store'), {
        onSuccess: () => {
            isScheduleOpen.value = false;
            scheduleForm.reset();
        }
    });
};

// Outcome & Feedback Modal
const isOutcomeOpen = ref(false);
const selectedVisit = ref(null);
const outcomeForm = useForm({
    status: 'completed',
    interest_level: 'high',
    customer_feedback: '',
    agent_notes: '',
    next_action: '',
});

const openOutcomeModal = (visit) => {
    selectedVisit.value = visit;
    outcomeForm.status = visit.status === 'scheduled' ? 'completed' : visit.status;
    outcomeForm.interest_level = visit.interest_level || 'high';
    outcomeForm.customer_feedback = visit.customer_feedback || '';
    outcomeForm.agent_notes = visit.agent_notes || '';
    outcomeForm.next_action = visit.next_action || '';
    isOutcomeOpen.value = true;
};

const submitOutcome = () => {
    if (!selectedVisit.value) return;
    outcomeForm.post(route('admin.site-visits.status', selectedVisit.value.id), {
        onSuccess: () => {
            isOutcomeOpen.value = false;
            selectedVisit.value = null;
        }
    });
};

// Reschedule Modal
const isRescheduleOpen = ref(false);
const rescheduleForm = useForm({
    scheduled_date: '',
    scheduled_time: '',
    agent_notes: '',
});

const openRescheduleModal = (visit) => {
    selectedVisit.value = visit;
    rescheduleForm.scheduled_date = visit.scheduled_date;
    rescheduleForm.scheduled_time = visit.scheduled_time;
    rescheduleForm.agent_notes = visit.agent_notes || '';
    isRescheduleOpen.value = true;
};

const submitReschedule = () => {
    if (!selectedVisit.value) return;
    rescheduleForm.post(route('admin.site-visits.reschedule', selectedVisit.value.id), {
        onSuccess: () => {
            isRescheduleOpen.value = false;
            selectedVisit.value = null;
        }
    });
};

const cancelVisit = (id) => {
    if (confirm('Are you sure you want to cancel this scheduled site visit?')) {
        router.delete(route('admin.site-visits.destroy', id));
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'scheduled': return { variant: 'primary', label: 'Scheduled' };
        case 'confirmed': return { variant: 'info', label: 'Confirmed' };
        case 'completed': return { variant: 'success', label: 'Completed' };
        case 'cancelled': return { variant: 'danger', label: 'Cancelled' };
        case 'no_show': return { variant: 'warning', label: 'No Show' };
        default: return { variant: 'secondary', label: status };
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Site Visits & Calendar" />

        <div class="space-y-6">
            <!-- Header -->
            <AdminPageHeader
                title="Site Visits & Appointments"
                description="Manage property tours, appointments, customer feedback, and post-visit actions."
            >
                <template #actions>
                    <AppButton variant="primary" @click="openScheduleModal">
                        <Plus class="w-4 h-4 mr-2" />
                        Schedule Visit
                    </AppButton>
                </template>
            </AdminPageHeader>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <AppCard class="p-4 border-l-4 border-primary-500">
                    <span class="text-xs text-slate-500 font-medium">Total Scheduled</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.total }}</h3>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-amber-500">
                    <span class="text-xs text-slate-500 font-medium">Today's Visits</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.today }}</h3>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-indigo-500">
                    <span class="text-xs text-slate-500 font-medium">Upcoming</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.upcoming }}</h3>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-emerald-500">
                    <span class="text-xs text-slate-500 font-medium">Completed</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.completed }}</h3>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-red-500">
                    <span class="text-xs text-slate-500 font-medium">Cancelled</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.cancelled }}</h3>
                </AppCard>
            </div>

            <!-- Tab Navigation & Filters -->
            <AppCard class="p-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <!-- Views Tabs -->
                    <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl">
                        <button
                            v-for="v in [
                                { id: 'all', label: 'All Visits' },
                                { id: 'today', label: 'Today' },
                                { id: 'upcoming', label: 'Upcoming' },
                                { id: 'past', label: 'Past' }
                            ]"
                            :key="v.id"
                            @click="setView(v.id)"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                            :class="currentView === v.id ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        >
                            {{ v.label }}
                        </button>
                    </div>

                    <!-- Filter Dropdowns -->
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model="selectedStatus"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Statuses</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>

                        <select
                            v-model="selectedAgent"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Agents</option>
                            <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                        </select>

                        <input
                            v-model="selectedDate"
                            @change="handleFilter"
                            type="date"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>
            </AppCard>

            <!-- Site Visits Table -->
            <AppCard>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] font-semibold uppercase text-slate-500 tracking-wider">
                                <th class="py-3 px-4">Visit Code & Time</th>
                                <th class="py-3 px-4">Customer</th>
                                <th class="py-3 px-4">Property</th>
                                <th class="py-3 px-4">Assigned Agent</th>
                                <th class="py-3 px-4">Status & Interest</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr
                                v-for="visit in visits.data"
                                :key="visit.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="py-3 px-4">
                                    <span class="font-mono font-bold text-xs text-primary-600 block">{{ visit.visit_code }}</span>
                                    <div class="flex items-center text-xs text-slate-700 mt-0.5">
                                        <Calendar class="w-3.5 h-3.5 mr-1 text-slate-400" />
                                        <span>{{ formatDate(visit.scheduled_date) }}</span>
                                    </div>
                                    <div class="flex items-center text-xs text-slate-500 mt-0.5">
                                        <Clock class="w-3.5 h-3.5 mr-1 text-slate-400" />
                                        <span>{{ visit.scheduled_time }}</span>
                                    </div>
                                </td>

                                <td class="py-3 px-4">
                                    <div v-if="visit.customer" class="space-y-0.5">
                                        <span class="font-semibold text-slate-900 block">{{ visit.customer.name }}</span>
                                        <div class="flex items-center text-xs text-slate-500">
                                            <Phone class="w-3 h-3 mr-1 text-slate-400" />
                                            <span>{{ visit.customer.phone }}</span>
                                        </div>
                                    </div>
                                    <span v-else class="text-xs text-slate-400">N/A</span>
                                </td>

                                <td class="py-3 px-4">
                                    <div v-if="visit.property" class="space-y-0.5 max-w-xs">
                                        <Link :href="route('admin.properties.show', visit.property.id)" class="font-semibold text-slate-900 hover:text-primary-600 truncate block">
                                            {{ visit.property.title }}
                                        </Link>
                                        <span class="text-xs font-mono text-slate-400">{{ visit.property.property_code }}</span>
                                    </div>
                                    <span v-else class="text-xs text-slate-400">No Property</span>
                                </td>

                                <td class="py-3 px-4 text-xs">
                                    <span class="font-medium text-slate-800">{{ visit.assigned_agent?.name || 'Unassigned' }}</span>
                                </td>

                                <td class="py-3 px-4">
                                    <div class="space-y-1">
                                        <AppBadge :variant="getStatusBadge(visit.status).variant" size="sm">
                                            {{ getStatusBadge(visit.status).label }}
                                        </AppBadge>
                                        <div v-if="visit.interest_level" class="text-[11px] text-slate-500">
                                            Interest: <span class="font-semibold capitalize text-slate-700">{{ visit.interest_level }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button
                                            @click="openOutcomeModal(visit)"
                                            title="Record Outcome & Feedback"
                                            class="p-1.5 text-primary-600 hover:bg-primary-50 rounded-lg transition"
                                        >
                                            <MessageSquare class="w-4 h-4" />
                                        </button>

                                        <button
                                            v-if="visit.status === 'scheduled' || visit.status === 'confirmed'"
                                            @click="openRescheduleModal(visit)"
                                            title="Reschedule Visit"
                                            class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                        >
                                            <CalendarCheck class="w-4 h-4" />
                                        </button>

                                        <button
                                            v-if="visit.status !== 'cancelled' && visit.status !== 'completed'"
                                            @click="cancelVisit(visit.id)"
                                            title="Cancel Visit"
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition"
                                        >
                                            <XCircle class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="visits.data.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                    No site visits found matching the criteria.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="visits.data.length > 0" class="p-4 border-t border-slate-100">
                    <DataTablePagination :pagination="visits" />
                </div>
            </AppCard>
        </div>

        <!-- Schedule Visit Modal -->
        <AppModal :show="isScheduleOpen" @close="isScheduleOpen = false" max-width="md">
            <template #title>
                <div class="flex items-center space-x-2">
                    <Calendar class="w-5 h-5 text-primary-600" />
                    <span>Schedule Property Site Visit</span>
                </div>
            </template>

            <form @submit.prevent="submitSchedule" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Select Property *</label>
                    <select
                        v-model="scheduleForm.property_id"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option value="">Select Property</option>
                        <option v-for="p in properties" :key="p.id" :value="p.id">
                            {{ p.property_code }} - {{ p.title }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Customer / Buyer *</label>
                    <select
                        v-model="scheduleForm.customer_id"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option value="">Select Customer</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">
                            {{ c.name }} ({{ c.phone }})
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Assigned Agent *</label>
                    <select
                        v-model="scheduleForm.assigned_agent_id"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option value="">Select Agent</option>
                        <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Visit Date *</label>
                        <input
                            v-model="scheduleForm.scheduled_date"
                            type="date"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Visit Time *</label>
                        <input
                            v-model="scheduleForm.scheduled_time"
                            type="time"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Agent Preparation Notes</label>
                    <textarea
                        v-model="scheduleForm.agent_notes"
                        rows="2"
                        placeholder="Key points, directions, keys pickup..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isScheduleOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="scheduleForm.processing">Schedule Visit</AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Record Outcome & Feedback Modal -->
        <AppModal :show="isOutcomeOpen" @close="isOutcomeOpen = false" max-width="md">
            <template #title>
                <div class="flex items-center space-x-2">
                    <MessageSquare class="w-5 h-5 text-primary-600" />
                    <span>Record Visit Outcome & Feedback</span>
                </div>
            </template>

            <form @submit.prevent="submitOutcome" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Visit Status *</label>
                    <select
                        v-model="outcomeForm.status"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no_show">No Show</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Customer Interest Level</label>
                    <select
                        v-model="outcomeForm.interest_level"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="very_high">Very High (Wants to make offer)</option>
                        <option value="high">High (Interested, discussing terms)</option>
                        <option value="medium">Medium (Considering other options)</option>
                        <option value="low">Low (Did not like property)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Customer Feedback</label>
                    <textarea
                        v-model="outcomeForm.customer_feedback"
                        rows="2"
                        placeholder="What did the client like/dislike? Price feedback..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Next Action Required</label>
                    <input
                        v-model="outcomeForm.next_action"
                        type="text"
                        placeholder="e.g. Send draft agreement, schedule second tour..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    />
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isOutcomeOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="outcomeForm.processing">Save Outcome</AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Reschedule Modal -->
        <AppModal :show="isRescheduleOpen" @close="isRescheduleOpen = false" max-width="md">
            <template #title>
                <div class="flex items-center space-x-2">
                    <CalendarCheck class="w-5 h-5 text-indigo-600" />
                    <span>Reschedule Site Visit</span>
                </div>
            </template>

            <form @submit.prevent="submitReschedule" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">New Date *</label>
                        <input
                            v-model="rescheduleForm.scheduled_date"
                            type="date"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">New Time *</label>
                        <input
                            v-model="rescheduleForm.scheduled_time"
                            type="time"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Reschedule Reason / Notes</label>
                    <textarea
                        v-model="rescheduleForm.agent_notes"
                        rows="2"
                        placeholder="Reason for reschedule..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isRescheduleOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="rescheduleForm.processing">Reschedule Visit</AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
