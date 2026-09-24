<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import {
    Building2,
    MapPin,
    DollarSign,
    BedDouble,
    Bath,
    Maximize2,
    Calendar,
    Users,
    CalendarCheck,
    Kanban,
    BookmarkCheck,
    FolderLock,
    Clock,
    Edit,
    ExternalLink,
    Check,
    Phone,
    Mail,
    ArrowLeft
} from 'lucide-vue-next';

const props = defineProps({
    property: Object,
    inquiries: Array,
    auditLogs: Array,
});

const activeTab = ref('overview'); // 'overview', 'owner', 'inquiries', 'visits', 'deals', 'documents'

const changeStatus = (newStatus) => {
    router.post(route('admin.properties.status', props.property.id), { status: newStatus }, { preserveScroll: true });
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'available': return { variant: 'emerald', label: 'Available' };
        case 'under_negotiation': return { variant: 'amber', label: 'Under Negotiation' };
        case 'reserved': return { variant: 'purple', label: 'Reserved' };
        case 'sold': return { variant: 'rose', label: 'Sold' };
        case 'rented':
        case 'leased': return { variant: 'indigo', label: 'Rented / Leased' };
        default: return { variant: 'slate', label: st };
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`${property.title} (${property.property_code})`" />

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-1.5 font-medium">
                    <Link :href="route('admin.properties.index')" class="hover:text-slate-700">Properties</Link>
                    <span>/</span>
                    <span class="font-semibold text-slate-600">{{ property.property_code }}</span>
                </nav>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                        {{ property.title }}
                    </h1>
                    <AppBadge :variant="getStatusBadge(property.status).variant">
                        {{ getStatusBadge(property.status).label }}
                    </AppBadge>
                </div>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                    <MapPin class="w-3.5 h-3.5 text-slate-400" />
                    <span>{{ property.address }}, {{ property.locality ? property.locality + ', ' : '' }}{{ property.city }}, {{ property.state }} {{ property.zip_code }}</span>
                </p>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Quick Status Dropdown -->
                <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 shadow-2xs">
                    <span class="text-[11px] font-bold text-slate-400 uppercase">Status:</span>
                    <select
                        :value="property.status"
                        @change="changeStatus($event.target.value)"
                        class="text-xs font-bold text-slate-800 bg-transparent border-none p-0 focus:ring-0 cursor-pointer"
                    >
                        <option value="available">Available</option>
                        <option value="under_negotiation">Under Negotiation</option>
                        <option value="reserved">Reserved</option>
                        <option value="sold">Sold</option>
                        <option value="rented">Rented</option>
                        <option value="draft">Draft</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <a
                    :href="route('properties.show', property.slug)"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-white text-slate-700 hover:text-indigo-600 border border-slate-200 shadow-2xs transition"
                >
                    <span>Public View</span>
                    <ExternalLink class="w-3.5 h-3.5" />
                </a>

                <Link :href="route('admin.properties.edit', property.id)">
                    <AppButton size="sm" variant="primary">
                        <Edit class="w-3.5 h-3.5 mr-1" />
                        <span>Edit Property</span>
                    </AppButton>
                </Link>
            </div>
        </div>

        <!-- Hero Ribbon: Key Financials & Specs -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Listing Price</span>
                <div class="text-2xl font-black text-slate-900 mt-0.5">
                    ${{ Number(property.price).toLocaleString() }}
                </div>
                <span v-if="property.is_negotiable" class="text-[10px] font-bold text-emerald-600">Negotiable</span>
            </div>

            <div v-if="property.rent_amount">
                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Monthly Rent</span>
                <div class="text-2xl font-black text-indigo-600 mt-0.5">
                    ${{ Number(property.rent_amount).toLocaleString() }} <span class="text-xs font-normal text-slate-500">/mo</span>
                </div>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Dimensions</span>
                <div class="text-lg font-extrabold text-slate-900 mt-1">
                    {{ Number(property.carpet_area || property.built_up_area || 0).toLocaleString() }} {{ property.area_unit }}
                </div>
                <span class="text-[11px] text-slate-500">{{ property.bedrooms }} Beds &bull; {{ property.bathrooms }} Baths</span>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Assigned Agent</span>
                <div class="text-sm font-extrabold text-slate-900 mt-1">
                    {{ property.listing_agent ? property.listing_agent.name : 'Unassigned' }}
                </div>
                <span class="text-[11px] text-slate-500">{{ property.branch ? property.branch.name : 'Main Agency' }}</span>
            </div>
        </div>

        <!-- Image Gallery Preview -->
        <div v-if="property.images && property.images.length > 0" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2 h-72 rounded-2xl overflow-hidden border border-slate-200 bg-slate-100">
                <img :src="property.featured_image || property.images[0].image_path" class="w-full h-full object-cover" />
            </div>
            <div class="grid grid-cols-2 gap-3 h-72">
                <div
                    v-for="(img, idx) in property.images.slice(0, 4)"
                    :key="idx"
                    class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100 h-full"
                >
                    <img :src="img.image_path" class="w-full h-full object-cover" />
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-slate-200 mb-6 flex items-center gap-2 overflow-x-auto text-xs font-bold">
            <button
                type="button"
                @click="activeTab = 'overview'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <Building2 class="w-4 h-4" />
                <span>Overview & Amenities</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'owner'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'owner' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <Users class="w-4 h-4" />
                <span>Owner & Ownership</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'inquiries'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'inquiries' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <Mail class="w-4 h-4" />
                <span>Inquiries & Leads ({{ inquiries ? inquiries.length : 0 }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'visits'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'visits' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <CalendarCheck class="w-4 h-4" />
                <span>Site Visits ({{ property.site_visits ? property.site_visits.length : 0 }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'deals'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'deals' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <Kanban class="w-4 h-4" />
                <span>Deals & Bookings ({{ (property.deals?.length || 0) + (property.bookings?.length || 0) }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'documents'"
                :class="['px-4 py-3 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap', activeTab === 'documents' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800']"
            >
                <FolderLock class="w-4 h-4" />
                <span>Documents & Audit</span>
            </button>
        </div>

        <!-- TAB 1: OVERVIEW & AMENITIES -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <AppCard title="About Property">
                        <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ property.description || 'No description provided.' }}
                        </p>
                    </AppCard>

                    <AppCard title="Amenities & Features">
                        <div v-if="property.amenities && property.amenities.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <div
                                v-for="amenity in property.amenities"
                                :key="amenity.id"
                                class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200/80 text-xs font-semibold text-slate-700 flex items-center gap-2"
                            >
                                <Check class="w-4 h-4 text-emerald-600" />
                                <span>{{ amenity.name }}</span>
                            </div>
                        </div>
                        <p v-else class="text-xs text-slate-400">No amenities listed.</p>
                    </AppCard>
                </div>

                <div class="space-y-6">
                    <AppCard title="Technical Specs">
                        <div class="divide-y divide-slate-100 text-xs">
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-500">Property Type:</span>
                                <span class="font-bold text-slate-800 capitalize">{{ property.property_type.replace('_', ' ') }}</span>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-500">Furnishing:</span>
                                <span class="font-bold text-slate-800 capitalize">{{ property.furnishing.replace('_', ' ') }}</span>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-500">Floor Level:</span>
                                <span class="font-bold text-slate-800">{{ property.floor }} of {{ property.total_floors }}</span>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-500">Parking Spaces:</span>
                                <span class="font-bold text-slate-800">{{ property.parking_spaces }} Covered</span>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-500">Construction Status:</span>
                                <span class="font-bold text-slate-800 capitalize">{{ property.construction_status.replace('_', ' ') }}</span>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-500">Year Built:</span>
                                <span class="font-bold text-slate-800">{{ property.year_built || 'N/A' }}</span>
                            </div>
                            <div class="py-2.5 flex justify-between">
                                <span class="text-slate-500">Maintenance Fee:</span>
                                <span class="font-bold text-slate-800">${{ Number(property.maintenance_charges || 0).toLocaleString() }}/mo</span>
                            </div>
                        </div>
                    </AppCard>
                </div>
            </div>
        </div>

        <!-- TAB 2: OWNER DETAILS -->
        <div v-if="activeTab === 'owner'">
            <AppCard v-if="property.owner" title="Owner Information (Confidential)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-black text-sm">
                                {{ property.owner.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base text-slate-900">{{ property.owner.name }}</h3>
                                <p class="text-xs text-slate-500">{{ property.owner.company_name || 'Individual Owner' }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-2 text-slate-700">
                                <Phone class="w-4 h-4 text-slate-400" />
                                <span>{{ property.owner.phone }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-700">
                                <Mail class="w-4 h-4 text-slate-400" />
                                <span>{{ property.owner.email || 'No email' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-700">
                                <MapPin class="w-4 h-4 text-slate-400" />
                                <span>{{ property.owner.address || 'Address on file' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
                        <span class="font-bold text-slate-700 block mb-1">Owner Notes:</span>
                        <p class="text-slate-600 leading-relaxed">{{ property.owner.notes || 'No confidential notes.' }}</p>
                    </div>
                </div>
            </AppCard>

            <div v-else class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-xs text-slate-400">
                No owner currently linked to this listing.
            </div>
        </div>

        <!-- TAB 3: INQUIRIES & LEADS -->
        <div v-if="activeTab === 'inquiries'">
            <AppCard title="Inquiries & Interested Leads">
                <div v-if="inquiries && inquiries.length > 0" class="divide-y divide-slate-100 text-xs">
                    <div v-for="inq in inquiries" :key="inq.id" class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 block">{{ inq.name }}</span>
                            <span class="text-slate-500 text-[11px] block">{{ inq.phone }} &bull; {{ inq.email }}</span>
                            <p class="text-[11px] text-slate-600 mt-1">{{ inq.requirements }}</p>
                        </div>
                        <div class="text-right">
                            <AppBadge variant="indigo">{{ inq.status }}</AppBadge>
                            <span class="block text-[10px] text-slate-400 mt-1">{{ inq.created_at ? inq.created_at.substring(0, 10) : '' }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="py-6 text-center text-xs text-slate-400">
                    No leads or inquiries received yet for this listing.
                </div>
            </AppCard>
        </div>

        <!-- TAB 4: SITE VISITS -->
        <div v-if="activeTab === 'visits'">
            <AppCard title="Site Visits Conducted & Scheduled">
                <div v-if="property.site_visits && property.site_visits.length > 0" class="divide-y divide-slate-100 text-xs">
                    <div v-for="visit in property.site_visits" :key="visit.id" class="py-3 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-slate-900">{{ visit.visit_code }}</span>
                                <span class="text-slate-500">{{ visit.scheduled_date }} at {{ visit.scheduled_time }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-0.5">
                                Buyer: <span class="font-semibold">{{ visit.customer ? visit.customer.name : 'Client' }}</span>
                                &bull; Agent: <span class="font-semibold">{{ visit.assigned_agent ? visit.assigned_agent.name : 'Assigned' }}</span>
                            </p>
                            <p v-if="visit.customer_feedback" class="text-[11px] text-indigo-700 bg-indigo-50/60 p-2 rounded-lg mt-1.5">
                                "Feedback: {{ visit.customer_feedback }}"
                            </p>
                        </div>
                        <AppBadge :variant="visit.status === 'completed' ? 'emerald' : 'indigo'">{{ visit.status }}</AppBadge>
                    </div>
                </div>
                <div v-else class="py-6 text-center text-xs text-slate-400">
                    No site visits scheduled for this property.
                </div>
            </AppCard>
        </div>

        <!-- TAB 5: DEALS & BOOKINGS -->
        <div v-if="activeTab === 'deals'" class="space-y-6">
            <AppCard title="Active Reservations & Bookings">
                <div v-if="property.bookings && property.bookings.length > 0" class="divide-y divide-slate-100 text-xs">
                    <div v-for="bkg in property.bookings" :key="bkg.id" class="py-3 flex items-center justify-between">
                        <div>
                            <span class="font-black text-indigo-600">{{ bkg.booking_number }}</span>
                            <span class="text-slate-600 font-bold ml-2">${{ Number(bkg.total_amount).toLocaleString() }}</span>
                            <span class="text-slate-400 text-[11px] block">Customer: {{ bkg.customer ? bkg.customer.name : 'Client' }}</span>
                        </div>
                        <AppBadge :variant="bkg.status === 'confirmed' ? 'emerald' : 'amber'">{{ bkg.status }}</AppBadge>
                    </div>
                </div>
                <div v-else class="py-6 text-center text-xs text-slate-400">
                    No active reservations on this property.
                </div>
            </AppCard>
        </div>

        <!-- TAB 6: DOCUMENTS & AUDIT -->
        <div v-if="activeTab === 'documents'" class="space-y-6">
            <AppCard title="Listing Audit Log History">
                <div v-if="auditLogs && auditLogs.length > 0" class="divide-y divide-slate-100 text-xs">
                    <div v-for="log in auditLogs" :key="log.id" class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800">{{ log.action }}</span>
                            <span class="text-[11px] text-slate-400 block">{{ log.created_at }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="py-6 text-center text-xs text-slate-400">
                    No audit records logged yet.
                </div>
            </AppCard>
        </div>
    </AdminLayout>
</template>
