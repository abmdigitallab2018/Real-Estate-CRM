<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Check, Star, Building } from 'lucide-vue-next';

const props = defineProps({
    plans: Array,
});
</script>

<template>
    <PublicLayout title="Pricing">
        <Head title="SaaS Pricing Plans" />

        <div class="bg-slate-900 py-20 px-4 sm:px-6 lg:px-8 text-center border-b border-slate-800">
            <h1 class="text-4xl md:text-5xl font-black text-white mb-6">Simple, Transparent Pricing</h1>
            <p class="text-xl text-slate-300 max-w-2xl mx-auto mb-10">Power your real estate agency with the ultimate CRM built specifically for property professionals.</p>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 -mt-16">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                <div v-for="plan in plans" :key="plan.id" 
                     class="bg-white rounded-3xl border shadow-xl flex flex-col relative transition duration-300 hover:-translate-y-2"
                     :class="plan.name.toLowerCase().includes('pro') ? 'border-indigo-500 ring-4 ring-indigo-500/20' : 'border-slate-200'">
                    
                    <div v-if="plan.name.toLowerCase().includes('pro')" class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-black uppercase tracking-wider px-4 py-1.5 rounded-full shadow-sm flex items-center gap-1">
                        <Star class="w-3.5 h-3.5" /> Most Popular
                    </div>

                    <div class="p-8 text-center border-b border-slate-100">
                        <h3 class="text-xl font-bold text-slate-900 mb-2">{{ plan.name }}</h3>
                        <p class="text-sm text-slate-500 mb-6 h-10">{{ plan.description || 'Perfect for growing agencies.' }}</p>
                        
                        <div class="flex items-baseline justify-center gap-1 mb-6">
                            <span class="text-5xl font-black text-slate-900">${{ plan.price }}</span>
                            <span class="text-slate-500 font-medium">/{{ plan.billing_interval === 'yearly' ? 'year' : 'mo' }}</span>
                        </div>

                        <Link :href="route('superadmin.dashboard')" class="block w-full py-3 rounded-xl text-sm font-bold transition shadow-sm"
                              :class="plan.name.toLowerCase().includes('pro') ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-slate-50 text-slate-900 hover:bg-slate-100 border border-slate-200'">
                            Start Free Trial
                        </Link>
                    </div>

                    <div class="p-8 flex-grow bg-slate-50/50 rounded-b-3xl">
                        <div class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <Building class="w-4 h-4 text-indigo-500" /> Plan Limits
                        </div>
                        <ul class="space-y-3 mb-8 text-sm font-medium text-slate-700">
                            <li class="flex justify-between border-b border-slate-200/60 pb-2">
                                <span>Agent Seats</span>
                                <span class="font-bold text-slate-900">{{ plan.max_agents }}</span>
                            </li>
                            <li class="flex justify-between border-b border-slate-200/60 pb-2">
                                <span>Property Listings</span>
                                <span class="font-bold text-slate-900">{{ plan.max_properties }}</span>
                            </li>
                            <li class="flex justify-between border-b border-slate-200/60 pb-2">
                                <span>Storage</span>
                                <span class="font-bold text-slate-900">{{ plan.max_storage_mb / 1024 }} GB</span>
                            </li>
                        </ul>

                        <div class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4">Included Features</div>
                        <ul class="space-y-3">
                            <li v-for="(feature, index) in plan.features || []" :key="index" class="flex items-start gap-3 text-sm text-slate-600">
                                <div class="mt-0.5 bg-emerald-100 text-emerald-600 rounded-full p-0.5 shrink-0">
                                    <Check class="w-3 h-3" />
                                </div>
                                <span>{{ feature }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="mt-20 text-center">
                <h2 class="text-2xl font-bold text-slate-900 mb-4">Need a custom enterprise solution?</h2>
                <p class="text-slate-500 mb-6">Contact our sales team for unlimited seats, dedicated support, and custom integrations.</p>
                <button class="bg-white border-2 border-slate-200 text-slate-700 hover:border-indigo-600 hover:text-indigo-600 px-6 py-3 rounded-xl font-bold transition">
                    Contact Sales
                </button>
            </div>
        </div>
    </PublicLayout>
</template>
