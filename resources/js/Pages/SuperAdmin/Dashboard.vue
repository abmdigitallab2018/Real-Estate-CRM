<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Users, Building, Activity, ShieldCheck, Database, CreditCard } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    recent_tenants: Array,
    recent_logs: Array,
});
</script>

<template>
    <SuperAdminLayout title="SaaS Dashboard">
        <Head title="SaaS Dashboard" />

        <div class="mb-6">
            <h1 class="text-2xl font-black text-slate-900">SaaS Overview</h1>
            <p class="text-sm text-slate-500 mt-1">Monitor the overall platform health, agencies, and subscriptions</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <Building class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Agencies</span>
                </div>
                <div class="text-2xl font-black text-slate-900">{{ stats?.total_tenants || 0 }}</div>
                <div class="text-[11px] text-emerald-600 font-bold mt-1">{{ stats?.active_tenants || 0 }} Active / {{ stats?.trial_tenants || 0 }} Trial</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <Users class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Users</span>
                </div>
                <div class="text-2xl font-black text-slate-900">{{ stats?.total_users || 0 }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                        <Database class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Properties</span>
                </div>
                <div class="text-2xl font-black text-slate-900">{{ stats?.total_properties || 0 }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <CreditCard class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Deals</span>
                </div>
                <div class="text-2xl font-black text-slate-900">{{ stats?.total_deals || 0 }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                        <Building class="w-5 h-5 text-indigo-500" />
                        Recent Agencies
                    </h2>
                    <Link :href="route('superadmin.tenants')" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">View All</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-slate-400 font-bold uppercase text-[10px] bg-slate-50">
                            <tr>
                                <th class="px-6 py-2">Agency</th>
                                <th class="px-6 py-2">Domain</th>
                                <th class="px-6 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="tenant in recent_tenants" :key="tenant.id" class="hover:bg-slate-50/50">
                                <td class="px-6 py-3 font-bold text-slate-900">{{ tenant.name }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ tenant.domain }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700': tenant.status === 'active',
                                            'bg-amber-100 text-amber-700': tenant.status === 'trial',
                                            'bg-rose-100 text-rose-700': tenant.status === 'suspended'
                                        }">
                                        {{ tenant.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!recent_tenants?.length">
                                <td colspan="3" class="px-6 py-8 text-center text-slate-500 text-xs">No agencies found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="font-bold text-slate-900 text-lg flex items-center gap-2">
                        <Activity class="w-5 h-5 text-slate-500" />
                        System Activity
                    </h2>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-slate-100">
                        <li v-for="log in recent_logs" :key="log.id" class="px-6 py-3 hover:bg-slate-50 transition">
                            <div class="text-sm font-bold text-slate-800">{{ log.action }}</div>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-[11px] text-slate-500">{{ log.tenant ? log.tenant.name : 'System' }}</span>
                                <span class="text-[10px] font-medium text-slate-400">{{ new Date(log.created_at).toLocaleString() }}</span>
                            </div>
                        </li>
                        <li v-if="!recent_logs?.length" class="px-6 py-8 text-center text-sm text-slate-500">
                            No recent activity logged.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>
