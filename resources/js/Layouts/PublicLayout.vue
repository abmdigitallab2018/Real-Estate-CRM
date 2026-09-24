<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Building2, Search, Phone, Mail, ArrowRight, ShieldCheck, ExternalLink, LogIn } from 'lucide-vue-next';
import AppToast from '@/Components/UI/AppToast.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);
const currentTenant = computed(() => page.props.currentTenant || null);
const brandName = computed(() => currentTenant.value ? currentTenant.value.name : 'Skyline Realty & Properties');
</script>

<template>
    <div class="min-h-screen bg-white text-slate-800 antialiased font-sans flex flex-col">
        <AppToast />

        <!-- Top Announcement Bar -->
        <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 sm:px-8 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-slate-400">
                    <Phone class="w-3.5 h-3.5 text-indigo-400" />
                    +1 (555) 234-5678
                </span>
                <span class="hidden sm:inline-flex items-center gap-1.5 text-slate-400">
                    <Mail class="w-3.5 h-3.5 text-indigo-400" />
                    contact@skylinerealty.com
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden md:inline text-[11px] text-slate-400">Trusted Real Estate Brokerage & Property Advisory</span>
                <Link v-if="!authUser" :href="route('login')" class="text-white hover:text-indigo-400 font-semibold flex items-center gap-1">
                    <span>Portal Login</span>
                    <ArrowRight class="w-3 h-3" />
                </Link>
                <Link v-else :href="route('dashboard')" class="text-indigo-400 hover:text-white font-semibold flex items-center gap-1">
                    <span>Go to CRM ({{ authUser.name }})</span>
                    <ArrowRight class="w-3 h-3" />
                </Link>
            </div>
        </div>

        <!-- Main Navigation Bar -->
        <header class="h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-40 transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-700 text-white flex items-center justify-center font-black shadow-md shadow-indigo-500/20">
                        <Building2 class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <span class="font-extrabold text-lg text-slate-900 leading-tight block tracking-tight">
                            {{ brandName }}
                        </span>
                        <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider block">
                            Luxury Listings & Commercial Estates
                        </span>
                    </div>
                </Link>

                <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                    <Link :href="route('home')" class="hover:text-indigo-600 transition">
                        Browse Properties
                    </Link>
                    <Link :href="route('pricing')" class="hover:text-indigo-600 transition">
                        Agency Plans & Pricing
                    </Link>
                </nav>

                <div class="flex items-center gap-3">
                    <Link
                        v-if="!authUser"
                        :href="route('login')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition border border-slate-200"
                    >
                        <LogIn class="w-4 h-4 text-slate-500" />
                        <span>Sign In</span>
                    </Link>
                    <Link
                        v-else
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition"
                    >
                        <span>Dashboard</span>
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </header>

        <!-- Page Body Slot -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-slate-950 text-slate-400 text-xs pt-16 pb-12 border-t border-slate-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-10">
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold">
                            <Building2 class="w-4 h-4 text-white" />
                        </div>
                        <span class="font-extrabold text-white text-base">{{ brandName }}</span>
                    </div>
                    <p class="text-slate-400 leading-relaxed text-xs">
                        Premier real estate agency and multi-tenant property management platform. Providing curated apartments, luxury villas, commercial offices, and investments.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-white uppercase tracking-wider text-xs mb-3">Quick Navigation</h4>
                    <ul class="space-y-2">
                        <li><Link :href="route('home')" class="hover:text-white transition">Featured Properties</Link></li>
                        <li><Link :href="route('pricing')" class="hover:text-white transition">Agency SaaS Plans</Link></li>
                        <li><Link :href="route('login')" class="hover:text-white transition">Agent & Client Login</Link></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white uppercase tracking-wider text-xs mb-3">Property Categories</h4>
                    <ul class="space-y-2">
                        <li><Link :href="route('home', { type: 'apartment' })" class="hover:text-white transition">Luxury Apartments</Link></li>
                        <li><Link :href="route('home', { type: 'villa' })" class="hover:text-white transition">Exclusive Villas</Link></li>
                        <li><Link :href="route('home', { type: 'commercial_office' })" class="hover:text-white transition">Commercial Corporate Offices</Link></li>
                        <li><Link :href="route('home', { type: 'shop' })" class="hover:text-white transition">Retail Storefronts</Link></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white uppercase tracking-wider text-xs mb-3">Contact & Offices</h4>
                    <p class="text-slate-400 leading-relaxed">
                        742 Evergreen Terrace, Suite 400<br>
                        New York, NY 10036<br>
                        Phone: +1 (555) 234-5678<br>
                        Email: info@skylinerealty.com
                    </p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <div>
                    &copy; {{ new Date().getFullYear() }} {{ brandName }}. Real Estate CRM & Property Management SaaS.
                </div>
                <div class="flex items-center gap-4">
                    <span>Privacy Policy</span>
                    <span>&bull;</span>
                    <span>Terms of Service</span>
                    <span>&bull;</span>
                    <span>Fair Housing Notice</span>
                </div>
            </div>
        </footer>
    </div>
</template>
