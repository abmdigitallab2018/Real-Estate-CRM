<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Edit, Trash2, Search, User, Shield, Briefcase, TrendingUp } from 'lucide-vue-next';
import AppModal from '@/Components/UI/AppModal.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';

const props = defineProps({
    agents: Object,
    branches: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');
const branchFilter = ref(props.filters.branch_id || '');

const applyFilters = () => {
    router.get(route('admin.agents.index'), {
        search: search.value,
        role: roleFilter.value,
        branch_id: branchFilter.value,
    }, { preserveState: true, replace: true });
};

const formModalOpen = ref(false);
const deleteModalOpen = ref(false);
const editingAgent = ref(null);
const agentToDelete = ref(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: 'agent',
    branch_id: '',
    status: 'active',
    password: '',
    password_confirmation: '',
    license_number: '',
    commission_rate: 50,
    specialization: '',
    target_amount: '',
});

const openCreateModal = () => {
    editingAgent.value = null;
    form.reset();
    form.clearErrors();
    formModalOpen.value = true;
};

const openEditModal = (agent) => {
    editingAgent.value = agent;
    form.name = agent.name;
    form.email = agent.email;
    form.phone = agent.phone || '';
    form.role = agent.role;
    form.branch_id = agent.branch_id || '';
    form.status = agent.status;
    form.license_number = agent.license_number || '';
    form.commission_rate = agent.commission_rate || 50;
    form.specialization = agent.specialization || '';
    form.target_amount = agent.target_amount || '';
    form.password = '';
    form.password_confirmation = '';
    form.clearErrors();
    formModalOpen.value = true;
};

const submitForm = () => {
    if (editingAgent.value) {
        form.put(route('admin.agents.update', editingAgent.value.id), {
            onSuccess: () => formModalOpen.value = false,
        });
    } else {
        form.post(route('admin.agents.store'), {
            onSuccess: () => formModalOpen.value = false,
        });
    }
};

const confirmDelete = (agent) => {
    agentToDelete.value = agent;
    deleteModalOpen.value = true;
};

const doDelete = () => {
    router.delete(route('admin.agents.destroy', agentToDelete.value.id), {
        onSuccess: () => deleteModalOpen.value = false,
    });
};
</script>

<template>
    <AdminLayout title="Team & Agents">
        <Head title="Team & Agents" />

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Team & Agents</h1>
                <p class="text-sm text-slate-500 mt-1">Manage agency staff, brokers, and their commissions</p>
            </div>
            <button @click="openCreateModal" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-sm shadow-indigo-600/20">
                <Plus class="w-4 h-4" />
                Add Agent
            </button>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search by name, email..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <select v-model="roleFilter" @change="applyFilters" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="agent">Agent</option>
                </select>
            </div>
            <div>
                <select v-model="branchFilter" @change="applyFilters" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Branches</option>
                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Agent Details</th>
                            <th class="py-3 px-4">Branch & Role</th>
                            <th class="py-3 px-4">Performance</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="agent in agents.data" :key="agent.id" class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                        {{ agent.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ agent.name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ agent.email }}</div>
                                        <div class="text-[11px] text-slate-500">{{ agent.phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 text-slate-800 mb-1">
                                    <Shield class="w-3.5 h-3.5 text-slate-400" />
                                    <span class="capitalize">{{ agent.role }}</span>
                                </div>
                                <div v-if="agent.branch" class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                    <Briefcase class="w-3.5 h-3.5 text-slate-400" />
                                    <span>{{ agent.branch.name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div v-if="agent.commission_rate" class="text-slate-800">
                                    Rate: {{ agent.commission_rate }}%
                                </div>
                                <div v-if="agent.target_amount" class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                    <TrendingUp class="w-3 h-3" /> Target: ${{ Number(agent.target_amount).toLocaleString() }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <AppBadge :variant="agent.status === 'active' ? 'success' : 'secondary'">
                                    {{ agent.status }}
                                </AppBadge>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="openEditModal(agent)" class="p-1 rounded-md text-slate-500 hover:text-amber-600" title="Edit">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(agent)" class="p-1 rounded-md text-slate-500 hover:text-rose-600" title="Remove">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="agents.data.length === 0">
                            <td colspan="5" class="py-8 text-center text-slate-500">No agents found matching your criteria.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="agents.total > 0" class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200">
            <span class="text-xs text-slate-500">Showing {{ agents.from }} to {{ agents.to }} of {{ agents.total }} agents</span>
            <DataTablePagination :pagination="agents" />
        </div>

        <AppModal :show="formModalOpen" :title="editingAgent ? 'Edit Agent' : 'Add New Agent'" @close="formModalOpen = false" maxWidth="2xl">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                        <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</span>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email <span class="text-rose-500">*</span></label>
                        <input v-model="form.email" type="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                        <span v-if="form.errors.email" class="text-xs text-rose-500 mt-1">{{ form.errors.email }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone</label>
                        <input v-model="form.phone" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Role <span class="text-rose-500">*</span></label>
                        <select v-model="form.role" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required>
                            <option value="agent">Agent / Broker</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Agency Admin</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Branch</label>
                        <select v-model="form.branch_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Branch</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                        <select v-model="form.status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-2">
                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">Agent Settings</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Commission Rate (%)</label>
                            <input v-model="form.commission_rate" type="number" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sales Target Amount</label>
                            <input v-model="form.target_amount" type="number" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-2">
                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">Authentication ({{ editingAgent ? 'Leave blank to keep current' : 'Required' }})</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                            <input v-model="form.password" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" :required="!editingAgent" />
                            <span v-if="form.errors.password" class="text-xs text-rose-500 mt-1">{{ form.errors.password }}</span>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" :required="!editingAgent" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="formModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm disabled:opacity-50">
                        {{ editingAgent ? 'Update Agent' : 'Create Agent' }}
                    </button>
                </div>
            </form>
        </AppModal>

        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Remove Team Member?"
            :message="`Are you sure you want to remove '${agentToDelete?.name}' from the team?`"
            confirm-text="Remove Member"
            confirm-variant="danger"
            @confirm="doDelete"
            @close="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
