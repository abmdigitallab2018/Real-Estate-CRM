<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    Building2,
    BookmarkCheck,
    CalendarCheck,
    CreditCard,
    FolderLock,
    ExternalLink,
    LogOut,
    Menu,
    X,
    User
} from 'lucide-vue-next';
import AppToast from '@/Components/UI/AppToast.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user || { name: 'Client' });
const currentTenant = computed(() => page.props.currentTenant || { name: 'Real Estate CRM' });

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col">
        <AppToast />

        <!-- Portal Navigation Bar -->
        <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <Link :href="route('portal.dashboard')" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold">
                            <Building2 class="w-4 h-4" />
                        </div>
                        <div>
                            <span class="font-extrabold text-sm text-slate-900 block leading-tight">{{ currentTenant.name }}</span>
                            <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider block">Buyer Portal</span>
                        </div>
                    </Link>

                    <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-slate-600">
                        <Link
                            :href="route('portal.dashboard')"
                            class="px-3 py-1.5 rounded-lg text-indigo-600 bg-indigo-50 font-bold"
                        >
                            My Dashboard
                        </Link>
                        <a
                            :href="route('home')"
                            target="_blank"
                            class="px-3 py-1.5 rounded-lg hover:text-slate-900 hover:bg-slate-100 transition flex items-center gap-1.5"
                        >
                            <span>Browse Properties</span>
                            <ExternalLink class="w-3.5 h-3.5" />
                        </a>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right text-xs hidden sm:block">
                        <span class="font-bold text-slate-900 block">{{ authUser.name }}</span>
                        <span class="text-[11px] text-slate-500 block">{{ authUser.email }}</span>
                    </div>

                    <button
                        type="button"
                        @click="logout"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition border border-rose-200"
                    >
                        <LogOut class="w-3.5 h-3.5" />
                        <span>Log Out</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
            &copy; {{ new Date().getFullYear() }} {{ currentTenant.name }} Client Portal &bull; Real Estate CRM
        </footer>
    </div>
</template>
