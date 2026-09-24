<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Search, MapPin, Home, DollarSign, Filter, BedDouble, Bath, Square } from 'lucide-vue-next';
import AppBadge from '@/Components/UI/AppBadge.vue';
import DataTablePagination from '@/Components/DataTable/DataTablePagination.vue';

const props = defineProps({
    properties: Object,
    filters: Object,
    availableCities: Array,
});

const form = ref({
    search: props.filters?.search || '',
    purpose: props.filters?.purpose || '',
    type: props.filters?.type || '',
    city: props.filters?.city || '',
    bedrooms: props.filters?.bedrooms || '',
    min_price: props.filters?.min_price || '',
    max_price: props.filters?.max_price || '',
    sort: props.filters?.sort || 'latest',
});

const applyFilters = () => {
    router.get(route('properties.index'), form.value, { preserveState: true, replace: true });
};
</script>

<template>
    <PublicLayout title="Search Properties">
        <Head title="Properties for Sale and Rent" />

        <!-- Search Header -->
        <div class="bg-indigo-600 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-black text-center mb-2">Find Your Dream Home</h1>
                <p class="text-indigo-200 text-center mb-8 max-w-2xl mx-auto">Browse our extensive collection of premium real estate listings.</p>
                
                <div class="bg-white p-4 rounded-2xl shadow-xl max-w-4xl mx-auto">
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-1 relative">
                            <Search class="w-5 h-5 text-slate-400 absolute left-3 top-3" />
                            <input v-model="form.search" type="text" placeholder="Search keywords..." class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div class="md:col-span-1 relative">
                            <MapPin class="w-5 h-5 text-slate-400 absolute left-3 top-3" />
                            <select v-model="form.city" class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Cities</option>
                                <option v-for="city in availableCities" :key="city" :value="city">{{ city }}</option>
                            </select>
                        </div>
                        <div class="md:col-span-1 relative">
                            <Home class="w-5 h-5 text-slate-400 absolute left-3 top-3" />
                            <select v-model="form.type" class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Types</option>
                                <option value="apartment">Apartment</option>
                                <option value="house">House</option>
                                <option value="villa">Villa</option>
                                <option value="commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition flex items-center justify-center gap-2">
                                <Search class="w-5 h-5" /> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm sticky top-6">
                    <h3 class="font-bold text-slate-900 text-lg mb-4 flex items-center gap-2">
                        <Filter class="w-5 h-5" /> Refine Search
                    </h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2 uppercase">Purpose</label>
                            <div class="flex gap-2">
                                <label class="flex-1 text-center cursor-pointer">
                                    <input type="radio" v-model="form.purpose" value="" class="peer sr-only" @change="applyFilters" />
                                    <div class="px-3 py-2 text-sm font-medium border border-slate-200 rounded-xl peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-700 transition">All</div>
                                </label>
                                <label class="flex-1 text-center cursor-pointer">
                                    <input type="radio" v-model="form.purpose" value="sale" class="peer sr-only" @change="applyFilters" />
                                    <div class="px-3 py-2 text-sm font-medium border border-slate-200 rounded-xl peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-700 transition">Sale</div>
                                </label>
                                <label class="flex-1 text-center cursor-pointer">
                                    <input type="radio" v-model="form.purpose" value="rent" class="peer sr-only" @change="applyFilters" />
                                    <div class="px-3 py-2 text-sm font-medium border border-slate-200 rounded-xl peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-700 transition">Rent</div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2 uppercase">Price Range</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input v-model="form.min_price" @change="applyFilters" type="number" placeholder="Min $" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" />
                                <input v-model="form.max_price" @change="applyFilters" type="number" placeholder="Max $" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2 uppercase">Bedrooms</label>
                            <select v-model="form.bedrooms" @change="applyFilters" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="">Any</option>
                                <option value="1">1+ Beds</option>
                                <option value="2">2+ Beds</option>
                                <option value="3">3+ Beds</option>
                                <option value="4">4+ Beds</option>
                                <option value="5">5+ Beds</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Grid -->
            <div class="lg:col-span-3">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-900">{{ properties.total }} Properties Found</h2>
                    <div>
                        <select v-model="form.sort" @change="applyFilters" class="pl-3 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="latest">Newest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="featured">Featured First</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <Link v-for="prop in properties.data" :key="prop.id" :href="route('properties.show', prop.slug)" class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-indigo-300 transition overflow-hidden flex flex-col">
                        <div class="aspect-[4/3] bg-slate-200 relative overflow-hidden">
                            <img v-if="prop.featured_image" :src="prop.featured_image" :alt="prop.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                <Home class="w-12 h-12 opacity-50" />
                            </div>
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase text-indigo-700 tracking-wider shadow-sm">
                                    For {{ prop.listing_purpose }}
                                </span>
                                <span v-if="prop.is_featured" class="bg-amber-400/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase text-white tracking-wider shadow-sm flex items-center gap-1">
                                    Featured
                                </span>
                            </div>
                        </div>
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="text-2xl font-black text-slate-900 mb-2">${{ Number(prop.price).toLocaleString() }}<span v-if="prop.listing_purpose === 'rent'" class="text-sm font-medium text-slate-500">/mo</span></div>
                            <h3 class="font-bold text-slate-800 text-lg mb-1 line-clamp-1 group-hover:text-indigo-600 transition">{{ prop.title }}</h3>
                            <p class="text-sm text-slate-500 mb-4 flex items-center gap-1.5 line-clamp-1">
                                <MapPin class="w-4 h-4 shrink-0" /> {{ prop.address }}, {{ prop.city }}
                            </p>
                            <div class="grid grid-cols-3 gap-2 py-3 border-t border-slate-100 mt-auto">
                                <div class="flex items-center gap-1.5 text-sm font-medium text-slate-700" title="Bedrooms">
                                    <BedDouble class="w-4 h-4 text-slate-400" /> {{ prop.bedrooms }}
                                </div>
                                <div class="flex items-center gap-1.5 text-sm font-medium text-slate-700" title="Bathrooms">
                                    <Bath class="w-4 h-4 text-slate-400" /> {{ prop.bathrooms }}
                                </div>
                                <div class="flex items-center gap-1.5 text-sm font-medium text-slate-700" title="Area">
                                    <Square class="w-4 h-4 text-slate-400" /> {{ prop.area_size }} {{ prop.area_unit }}
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
                
                <div v-if="properties.data.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
                    <Search class="w-12 h-12 text-slate-300 mx-auto mb-4" />
                    <h3 class="text-xl font-bold text-slate-900 mb-2">No properties found</h3>
                    <p class="text-slate-500">Try adjusting your filters or search criteria to find what you're looking for.</p>
                    <button @click="router.get(route('properties.index'))" class="mt-6 px-6 py-2 bg-indigo-50 text-indigo-700 font-bold rounded-xl hover:bg-indigo-100 transition">
                        Clear All Filters
                    </button>
                </div>

                <div v-if="properties.total > 0" class="flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                    <span class="text-sm text-slate-500">Showing {{ properties.from }} to {{ properties.to }} of {{ properties.total }}</span>
                    <DataTablePagination :pagination="properties" />
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
