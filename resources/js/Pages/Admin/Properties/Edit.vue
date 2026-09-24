<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPageHeader from '@/Components/Admin/AdminPageHeader.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import {
    ArrowLeft,
    Check,
    Plus,
    X
} from 'lucide-vue-next';

const props = defineProps({
    property: Object,
    agents: Array,
    branches: Array,
    owners: Array,
    defaultAmenities: Array,
});

const existingAmenities = props.property.amenities ? props.property.amenities.map(a => a.name) : [];
const existingImages = props.property.images && props.property.images.length > 0
    ? props.property.images.map(img => img.image_path)
    : [props.property.featured_image || ''];

const form = useForm({
    title: props.property.title,
    property_code: props.property.property_code,
    description: props.property.description || '',
    property_type: props.property.property_type,
    listing_purpose: props.property.listing_purpose,
    status: props.property.status,
    price: props.property.price,
    rent_amount: props.property.rent_amount || '',
    security_deposit: props.property.security_deposit || '',
    maintenance_charges: props.property.maintenance_charges || '',
    is_negotiable: Boolean(props.property.is_negotiable),
    address: props.property.address,
    locality: props.property.locality || '',
    city: props.property.city,
    state: props.property.state || '',
    zip_code: props.property.zip_code || '',
    latitude: props.property.latitude || '',
    longitude: props.property.longitude || '',
    bedrooms: props.property.bedrooms || 0,
    bathrooms: props.property.bathrooms || 0,
    balconies: props.property.balconies || 0,
    carpet_area: props.property.carpet_area || '',
    built_up_area: props.property.built_up_area || '',
    area_unit: props.property.area_unit || 'sqft',
    furnishing: props.property.furnishing || 'unfurnished',
    floor: props.property.floor || '',
    total_floors: props.property.total_floors || '',
    parking_spaces: props.property.parking_spaces || 0,
    construction_status: props.property.construction_status || 'ready_to_move',
    year_built: props.property.year_built || '',
    available_from: props.property.available_from || '',
    is_featured: Boolean(props.property.is_featured),
    is_published: Boolean(props.property.is_published),
    listing_agent_id: props.property.listing_agent_id || '',
    owner_id: props.property.owner_id || '',
    branch_id: props.property.branch_id || '',
    featured_image: props.property.featured_image || '',
    amenities: existingAmenities,
    image_urls: existingImages,
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
    form.image_urls = form.image_urls.filter((url) => url && url.trim().length > 0);
    form.put(route('admin.properties.update', props.property.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit Property: ${property.property_code}`" />

        <AdminPageHeader
            :title="`Edit Property: ${property.property_code}`"
            :description="`Updating details for ${property.title}`"
            :breadcrumbs="[
                { label: 'Properties', href: route('admin.properties.index') },
                { label: property.property_code, href: route('admin.properties.show', property.id) },
                { label: 'Edit' }
            ]"
        >
            <template #actions>
                <Link :href="route('admin.properties.show', property.id)">
                    <AppButton size="sm" variant="secondary">
                        <ArrowLeft class="w-3.5 h-3.5 mr-1" />
                        <span>Back to Property</span>
                    </AppButton>
                </Link>
            </template>
        </AdminPageHeader>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- 1. Basic Information -->
            <AppCard title="1. Property Classification & Status">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Title *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Code</label>
                        <input
                            v-model="form.property_code"
                            type="text"
                            required
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
                            <option value="sold">Sold</option>
                            <option value="rented">Rented</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Property Description</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        ></textarea>
                    </div>
                </div>
            </AppCard>

            <!-- 2. Pricing -->
            <AppCard title="2. Pricing & Commercials">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Price / Total Selling Price ($) *</label>
                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Monthly Rent ($ if rental)</label>
                        <input
                            v-model="form.rent_amount"
                            type="number"
                            step="0.01"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Security Deposit ($)</label>
                        <input
                            v-model="form.security_deposit"
                            type="number"
                            step="0.01"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Maintenance Charges ($/mo)</label>
                        <input
                            v-model="form.maintenance_charges"
                            type="number"
                            step="0.01"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white"
                        />
                    </div>

                    <div class="md:col-span-4 flex items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input v-model="form.is_negotiable" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                            <span>Price is Negotiable</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input v-model="form.is_featured" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                            <span>Featured Listing</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input v-model="form.is_published" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                            <span>Publish on Public Website</span>
                        </label>
                    </div>
                </div>
            </AppCard>

            <!-- 3. Location -->
            <AppCard title="3. Location & Address">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Street Address *</label>
                        <input v-model="form.address" type="text" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Locality</label>
                        <input v-model="form.locality" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">City *</label>
                        <input v-model="form.city" type="text" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">State</label>
                        <input v-model="form.state" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Zip Code</label>
                        <input v-model="form.zip_code" type="text" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude</label>
                        <input v-model="form.latitude" type="number" step="0.000001" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude</label>
                        <input v-model="form.longitude" type="number" step="0.000001" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:bg-white" />
                    </div>
                </div>
            </AppCard>

            <!-- 4. Specifications -->
            <AppCard title="4. Specifications">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bedrooms</label>
                        <input v-model="form.bedrooms" type="number" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bathrooms</label>
                        <input v-model="form.bathrooms" type="number" min="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Carpet Area (sqft)</label>
                        <input v-model="form.carpet_area" type="number" step="0.01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Furnishing</label>
                        <select v-model="form.furnishing" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="unfurnished">Unfurnished</option>
                            <option value="semi_furnished">Semi-Furnished</option>
                            <option value="fully_furnished">Fully-Furnished</option>
                        </select>
                    </div>
                </div>
            </AppCard>

            <!-- 5. Amenities -->
            <AppCard title="5. Amenities & Facilities">
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
            <AppCard title="6. Photos & Media">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Featured Main Image URL</label>
                        <input v-model="form.featured_image" type="url" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Gallery Image URLs</label>
                        <div class="space-y-2">
                            <div v-for="(url, idx) in form.image_urls" :key="idx" class="flex items-center gap-2">
                                <input v-model="form.image_urls[idx]" type="url" class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                                <button type="button" @click="removeImageUrl(idx)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                        <button type="button" @click="addImageUrl" class="mt-2 text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                            <Plus class="w-3.5 h-3.5" />
                            <span>Add Photo URL</span>
                        </button>
                    </div>
                </div>
            </AppCard>

            <!-- 7. Owner & Listing Agent -->
            <AppCard title="7. Assignment">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Listing Agent *</label>
                        <select v-model="form.listing_agent_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option v-for="agent in agents" :key="agent.id" :value="agent.id">{{ agent.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Branch Office</label>
                        <select v-model="form.branch_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">None</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Owner</label>
                        <select v-model="form.owner_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option value="">None</option>
                            <option v-for="o in owners" :key="o.id" :value="o.id">{{ o.name }}</option>
                        </select>
                    </div>
                </div>
            </AppCard>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <Link :href="route('admin.properties.show', property.id)">
                    <AppButton size="md" variant="secondary" type="button">Cancel</AppButton>
                </Link>
                <AppButton size="md" variant="primary" type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Update Property Listing' }}
                </AppButton>
            </div>
        </form>
    </AdminLayout>
</template>
