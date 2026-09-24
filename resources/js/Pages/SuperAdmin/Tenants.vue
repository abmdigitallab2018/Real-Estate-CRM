<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Plus, Building, Power, Search } from 'lucide-vue-next';
import AppBadge from '@/Components/UI/AppBadge.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';
import AppModal from '@/Components/UI/AppModal.vue';

const props = defineProps({
    tenants: Object,
    plans: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

const applyFilters = () => {
    router.get(route('superadmin.tenants'), {
        search: search.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true });
};

const formModalOpen = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    city: '',
    country: '',
    subscription_plan_id: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    formModalOpen.value = true;
};

const submitForm = () => {
    form.post(route('superadmin.tenants.store'), {
        onSuccess: () => formModalOpen.value = false,
    });
};

const toggleStatus = (tenant) => {
    router.post(route('superadmin.tenants.toggle', tenant.id), {}, { preserveScroll: true });
};
</script>

<template>
    <SuperAdminLayout title="Agencies & Tenants">
        <Head title="Agencies (Tenants)" />

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Agencies / Tenants</h1>
                <p class="text-sm text-slate-500 mt-1">Manage CRM instances, domains, and subscriptions</p>
            </div>
            <button @click="openCreateModal" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-sm">
                <Plus class="w-4 h-4" />
                New Agency
            </button>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search agencies..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <select v-model="statusFilter" @change="applyFilters" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="trial">Trial</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Agency Details</th>
                            <th class="py-3 px-4">Subdomain / Domain</th>
                            <th class="py-3 px-4">Plan / Users</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="tenant in tenants.data" :key="tenant.id" class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ tenant.name }}</div>
                                <div class="text-[10px] text-slate-500 mt-0.5">Created: {{ new Date(tenant.created_at).toLocaleDateString() }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <a :href="`http://${tenant.domain || tenant.slug}.localhost`" target="_blank" class="text-indigo-600 hover:underline">{{ tenant.domain || tenant.slug }}</a>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ tenant.plan?.name || 'Custom' }}</div>
                                <div class="text-[10px] text-slate-500">{{ tenant.users_count || 0 }} Active Users</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <AppBadge :variant="tenant.status === 'active' ? 'success' : (tenant.status === 'trial' ? 'warning' : 'danger')">
                                    {{ tenant.status }}
                                </AppBadge>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button @click="toggleStatus(tenant)" class="px-3 py-1 text-xs font-bold rounded-lg transition"
                                        :class="tenant.status === 'suspended' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-rose-50 text-rose-700 hover:bg-rose-100'">
                                    {{ tenant.status === 'suspended' ? 'Reactivate' : 'Suspend' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="tenants.data.length === 0">
                            <td colspan="5" class="py-8 text-center text-slate-500">No agencies found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div v-if="tenants.total > 0" class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200">
            <span class="text-xs text-slate-500">Showing {{ tenants.from }} to {{ tenants.to }} of {{ tenants.total }} agencies</span>
            <DataTablePagination :pagination="tenants" />
        </div>

        <AppModal :show="formModalOpen" title="Create New Agency" @close="formModalOpen = false" maxWidth="2xl">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Agency Name <span class="text-rose-500">*</span></label>
                        <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                        <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</span>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Agency Email <span class="text-rose-500">*</span></label>
                        <input v-model="form.email" type="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                        <span v-if="form.errors.email" class="text-xs text-rose-500 mt-1">{{ form.errors.email }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-3 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone</label>
                        <input v-model="form.phone" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="col-span-3 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                        <input v-model="form.city" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="col-span-3 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Country</label>
                        <input v-model="form.country" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Subscription Plan <span class="text-rose-500">*</span></label>
                    <select v-model="form.subscription_plan_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">Select Plan</option>
                        <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }} - ${{ plan.price }}/mo</option>
                    </select>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-2">
                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">Admin Account</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Admin Name <span class="text-rose-500">*</span></label>
                            <input v-model="form.admin_name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Admin Email <span class="text-rose-500">*</span></label>
                            <input v-model="form.admin_email" type="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Initial Password <span class="text-rose-500">*</span></label>
                            <input v-model="form.admin_password" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-2">
                    <button type="button" @click="formModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm disabled:opacity-50">Create Agency Instance</button>
                </div>
            </form>
        </AppModal>
    </SuperAdminLayout>
</template>
