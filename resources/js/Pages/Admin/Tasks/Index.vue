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
    CheckCircle2,
    Clock,
    Plus,
    Calendar,
    PhoneCall,
    Users,
    Mail,
    MessageCircle,
    FileText,
    AlertTriangle,
    Trash2,
    Building2,
    User,
    CheckSquare,
    Square
} from 'lucide-vue-next';

const props = defineProps({
    tasks: Object,
    stats: Object,
    agents: Array,
    customers: Array,
    properties: Array,
    filters: Object,
});

const currentView = ref(props.filters?.view || 'pending');
const selectedType = ref(props.filters?.type || 'all');
const selectedPriority = ref(props.filters?.priority || 'all');
const selectedAgent = ref(props.filters?.agent_id || 'all');

const handleFilter = () => {
    router.get(route('admin.tasks.index'), {
        view: currentView.value,
        type: selectedType.value,
        priority: selectedPriority.value,
        agent_id: selectedAgent.value,
    }, { preserveState: true });
};

const setView = (v) => {
    currentView.value = v;
    handleFilter();
};

// Create Task Modal
const isCreateOpen = ref(false);
const taskForm = useForm({
    subject: '',
    activity_type: 'call',
    priority: 'medium',
    due_date: new Date().toISOString().split('T')[0],
    due_time: '14:00',
    assigned_to: props.agents?.[0]?.id || '',
    customer_id: '',
    property_id: '',
    description: '',
});

const openCreateModal = () => {
    taskForm.reset();
    taskForm.due_date = new Date().toISOString().split('T')[0];
    taskForm.assigned_to = props.agents?.[0]?.id || '';
    isCreateOpen.value = true;
};

const submitCreate = () => {
    taskForm.post(route('admin.tasks.store'), {
        onSuccess: () => {
            isCreateOpen.value = false;
            taskForm.reset();
        }
    });
};

const toggleTask = (task) => {
    router.post(route('admin.tasks.toggle', task.id), {}, { preserveScroll: true });
};

const deleteTask = (id) => {
    if (confirm('Delete this task?')) {
        router.delete(route('admin.tasks.destroy', id), { preserveScroll: true });
    }
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const isOverdue = (task) => {
    if (task.status === 'completed' || !task.due_date) return false;
    const due = new Date(task.due_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return due < today;
};

const getActivityIcon = (type) => {
    switch (type) {
        case 'call': return PhoneCall;
        case 'meeting': return Users;
        case 'email': return Mail;
        case 'whatsapp': return MessageCircle;
        case 'document_prep': return FileText;
        default: return Clock;
    }
};

const getPriorityBadge = (priority) => {
    switch (priority) {
        case 'urgent': return { variant: 'danger', label: 'Urgent' };
        case 'high': return { variant: 'warning', label: 'High' };
        case 'medium': return { variant: 'primary', label: 'Medium' };
        case 'low': return { variant: 'secondary', label: 'Low' };
        default: return { variant: 'secondary', label: priority };
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Follow-up Activities & Agenda" />

        <div class="space-y-6">
            <!-- Header -->
            <AdminPageHeader
                title="Follow-ups & Activities"
                description="Keep track of client follow-ups, calls, negotiations, and daily agent agendas."
            >
                <template #actions>
                    <AppButton variant="primary" @click="openCreateModal">
                        <Plus class="w-4 h-4 mr-2" />
                        New Activity
                    </AppButton>
                </template>
            </AdminPageHeader>

            <!-- Metrics Overview -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <AppCard class="p-4 border-l-4 border-primary-500">
                    <span class="text-xs text-slate-500 font-medium">Pending Tasks</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.pending }}</h3>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-indigo-500">
                    <span class="text-xs text-slate-500 font-medium">Due Today</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.today }}</h3>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-red-600 font-medium">Overdue Reminders</span>
                        <AlertTriangle v-if="stats.overdue > 0" class="w-4 h-4 text-red-500" />
                    </div>
                    <h3 class="text-xl font-bold text-red-700 mt-0.5">{{ stats.overdue }}</h3>
                </AppCard>

                <AppCard class="p-4 border-l-4 border-emerald-500">
                    <span class="text-xs text-slate-500 font-medium">Completed</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-0.5">{{ stats.completed }}</h3>
                </AppCard>
            </div>

            <!-- Tabs & Filters Bar -->
            <AppCard class="p-4">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <!-- Views Tabs -->
                    <div class="flex items-center space-x-1 bg-slate-100 p-1 rounded-xl">
                        <button
                            v-for="v in [
                                { id: 'pending', label: 'All Pending' },
                                { id: 'today', label: 'Today' },
                                { id: 'overdue', label: 'Overdue' },
                                { id: 'upcoming', label: 'Upcoming' },
                                { id: 'completed', label: 'Completed' }
                            ]"
                            :key="v.id"
                            @click="setView(v.id)"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                            :class="currentView === v.id ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        >
                            {{ v.label }}
                        </button>
                    </div>

                    <!-- Dropdowns -->
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model="selectedType"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Activity Types</option>
                            <option value="call">Call</option>
                            <option value="meeting">Meeting</option>
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="document_prep">Document Prep</option>
                        </select>

                        <select
                            v-model="selectedPriority"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Priorities</option>
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>

                        <select
                            v-model="selectedAgent"
                            @change="handleFilter"
                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">All Agents</option>
                            <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                        </select>
                    </div>
                </div>
            </AppCard>

            <!-- Tasks List Table -->
            <AppCard>
                <div class="divide-y divide-slate-100">
                    <div
                        v-for="task in tasks.data"
                        :key="task.id"
                        class="p-4 flex items-start sm:items-center justify-between gap-4 hover:bg-slate-50 transition"
                        :class="{ 'bg-red-50/30': isOverdue(task), 'opacity-60 bg-slate-50/50': task.status === 'completed' }"
                    >
                        <!-- Checkbox & Subject Info -->
                        <div class="flex items-start space-x-3 flex-1 min-w-0">
                            <button
                                @click="toggleTask(task)"
                                class="mt-0.5 flex-shrink-0 text-slate-400 hover:text-primary-600 transition"
                                :class="{ 'text-emerald-600': task.status === 'completed' }"
                            >
                                <CheckSquare v-if="task.status === 'completed'" class="w-5 h-5 text-emerald-600" />
                                <Square v-else class="w-5 h-5" />
                            </button>

                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="font-semibold text-sm text-slate-900"
                                        :class="{ 'line-through text-slate-500': task.status === 'completed' }"
                                    >
                                        {{ task.subject }}
                                    </span>
                                    <AppBadge :variant="getPriorityBadge(task.priority).variant" size="sm">
                                        {{ getPriorityBadge(task.priority).label }}
                                    </AppBadge>
                                    <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 capitalize font-medium">
                                        <component :is="getActivityIcon(task.activity_type)" class="w-3 h-3 mr-1 text-slate-500" />
                                        {{ task.activity_type.replace('_', ' ') }}
                                    </span>
                                </div>

                                <p v-if="task.description" class="text-xs text-slate-500 truncate">
                                    {{ task.description }}
                                </p>

                                <!-- Context tags -->
                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 pt-1">
                                    <span v-if="task.customer" class="flex items-center text-slate-700 font-medium">
                                        <User class="w-3 h-3 mr-1 text-slate-400" />
                                        {{ task.customer.name }}
                                    </span>
                                    <span v-if="task.property" class="flex items-center text-slate-600">
                                        <Building2 class="w-3 h-3 mr-1 text-slate-400" />
                                        {{ task.property.title }}
                                    </span>
                                    <span class="text-slate-400">Assigned: {{ task.assigned_user?.name || 'Unassigned' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Date & Actions -->
                        <div class="flex items-center space-x-4 flex-shrink-0 text-right">
                            <div>
                                <div
                                    class="text-xs font-semibold flex items-center justify-end"
                                    :class="isOverdue(task) ? 'text-red-600' : 'text-slate-700'"
                                >
                                    <Clock class="w-3.5 h-3.5 mr-1" />
                                    <span>{{ formatDate(task.due_date) }}</span>
                                    <span v-if="task.due_time" class="ml-1 text-slate-400 font-normal">({{ task.due_time }})</span>
                                </div>
                                <span v-if="isOverdue(task)" class="text-[10px] font-bold text-red-500 uppercase tracking-wider block">
                                    Overdue
                                </span>
                            </div>

                            <button
                                @click="deleteTask(task.id)"
                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                title="Delete Task"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div v-if="tasks.data.length === 0" class="p-8 text-center text-slate-400 text-sm">
                        No activities or tasks found in this view.
                    </div>
                </div>

                <div v-if="tasks.data.length > 0" class="p-4 border-t border-slate-100">
                    <DataTablePagination :pagination="tasks" />
                </div>
            </AppCard>
        </div>

        <!-- Add Activity Modal -->
        <AppModal :show="isCreateOpen" @close="isCreateOpen = false" max-width="md">
            <template #title>
                <div class="flex items-center space-x-2">
                    <Plus class="w-5 h-5 text-primary-600" />
                    <span>Schedule Activity / Follow-up</span>
                </div>
            </template>

            <form @submit.prevent="submitCreate" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Subject / Objective *</label>
                    <input
                        v-model="taskForm.subject"
                        type="text"
                        placeholder="e.g. Call client regarding price negotiation"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Activity Type *</label>
                        <select
                            v-model="taskForm.activity_type"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="call">Call</option>
                            <option value="meeting">Meeting</option>
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="document_prep">Document Prep</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Priority *</label>
                        <select
                            v-model="taskForm.priority"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Due Date *</label>
                        <input
                            v-model="taskForm.due_date"
                            type="date"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Due Time</label>
                        <input
                            v-model="taskForm.due_time"
                            type="time"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Assign To *</label>
                    <select
                        v-model="taskForm.assigned_to"
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        required
                    >
                        <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Customer (Optional)</label>
                        <select
                            v-model="taskForm.customer_id"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">None</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Property (Optional)</label>
                        <select
                            v-model="taskForm.property_id"
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">None</option>
                            <option v-for="p in properties" :key="p.id" :value="p.id">{{ p.property_code }} - {{ p.title }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Description / Notes</label>
                    <textarea
                        v-model="taskForm.description"
                        rows="2"
                        placeholder="Details of what needs to be discussed..."
                        class="w-full text-sm rounded-lg border-slate-300 focus:border-primary-500 focus:ring-primary-500"
                    ></textarea>
                </div>

                <div class="pt-3 flex justify-end space-x-3">
                    <AppButton variant="secondary" @click="isCreateOpen = false">Cancel</AppButton>
                    <AppButton variant="primary" type="submit" :loading="taskForm.processing">Save Activity</AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
