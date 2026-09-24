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
    Sparkles,
    Building2,
    BedDouble,
    Bath,
    Maximize2,
    Check,
    Share2,
    BookmarkCheck,
    MessageSquare,
    ExternalLink,
    MapPin,
    DollarSign,
    UserCheck
} from 'lucide-vue-next';

const props = defineProps({
    matches: Array,
    selectedCustomer: Object,
    selectedLead: Object,
    customers: Array,
});

const selectedCustomerId = ref(props.selectedCustomer?.id || '');

const onCustomerChange = () => {
    if (selectedCustomerId.value) {
        router.get(route('admin.matching.index'), { customer_id: selectedCustomerId.value }, { preserveState: true });
    }
};

const shortlistProperty = (propertyId, matchScore) => {
    if (!props.selectedCustomer) return;
    router.post(route('admin.matching.shortlist'), {
        customer_id: props.selectedCustomer.id,
        property_id: propertyId,
        match_score: matchScore,
        lead_id: props.selectedLead?.id || null,
    }, { preserveScroll: true });
};

const shareProperty = (propertyId) => {
    if (!props.selectedCustomer) return;
    router.post(route('admin.matching.share'), {
        customer_id: props.selectedCustomer.id,
        property_id: propertyId,
        lead_id: props.selectedLead?.id || null,
    }, { preserveScroll: true });
};

// Feedback Modal
const feedbackModalOpen = ref(false);
const feedbackForm = useForm({
    match_id: null,
    status: 'shortlisted',
    feedback: '',
});

const openFeedbackModal = (match) => {
    feedbackForm.match_id = match.id;
    feedbackForm.status = match.status || 'shortlisted';
    feedbackForm.feedback = match.customer_feedback || '';
    feedbackModalOpen.value = true;
};

const submitFeedback = () => {
    feedbackForm.post(route('admin.matching.feedback'), {
        onSuccess: () => {
            feedbackModalOpen.value = false;
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Property Matching Engine - Real Estate CRM" />

        <AdminPageHeader
            title="Intelligent Property Matching Engine"
            description="Algorithmic scoring that maps registered buyer requirements & budgets to available property listings."
        />

        <!-- Customer Selector Ribbon -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Buyer / Client to Match *</label>
                    <select
                        v-model="selectedCustomerId"
                        @change="onCustomerChange"
                        class="w-full sm:w-80 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">-- Choose a registered customer --</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">
                            {{ c.name }} ({{ c.customer_type.toUpperCase() }}) - {{ c.phone }}
                        </option>
                    </select>
                </div>

                <!-- Buyer Preference Summary Pill -->
                <div v-if="selectedCustomer && selectedCustomer.preferences" class="bg-indigo-50/70 border border-indigo-100 p-3.5 rounded-xl text-xs flex items-center gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase text-indigo-600 block">Target Budget</span>
                        <span class="font-extrabold text-slate-900">${{ Number(selectedCustomer.preferences.max_budget || 0).toLocaleString() }}</span>
                    </div>
                    <div class="h-8 w-px bg-indigo-200"></div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-indigo-600 block">Bedrooms</span>
                        <span class="font-extrabold text-slate-900">{{ selectedCustomer.preferences.min_bedrooms || 0 }}+ BHK</span>
                    </div>
                    <div class="h-8 w-px bg-indigo-200"></div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-indigo-600 block">Purpose</span>
                        <span class="font-extrabold text-slate-900 uppercase">{{ selectedCustomer.preferences.purpose }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matched Listings List -->
        <div v-if="matches && matches.length > 0" class="space-y-4 mb-6">
            <div
                v-for="item in matches"
                :key="item.property.id"
                class="bg-white rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md transition p-5 flex flex-col md:flex-row md:items-center justify-between gap-5"
            >
                <!-- Property Media + Information -->
                <div class="flex items-start gap-4 flex-1">
                    <img
                        :src="item.property.featured_image || (item.property.images && item.property.images[0] ? item.property.images[0].image_path : 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=400&q=80')"
                        :alt="item.property.title"
                        class="w-28 h-24 rounded-xl object-cover border border-slate-200 flex-shrink-0"
                    />

                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
                                {{ item.property.property_code }}
                            </span>
                            <span class="text-[10px] font-bold uppercase text-slate-500">
                                {{ item.property.property_type.replace('_', ' ') }} &bull; {{ item.property.listing_purpose }}
                            </span>
                        </div>

                        <Link :href="route('admin.properties.show', item.property.id)" class="font-extrabold text-sm text-slate-900 hover:text-indigo-600 transition mt-1 block">
                            {{ item.property.title }}
                        </Link>

                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                            <MapPin class="w-3.5 h-3.5 text-slate-400" />
                            <span>{{ item.property.locality ? `${item.property.locality}, ${item.property.city}` : item.property.address }}</span>
                        </p>

                        <div class="flex items-center gap-4 text-xs font-semibold text-slate-600 mt-2">
                            <span>{{ item.property.bedrooms }} Beds</span>
                            <span>&bull;</span>
                            <span>{{ item.property.bathrooms }} Baths</span>
                            <span>&bull;</span>
                            <span>{{ Number(item.property.carpet_area || item.property.built_up_area || 0).toLocaleString() }} sqft</span>
                        </div>

                        <!-- Match Criteria Tags -->
                        <div class="flex items-center gap-1.5 flex-wrap mt-2.5">
                            <span
                                v-for="(reason, rIdx) in item.match_reasons"
                                :key="rIdx"
                                class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center gap-1"
                            >
                                <Check class="w-3 h-3 text-emerald-500" />
                                <span>{{ reason }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Price + Score Meter + Actions -->
                <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-3 border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-5">
                    <div class="text-left md:text-right">
                        <span class="text-xs text-slate-400 font-bold block uppercase">Price</span>
                        <span class="text-xl font-black text-slate-900">
                            ${{ Number(item.property.listing_purpose === 'rent' || item.property.listing_purpose === 'lease' ? (item.property.rent_amount || item.property.price) : item.property.price).toLocaleString() }}
                        </span>
                    </div>

                    <!-- Match Score Badge -->
                    <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black" :class="item.match_score >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                        <Sparkles class="w-3.5 h-3.5" :class="item.match_score >= 80 ? 'text-emerald-500' : 'text-amber-500'" />
                        <span>{{ item.match_score }}% Match</span>
                    </div>

                    <!-- Actions -->
                    <div v-if="selectedCustomer" class="flex items-center gap-2 mt-1">
                        <button
                            type="button"
                            @click="shortlistProperty(item.property.id, item.match_score)"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1"
                            :class="item.match_record?.status === 'shortlisted' ? 'bg-indigo-600 text-white' : 'bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 border border-slate-200'"
                        >
                            <BookmarkCheck class="w-3.5 h-3.5" />
                            <span>{{ item.match_record?.status === 'shortlisted' ? 'Shortlisted' : 'Shortlist' }}</span>
                        </button>

                        <button
                            type="button"
                            @click="shareProperty(item.property.id)"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1"
                            :class="item.match_record?.status === 'shared' ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-600 border border-slate-200'"
                        >
                            <Share2 class="w-3.5 h-3.5" />
                            <span>{{ item.match_record?.status === 'shared' ? 'Shared' : 'Share' }}</span>
                        </button>

                        <button
                            v-if="item.match_record"
                            type="button"
                            @click="openFeedbackModal(item.match_record)"
                            class="p-1.5 rounded-xl text-slate-500 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition"
                            title="Record Customer Feedback"
                        >
                            <MessageSquare class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="bg-white p-12 rounded-2xl border border-slate-200 text-center text-xs text-slate-400">
            Select a buyer above to calculate and display real-time matching properties.
        </div>

        <!-- Feedback Modal -->
        <AppModal
            :show="feedbackModalOpen"
            title="Record Customer Feedback & Rating"
            @close="feedbackModalOpen = false"
        >
            <form @submit.prevent="submitFeedback" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Customer Interest Status</label>
                    <select v-model="feedbackForm.status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        <option value="shortlisted">Interested / Shortlisted</option>
                        <option value="shared">Shared with Customer</option>
                        <option value="visited">Site Visit Planned</option>
                        <option value="rejected">Not Interested / Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Customer Notes / Feedback</label>
                    <textarea
                        v-model="feedbackForm.feedback"
                        rows="3"
                        placeholder="Customer loved the layout, wants to check if parking is covered..."
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <AppButton size="sm" variant="secondary" type="button" @click="feedbackModalOpen = false">Cancel</AppButton>
                    <AppButton size="sm" variant="primary" type="submit" :disabled="feedbackForm.processing">Save Feedback</AppButton>
                </div>
            </form>
        </AppModal>
    </AdminLayout>
</template>
