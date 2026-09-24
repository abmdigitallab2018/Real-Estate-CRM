<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';
import {
    Plus,
    Search,
    Phone,
    Mail,
    UserCheck,
    Eye,
    Edit,
    Trash2,
    Building2,
    Users
} from 'lucide-vue-next';

const props = defineProps({
    customers: Object,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || 'all');
const status = ref(props.filters.status || 'all');

const applyFilters = () => {
    router.get(
        route('admin.customers.index'),
        {
            search: search.value || undefined,
            type: type.value !== 'all' ? type.value : undefined,
            status: status.value !== 'all' ? status.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

const deleteModalOpen = ref(false);
const customerToDelete = ref(null);

const confirmDelete = (cust) => {
    customerToDelete.value = cust;
    deleteModalOpen.value = true;
};

const doDelete = () => {
    if (!customerToDelete.value) return;
    router.delete(route('admin.customers.destroy', customerToDelete.value.id), {
        onSuccess: () => {
            deleteModalOpen.value = false;
            customerToDelete.value = null;
        }
    });
};

const getTypeBadge = (tp) => {
    switch (tp) {
        case 'buyer': return { variant: 'indigo', label: 'Buyer' };
        case 'seller': return { variant: 'emerald', label: 'Seller' };
        case 'landlord': return { variant: 'amber', label: 'Landlord' };
        case 'tenant': return { variant: 'purple', label: 'Tenant' };
        case 'investor': return { variant: 'rose', label: 'Investor' };
        default: return { variant: 'slate', label: tp };
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Customers & Property Owners - Real Estate CRM" />

        <AdminPageHeader
            title="Customers & Property Owners"
            description="Manage buyer, seller, landlord, tenant, and investor profiles with complete property matching and deals history."
        >
            <template #actions>
                <Link :href="route('admin.customers.create')">
                    <AppButton size="sm" variant="primary">
                        <Plus class="w-4 h-4 mr-1" />
                        <span>Add New Customer</span>
                    </AppButton>
                </Link>
            </template>
        </AdminPageHeader>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
            <button
                type="button"
                @click="type = 'all'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', type === 'all' ? 'bg-white border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-slate-400 uppercase">All Clients</span>
                <span class="block text-xl font-black text-slate-900 mt-1">{{ stats.total }}</span>
            </button>

            <button
                type="button"
                @click="type = 'buyer'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', type === 'buyer' ? 'bg-indigo-50/40 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-indigo-600 uppercase">Buyers</span>
                <span class="block text-xl font-black text-indigo-700 mt-1">{{ stats.buyers }}</span>
            </button>

            <button
                type="button"
                @click="type = 'seller'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', type === 'seller' ? 'bg-emerald-50/40 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-emerald-600 uppercase">Sellers</span>
                <span class="block text-xl font-black text-emerald-700 mt-1">{{ stats.sellers }}</span>
            </button>

            <button
                type="button"
                @click="type = 'tenant'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', type === 'tenant' ? 'bg-purple-50/40 border-purple-500 ring-2 ring-purple-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-purple-600 uppercase">Tenants</span>
                <span class="block text-xl font-black text-purple-700 mt-1">{{ stats.tenants }}</span>
            </button>

            <button
                type="button"
                @click="type = 'investor'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', type === 'investor' ? 'bg-rose-50/40 border-rose-500 ring-2 ring-rose-500/20' : 'bg-white/80 border-slate-200']"
            >
                <span class="block text-[10px] font-bold text-rose-600 uppercase">Investors</span>
                <span class="block text-xl font-black text-rose-700 mt-1">{{ stats.investors }}</span>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs mb-6">
            <div class="relative">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                <input
                    v-model="search"
                    @keyup.enter="applyFilters"
                    type="text"
                    placeholder="Search by customer name, phone, email, company, or city..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                />
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Customer Name</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Contact Details</th>
                            <th class="py-3 px-4">Preferences / Budget</th>
                            <th class="py-3 px-4">Assigned Agent</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="cust in customers.data" :key="cust.id" class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <Link :href="route('admin.customers.show', cust.id)" class="font-bold text-slate-900 hover:text-indigo-600 transition block">
                                    {{ cust.name }}
                                </Link>
                                <span v-if="cust.company_name" class="text-[11px] text-slate-400 block">{{ cust.company_name }}</span>
                            </td>

                            <td class="py-3.5 px-4">
                                <AppBadge :variant="getTypeBadge(cust.customer_type).variant">
                                    {{ getTypeBadge(cust.customer_type).label }}
                                </AppBadge>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 text-slate-800">
                                    <Phone class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ cust.phone }}</span>
                                </div>
                                <div v-if="cust.email" class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                                    <Mail class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ cust.email }}</span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-slate-600">
                                <div v-if="cust.preferences && cust.preferences.max_budget">
                                    <span class="font-bold text-slate-900">Up to ${{ Number(cust.preferences.max_budget).toLocaleString() }}</span>
                                </div>
                                <span v-else class="text-slate-400">No preference set</span>
                            </td>

                            <td class="py-3.5 px-4 text-slate-700 font-semibold">
                                {{ cust.assigned_agent ? cust.assigned_agent.name : 'Unassigned' }}
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('admin.customers.show', cust.id)" class="p-1 rounded-md text-slate-500 hover:text-indigo-600" title="360 Profile">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="route('admin.customers.edit', cust.id)" class="p-1 rounded-md text-slate-500 hover:text-amber-600" title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(cust)" class="p-1 rounded-md text-slate-500 hover:text-rose-600" title="Archive">
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
                Showing {{ customers.from || 0 }} to {{ customers.to || 0 }} of {{ customers.total }} customers
            </span>
            <DataTablePagination :pagination="customers" @page="applyFilters" />
        </div>

        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Archive Customer Profile?"
            :message="`Are you sure you want to archive profile for '${customerToDelete?.name}'?`"
            confirm-text="Archive Customer"
            confirm-variant="danger"
            @confirm="doDelete"
            @close="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
