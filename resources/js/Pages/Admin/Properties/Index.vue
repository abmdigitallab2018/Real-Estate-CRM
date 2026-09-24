<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppConfirmDialog from '@/Components/UI/AppConfirmDialog.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';
import {
    Plus,
    Building2,
    Search,
    MapPin,
    BedDouble,
    Bath,
    Maximize2,
    Eye,
    Edit,
    Trash2,
    LayoutGrid,
    Table as TableIcon,
    Filter
} from 'lucide-vue-next';

const props = defineProps({
    properties: Object,
    filters: Object,
    stats: Object,
    agents: Array,
});

const viewMode = ref('cards'); // 'cards' or 'table'
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const purpose = ref(props.filters.purpose || 'all');
const type = ref(props.filters.type || 'all');
const agentId = ref(props.filters.agent_id || 'all');

const applyFilters = () => {
    router.get(
        route('admin.properties.index'),
        {
            search: search.value || undefined,
            status: status.value !== 'all' ? status.value : undefined,
            purpose: purpose.value !== 'all' ? purpose.value : undefined,
            type: type.value !== 'all' ? type.value : undefined,
            agent_id: agentId.value !== 'all' ? agentId.value : undefined,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilters = () => {
    search.value = '';
    status.value = 'all';
    purpose.value = 'all';
    type.value = 'all';
    agentId.value = 'all';
    applyFilters();
};

const confirmDeleteModal = ref(false);
const propertyToDelete = ref(null);

const confirmDelete = (prop) => {
    propertyToDelete.value = prop;
    confirmDeleteModal.value = true;
};

const doDelete = () => {
    if (!propertyToDelete.value) return;
    router.delete(route('admin.properties.destroy', propertyToDelete.value.id), {
        onSuccess: () => {
            confirmDeleteModal.value = false;
            propertyToDelete.value = null;
        }
    });
};

const updateStatus = (propertyId, newStatus) => {
    router.post(route('admin.properties.status', propertyId), { status: newStatus }, { preserveScroll: true });
};

const getStatusBadge = (st) => {
    switch (st) {
        case 'available': return { variant: 'emerald', label: 'Available' };
        case 'under_negotiation': return { variant: 'amber', label: 'Under Negotiation' };
        case 'reserved': return { variant: 'purple', label: 'Reserved' };
        case 'sold': return { variant: 'rose', label: 'Sold' };
        case 'rented':
        case 'leased': return { variant: 'indigo', label: 'Rented' };
        default: return { variant: 'slate', label: st };
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Property Inventory - Real Estate CRM" />

        <AdminPageHeader
            title="Property Listings & Inventory"
            description="Manage residential, commercial, and investment property listings with pricing, specifications, and availability status."
        >
            <template #actions>
                <div class="flex items-center gap-2">
                    <!-- Cards / Table Toggle -->
                    <div class="bg-white border border-slate-200 rounded-xl p-0.5 flex items-center shadow-2xs">
                        <button
                            type="button"
                            @click="viewMode = 'cards'"
                            :class="['p-1.5 rounded-lg text-xs transition', viewMode === 'cards' ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-slate-400 hover:text-slate-700']"
                            title="Cards View"
                        >
                            <LayoutGrid class="w-4 h-4" />
                        </button>
                        <button
                            type="button"
                            @click="viewMode = 'table'"
                            :class="['p-1.5 rounded-lg text-xs transition', viewMode === 'table' ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-slate-400 hover:text-slate-700']"
                            title="Table View"
                        >
                            <TableIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <Link :href="route('admin.properties.create')">
                        <AppButton size="sm" variant="primary">
                            <Plus class="w-4 h-4 mr-1" />
                            <span>Add New Property</span>
                        </AppButton>
                    </Link>
                </div>
            </template>
        </AdminPageHeader>

        <!-- Status Counters Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <button
                type="button"
                @click="status = 'all'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'all' ? 'bg-white border-indigo-500 ring-2 ring-indigo-500/20 shadow-xs' : 'bg-white/80 border-slate-200 hover:border-slate-300']"
            >
                <span class="block text-[11px] font-bold text-slate-400 uppercase">All Properties</span>
                <span class="block text-xl font-black text-slate-900 mt-1">{{ stats.total }}</span>
            </button>

            <button
                type="button"
                @click="status = 'available'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'available' ? 'bg-emerald-50/40 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white/80 border-slate-200 hover:border-slate-300']"
            >
                <span class="block text-[11px] font-bold text-emerald-600 uppercase">Available</span>
                <span class="block text-xl font-black text-emerald-700 mt-1">{{ stats.available }}</span>
            </button>

            <button
                type="button"
                @click="status = 'under_negotiation'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'under_negotiation' ? 'bg-amber-50/40 border-amber-500 ring-2 ring-amber-500/20' : 'bg-white/80 border-slate-200 hover:border-slate-300']"
            >
                <span class="block text-[11px] font-bold text-amber-600 uppercase">Negotiation</span>
                <span class="block text-xl font-black text-amber-700 mt-1">{{ stats.under_negotiation }}</span>
            </button>

            <button
                type="button"
                @click="status = 'reserved'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'reserved' ? 'bg-purple-50/40 border-purple-500 ring-2 ring-purple-500/20' : 'bg-white/80 border-slate-200 hover:border-slate-300']"
            >
                <span class="block text-[11px] font-bold text-purple-600 uppercase">Reserved</span>
                <span class="block text-xl font-black text-purple-700 mt-1">{{ stats.reserved }}</span>
            </button>

            <button
                type="button"
                @click="status = 'sold'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'sold' ? 'bg-rose-50/40 border-rose-500 ring-2 ring-rose-500/20' : 'bg-white/80 border-slate-200 hover:border-slate-300']"
            >
                <span class="block text-[11px] font-bold text-rose-600 uppercase">Sold</span>
                <span class="block text-xl font-black text-rose-700 mt-1">{{ stats.sold }}</span>
            </button>

            <button
                type="button"
                @click="status = 'rented'; applyFilters()"
                :class="['p-3 rounded-2xl border text-left transition', status === 'rented' ? 'bg-indigo-50/40 border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-white/80 border-slate-200 hover:border-slate-300']"
            >
                <span class="block text-[11px] font-bold text-indigo-600 uppercase">Rented / Leased</span>
                <span class="block text-xl font-black text-indigo-700 mt-1">{{ stats.rented }}</span>
            </button>
        </div>

        <!-- Filter / Search Header -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search input -->
                <div class="relative lg:col-span-2">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Search title, property code, locality, city..."
                        class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                    />
                </div>

                <!-- Purpose Filter -->
                <select
                    v-model="purpose"
                    @change="applyFilters"
                    class="bg-slate-50 border border-slate-200 text-xs rounded-xl px-3 py-2 text-slate-700 focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="all">All Purposes (Sale/Rent)</option>
                    <option value="sale">For Sale</option>
                    <option value="rent">For Rent</option>
                    <option value="lease">Commercial Lease</option>
                    <option value="resale">Resale</option>
                </select>

                <!-- Type Filter -->
                <select
                    v-model="type"
                    @change="applyFilters"
                    class="bg-slate-50 border border-slate-200 text-xs rounded-xl px-3 py-2 text-slate-700 focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="all">All Property Types</option>
                    <option value="apartment">Apartment</option>
                    <option value="villa">Villa / House</option>
                    <option value="flat">Flat / Condo</option>
                    <option value="commercial_office">Commercial Office</option>
                    <option value="shop">Retail Shop</option>
                    <option value="plot">Plot / Land</option>
                    <option value="warehouse">Warehouse</option>
                </select>

                <!-- Agent Filter -->
                <select
                    v-model="agentId"
                    @change="applyFilters"
                    class="bg-slate-50 border border-slate-200 text-xs rounded-xl px-3 py-2 text-slate-700 focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="all">All Listing Agents</option>
                    <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                        {{ agent.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- CARDS VIEW -->
        <div v-if="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div
                v-for="prop in properties.data"
                :key="prop.id"
                class="bg-white rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md transition-shadow overflow-hidden flex flex-col group"
            >
                <!-- Property Image Cover -->
                <div class="relative h-48 bg-slate-100 overflow-hidden">
                    <img
                        :src="prop.featured_image || (prop.images && prop.images[0] ? prop.images[0].image_path : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=600&q=80')"
                        :alt="prop.title"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />

                    <!-- Badges on image -->
                    <div class="absolute top-3 left-3 flex items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-900/80 text-white backdrop-blur-xs">
                            {{ prop.property_code }}
                        </span>
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold capitalize bg-indigo-600 text-white">
                            {{ prop.listing_purpose }}
                        </span>
                    </div>

                    <div class="absolute top-3 right-3">
                        <AppBadge :variant="getStatusBadge(prop.status).variant">
                            {{ getStatusBadge(prop.status).label }}
                        </AppBadge>
                    </div>

                    <!-- Price Ribbon -->
                    <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur-xs px-3 py-1.5 rounded-xl shadow-xs">
                        <span class="text-sm font-black text-slate-900">
                            ${{ Number(prop.listing_purpose === 'rent' || prop.listing_purpose === 'lease' ? (prop.rent_amount || prop.price) : prop.price).toLocaleString() }}
                        </span>
                        <span v-if="prop.listing_purpose === 'rent' || prop.listing_purpose === 'lease'" class="text-[10px] text-slate-500 font-semibold">/mo</span>
                    </div>
                </div>

                <!-- Card Content -->
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider capitalize">
                            {{ prop.property_type.replace('_', ' ') }}
                        </div>
                        <Link :href="route('admin.properties.show', prop.id)" class="block mt-1">
                            <h3 class="font-extrabold text-sm text-slate-900 line-clamp-1 hover:text-indigo-600 transition">
                                {{ prop.title }}
                            </h3>
                        </Link>
                        <p class="text-xs text-slate-500 mt-1 flex items-center gap-1 line-clamp-1">
                            <MapPin class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" />
                            <span>{{ prop.locality ? `${prop.locality}, ${prop.city}` : prop.address }}</span>
                        </p>

                        <!-- Key Specs -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600 font-semibold">
                            <div class="flex items-center gap-1" title="Bedrooms">
                                <BedDouble class="w-4 h-4 text-slate-400" />
                                <span>{{ prop.bedrooms }} BHK</span>
                            </div>
                            <div class="flex items-center gap-1" title="Bathrooms">
                                <Bath class="w-4 h-4 text-slate-400" />
                                <span>{{ prop.bathrooms }} Bath</span>
                            </div>
                            <div class="flex items-center gap-1" title="Area">
                                <Maximize2 class="w-4 h-4 text-slate-400" />
                                <span>{{ Number(prop.carpet_area || prop.built_up_area || 0).toLocaleString() }} sqft</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer & Actions -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <div class="text-[11px] text-slate-500 truncate max-w-[130px]">
                            Agent: <span class="font-semibold text-slate-800">{{ prop.listing_agent ? prop.listing_agent.name : 'Unassigned' }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <Link :href="route('admin.properties.show', prop.id)" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition" title="View Details">
                                <Eye class="w-4 h-4" />
                            </Link>
                            <Link :href="route('admin.properties.edit', prop.id)" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition" title="Edit Property">
                                <Edit class="w-4 h-4" />
                            </Link>
                            <button
                                type="button"
                                @click="confirmDelete(prop)"
                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                title="Delete Property"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE VIEW -->
        <div v-else class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Code / Property</th>
                            <th class="py-3 px-4">Type & Purpose</th>
                            <th class="py-3 px-4">Location</th>
                            <th class="py-3 px-4">Specs</th>
                            <th class="py-3 px-4 text-right">Price / Rent</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr v-for="prop in properties.data" :key="prop.id" class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-extrabold text-indigo-600 text-[11px]">{{ prop.property_code }}</div>
                                <Link :href="route('admin.properties.show', prop.id)" class="font-bold text-slate-900 hover:text-indigo-600 transition block">
                                    {{ prop.title }}
                                </Link>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="capitalize text-slate-900 font-bold">{{ prop.property_type.replace('_', ' ') }}</div>
                                <div class="text-[10px] text-slate-400 uppercase font-semibold">{{ prop.listing_purpose }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ prop.city }} <span v-if="prop.locality">({{ prop.locality }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ prop.bedrooms }} BHK &bull; {{ prop.carpet_area || prop.built_up_area }} sqft
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-900 text-sm">
                                ${{ Number(prop.listing_purpose === 'rent' || prop.listing_purpose === 'lease' ? (prop.rent_amount || prop.price) : prop.price).toLocaleString() }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <AppBadge :variant="getStatusBadge(prop.status).variant">
                                    {{ getStatusBadge(prop.status).label }}
                                </AppBadge>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('admin.properties.show', prop.id)" class="p-1 rounded-md text-slate-500 hover:text-indigo-600">
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <Link :href="route('admin.properties.edit', prop.id)" class="p-1 rounded-md text-slate-500 hover:text-amber-600">
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button @click="confirmDelete(prop)" class="p-1 rounded-md text-slate-500 hover:text-rose-600">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200">
            <span class="text-xs text-slate-500">
                Showing {{ properties.from || 0 }} to {{ properties.to || 0 }} of {{ properties.total }} properties
            </span>
            <DataTablePagination :pagination="properties" @page="applyFilters" />
        </div>

        <!-- Delete Modal -->
        <AppConfirmDialog
            :show="confirmDeleteModal"
            title="Archive Property Listing?"
            :message="`Are you sure you want to archive '${propertyToDelete?.title}' (${propertyToDelete?.property_code})?`"
            confirm-text="Archive Property"
            confirm-variant="danger"
            @confirm="doDelete"
            @close="confirmDeleteModal = false"
        />
    </AdminLayout>
</template>
