<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Plus, Check, Star } from 'lucide-vue-next';
import AppModal from '@/Components/UI/AppModal.vue';

const props = defineProps({
    plans: Array,
});

const formModalOpen = ref(false);

const form = useForm({
    name: '',
    description: '',
    price: '',
    billing_interval: 'monthly',
    max_agents: 5,
    max_properties: 50,
    max_storage_mb: 1024,
    features_input: '',
});

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    formModalOpen.value = true;
};

const submitForm = () => {
    const featuresArray = form.features_input
        ? form.features_input.split('\n').map(f => f.trim()).filter(f => f)
        : [];
        
    form.transform((data) => ({
        ...data,
        features: featuresArray,
    })).post(route('superadmin.plans.store'), {
        onSuccess: () => formModalOpen.value = false,
    });
};
</script>

<template>
    <SuperAdminLayout title="Subscription Plans">
        <Head title="Plans" />

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Subscription Plans</h1>
                <p class="text-sm text-slate-500 mt-1">Manage SaaS pricing and limits</p>
            </div>
            <button @click="openCreateModal" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-sm">
                <Plus class="w-4 h-4" />
                Add Plan
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="plan in plans" :key="plan.id" class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col relative">
                <div v-if="plan.name.toLowerCase().includes('pro')" class="absolute top-0 right-0 bg-gradient-to-r from-amber-400 to-amber-500 text-white text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-bl-lg shadow-sm flex items-center gap-1">
                    <Star class="w-3 h-3" /> Most Popular
                </div>
                <div class="p-6 border-b border-slate-100 text-center">
                    <h3 class="text-lg font-bold text-slate-900 mb-1">{{ plan.name }}</h3>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-3xl font-black text-slate-900">${{ plan.price }}</span>
                        <span class="text-sm font-medium text-slate-500">/{{ plan.billing_interval === 'yearly' ? 'yr' : 'mo' }}</span>
                    </div>
                </div>
                <div class="p-6 flex-grow">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-3">Limits</div>
                    <ul class="space-y-2 mb-6 text-sm text-slate-600 font-medium">
                        <li>{{ plan.max_agents }} Agents</li>
                        <li>{{ plan.max_properties }} Properties</li>
                        <li>{{ plan.max_storage_mb }} MB Storage</li>
                    </ul>
                    <div class="text-xs font-bold text-slate-400 uppercase mb-3">Features</div>
                    <ul class="space-y-3">
                        <li v-for="(feature, index) in plan.features || []" :key="index" class="flex items-start gap-2 text-sm text-slate-600">
                            <Check class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                            <span>{{ feature }}</span>
                        </li>
                    </ul>
                </div>
                <div class="p-6 pt-0 mt-auto">
                    <button class="w-full py-2.5 rounded-xl text-sm font-bold border-2 transition"
                            :class="plan.name.toLowerCase().includes('pro') ? 'bg-indigo-600 border-indigo-600 text-white hover:bg-indigo-700' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                        Edit Plan
                    </button>
                </div>
            </div>
        </div>

        <AppModal :show="formModalOpen" title="Add Plan" @close="formModalOpen = false" maxWidth="2xl">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Plan Name <span class="text-rose-500">*</span></label>
                        <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Price <span class="text-rose-500">*</span></label>
                        <input v-model="form.price" type="number" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                    </div>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                    <input v-model="form.description" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Billing Interval <span class="text-rose-500">*</span></label>
                        <select v-model="form.billing_interval" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Max Agents <span class="text-rose-500">*</span></label>
                        <input v-model="form.max_agents" type="number" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Max Properties <span class="text-rose-500">*</span></label>
                        <input v-model="form.max_properties" type="number" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Max Storage (MB) <span class="text-rose-500">*</span></label>
                        <input v-model="form.max_storage_mb" type="number" min="100" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Features (one per line)</label>
                    <textarea v-model="form.features_input" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Unlimited Users&#10;Advanced Reports&#10;API Access"></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="formModalOpen = false" class="px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-sm">Save Plan</button>
                </div>
            </form>
        </AppModal>
    </SuperAdminLayout>
</template>
