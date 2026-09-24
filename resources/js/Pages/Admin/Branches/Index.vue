<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Edit, Trash2, Building, Phone, Mail, MapPin } from 'lucide-vue-next';
import AppModal from '@/Components/UI/AppModal.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';

const props = defineProps({
    branches: Array,
});

const formModalOpen = ref(false);
const deleteModalOpen = ref(false);
const editingBranch = ref(null);
const branchToDelete = ref(null);

const form = useForm({
    name: '',
    code: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    is_main: false,
    status: 'active',
});

const openCreateModal = () => {
    editingBranch.value = null;
    form.reset();
    form.clearErrors();
    formModalOpen.value = true;
};

const openEditModal = (branch) => {
    editingBranch.value = branch;
    form.name = branch.name;
    form.code = branch.code || '';
    form.phone = branch.phone || '';
    form.email = branch.email || '';
    form.address = branch.address || '';
    form.city = branch.city || '';
    form.is_main = branch.is_main == 1;
    form.status = branch.status;
    form.clearErrors();
    formModalOpen.value = true;
};

const submitForm = () => {
    if (editingBranch.value) {
        form.put(route('admin.branches.update', editingBranch.value.id), {
            onSuccess: () => formModalOpen.value = false,
        });
    } else {
        form.post(route('admin.branches.store'), {
            onSuccess: () => formModalOpen.value = false,
        });
    }
};

const confirmDelete = (branch) => {
    branchToDelete.value = branch;
    deleteModalOpen.value = true;
};

const doDelete = () => {
    router.delete(route('admin.branches.destroy', branchToDelete.value.id), {
        onSuccess: () => deleteModalOpen.value = false,
    });
};
</script>

<template>
    <AdminLayout title="Branches">
        <Head title="Branches" />

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Branch Offices</h1>
                <p class="text-sm text-slate-500 mt-1">Manage agency locations and regional offices</p>
            </div>
            <button @click="openCreateModal" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-sm shadow-indigo-600/20">
                <Plus class="w-4 h-4" />
                Add Branch
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="branch in branches" :key="branch.id" class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5 relative group">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-indigo-50 text-indigo-600">
                            <Building class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 flex items-center gap-2">
                                {{ branch.name }}
                                <span v-if="branch.is_main" class="bg-indigo-100 text-indigo-700 text-[9px] uppercase font-black px-1.5 py-0.5 rounded">Main</span>
                            </h3>
                            <p v-if="branch.code" class="text-xs text-slate-500 font-medium">Code: {{ branch.code }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center opacity-0 group-hover:opacity-100 transition">
                        <button @click="openEditModal(branch)" class="p-1.5 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-amber-50">
                            <Edit class="w-4 h-4" />
                        </button>
                        <button @click="confirmDelete(branch)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50" :disabled="branch.is_main">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <div class="space-y-2 mb-4 text-sm text-slate-600">
                    <div v-if="branch.email" class="flex items-center gap-2">
                        <Mail class="w-4 h-4 text-slate-400" />
                        <span>{{ branch.email }}</span>
                    </div>
                    <div v-if="branch.phone" class="flex items-center gap-2">
                        <Phone class="w-4 h-4 text-slate-400" />
                        <span>{{ branch.phone }}</span>
                    </div>
                    <div v-if="branch.city || branch.address" class="flex items-start gap-2">
                        <MapPin class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                        <span>{{ branch.address }} {{ branch.city }}</span>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-slate-100 mt-auto">
                    <div class="text-center">
                        <span class="block text-xl font-black text-slate-800">{{ branch.users_count || 0 }}</span>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Agents</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-xl font-black text-slate-800">{{ branch.properties_count || 0 }}</span>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Properties</span>
                    </div>
                    <div class="ml-auto flex items-end">
                        <AppBadge :variant="branch.status === 'active' ? 'success' : 'secondary'">
                            {{ branch.status }}
                        </AppBadge>
                    </div>
                </div>
            </div>
            
            <div v-if="!branches.length" class="col-span-full bg-white rounded-2xl border border-slate-200 border-dashed p-12 text-center">
                <Building class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                <h3 class="text-lg font-bold text-slate-900 mb-1">No branches added</h3>
                <p class="text-sm text-slate-500 mb-4">Add your main office or regional branches to start assigning properties and agents.</p>
                <button @click="openCreateModal" class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-4 py-2 rounded-xl text-sm font-bold transition">
                    <Plus class="w-4 h-4" />
                    Add First Branch
                </button>
            </div>
        </div>

        <AppModal :show="formModalOpen" :title="editingBranch ? 'Edit Branch' : 'Add New Branch'" @close="formModalOpen = false">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Branch Name <span class="text-rose-500">*</span></label>
                        <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white" required />
                        <span v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</span>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Branch Code</label>
                        <input v-model="form.code" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone</label>
                        <input v-model="form.phone" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                    <input v-model="form.city" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                </div>

                <div class="col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Address</label>
                    <textarea v-model="form.address" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white"></textarea>
                </div>

                <div class="flex gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input v-model="form.is_main" type="checkbox" class="rounded text-indigo-600 border-slate-300 focus:ring-indigo-500" />
                        <span class="text-sm font-medium text-slate-700">Main Office</span>
                    </label>

                    <label v-if="editingBranch" class="flex items-center gap-2 cursor-pointer">
                        <input v-model="form.status" type="checkbox" true-value="active" false-value="inactive" class="rounded text-indigo-600 border-slate-300 focus:ring-indigo-500" />
                        <span class="text-sm font-medium text-slate-700">Active</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="formModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm disabled:opacity-50">
                        {{ editingBranch ? 'Update Branch' : 'Create Branch' }}
                    </button>
                </div>
            </form>
        </AppModal>

        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Remove Branch?"
            :message="`Are you sure you want to remove '${branchToDelete?.name}'? This action cannot be undone.`"
            confirm-text="Delete Branch"
            confirm-variant="danger"
            @confirm="doDelete"
            @close="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
