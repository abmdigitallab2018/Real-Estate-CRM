<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { Home, Calendar, CreditCard, Heart, MapPin } from 'lucide-vue-next';
import AppBadge from '@/Components/UI/AppBadge.vue';

const props = defineProps({
    customer: Object,
    shortlisted: Array,
    visits: Array,
    bookings: Array,
    payments: Array,
});
</script>

<template>
    <PortalLayout title="My Dashboard">
        <Head title="My Dashboard" />

        <div class="mb-6">
            <h1 class="text-2xl font-black text-slate-900">Welcome back, {{ customer?.name }}!</h1>
            <p class="text-sm text-slate-500 mt-1">Track your property searches, visits, and bookings</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-4">
                <div class="p-3 bg-rose-50 text-rose-500 rounded-xl"><Heart class="w-6 h-6" /></div>
                <div>
                    <div class="text-2xl font-black text-slate-900">{{ shortlisted?.length || 0 }}</div>
                    <div class="text-[10px] font-bold uppercase text-slate-500">Shortlisted</div>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-4">
                <div class="p-3 bg-indigo-50 text-indigo-500 rounded-xl"><Calendar class="w-6 h-6" /></div>
                <div>
                    <div class="text-2xl font-black text-slate-900">{{ visits?.length || 0 }}</div>
                    <div class="text-[10px] font-bold uppercase text-slate-500">Site Visits</div>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-500 rounded-xl"><Home class="w-6 h-6" /></div>
                <div>
                    <div class="text-2xl font-black text-slate-900">{{ bookings?.length || 0 }}</div>
                    <div class="text-[10px] font-bold uppercase text-slate-500">My Bookings</div>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-4">
                <div class="p-3 bg-amber-50 text-amber-500 rounded-xl"><CreditCard class="w-6 h-6" /></div>
                <div>
                    <div class="text-2xl font-black text-slate-900">{{ payments?.length || 0 }}</div>
                    <div class="text-[10px] font-bold uppercase text-slate-500">Payments</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Upcoming Site Visits -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <Calendar class="w-5 h-5 text-indigo-500" />
                    <h2 class="font-bold text-slate-900 text-lg">Upcoming Site Visits</h2>
                </div>
                <div class="p-0">
                    <ul class="divide-y divide-slate-100">
                        <li v-for="visit in visits" :key="visit.id" class="p-4 flex gap-4 items-start hover:bg-slate-50 transition">
                            <div class="text-center p-2 bg-indigo-50 rounded-xl min-w-[60px]">
                                <div class="text-xs font-bold text-indigo-600 uppercase">{{ new Date(visit.scheduled_at).toLocaleString('en-US', { month: 'short' }) }}</div>
                                <div class="text-xl font-black text-indigo-700">{{ new Date(visit.scheduled_at).getDate() }}</div>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ visit.property?.title }}</h3>
                                <div class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                    <MapPin class="w-3 h-3" /> {{ visit.property?.address }}
                                </div>
                                <div class="mt-2">
                                    <AppBadge :variant="visit.status === 'scheduled' ? 'primary' : 'secondary'">{{ visit.status }}</AppBadge>
                                </div>
                            </div>
                        </li>
                        <li v-if="!visits?.length" class="p-8 text-center text-slate-500 text-sm">
                            No upcoming site visits scheduled.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recent Shortlisted -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
                    <Heart class="w-5 h-5 text-rose-500" />
                    <h2 class="font-bold text-slate-900 text-lg">Recently Shortlisted</h2>
                </div>
                <div class="p-4 grid grid-cols-1 gap-4">
                    <div v-for="item in shortlisted?.slice(0, 3)" :key="item.id" class="flex gap-4 border border-slate-100 rounded-xl p-3 hover:border-slate-300 transition cursor-pointer">
                        <div class="w-20 h-20 bg-slate-200 rounded-lg shrink-0 bg-cover bg-center" :style="{ backgroundImage: `url(${item.property?.featured_image || ''})` }"></div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">{{ item.property?.title }}</h3>
                            <div class="text-lg font-black text-indigo-600 mt-1">${{ Number(item.property?.price).toLocaleString() }}</div>
                            <div class="text-xs text-slate-500 mt-1 line-clamp-1">{{ item.property?.address }}</div>
                        </div>
                    </div>
                    <div v-if="!shortlisted?.length" class="p-4 text-center text-slate-500 text-sm">
                        You haven't shortlisted any properties yet.
                    </div>
                </div>
            </div>
        </div>
    </PortalLayout>
</template>
