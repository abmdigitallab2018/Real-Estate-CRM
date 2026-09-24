<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    agents: Array,
});

const form = useForm({
    name: '',
    phone: '',
    email: '',
    secondary_phone: '',
    customer_type: 'buyer',
    company_name: '',
    address: '',
    city: 'New York',
    assigned_agent_id: props.agents[0]?.id || '',
    source: 'Website',
    notes: '',
    status: 'active',
    // Buyer Preferences
    purpose: 'sale',
    property_types: ['apartment'],
    min_budget: '',
    max_budget: '',
    preferred_locations: ['Downtown'],
    min_bedrooms: 2,
    min_bathrooms: 2,
    min_area: '',
    possession_timeline: 'immediate',
    furnishing: 'unfurnished',
});

const submit = () => {
    form.post(route('admin.customers.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Create Customer Profile - Real Estate CRM" />

        <AdminPageHeader
            title="Create Customer Profile"
            description="Register a new buyer, property owner, landlord, or investor account."
            :breadcrumbs="[
                { label: 'Customers', href: route('admin.customers.index') },
                { label: 'Create' }
            ]"
        >
            <template #actions>
                <Link :href="route('admin.customers.index')">
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
                        <input v-model="form.name" type="text" required placeholder="e.g. Eleanor Vance" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="form.errors.name" class="text-xs text-rose-500 mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number *</label>
                        <input v-model="form.phone" type="tel" required placeholder="+1 (555) 000-0000" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                        <p v-if="form.errors.phone" class="text-xs text-rose-500 mt-1">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                        <input v-model="form.email" type="email" placeholder="eleanor@vancecap.com" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Customer Classification *</label>
                        <select v-model="form.customer_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                            <option value="buyer">Prospective Buyer</option>
                            <option value="seller">Property Owner / Seller</option>
                            <option value="landlord">Landlord (Rentals)</option>
                            <option value="tenant">Tenant / Lessee</option>
                            <option value="investor">Real Estate Investor</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Company / Entity Name</label>
                        <input v-model="form.company_name" type="text" placeholder="e.g. Vance Capital Trust" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Agent</label>
                        <select v-model="form.assigned_agent_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                            <option value="">Unassigned</option>
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Address</label>
                        <input v-model="form.address" type="text" placeholder="Street Address" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City</label>
                        <input v-model="form.city" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Profile Notes</label>
                        <textarea v-model="form.notes" rows="2" placeholder="Background, financial qualifications, communication preferences..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"></textarea>
                    </div>
                </div>
            </AppCard>

            <AppCard v-if="['buyer', 'tenant', 'investor'].includes(form.customer_type)" title="2. Buyer Preferences & Property Matching Criteria">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Purpose</label>
                        <select v-model="form.purpose" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="sale">Buy</option>
                            <option value="rent">Rent</option>
                            <option value="lease">Lease</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min Budget ($)</label>
                        <input v-model="form.min_budget" type="number" step="0.01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Max Budget ($)</label>
                        <input v-model="form.max_budget" type="number" step="0.01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Min Bedrooms (BHK)</label>
                        <input v-model="form.min_bedrooms" type="number" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                </div>
            </AppCard>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <Link :href="route('admin.customers.index')">
                    <AppButton size="md" variant="secondary" type="button">Cancel</AppButton>
                </Link>
                <AppButton size="md" variant="primary" type="submit" :disabled="form.processing">
                    Save Customer Profile
                </AppButton>
            </div>
        </form>
    </AdminLayout>
</template>
