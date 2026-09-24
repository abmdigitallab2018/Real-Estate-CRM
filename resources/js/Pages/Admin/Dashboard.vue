<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import {
    Building2,
    Users,
    Kanban,
    DollarSign,
    CalendarCheck,
    AlertCircle,
    CheckCircle2,
    Clock,
    Plus,
    ArrowUpRight,
    Sparkles,
    CheckSquare,
    ExternalLink
} from 'lucide-vue-next';

const props = defineProps({
    metrics: Object,
    todaySiteVisits: Array,
    overdueTasks: Array,
    todayTasks: Array,
    upcomingTasks: Array,
    agentPerformance: Array,
    leadSources: Object,
    recentLeads: Array,
    recentDeals: Array,
    currentPeriod: String,
});

const selectedPeriod = ref(props.currentPeriod || 'this_month');

const changePeriod = () => {
    router.get(route('admin.dashboard'), { period: selectedPeriod.value }, { preserveState: true });
};

const toggleTask = (taskId) => {
    router.post(route('admin.tasks.toggle', taskId), {}, { preserveScroll: true });
};
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard - Real Estate CRM" />

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Agency Operations Dashboard</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Centralized overview of property inventory, buyer leads, site visits, and sales pipelines.
                </p>
            </div>

            <!-- Date Filter & Quick Actions -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <select
                    v-model="selectedPeriod"
                    @change="changePeriod"
                    class="bg-white border border-slate-200 text-xs font-semibold rounded-xl px-3 py-2 text-slate-700 shadow-2xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                >
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="this_quarter">This Quarter</option>
                    <option value="this_year">This Year</option>
                </select>

                <Link :href="route('admin.properties.create')">
                    <AppButton size="sm" variant="primary">
                        <Plus class="w-3.5 h-3.5 mr-1" />
                        <span>Add Property</span>
                    </AppButton>
                </Link>

                <Link :href="route('admin.leads.create')">
                    <AppButton size="sm" variant="secondary">
                        <Plus class="w-3.5 h-3.5 mr-1" />
                        <span>Add Lead</span>
                    </AppButton>
                </Link>
            </div>
        </div>

        <!-- 4-Stat Cards Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Property Inventory -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Properties</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <Building2 class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900">{{ metrics.properties.total }}</span>
                    <span class="text-xs text-slate-500">listed</span>
                </div>
                <div class="mt-3 flex items-center gap-1.5 flex-wrap text-[11px]">
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">
                        {{ metrics.properties.available }} Available
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-bold border border-amber-100">
                        {{ metrics.properties.reserved }} Reserved
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 font-bold border border-purple-100">
                        {{ metrics.properties.sold }} Sold
                    </span>
                </div>
            </div>

            <!-- Leads Funnel -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Buyer & Seller Leads</span>
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                        <Users class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900">{{ metrics.leads.total }}</span>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                        {{ metrics.leads.conversion_rate }}% Conv.
                    </span>
                </div>
                <div class="mt-3 flex items-center gap-1.5 flex-wrap text-[11px]">
                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-bold border border-blue-100">
                        {{ metrics.leads.new }} New
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                        {{ metrics.leads.qualified }} Qualified
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">
                        {{ metrics.leads.converted }} Converted
                    </span>
                </div>
            </div>

            <!-- Pipeline Value -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Deals Value</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <Kanban class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900">
                        ${{ (metrics.deals.active_value / 1000).toLocaleString() }}k
                    </span>
                    <span class="text-xs text-slate-500 font-medium">({{ metrics.deals.active_count }} active)</span>
                </div>
                <div class="mt-3 text-[11px] text-slate-500 font-medium">
                    Closed Won: <span class="font-bold text-emerald-600">${{ (metrics.deals.closed_won_value / 1000).toLocaleString() }}k</span>
                </div>
            </div>

            <!-- Payments & Cash Collection -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Payments Collected</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <DollarSign class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-slate-900">
                        ${{ Number(metrics.financials.total_collected).toLocaleString() }}
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500">Commissions Paid:</span>
                    <span class="font-bold text-slate-800">${{ Number(metrics.financials.paid_commissions).toLocaleString() }}</span>
                </div>
            </div>
        </div>

        <!-- 2-Column Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Visits, Leaderboard, Recent Deals -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Today's Site Visits -->
                <AppCard>
                    <template #header>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <CalendarCheck class="w-4 h-4 text-indigo-600" />
                                <h3 class="font-extrabold text-sm text-slate-900">Today's Scheduled Site Visits</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                    {{ todaySiteVisits.length }}
                                </span>
                            </div>
                            <Link :href="route('admin.site-visits.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                <span>View Calendar</span>
                                <ArrowUpRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </template>

                    <div v-if="todaySiteVisits.length === 0" class="py-8 text-center text-xs text-slate-400">
                        No property visits scheduled for today.
                    </div>

                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="visit in todaySiteVisits"
                            :key="visit.id"
                            class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-10 rounded-xl bg-slate-100 flex flex-col items-center justify-center text-slate-700 font-extrabold text-xs">
                                    <span>{{ visit.scheduled_time ? visit.scheduled_time.substring(0, 5) : '10:00' }}</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-slate-900">{{ visit.property ? visit.property.title : 'Property Showing' }}</span>
                                        <AppBadge :variant="visit.status === 'confirmed' ? 'emerald' : (visit.status === 'completed' ? 'indigo' : 'amber')">
                                            {{ visit.status }}
                                        </AppBadge>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Buyer: <span class="font-semibold text-slate-700">{{ visit.customer ? visit.customer.name : 'Client' }}</span>
                                        &bull; Agent: <span class="font-semibold text-slate-700">{{ visit.assigned_agent ? visit.assigned_agent.name : 'Assigned' }}</span>
                                    </div>
                                </div>
                            </div>

                            <Link :href="route('admin.site-visits.index')">
                                <AppButton size="xs" variant="secondary">
                                    Update Outcome
                                </AppButton>
                            </Link>
                        </div>
                    </div>
                </AppCard>

                <!-- Agent Leaderboard (for managers & admins) -->
                <AppCard v-if="agentPerformance && agentPerformance.length > 0">
                    <template #header>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <Sparkles class="w-4 h-4 text-amber-500" />
                                <h3 class="font-extrabold text-sm text-slate-900">Agent Performance & Sales Volume</h3>
                            </div>
                            <Link :href="route('admin.agents.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                <span>All Agents</span>
                                <ArrowUpRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </template>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-100">
                                <tr>
                                    <th class="py-2.5 px-3">Agent</th>
                                    <th class="py-2.5 px-3 text-center">Leads</th>
                                    <th class="py-2.5 px-3 text-center">Visits Done</th>
                                    <th class="py-2.5 px-3 text-right">Closed Volume</th>
                                    <th class="py-2.5 px-3 text-right">Commission</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr v-for="agent in agentPerformance" :key="agent.id" class="hover:bg-slate-50/60">
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-900">{{ agent.name }}</div>
                                        <div class="text-[10px] text-slate-400 capitalize">{{ agent.role.replace('_', ' ') }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-700">{{ agent.leads_count }}</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-700">{{ agent.completed_visits }}</td>
                                    <td class="py-2.5 px-3 text-right font-extrabold text-emerald-600">
                                        ${{ agent.closed_volume.toLocaleString() }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-extrabold text-slate-900">
                                        ${{ agent.commission_earned.toLocaleString() }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </AppCard>

                <!-- Recent Deals in Pipeline -->
                <AppCard>
                    <template #header>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <Kanban class="w-4 h-4 text-indigo-600" />
                                <h3 class="font-extrabold text-sm text-slate-900">Recent Deals in Pipeline</h3>
                            </div>
                            <Link :href="route('admin.deals.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                                <span>Open Kanban</span>
                                <ArrowUpRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </template>

                    <div class="divide-y divide-slate-100">
                        <div
                            v-for="deal in recentDeals"
                            :key="deal.id"
                            class="py-3 flex items-center justify-between"
                        >
                            <div>
                                <div class="font-bold text-xs text-slate-900">{{ deal.title }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ deal.customer ? deal.customer.name : 'Client' }} &bull;
                                    Agent: {{ deal.assigned_agent ? deal.assigned_agent.name : 'Assigned' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-extrabold text-xs text-slate-900">${{ Number(deal.expected_value).toLocaleString() }}</div>
                                <AppBadge :variant="deal.stage?.is_won ? 'emerald' : 'indigo'" class="mt-0.5">
                                    {{ deal.stage ? deal.stage.name : 'Pipeline' }}
                                </AppBadge>
                            </div>
                        </div>
                    </div>
                </AppCard>
            </div>

            <!-- Right 1 Col: Overdue Follow-ups, Lead Sources, Quick Tools -->
            <div class="space-y-6">
                <!-- Overdue & Today's Follow-up Tasks -->
                <AppCard>
                    <template #header>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <AlertCircle class="w-4 h-4 text-rose-500" />
                                <h3 class="font-extrabold text-sm text-slate-900">Actionable Tasks & Follow-ups</h3>
                            </div>
                            <Link :href="route('admin.tasks.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                                View all
                            </Link>
                        </div>
                    </template>

                    <div v-if="overdueTasks.length === 0 && todayTasks.length === 0" class="py-6 text-center text-xs text-slate-400">
                        Great job! No pending or overdue tasks.
                    </div>

                    <div v-else class="space-y-3">
                        <!-- Overdue section -->
                        <div v-if="overdueTasks.length > 0">
                            <div class="text-[10px] font-black uppercase text-rose-500 tracking-wider mb-2 flex items-center gap-1">
                                <AlertCircle class="w-3 h-3" />
                                <span>Overdue Attention Required</span>
                            </div>
                            <div class="space-y-2">
                                <div
                                    v-for="task in overdueTasks"
                                    :key="task.id"
                                    class="p-2.5 rounded-xl bg-rose-50/60 border border-rose-100 flex items-start justify-between gap-2"
                                >
                                    <div class="flex items-start gap-2">
                                        <button
                                            type="button"
                                            @click="toggleTask(task.id)"
                                            class="mt-0.5 text-slate-400 hover:text-emerald-600 transition"
                                            title="Mark Complete"
                                        >
                                            <CheckSquare class="w-4 h-4" />
                                        </button>
                                        <div>
                                            <p class="text-xs font-bold text-slate-900 leading-snug">{{ task.subject }}</p>
                                            <p class="text-[10px] text-rose-600 font-semibold mt-0.5">Due: {{ task.due_date }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded bg-rose-100 text-rose-700">
                                        {{ task.priority }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Today section -->
                        <div v-if="todayTasks.length > 0" class="pt-2">
                            <div class="text-[10px] font-black uppercase text-indigo-600 tracking-wider mb-2 flex items-center gap-1">
                                <Clock class="w-3 h-3" />
                                <span>Scheduled For Today</span>
                            </div>
                            <div class="space-y-2">
                                <div
                                    v-for="task in todayTasks"
                                    :key="task.id"
                                    class="p-2.5 rounded-xl bg-indigo-50/40 border border-indigo-100/60 flex items-start justify-between gap-2"
                                >
                                    <div class="flex items-start gap-2">
                                        <button
                                            type="button"
                                            @click="toggleTask(task.id)"
                                            class="mt-0.5 text-slate-400 hover:text-emerald-600 transition"
                                            title="Mark Complete"
                                        >
                                            <CheckSquare class="w-4 h-4" />
                                        </button>
                                        <div>
                                            <p class="text-xs font-bold text-slate-900 leading-snug">{{ task.subject }}</p>
                                            <p class="text-[10px] text-slate-500 mt-0.5">{{ task.activity_type.toUpperCase() }} &bull; {{ task.due_time || 'All day' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </AppCard>

                <!-- Lead Source Breakdown -->
                <AppCard>
                    <template #header>
                        <h3 class="font-extrabold text-sm text-slate-900">Lead Sources Distribution</h3>
                    </template>
                    <div class="space-y-3">
                        <div
                            v-for="(count, src) in leadSources"
                            :key="src"
                            class="space-y-1"
                        >
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="capitalize text-slate-700">{{ String(src).replace('_', ' ') }}</span>
                                <span class="text-slate-900 font-bold">{{ count }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div
                                    class="h-full bg-indigo-600 rounded-full"
                                    :style="{ width: `${Math.min(100, (count / (metrics.leads.total || 1)) * 100)}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </AppCard>

                <!-- Quick Matching Banner -->
                <div class="bg-gradient-to-br from-indigo-900 to-indigo-950 p-5 rounded-2xl text-white shadow-md">
                    <div class="flex items-center gap-2 text-amber-400 text-xs font-bold mb-2">
                        <Sparkles class="w-4 h-4" />
                        <span>AI & Requirement Matcher</span>
                    </div>
                    <h4 class="font-extrabold text-base mb-1">Match Buyers with Listings</h4>
                    <p class="text-xs text-indigo-200 mb-4 leading-relaxed">
                        Instantly score available property inventory against registered buyer budgets, locations, and BHK requirements.
                    </p>
                    <Link :href="route('admin.matching.index')">
                        <AppButton size="sm" class="bg-white text-indigo-950 hover:bg-indigo-50 border-0 font-bold">
                            Run Property Matcher
                        </AppButton>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
