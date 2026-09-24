<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import {
    Building2,
    MapPin,
    DollarSign,
    Sliders,
    Image,
    Users,
    Check,
    ArrowLeft,
    Plus,
    X
} from 'lucide-vue-next';

const props = defineProps({
    agents: Array,
    branches: Array,
    owners: Array,
    defaultAmenities: Array,
});

const form = useForm({
    title: '',
    property_code: '',
    description: '',
    property_type: 'apartment',
    listing_purpose: 'sale',
    status: 'available',
    price: '',
    rent_amount: '',
    security_deposit: '',
    maintenance_charges: '',
    is_negotiable: true,
    address: '',
    locality: '',
    city: 'New York',
    state: 'NY',
    zip_code: '',
    latitude: '',
    longitude: '',
    bedrooms: 2,
    bathrooms: 2,
    balconies: 1,
    carpet_area: 1200,
    built_up_area: 1400,
    area_unit: 'sqft',
    furnishing: 'unfurnished',
    floor: 2,
    total_floors: 10,
    parking_spaces: 1,
    construction_status: 'ready_to_move',
    year_built: 2022,
    available_from: new Date().toISOString().split('T')[0],
    is_featured: false,
    is_published: true,
    listing_agent_id: props.agents[0]?.id || '',
    owner_id: '',
    branch_id: props.branches[0]?.id || '',
    featured_image: '',
    amenities: ['Swimming Pool', 'Gym & Fitness Center', '24/7 Security & CCTV', 'Power Backup', 'Elevator'],
    image_urls: [''],
});

const addImageUrl = () => {
    form.image_urls.push('');
};

const removeImageUrl = (index) => {
    form.image_urls.splice(index, 1);
};

const toggleAmenity = (name) => {
    const idx = form.amenities.indexOf(name);
    if (idx > -1) {
        form.amenities.splice(idx, 1);
    } else {
        form.amenities.push(name);
    }
};

const submit = () => {
    // Filter empty image URLs
    form.image_urls = form.image_urls.filter((url) => url && url.trim().length > 0);
    form.post(route('admin.properties.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Add Property Listing - Real Estate CRM" />

        <AdminPageHeader
            title="Create New Property Listing"
            description="Add a new residential, commercial, or plot listing to the agency inventory."
            :breadcrumbs="[
                { label: 'Properties', href: route('admin.properties.index') },
                { label: 'Create Listing' }
            ]"
        >
            <template #actions>
                <Link :href="route('admin.properties.index')">
                    <AppButton size="sm" variant="secondary">
                        <ArrowLeft class="w-3.5 h-3.5 mr-1" />
                        <span>Cancel & Back</span>
                    </AppButton>
                </Link>
            </template>
        </AdminPageHeader>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- 1. Basic Information -->
            <AppCard title="1. Property Information & Classification">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Title *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            placeholder="e.g. Skyline Luxury 3BHK Penthouse with River View"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                        <p v-if="form.errors.title" class="text-xs text-rose-500 mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Code (Optional)</label>
                        <input
                            v-model="form.property_code"
                            type="text"
                            placeholder="e.g. PROP-1008 (auto if blank)"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Type *</label>
                        <select
                            v-model="form.property_type"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        >
                            <option value="apartment">Apartment</option>
                            <option value="flat">Flat / Condo</option>
                            <option value="villa">Villa</option>
                            <option value="bungalow">Bungalow</option>
                            <option value="plot">Plot / Land</option>
                            <option value="commercial_office">Commercial Office</option>
                            <option value="shop">Retail Shop</option>
                            <option value="warehouse">Warehouse</option>
                            <option value="agricultural_land">Agricultural Land</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Listing Purpose *</label>
                        <select
                            v-model="form.listing_purpose"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        >
                            <option value="sale">For Sale</option>
                            <option value="rent">For Rent</option>
                            <option value="lease">Commercial Lease</option>
                            <option value="resale">Resale</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Listing Status *</label>
                        <select
                            v-model="form.status"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        >
                            <option value="available">Available</option>
                            <option value="under_negotiation">Under Negotiation</option>
                            <option value="reserved">Reserved</option>
                            <option value="draft">Draft</option>
                            <option value="sold">Sold</option>
                            <option value="rented">Rented</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Detailed overview of interior layout, finishes, sunlight, view, nearby landmarks..."
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        ></textarea>
                    </div>
                </div>
            </AppCard>

            <!-- 2. Pricing & Terms -->
            <AppCard title="2. Pricing, Rent & Financials">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Price / Total Selling Price ($) *</label>
                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            required
                            placeholder="1250000"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Monthly Rent ($ if rental)</label>
                        <input
                            v-model="form.rent_amount"
                            type="number"
                            step="0.01"
                            placeholder="4500"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Security Deposit ($)</label>
                        <input
                            v-model="form.security_deposit"
                            type="number"
                            step="0.01"
                            placeholder="9000"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maintenance Charges ($/mo)</label>
                        <input
                            v-model="form.maintenance_charges"
                            type="number"
                            step="0.01"
                            placeholder="450"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div class="md:col-span-4 flex items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input
                                v-model="form.is_negotiable"
                                type="checkbox"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span>Price is Negotiable</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input
                                v-model="form.is_featured"
                                type="checkbox"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span>Mark as Featured Listing</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input
                                v-model="form.is_published"
                                type="checkbox"
                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span>Publish on Public Website</span>
                        </label>
                    </div>
                </div>
            </AppCard>

            <!-- 3. Location Details -->
            <AppCard title="3. Location & Geographical Coordinates">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Street Address *</label>
                        <input
                            v-model="form.address"
                            type="text"
                            required
                            placeholder="e.g. 100 Riverside Boulevard, Apt 14B"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Locality / Neighborhood</label>
                        <input
                            v-model="form.locality"
                            type="text"
                            placeholder="e.g. Upper West Side"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City *</label>
                        <input
                            v-model="form.city"
                            type="text"
                            required
                            placeholder="New York"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">State</label>
                        <input
                            v-model="form.state"
                            type="text"
                            placeholder="NY"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Zip Code</label>
                        <input
                            v-model="form.zip_code"
                            type="text"
                            placeholder="10069"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude</label>
                        <input
                            v-model="form.latitude"
                            type="number"
                            step="0.000001"
                            placeholder="40.771200"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude</label>
                        <input
                            v-model="form.longitude"
                            type="number"
                            step="0.000001"
                            placeholder="-73.989200"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>
                </div>
            </AppCard>

            <!-- 4. Specifications -->
            <AppCard title="4. Specifications & Interior Details">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bedrooms</label>
                        <input v-model="form.bedrooms" type="number" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bathrooms</label>
                        <input v-model="form.bathrooms" type="number" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Balconies</label>
                        <input v-model="form.balconies" type="number" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Carpet Area (sqft)</label>
                        <input v-model="form.carpet_area" type="number" step="0.01" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Furnishing Status</label>
                        <select v-model="form.furnishing" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="unfurnished">Unfurnished</option>
                            <option value="semi_furnished">Semi-Furnished</option>
                            <option value="fully_furnished">Fully-Furnished</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Floor No.</label>
                        <input v-model="form.floor" type="number" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Parking Spaces</label>
                        <input v-model="form.parking_spaces" type="number" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Construction Status</label>
                        <select v-model="form.construction_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white">
                            <option value="ready_to_move">Ready to Move</option>
                            <option value="under_construction">Under Construction</option>
                            <option value="new_launch">New Launch</option>
                        </select>
                    </div>
                </div>
            </AppCard>

            <!-- 5. Amenities -->
            <AppCard title="5. Property Amenities & Lifestyle Facilities">
                <p class="text-xs text-slate-500 mb-3">Select all amenities available at this property:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                    <button
                        type="button"
                        v-for="amenity in defaultAmenities"
                        :key="amenity"
                        @click="toggleAmenity(amenity)"
                        :class="[
                            'p-2.5 rounded-xl border text-left text-xs font-bold flex items-center justify-between transition',
                            form.amenities.includes(amenity)
                                ? 'bg-indigo-50 border-indigo-500 text-indigo-700 shadow-2xs'
                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:border-slate-300'
                        ]"
                    >
                        <span>{{ amenity }}</span>
                        <Check v-if="form.amenities.includes(amenity)" class="w-4 h-4 text-indigo-600" />
                    </button>
                </div>
            </AppCard>

            <!-- 6. Photos & Media -->
            <AppCard title="6. Photos & Media URLs">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Featured Main Cover Image (URL)</label>
                        <input
                            v-model="form.featured_image"
                            type="url"
                            placeholder="https://images.unsplash.com/..."
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                        <p class="text-[11px] text-slate-400 mt-1">Direct image URL for property card cover & hero display.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Additional Gallery Image URLs</label>
                        <div class="space-y-2">
                            <div v-for="(url, idx) in form.image_urls" :key="idx" class="flex items-center gap-2">
                                <input
                                    v-model="form.image_urls[idx]"
                                    type="url"
                                    placeholder="https://..."
                                    class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                                />
                                <button
                                    type="button"
                                    @click="removeImageUrl(idx)"
                                    class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg"
                                >
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="addImageUrl"
                            class="mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Add Another Photo URL</span>
                        </button>
                    </div>
                </div>
            </AppCard>

            <!-- 7. Owner & Listing Agent Assignment -->
            <AppCard title="7. Assignment & Ownership">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Assigned Listing Agent *</label>
                        <select
                            v-model="form.listing_agent_id"
                            required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        >
                            <option value="">Select Agent</option>
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                                {{ agent.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Branch Office</label>
                        <select
                            v-model="form.branch_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        >
                            <option value="">Select Branch</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                {{ branch.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Owner (Customer)</label>
                        <select
                            v-model="form.owner_id"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        >
                            <option value="">Select or None</option>
                            <option v-for="owner in owners" :key="owner.id" :value="owner.id">
                                {{ owner.name }} ({{ owner.phone }})
                            </option>
                        </select>
                    </div>
                </div>
            </AppCard>

            <!-- Submit Action Bar -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <Link :href="route('admin.properties.index')">
                    <AppButton size="md" variant="secondary" type="button">
                        Cancel
                    </AppButton>
                </Link>
                <AppButton size="md" variant="primary" type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving Property...' : 'Save & Publish Property' }}
                </AppButton>
            </div>
        </form>
    </AdminLayout>
</template>
