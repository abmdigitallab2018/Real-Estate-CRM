<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, DollarSign, PieChart, CheckCircle, Clock } from 'lucide-vue-next';
import AppBadge from '@/Components/UI/AppBadge.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';
import AppModal from '@/Components/UI/AppModal.vue';

const props = defineProps({
    commissions: Object,
    rules: Array,
    stats: Object,
    agents: Array,
    deals: Array,
    bookings: Array,
    filters: Object,
});

const statusFilter = ref(props.filters.status || '');
const agentFilter = ref(props.filters.agent_id || '');

const applyFilters = () => {
    router.get(route('admin.commissions.index'), {
        status: statusFilter.value,
        agent_id: agentFilter.value,
    }, { preserveState: true, replace: true });
};

const approveModalOpen = ref(false);
const payModalOpen = ref(false);
const selectedCommission = ref(null);

const approveForm = useForm({});
const payForm = useForm({});

const confirmApprove = (commission) => {
    selectedCommission.value = commission;
    approveModalOpen.value = true;
};

const doApprove = () => {
    approveForm.post(route('admin.commissions.approve', selectedCommission.value.id), {
        onSuccess: () => approveModalOpen.value = false,
    });
};

const confirmPay = (commission) => {
    selectedCommission.value = commission;
    payModalOpen.value = true;
};

const doPay = () => {
    payForm.post(route('admin.commissions.pay', selectedCommission.value.id), {
        onSuccess: () => payModalOpen.value = false,
    });
};
</script>

<template>
    <AdminLayout title="Commissions">
        <Head title="Commissions" />

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Commissions & Payouts</h1>
                <p class="text-sm text-slate-500 mt-1">Track agent commissions, rules, and process payouts</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <DollarSign class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Expected</span>
                </div>
                <div class="text-2xl font-black text-slate-900">${{ Number(stats?.total_expected || 0).toLocaleString() }}</div>
            </div>
            
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                        <Clock class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Pending Approval</span>
                </div>
                <div class="text-2xl font-black text-slate-900">${{ Number(stats?.total_pending || 0).toLocaleString() }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <CheckCircle class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Paid</span>
                </div>
                <div class="text-2xl font-black text-slate-900">${{ Number(stats?.total_paid || 0).toLocaleString() }}</div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <select v-model="statusFilter" @change="applyFilters" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div>
                <select v-model="agentFilter" @change="applyFilters" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Agents</option>
                    <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Agent</th>
                            <th class="py-3 px-4">Source / Deal</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="comm in commissions.data" :key="comm.id" class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ comm.agent?.name }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div>{{ comm.deal ? comm.deal.title : (comm.booking ? 'Booking #' + comm.booking.id : 'Manual') }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">{{ comm.description }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">${{ Number(comm.amount).toLocaleString() }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <AppBadge :variant="comm.status === 'paid' ? 'success' : (comm.status === 'approved' ? 'primary' : 'warning')">
                                    {{ comm.status }}
                                </AppBadge>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button v-if="comm.status === 'pending'" @click="confirmApprove(comm)" class="px-3 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-bold transition">
                                    Approve
                                </button>
                                <button v-if="comm.status === 'approved'" @click="confirmPay(comm)" class="px-3 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-bold transition ml-2">
                                    Mark Paid
                                </button>
                            </td>
                        </tr>
                        <tr v-if="commissions.data.length === 0">
                            <td colspan="5" class="py-8 text-center text-slate-500">No commissions found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div v-if="commissions.total > 0" class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200">
            <span class="text-xs text-slate-500">Showing {{ commissions.from }} to {{ commissions.to }} of {{ commissions.total }} commissions</span>
            <DataTablePagination :pagination="commissions" />
        </div>

        <AppModal :show="approveModalOpen" title="Approve Commission" @close="approveModalOpen = false">
            <div class="p-1">
                <p class="text-sm text-slate-600 mb-4">Are you sure you want to approve this commission for <strong>{{ selectedCommission?.agent?.name }}</strong>?</p>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button @click="approveModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button @click="doApprove" :disabled="approveForm.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm">Approve</button>
                </div>
            </div>
        </AppModal>

        <AppModal :show="payModalOpen" title="Mark as Paid" @close="payModalOpen = false">
            <div class="p-1">
                <p class="text-sm text-slate-600 mb-4">Are you sure you want to mark this commission as paid for <strong>{{ selectedCommission?.agent?.name }}</strong>?</p>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button @click="payModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button @click="doPay" :disabled="payForm.processing" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm">Mark Paid</button>
                </div>
            </div>
        </AppModal>
    </AdminLayout>
</template>
