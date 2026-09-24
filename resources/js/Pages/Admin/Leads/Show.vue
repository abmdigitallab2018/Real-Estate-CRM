<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import {
    Users,
    Phone,
    Mail,
    MapPin,
    DollarSign,
    CheckCircle,
    CalendarCheck,
    CheckSquare,
    Kanban,
    Sparkles,
    Edit,
    ArrowLeft,
    Building2,
    Clock,
    Flame
} from 'lucide-vue-next';

const props = defineProps({
    lead: Object,
    agents: Array,
    pipelines: Array,
});

// Convert Modal
const convertModalOpen = ref(false);
const convertForm = useForm({
    create_deal: true,
    deal_title: `${props.lead.name} - Deal`,
    pipeline_id: props.pipelines[0]?.id || '',
    stage_id: props.pipelines[0]?.stages[0]?.id || '',
    expected_value: props.lead.budget_max || 500000,
    expected_close_date: new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0],
});

const submitConvert = () => {
    convertForm.post(route('admin.leads.convert', props.lead.id), {
        onSuccess: () => {
            convertModalOpen.value = false;
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Lead: ${lead.name}`" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-1.5 font-medium">
                    <Link :href="route('admin.leads.index')" class="hover:text-slate-700">Leads</Link>
                    <span>/</span>
                    <span class="font-semibold text-slate-600">{{ lead.name }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ lead.name }}</h1>
                    <AppBadge variant="indigo" class="capitalize">{{ lead.lead_type }}</AppBadge>
                    <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200">
                        <Flame class="w-3.5 h-3.5 text-amber-500" />
                        <span>Score: {{ lead.score }}/100</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    v-if="lead.status !== 'converted'"
                    type="button"
                    @click="convertModalOpen = true"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5"
                >
                    <CheckCircle class="w-4 h-4" />
                    <span>Convert to Customer & Deal</span>
                </button>

                <Link :href="route('admin.leads.edit', lead.id)">
                    <AppButton size="sm" variant="secondary">
                        <Edit class="w-3.5 h-3.5 mr-1" />
                        <span>Edit</span>
                    </AppButton>
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Details, Requirements, Linked Property, Site Visits -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Requirements Card -->
                <AppCard title="Buyer / Lead Requirements">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                            <span class="text-slate-400 font-bold block uppercase text-[10px]">Budget Range</span>
                            <span class="text-base font-black text-slate-900 mt-1 block">
                                ${{ Number(lead.budget_min || 0).toLocaleString() }} - ${{ Number(lead.budget_max || 0).toLocaleString() }}
                            </span>
                        </div>

                        <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                            <span class="text-slate-400 font-bold block uppercase text-[10px]">Preferred Location</span>
                            <span class="text-sm font-extrabold text-slate-900 mt-1 block">
                                {{ lead.preferred_location || 'Any location in city' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <span class="text-xs font-bold text-slate-700 block mb-1">Requirement Notes:</span>
                        <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ lead.requirements || 'No specific notes recorded.' }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Want to find matching properties for this lead?</span>
                        <Link :href="route('admin.matching.index', { lead_id: lead.id })">
                            <AppButton size="xs" variant="primary">
                                <Sparkles class="w-3.5 h-3.5 mr-1" />
                                <span>Find Matching Properties</span>
                            </AppButton>
                        </Link>
                    </div>
                </AppCard>

                <!-- Linked Property Card (if inquiry was on specific listing) -->
                <AppCard v-if="lead.property" title="Inquired Property Listing">
                    <div class="flex items-center gap-4">
                        <img
                            :src="lead.property.featured_image || 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=400&q=80'"
                            class="w-20 h-16 rounded-xl object-cover border border-slate-200"
                        />
                        <div class="flex-1">
                            <span class="text-[10px] font-black uppercase text-indigo-600">{{ lead.property.property_code }}</span>
                            <Link :href="route('admin.properties.show', lead.property.id)" class="font-extrabold text-sm text-slate-900 hover:text-indigo-600 block">
                                {{ lead.property.title }}
                            </Link>
                            <span class="text-xs text-slate-500">${{ Number(lead.property.price).toLocaleString() }} &bull; {{ lead.property.city }}</span>
                        </div>
                    </div>
                </AppCard>

                <!-- Site Visits -->
                <AppCard title="Scheduled & Past Site Visits">
                    <div v-if="lead.site_visits && lead.site_visits.length > 0" class="divide-y divide-slate-100 text-xs">
                        <div v-for="visit in lead.site_visits" :key="visit.id" class="py-3 flex items-center justify-between">
                            <div>
                                <span class="font-bold text-slate-900">{{ visit.visit_code }} - {{ visit.scheduled_date }} at {{ visit.scheduled_time }}</span>
                                <span class="text-slate-500 text-[11px] block">{{ visit.property ? visit.property.title : 'Property Showing' }}</span>
                            </div>
                            <AppBadge :variant="visit.status === 'completed' ? 'emerald' : 'indigo'">{{ visit.status }}</AppBadge>
                        </div>
                    </div>
                    <p v-else class="text-xs text-slate-400 py-3">No site visits scheduled yet.</p>
                </AppCard>
            </div>

            <!-- Right 1 Col: Contact Info & Assignment -->
            <div class="space-y-6">
                <AppCard title="Contact Card">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-2 text-slate-700">
                            <Phone class="w-4 h-4 text-slate-400 flex-shrink-0" />
                            <a :href="`tel:${lead.phone}`" class="font-bold text-indigo-600 hover:underline">{{ lead.phone }}</a>
                        </div>

                        <div class="flex items-center gap-2 text-slate-700">
                            <Mail class="w-4 h-4 text-slate-400 flex-shrink-0" />
                            <a v-if="lead.email" :href="`mailto:${lead.email}`" class="text-slate-800 hover:underline">{{ lead.email }}</a>
                            <span v-else class="text-slate-400">No email</span>
                        </div>

                        <div class="pt-3 border-t border-slate-100 divide-y divide-slate-100">
                            <div class="py-2 flex justify-between">
                                <span class="text-slate-500">Source:</span>
                                <span class="font-bold capitalize">{{ lead.source.replace('_', ' ') }}</span>
                            </div>
                            <div class="py-2 flex justify-between">
                                <span class="text-slate-500">Priority:</span>
                                <span class="font-bold uppercase text-indigo-600">{{ lead.priority }}</span>
                            </div>
                            <div class="py-2 flex justify-between">
                                <span class="text-slate-500">Assigned Agent:</span>
                                <span class="font-bold text-slate-900">{{ lead.assigned_agent ? lead.assigned_agent.name : 'Unassigned' }}</span>
                            </div>
                            <div class="py-2 flex justify-between">
                                <span class="text-slate-500">Branch Office:</span>
                                <span class="font-bold text-slate-900">{{ lead.branch ? lead.branch.name : 'Main HQ' }}</span>
                            </div>
                            <div class="py-2 flex justify-between">
                                <span class="text-slate-500">Registered:</span>
                                <span class="font-medium text-slate-500">{{ lead.created_at ? lead.created_at.substring(0, 10) : '' }}</span>
                            </div>
                        </div>
                    </div>
                </AppCard>

                <!-- Linked Customer / Deals -->
                <AppCard v-if="lead.customer" title="Linked Customer Record">
                    <div class="text-xs">
                        <Link :href="route('admin.customers.show', lead.customer.id)" class="font-bold text-indigo-600 hover:underline block text-sm">
                            {{ lead.customer.name }} (Profile #{{ lead.customer.id }})
                        </Link>
                        <p class="text-[11px] text-slate-500 mt-1">This lead has graduated to an active customer.</p>
                    </div>
                </AppCard>
            </div>
        </div>

        <!-- Convert Lead Modal -->
        <AppModal
            :show="convertModalOpen"
            title="Convert Lead into Opportunity & Customer"
            @close="convertModalOpen = false"
        >
            <form @submit.prevent="submitConvert" class="space-y-4">
                <p class="text-xs text-slate-600">
                    Graduating <strong class="text-slate-900">{{ lead.name }}</strong> will create a permanent customer file and launch an active opportunity in your sales pipeline.
                </p>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deal Title *</label>
                    <input v-model="convertForm.deal_title" type="text" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pipeline</label>
                        <select v-model="convertForm.pipeline_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option v-for="pl in pipelines" :key="pl.id" :value="pl.id">{{ pl.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Expected Value ($)</label>
                        <input v-model="convertForm.expected_value" type="number" step="0.01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <AppButton size="sm" variant="secondary" type="button" @click="convertModalOpen = false">Cancel</AppButton>
                    <AppButton size="sm" variant="primary" type="submit" :disabled="convertForm.processing">
                        Confirm Conversion
                    </AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
