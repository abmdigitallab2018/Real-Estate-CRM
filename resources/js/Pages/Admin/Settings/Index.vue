<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Settings, Shield, Building, Save } from 'lucide-vue-next';

const props = defineProps({
    tenant: Object,
    auditLogs: Array,
});

const form = useForm({
    name: props.tenant?.name || '',
    email: props.tenant?.email || '',
    phone: props.tenant?.phone || '',
    address: props.tenant?.address || '',
    city: props.tenant?.city || '',
    country: props.tenant?.country || '',
    currency: props.tenant?.currency || 'USD',
    currency_symbol: props.tenant?.currency_symbol || '$',
    logo_url: props.tenant?.logo_url || '',
    settings: props.tenant?.settings || {},
});

const submitSettings = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout title="Agency Settings">
        <Head title="Settings" />

        <div class="mb-6">
            <h1 class="text-2xl font-black text-slate-900">Agency Settings</h1>
            <p class="text-sm text-slate-500 mt-1">Configure your agency details and branding</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Branding Settings -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <Building class="w-5 h-5" />
                        </div>
                        <h2 class="font-bold text-slate-900 text-lg">Agency Profile</h2>
                    </div>
                    <form @submit.prevent="submitSettings" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Agency Name <span class="text-rose-500">*</span></label>
                                <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Contact Email <span class="text-rose-500">*</span></label>
                                <input v-model="form.email" type="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Contact Phone</label>
                                <input v-model="form.phone" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Currency Code <span class="text-rose-500">*</span></label>
                                <input v-model="form.currency" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="USD, EUR, GBP" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Currency Symbol <span class="text-rose-500">*</span></label>
                                <input v-model="form.currency_symbol" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="$" required />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Logo URL</label>
                                <input v-model="form.logo_url" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="https://..." />
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                                <input v-model="form.city" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
                                <input v-model="form.country" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Full Address</label>
                                <textarea v-model="form.address" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" :disabled="form.processing" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-sm">
                            <Save class="w-4 h-4" />
                            Save Settings
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <!-- Audit Logs -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                        <div class="p-2 bg-slate-100 text-slate-600 rounded-lg">
                            <Shield class="w-5 h-5" />
                        </div>
                        <h2 class="font-bold text-slate-900 text-lg">Recent Activity</h2>
                    </div>
                    <div class="p-0">
                        <ul class="divide-y divide-slate-100">
                            <li v-for="log in auditLogs" :key="log.id" class="px-6 py-3 hover:bg-slate-50 transition">
                                <div class="text-sm font-bold text-slate-800">{{ log.action }}</div>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-[11px] text-slate-500">{{ log.user?.name || 'System' }}</span>
                                    <span class="text-[10px] font-medium text-slate-400">{{ new Date(log.created_at).toLocaleString() }}</span>
                                </div>
                            </li>
                            <li v-if="!auditLogs?.length" class="px-6 py-4 text-center text-sm text-slate-500">
                                No recent activity logged.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
