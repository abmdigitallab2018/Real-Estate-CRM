<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    agents: Array,
    branches: Array,
    properties: Array,
});

const form = useForm({
    name: '',
    phone: '',
    email: '',
    lead_type: 'buyer',
    source: 'website',
    status: 'new',
    priority: 'medium',
    score: 50,
    budget_min: '',
    budget_max: '',
    preferred_location: '',
    property_type: 'apartment',
    requirements: '',
    assigned_agent_id: props.agents[0]?.id || '',
    branch_id: props.branches[0]?.id || '',
    property_id: '',
    tags: [],
});

const submit = () => {
    form.post(route('admin.leads.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Create Lead - Real Estate CRM" />

        <AdminPageHeader
            title="Create New Lead"
            description="Record a new buyer, seller, or tenant inquiry with requirements and budget."
            :breadcrumbs="[
                { label: 'Leads', href: route('admin.leads.index') },
                { label: 'Create' }
            ]"
        >
            <template #actions>
                <Link :href="route('admin.leads.index')">
                    <AppButton size="sm" variant="secondary">
                        <ArrowLeft class="w-3.5 h-3.5 mr-1" />
                        <span>Back to Leads</span>
                    </AppButton>
                </Link>
            </template>
        </AdminPageHeader>

        <form @submit.prevent="submit" class="space-y-6">
            <AppCard title="1. Lead Contact & Lead Source">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                        <input v-model="form.name" type="text" required placeholder="e.g. Jonathan Hayes" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobile / Phone Number *</label>
                        <input v-model="form.phone" type="tel" required placeholder="+1 (555) 000-0000" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="form.errors.phone" class="text-xs text-rose-500 mt-1">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                        <input v-model="form.email" type="email" placeholder="jonathan@domain.com" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lead Type *</label>
                        <select v-model="form.lead_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller / Landlord</option>
                            <option value="renter">Renter / Tenant</option>
                            <option value="investor">Real Estate Investor</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Acquisition Source *</label>
                        <select v-model="form.source" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                            <option value="website">Website</option>
                            <option value="social_media">Social Media (FB/IG/LinkedIn)</option>
                            <option value="referrals">Referrals / Client Network</option>
                            <option value="property_portals">Property Portals (Zillow/Realtor)</option>
                            <option value="advertisements">Paid Advertisements</option>
                            <option value="calls">Inbound Call</option>
                            <option value="walk_ins">Office Walk-in</option>
                            <option value="direct">Direct</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lead Priority *</label>
                        <select v-model="form.priority" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>
            </AppCard>

            <AppCard title="2. Buyer / Customer Requirements & Budget">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min Budget ($)</label>
                        <input v-model="form.budget_min" type="number" step="0.01" placeholder="500000" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Max Budget ($)</label>
                        <input v-model="form.budget_max" type="number" step="0.01" placeholder="1200000" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Preferred Location / Area</label>
                        <input v-model="form.preferred_location" type="text" placeholder="e.g. Downtown Manhattan, Chelsea" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Desired Property Type</label>
                        <select v-model="form.property_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                            <option value="apartment">Apartment</option>
                            <option value="villa">Villa</option>
                            <option value="flat">Flat / Condo</option>
                            <option value="commercial_office">Commercial Office</option>
                            <option value="shop">Shop</option>
                            <option value="plot">Plot</option>
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Requirements & Timeline Notes</label>
                        <textarea v-model="form.requirements" rows="3" placeholder="Specific requirements like floor height, amenities, possession timeline..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
            </AppCard>

            <AppCard title="3. Assignment & Inquiry Link">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Assign to Agent</label>
                        <select v-model="form.assigned_agent_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">Unassigned</option>
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Branch Office</label>
                        <select v-model="form.branch_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">Select Branch</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Inquired For Specific Property</label>
                        <select v-model="form.property_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">General Inquiry (No specific listing)</option>
                            <option v-for="p in properties" :key="p.id" :value="p.id">{{ p.property_code }} - {{ p.title }}</option>
                        </select>
                    </div>
                </div>
            </AppCard>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <Link :href="route('admin.leads.index')">
                    <AppButton size="md" variant="secondary" type="button">Cancel</AppButton>
                </Link>
                <AppButton size="md" variant="primary" type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Registering Lead...' : 'Register Lead' }}
                </AppButton>
            </div>
        </form>
    </AdminLayout>
</template>
