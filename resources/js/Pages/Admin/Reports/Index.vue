<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { BarChart, PieChart, TrendingUp, Users } from 'lucide-vue-next';

const props = defineProps({
    propertyTypeStats: Array,
    propertyStatusStats: Array,
    leadSourceStats: Array,
    agentLeaderboard: Array,
    monthlyCollections: Array,
});
</script>

<template>
    <AdminLayout title="Reports & Analytics">
        <Head title="Reports & Analytics" />

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Reports & Analytics</h1>
                <p class="text-sm text-slate-500 mt-1">Agency performance, revenue, and property statistics</p>
            </div>
            <a :href="route('admin.reports.export')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-sm">
                Export Data
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-2 mb-4 text-slate-800 font-bold text-lg">
                    <PieChart class="w-5 h-5 text-indigo-500" />
                    Properties by Status
                </div>
                <div class="space-y-4">
                    <div v-for="stat in propertyStatusStats" :key="stat.status" class="flex justify-between items-center">
                        <span class="text-sm text-slate-600 capitalize font-medium">{{ stat.status }}</span>
                        <span class="text-sm font-bold bg-slate-100 px-3 py-1 rounded-full text-slate-800">{{ stat.total }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-2 mb-4 text-slate-800 font-bold text-lg">
                    <BarChart class="w-5 h-5 text-emerald-500" />
                    Lead Sources
                </div>
                <div class="space-y-4">
                    <div v-for="stat in leadSourceStats" :key="stat.source" class="flex justify-between items-center">
                        <span class="text-sm text-slate-600 capitalize font-medium">{{ stat.source || 'Direct/Other' }}</span>
                        <span class="text-sm font-bold bg-slate-100 px-3 py-1 rounded-full text-slate-800">{{ stat.total }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-2 mb-4 text-slate-800 font-bold text-lg">
                    <Users class="w-5 h-5 text-amber-500" />
                    Agent Leaderboard (Deals)
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-slate-400 font-bold uppercase text-[10px] border-b border-slate-100">
                            <tr>
                                <th class="pb-2">Agent Name</th>
                                <th class="pb-2 text-right">Deals Won</th>
                                <th class="pb-2 text-right">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="agent in agentLeaderboard" :key="agent.id">
                                <td class="py-3 font-bold text-slate-900">{{ agent.name }}</td>
                                <td class="py-3 text-right text-slate-600">{{ agent.deals_won_count }}</td>
                                <td class="py-3 text-right font-bold text-emerald-600">${{ Number(agent.total_revenue).toLocaleString() }}</td>
                            </tr>
                            <tr v-if="!agentLeaderboard.length">
                                <td colspan="3" class="py-4 text-center text-slate-500 text-xs">No data available yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-2 mb-4 text-slate-800 font-bold text-lg">
                    <TrendingUp class="w-5 h-5 text-rose-500" />
                    Monthly Collections
                </div>
                <div class="space-y-4">
                    <div v-for="month in monthlyCollections" :key="month.month" class="flex justify-between items-center">
                        <span class="text-sm text-slate-600 font-medium">{{ month.month }}</span>
                        <span class="text-sm font-bold text-slate-900">${{ Number(month.total).toLocaleString() }}</span>
                    </div>
                    <div v-if="!monthlyCollections.length" class="text-center text-slate-500 text-xs py-4">
                        No payment collections recorded.
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
