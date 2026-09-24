<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FileText, Upload, Download, Trash2, Search, Filter } from 'lucide-vue-next';
import AppBadge from '@/Components/UI/AppBadge.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';

const props = defineProps({
    documents: Object,
    stats: Object,
    properties: Array,
    customers: Array,
    bookings: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');

const applyFilters = () => {
    router.get(route('admin.documents.index'), {
        search: search.value,
        type: typeFilter.value,
    }, { preserveState: true, replace: true });
};

const uploadModalOpen = ref(false);
const deleteModalOpen = ref(false);
const documentToDelete = ref(null);

const form = useForm({
    title: '',
    type: '',
    file: null,
    documentable_type: '',
    documentable_id: '',
});

const openUploadModal = () => {
    form.reset();
    form.clearErrors();
    uploadModalOpen.value = true;
};

const submitUpload = () => {
    form.post(route('admin.documents.store'), {
        onSuccess: () => uploadModalOpen.value = false,
    });
};

const confirmDelete = (doc) => {
    documentToDelete.value = doc;
    deleteModalOpen.value = true;
};

const doDelete = () => {
    router.delete(route('admin.documents.destroy', documentToDelete.value.id), {
        onSuccess: () => deleteModalOpen.value = false,
    });
};
</script>

<template>
    <AdminLayout title="Documents">
        <Head title="Documents" />

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Documents Vault</h1>
                <p class="text-sm text-slate-500 mt-1">Manage contracts, deeds, ID proofs, and receipts</p>
            </div>
            <button @click="openUploadModal" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-sm shadow-indigo-600/20">
                <Upload class="w-4 h-4" />
                Upload Document
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="text-sm font-bold text-slate-500 uppercase">Total Files</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ stats?.total || 0 }}</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="text-sm font-bold text-slate-500 uppercase">Contracts</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ stats?.contracts || 0 }}</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="text-sm font-bold text-slate-500 uppercase">ID Proofs</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ stats?.id_proofs || 0 }}</div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
                <div class="text-sm font-bold text-slate-500 uppercase">Property Deeds</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ stats?.deeds || 0 }}</div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search documents..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <select v-model="typeFilter" @change="applyFilters" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Document Types</option>
                    <option value="contract">Contracts</option>
                    <option value="id_proof">ID Proofs</option>
                    <option value="deed">Property Deeds</option>
                    <option value="receipt">Receipts</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Document Title</th>
                            <th class="py-3 px-4">Type & Extension</th>
                            <th class="py-3 px-4">Linked To</th>
                            <th class="py-3 px-4">Uploaded By</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="doc in documents.data" :key="doc.id" class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                        <FileText class="w-4 h-4" />
                                    </div>
                                    <div class="font-bold text-slate-900">{{ doc.title }}</div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <AppBadge variant="secondary" class="uppercase">{{ doc.type }}</AppBadge>
                                <span class="text-slate-400 text-[10px] uppercase ml-2 font-bold">{{ doc.file_extension }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span v-if="doc.documentable_type">
                                    {{ doc.documentable_type.split('\\').pop() }} #{{ doc.documentable_id }}
                                </span>
                                <span v-else class="text-slate-400 italic">Unlinked</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ doc.uploader?.name || 'System' }}
                                <div class="text-[10px] text-slate-400">{{ new Date(doc.created_at).toLocaleDateString() }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a :href="doc.file_url" target="_blank" class="inline-flex p-1.5 rounded-md text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Download">
                                    <Download class="w-4 h-4" />
                                </a>
                                <button @click="confirmDelete(doc)" class="p-1.5 rounded-md text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition ml-1" title="Delete">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="documents.data.length === 0">
                            <td colspan="5" class="py-8 text-center text-slate-500">No documents found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div v-if="documents.total > 0" class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200">
            <span class="text-xs text-slate-500">Showing {{ documents.from }} to {{ documents.to }} of {{ documents.total }} documents</span>
            <DataTablePagination :pagination="documents" />
        </div>

        <AppModal :show="uploadModalOpen" title="Upload Document" @close="uploadModalOpen = false">
            <form @submit.prevent="submitUpload" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Document Title <span class="text-rose-500">*</span></label>
                    <input v-model="form.title" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                    <span v-if="form.errors.title" class="text-xs text-rose-500 mt-1">{{ form.errors.title }}</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Document Type <span class="text-rose-500">*</span></label>
                    <select v-model="form.type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required>
                        <option value="contract">Contract</option>
                        <option value="id_proof">ID Proof</option>
                        <option value="deed">Property Deed</option>
                        <option value="receipt">Receipt</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">File (PDF, JPG, PNG) <span class="text-rose-500">*</span></label>
                    <input type="file" @input="form.file = $event.target.files[0]" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-2">
                    <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm disabled:opacity-50">Upload</button>
                </div>
            </form>
        </AppModal>

        <AppConfirmDialog
            :show="deleteModalOpen"
            title="Delete Document?"
            :message="`Are you sure you want to delete '${documentToDelete?.title}'? This action cannot be undone.`"
            confirm-text="Delete"
            confirm-variant="danger"
            @confirm="doDelete"
            @close="deleteModalOpen = false"
        />
    </AdminLayout>
</template>
