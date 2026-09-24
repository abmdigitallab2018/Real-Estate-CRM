<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { MapPin, BedDouble, Bath, Square, Calendar, User, Phone, Mail, CheckCircle2, ChevronRight, Home } from 'lucide-vue-next';

const props = defineProps({
    property: Object,
    similarProperties: Array,
});

const inquiryForm = useForm({
    property_id: props.property.id,
    name: '',
    email: '',
    phone: '',
    message: 'I am interested in ' + props.property.title + '. Please contact me with more information.',
});

const visitForm = useForm({
    property_id: props.property.id,
    name: '',
    email: '',
    phone: '',
    visit_date: '',
    visit_time: '10:00',
    notes: '',
});

const submitInquiry = () => {
    inquiryForm.post(route('properties.inquiry'), {
        preserveScroll: true,
        onSuccess: () => inquiryForm.reset('message'),
    });
};

const submitVisit = () => {
    visitForm.post(route('properties.schedule-visit'), {
        preserveScroll: true,
        onSuccess: () => visitForm.reset(),
    });
};
</script>

<template>
    <PublicLayout :title="property.title">
        <Head :title="property.title" />

        <!-- Property Hero -->
        <div class="bg-slate-900 h-[50vh] min-h-[400px] relative">
            <img v-if="property.featured_image" :src="property.featured_image" class="w-full h-full object-cover opacity-60" :alt="property.title" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
            <div class="absolute bottom-0 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="bg-indigo-600 text-white px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider">
                        For {{ property.listing_purpose }}
                    </span>
                    <span class="bg-white/20 backdrop-blur-md text-white px-3 py-1 rounded-full text-xs font-bold capitalize">
                        {{ property.property_type }}
                    </span>
                </div>
                <h1 class="text-3xl md:text-5xl font-black text-white mb-4">{{ property.title }}</h1>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <p class="text-lg text-slate-300 flex items-center gap-2">
                        <MapPin class="w-5 h-5 text-indigo-400" /> {{ property.address }}, {{ property.city }}, {{ property.state }} {{ property.zip_code }}
                    </p>
                    <div class="text-4xl font-black text-white bg-white/10 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/20">
                        ${{ Number(property.price).toLocaleString() }}
                        <span v-if="property.listing_purpose === 'rent'" class="text-lg font-medium text-slate-300">/mo</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-12">
                <!-- Key Features Bar -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-center">
                        <BedDouble class="w-6 h-6 text-indigo-500 mx-auto mb-2" />
                        <div class="text-2xl font-black text-slate-900">{{ property.bedrooms }}</div>
                        <div class="text-[10px] font-bold uppercase text-slate-500">Bedrooms</div>
                    </div>
                    <div class="text-center">
                        <Bath class="w-6 h-6 text-indigo-500 mx-auto mb-2" />
                        <div class="text-2xl font-black text-slate-900">{{ property.bathrooms }}</div>
                        <div class="text-[10px] font-bold uppercase text-slate-500">Bathrooms</div>
                    </div>
                    <div class="text-center">
                        <Square class="w-6 h-6 text-indigo-500 mx-auto mb-2" />
                        <div class="text-2xl font-black text-slate-900">{{ property.area_size }}</div>
                        <div class="text-[10px] font-bold uppercase text-slate-500">{{ property.area_unit }}</div>
                    </div>
                    <div class="text-center">
                        <Calendar class="w-6 h-6 text-indigo-500 mx-auto mb-2" />
                        <div class="text-2xl font-black text-slate-900">{{ property.year_built || '-' }}</div>
                        <div class="text-[10px] font-bold uppercase text-slate-500">Year Built</div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h2 class="text-2xl font-black text-slate-900 mb-6">Property Description</h2>
                    <div class="prose prose-slate max-w-none text-slate-600" v-html="property.description"></div>
                </div>

                <!-- Image Gallery (Basic) -->
                <div v-if="property.images && property.images.length > 0">
                    <h2 class="text-2xl font-black text-slate-900 mb-6">Gallery</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <img v-for="img in property.images" :key="img.id" :src="img.url" class="w-full aspect-square object-cover rounded-xl shadow-sm hover:scale-105 transition cursor-pointer" />
                    </div>
                </div>

                <!-- Amenities -->
                <div v-if="property.amenities && property.amenities.length > 0">
                    <h2 class="text-2xl font-black text-slate-900 mb-6">Amenities & Features</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div v-for="amenity in property.amenities" :key="amenity.id" class="flex items-center gap-2 text-slate-700 font-medium">
                            <CheckCircle2 class="w-5 h-5 text-emerald-500 shrink-0" />
                            {{ amenity.name }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Agent Contact -->
                <div v-if="property.listing_agent" class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-4 uppercase text-xs tracking-wider">Listed By</h3>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-indigo-600 font-black text-xl shadow-sm border border-indigo-100">
                            {{ property.listing_agent.name.charAt(0) }}
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-lg">{{ property.listing_agent.name }}</div>
                            <div class="text-sm text-slate-600 capitalize">{{ property.listing_agent.role }}</div>
                            <div class="text-xs text-indigo-600 font-bold mt-1">{{ property.branch?.name || property.tenant?.name }}</div>
                        </div>
                    </div>
                    
                    <form @submit.prevent="submitInquiry" class="space-y-4">
                        <div>
                            <input v-model="inquiryForm.name" type="text" placeholder="Your Name *" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" required />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <input v-model="inquiryForm.phone" type="text" placeholder="Phone *" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" required />
                            <input v-model="inquiryForm.email" type="email" placeholder="Email" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <textarea v-model="inquiryForm.message" rows="3" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        <button type="submit" :disabled="inquiryForm.processing" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm disabled:opacity-50">
                            Request Information
                        </button>
                    </form>
                </div>

                <!-- Schedule Visit -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-900 text-lg mb-2 flex items-center gap-2">
                        <Calendar class="w-5 h-5 text-indigo-600" /> Schedule a Tour
                    </h3>
                    <p class="text-sm text-slate-500 mb-6">Want to see this property in person? Request a site visit.</p>
                    
                    <form @submit.prevent="submitVisit" class="space-y-4">
                        <input v-model="visitForm.name" type="text" placeholder="Your Name *" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" required />
                        <input v-model="visitForm.phone" type="text" placeholder="Phone *" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" required />
                        <div class="grid grid-cols-2 gap-4">
                            <input v-model="visitForm.visit_date" type="date" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" required />
                            <input v-model="visitForm.visit_time" type="time" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" required />
                        </div>
                        <button type="submit" :disabled="visitForm.processing" class="w-full py-2.5 border-2 border-indigo-600 text-indigo-700 hover:bg-indigo-50 font-bold rounded-xl transition">
                            Request Tour
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Similar Properties -->
        <div v-if="similarProperties && similarProperties.length > 0" class="bg-slate-50 py-16 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900">Similar Properties</h2>
                        <p class="text-slate-500 mt-2">Explore other properties that match this style and location.</p>
                    </div>
                    <Link :href="route('properties.index', { type: property.property_type })" class="hidden md:flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-700">
                        View All <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Link v-for="prop in similarProperties" :key="prop.id" :href="route('properties.show', prop.slug)" class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl transition overflow-hidden">
                        <div class="aspect-[4/3] bg-slate-200 relative overflow-hidden">
                            <img v-if="prop.featured_image" :src="prop.featured_image" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                            <div class="absolute bottom-4 left-4">
                                <span class="bg-indigo-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm">
                                    ${{ Number(prop.price).toLocaleString() }}
                                </span>
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-slate-800 text-lg mb-1 line-clamp-1 group-hover:text-indigo-600 transition">{{ prop.title }}</h3>
                            <p class="text-sm text-slate-500 flex items-center gap-1.5 mb-3 line-clamp-1">
                                <MapPin class="w-4 h-4 shrink-0" /> {{ prop.city }}
                            </p>
                            <div class="flex gap-4 text-sm font-medium text-slate-700">
                                <span class="flex items-center gap-1"><BedDouble class="w-4 h-4 text-slate-400" /> {{ prop.bedrooms }}</span>
                                <span class="flex items-center gap-1"><Bath class="w-4 h-4 text-slate-400" /> {{ prop.bathrooms }}</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
