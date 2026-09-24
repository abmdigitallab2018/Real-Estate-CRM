<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import {
    UserCheck,
    Phone,
    Mail,
    MapPin,
    Building2,
    CalendarCheck,
    Kanban,
    BookmarkCheck,
    CreditCard,
    Sparkles,
    Edit,
    ArrowLeft
} from 'lucide-vue-next';

const props = defineProps({
    customer: Object,
});

const activeTab = ref('overview'); // 'overview', 'matches', 'visits', 'deals', 'bookings', 'payments'
</script>

<template>
    <AdminLayout>
        <Head :title="`Customer: ${customer.name}`" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-1.5 font-medium">
                    <Link :href="route('admin.customers.index')" class="hover:text-slate-700">Customers</Link>
                    <span>/</span>
                    <span class="font-semibold text-slate-600">{{ customer.name }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ customer.name }}</h1>
                    <AppBadge variant="indigo" class="capitalize">{{ customer.customer_type }}</AppBadge>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link :href="route('admin.matching.index', { customer_id: customer.id })">
                    <AppButton size="sm" variant="primary">
                        <Sparkles class="w-3.5 h-3.5 mr-1 text-amber-300" />
                        <span>Match Properties</span>
                    </AppButton>
                </Link>

                <Link :href="route('admin.customers.edit', customer.id)">
                    <AppButton size="sm" variant="secondary">
                        <Edit class="w-3.5 h-3.5 mr-1" />
                        <span>Edit Profile</span>
                    </AppButton>
                </Link>
            </div>
        </div>

        <!-- 360 Header Stats -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Budget Range</span>
                <div class="text-lg font-black text-slate-900 mt-0.5">
                    {{ customer.preferences?.max_budget ? `$${Number(customer.preferences.max_budget).toLocaleString()}` : 'Not Specified' }}
                </div>
                <span class="text-[11px] text-slate-500 capitalize">{{ customer.preferences?.purpose || 'Sale' }}</span>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Scheduled Visits</span>
                <div class="text-lg font-black text-slate-900 mt-0.5">
                    {{ customer.site_visits ? customer.site_visits.length : 0 }}
                </div>
                <span class="text-[11px] text-indigo-600 font-semibold">Tours Recorded</span>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Active Deals</span>
                <div class="text-lg font-black text-slate-900 mt-0.5">
                    {{ customer.deals ? customer.deals.length : 0 }}
                </div>
                <span class="text-[11px] text-slate-500">In Pipeline</span>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400">Bookings / Escrows</span>
                <div class="text-lg font-black text-emerald-600 mt-0.5">
                    {{ customer.bookings ? customer.bookings.length : 0 }}
                </div>
                <span class="text-[11px] text-slate-500">Reservations</span>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-slate-200 mb-6 flex items-center gap-2 overflow-x-auto text-xs font-bold">
            <button
                type="button"
                @click="activeTab = 'overview'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <UserCheck class="w-4 h-4" />
                <span>Overview & Preferences</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'matches'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'matches' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <Sparkles class="w-4 h-4 text-amber-500" />
                <span>Shortlisted Properties ({{ customer.property_matches ? customer.property_matches.length : 0 }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'visits'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'visits' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <CalendarCheck class="w-4 h-4" />
                <span>Site Visits ({{ customer.site_visits ? customer.site_visits.length : 0 }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'deals'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'deals' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <Kanban class="w-4 h-4" />
                <span>Pipeline Deals ({{ customer.deals ? customer.deals.length : 0 }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'bookings'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'bookings' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <BookmarkCheck class="w-4 h-4" />
                <span>Bookings & Payments</span>
            </button>
        </div>

        <!-- TAB 1: OVERVIEW -->
        <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <AppCard title="Contact & Profile Information">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 font-bold uppercase text-[10px] block">Primary Phone</span>
                            <span class="text-sm font-extrabold text-slate-800">{{ customer.phone }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-bold uppercase text-[10px] block">Email</span>
                            <span class="text-sm font-extrabold text-slate-800">{{ customer.email || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-bold uppercase text-[10px] block">Company</span>
                            <span class="font-semibold text-slate-800">{{ customer.company_name || 'Individual' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-bold uppercase text-[10px] block">City</span>
                            <span class="font-semibold text-slate-800">{{ customer.city || 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 text-xs">
                        <span class="font-bold text-slate-700 block mb-1">Notes:</span>
                        <p class="text-slate-600 leading-relaxed">{{ customer.notes || 'No notes.' }}</p>
                    </div>
                </AppCard>

                <!-- Owned properties if seller -->
                <AppCard v-if="customer.owned_properties && customer.owned_properties.length > 0" title="Properties Owned / Listed">
                    <div class="divide-y divide-slate-100 text-xs">
                        <div v-for="p in customer.owned_properties" :key="p.id" class="py-2.5 flex justify-between items-center">
                            <div>
                                <Link :href="route('admin.properties.show', p.id)" class="font-bold text-indigo-600 hover:underline">
                                    {{ p.property_code }} - {{ p.title }}
                                </Link>
                                <span class="text-slate-400 text-[11px] block">{{ p.city }} &bull; ${{ Number(p.price).toLocaleString() }}</span>
                            </div>
                            <AppBadge variant="emerald">{{ p.status }}</AppBadge>
                        </div>
                    </div>
                </AppCard>
            </div>

            <div class="space-y-6">
                <AppCard title="Buyer Preferences">
                    <div v-if="customer.preferences" class="divide-y divide-slate-100 text-xs">
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500">Purpose:</span>
                            <span class="font-bold uppercase text-slate-800">{{ customer.preferences.purpose }}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500">Max Budget:</span>
                            <span class="font-bold text-slate-800">${{ Number(customer.preferences.max_budget || 0).toLocaleString() }}</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500">Min Bedrooms:</span>
                            <span class="font-bold text-slate-800">{{ customer.preferences.min_bedrooms || 0 }} BHK</span>
                        </div>
                        <div class="py-2 flex justify-between">
                            <span class="text-slate-500">Timeline:</span>
                            <span class="font-bold capitalize text-slate-800">{{ customer.preferences.possession_timeline }}</span>
                        </div>
                    </div>
                    <p v-else class="text-xs text-slate-400">No preferences configured.</p>
                </AppCard>
            </div>
        </div>

        <!-- TAB 2: SHORTLISTED PROPERTIES -->
        <div v-if="activeTab === 'matches'">
            <AppCard title="Shortlisted & Matched Properties">
                <div v-if="customer.property_matches && customer.property_matches.length > 0" class="divide-y divide-slate-100 text-xs">
                    <div v-for="match in customer.property_matches" :key="match.id" class="py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img
                                :src="match.property ? match.property.featured_image : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=400&q=80'"
                                class="w-16 h-12 rounded-xl object-cover"
                            />
                            <div>
                                <Link :href="route('admin.properties.show', match.property.id)" class="font-bold text-slate-900 hover:text-indigo-600 block">
                                    {{ match.property.title }}
                                </Link>
                                <span class="text-[11px] text-slate-500">${{ Number(match.property.price).toLocaleString() }} &bull; {{ match.property.city }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <AppBadge variant="emerald">{{ match.match_score }}% Match</AppBadge>
                            <span class="text-[10px] text-slate-400 capitalize block mt-1">{{ match.status }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="py-8 text-center text-xs text-slate-400">
                    No properties shortlisted yet.
                </div>
            </AppCard>
        </div>

        <!-- TAB 3: SITE VISITS -->
        <div v-if="activeTab === 'visits'">
            <AppCard title="Site Visits Schedule">
                <div v-if="customer.site_visits && customer.site_visits.length > 0" class="divide-y divide-slate-100 text-xs">
                    <div v-for="v in customer.site_visits" :key="v.id" class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 block">{{ v.visit_code }} - {{ v.scheduled_date }} at {{ v.scheduled_time }}</span>
                            <span class="text-slate-500 text-[11px]">{{ v.property ? v.property.title : 'Showing' }}</span>
                        </div>
                        <AppBadge :variant="v.status === 'completed' ? 'emerald' : 'indigo'">{{ v.status }}</AppBadge>
                    </div>
                </div>
                <p v-else class="text-xs text-slate-400 py-4">No visits scheduled.</p>
            </AppCard>
        </div>

        <!-- TAB 4: DEALS -->
        <div v-if="activeTab === 'deals'">
            <AppCard title="Active Sales Pipeline Deals">
                <div v-if="customer.deals && customer.deals.length > 0" class="divide-y divide-slate-100 text-xs">
                    <div v-for="d in customer.deals" :key="d.id" class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 block">{{ d.title }}</span>
                            <span class="text-[11px] text-slate-500">{{ d.deal_code }} &bull; Value: ${{ Number(d.expected_value).toLocaleString() }}</span>
                        </div>
                        <AppBadge variant="indigo">{{ d.stage ? d.stage.name : 'Pipeline' }}</AppBadge>
                    </div>
                </div>
                <p v-else class="text-xs text-slate-400 py-4">No deals currently in pipeline.</p>
            </AppCard>
        </div>

        <!-- TAB 5: BOOKINGS & PAYMENTS -->
        <div v-if="activeTab === 'bookings'" class="space-y-6">
            <AppCard title="Bookings & Reservations">
                <div v-if="customer.bookings && customer.bookings.length > 0" class="divide-y divide-slate-100 text-xs">
                    <div v-for="b in customer.bookings" :key="b.id" class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-black text-indigo-600 block">{{ b.booking_number }}</span>
                            <span class="text-slate-600 font-semibold">${{ Number(b.total_amount).toLocaleString() }} (Paid: ${{ Number(b.paid_amount).toLocaleString() }})</span>
                        </div>
                        <AppBadge :variant="b.status === 'confirmed' ? 'emerald' : 'amber'">{{ b.status }}</AppBadge>
                    </div>
                </div>
                <p v-else class="text-xs text-slate-400 py-4">No bookings on record.</p>
            </AppCard>
        </div>
    </AdminLayout>
</template>
