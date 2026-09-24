<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    lead: Object,
    agents: Array,
    branches: Array,
    properties: Array,
});

const form = useForm({
    name: props.lead.name,
    phone: props.lead.phone,
    email: props.lead.email || '',
    lead_type: props.lead.lead_type,
    source: props.lead.source,
    status: props.lead.status,
    priority: props.lead.priority,
    score: props.lead.score,
    budget_min: props.lead.budget_min || '',
    budget_max: props.lead.budget_max || '',
    preferred_location: props.lead.preferred_location || '',
    property_type: props.lead.property_type || 'apartment',
    requirements: props.lead.requirements || '',
    assigned_agent_id: props.lead.assigned_agent_id || '',
    branch_id: props.lead.branch_id || '',
    property_id: props.lead.property_id || '',
    tags: props.lead.tags || [],
    lost_reason: props.lead.lost_reason || '',
});

const submit = () => {
    form.put(route('admin.leads.update', props.lead.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit Lead: ${lead.name}`" />

        <AdminPageHeader
            :title="`Edit Lead: ${lead.name}`"
            description="Update lead contact details, stage, and requirements."
            :breadcrumbs="[
                { label: 'Leads', href: route('admin.leads.index') },
                { label: lead.name, href: route('admin.leads.show', lead.id) },
                { label: 'Edit' }
            ]"
        >
            <template #actions>
                <Link :href="route('admin.leads.show', lead.id)">
                    <AppButton size="sm" variant="secondary">
                        <ArrowLeft class="w-3.5 h-3.5 mr-1" />
                        <span>Cancel & Back</span>
                    </AppButton>
                </Link>
            </template>
        </AdminPageHeader>

        <form @submit.prevent="submit" class="space-y-6">
            <AppCard title="1. Lead Status & Contact">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                        <input v-model="form.name" type="text" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobile / Phone *</label>
                        <input v-model="form.phone" type="tel" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status *</label>
                        <select v-model="form.status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="new">New Inquiry</option>
                            <option value="contacted">Contacted</option>
                            <option value="qualified">Qualified</option>
                            <option value="site_visit_scheduled">Site Visit Scheduled</option>
                            <option value="negotiation">Negotiation</option>
                            <option value="converted">Converted</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Priority</label>
                        <select v-model="form.priority" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Score (0 - 100)</label>
                        <input v-model="form.score" type="number" min="0" max="100" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div v-if="form.status === 'lost'" class="md:col-span-3">
                        <label class="block text-xs font-bold text-rose-600 mb-1">Lost Reason</label>
                        <input v-model="form.lost_reason" type="text" placeholder="e.g. Budget mismatch, bought elsewhere" class="w-full px-3 py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs" />
                    </div>
                </div>
            </AppCard>

            <AppCard title="2. Requirements & Preferences">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min Budget ($)</label>
                        <input v-model="form.budget_min" type="number" step="0.01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Max Budget ($)</label>
                        <input v-model="form.budget_max" type="number" step="0.01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Preferred Location</label>
                        <input v-model="form.preferred_location" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Type</label>
                        <select v-model="form.property_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="apartment">Apartment</option>
                            <option value="villa">Villa</option>
                            <option value="flat">Flat / Condo</option>
                            <option value="commercial_office">Commercial Office</option>
                            <option value="shop">Shop</option>
                            <option value="plot">Plot</option>
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Requirements Notes</label>
                        <textarea v-model="form.requirements" rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                    </div>
                </div>
            </AppCard>

            <AppCard title="3. Assignment">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Agent</label>
                        <select v-model="form.assigned_agent_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">Unassigned</option>
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Branch</label>
                        <select v-model="form.branch_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">None</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Specific Property Inquired</label>
                        <select v-model="form.property_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">None</option>
                            <option v-for="p in properties" :key="p.id" :value="p.id">{{ p.property_code }} - {{ p.title }}</option>
                        </select>
                    </div>
                </div>
            </AppCard>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <Link :href="route('admin.leads.show', lead.id)">
                    <AppButton size="md" variant="secondary" type="button">Cancel</AppButton>
                </Link>
                <AppButton size="md" variant="primary" type="submit" :disabled="form.processing">
                    Save Changes
                </AppButton>
            </div>
        </form>
    </AdminLayout>
</template>
