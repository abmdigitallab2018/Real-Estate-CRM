<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    customer: Object,
    agents: Array,
});

const form = useForm({
    name: props.customer.name,
    phone: props.customer.phone,
    email: props.customer.email || '',
    secondary_phone: props.customer.secondary_phone || '',
    customer_type: props.customer.customer_type,
    company_name: props.customer.company_name || '',
    address: props.customer.address || '',
    city: props.customer.city || '',
    assigned_agent_id: props.customer.assigned_agent_id || '',
    source: props.customer.source || '',
    notes: props.customer.notes || '',
    status: props.customer.status,
    // Preferences
    purpose: props.customer.preferences?.purpose || 'sale',
    property_types: props.customer.preferences?.property_types || ['apartment'],
    min_budget: props.customer.preferences?.min_budget || '',
    max_budget: props.customer.preferences?.max_budget || '',
    min_bedrooms: props.customer.preferences?.min_bedrooms || 2,
    min_bathrooms: props.customer.preferences?.min_bathrooms || 2,
    possession_timeline: props.customer.preferences?.possession_timeline || 'immediate',
});

const submit = () => {
    form.put(route('admin.customers.update', props.customer.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit Customer: ${customer.name}`" />

        <AdminPageHeader
            :title="`Edit Customer: ${customer.name}`"
            description="Update client details and matching preferences."
            :breadcrumbs="[
                { label: 'Customers', href: route('admin.customers.index') },
                { label: customer.name, href: route('admin.customers.show', customer.id) },
                { label: 'Edit' }
            ]"
        >
            <template #actions>
                <Link :href="route('admin.customers.show', customer.id)">
                    <AppButton size="sm" variant="secondary">
                        <ArrowLeft class="w-3.5 h-3.5 mr-1" />
                        <span>Cancel & Back</span>
                    </AppButton>
                </Link>
            </template>
        </AdminPageHeader>

        <form @submit.prevent="submit" class="space-y-6">
            <AppCard title="1. Profile & Contact Information">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                        <input v-model="form.name" type="text" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number *</label>
                        <input v-model="form.phone" type="tel" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                        <input v-model="form.email" type="email" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Customer Classification *</label>
                        <select v-model="form.customer_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="buyer">Prospective Buyer</option>
                            <option value="seller">Property Owner / Seller</option>
                            <option value="landlord">Landlord</option>
                            <option value="tenant">Tenant</option>
                            <option value="investor">Investor</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Company / Trust</label>
                        <input v-model="form.company_name" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Agent</label>
                        <select v-model="form.assigned_agent_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">Unassigned</option>
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Address</label>
                        <input v-model="form.address" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                        <input v-model="form.city" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Profile Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                    </div>
                </div>
            </AppCard>

            <AppCard v-if="['buyer', 'tenant', 'investor'].includes(form.customer_type)" title="2. Preferences">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min Budget ($)</label>
                        <input v-model="form.min_budget" type="number" step="0.01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Max Budget ($)</label>
                        <input v-model="form.max_budget" type="number" step="0.01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bedrooms</label>
                        <input v-model="form.min_bedrooms" type="number" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Possession</label>
                        <input v-model="form.possession_timeline" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                </div>
            </AppCard>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <Link :href="route('admin.customers.show', customer.id)">
                    <AppButton size="md" variant="secondary" type="button">Cancel</AppButton>
                </Link>
                <AppButton size="md" variant="primary" type="submit" :disabled="form.processing">
                    Update Profile
                </AppButton>
            </div>
        </form>
    </AdminLayout>
</template>
