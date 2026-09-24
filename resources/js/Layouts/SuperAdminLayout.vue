<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    Shield,
    LayoutDashboard,
    Building2,
    CreditCard,
    Layers,
    ArrowLeft,
    LogOut,
    Menu,
    X,
    ExternalLink
} from 'lucide-vue-next';
import AppToast from '@/Components/UI/AppToast.vue';

const page = usePage();
const mobileSidebarOpen = ref(false);
const authUser = computed(() => page.props.auth?.user || { name: 'Super Admin', email: '' });

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-slate-100 antialiased font-sans flex">
        <AppToast />

        <!-- Mobile Drawer Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 bg-black/70 backdrop-blur-xs z-40 lg:hidden"
                @click="mobileSidebarOpen = false"
            ></div>
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-slate-950 border-r border-slate-800 flex flex-col justify-between transition-all duration-200 w-64 lg:static',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <div>
                <div class="h-16 border-b border-slate-800 px-6 flex items-center justify-between">
                    <Link :href="route('superadmin.dashboard')" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-violet-600 text-white flex items-center justify-center font-bold shadow-md shadow-violet-500/20">
                            <Shield class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <span class="block font-extrabold text-sm text-white">SaaS Master</span>
                            <span class="block text-[10px] font-bold text-violet-400 uppercase tracking-widest">Platform Admin</span>
                        </div>
                    </Link>
                    <button @click="mobileSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="px-3 py-5 space-y-1 text-xs font-semibold">
                    <div class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                        Platform Management
                    </div>

                    <Link
                        :href="route('superadmin.dashboard')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors',
                            route().current('superadmin.dashboard')
                                ? 'bg-violet-600/20 text-violet-400 border border-violet-500/30 font-bold'
                                : 'text-slate-400 hover:text-white hover:bg-slate-900'
                        ]"
                    >
                        <LayoutDashboard class="w-4 h-4 flex-shrink-0" />
                        <span>Platform Overview</span>
                    </Link>

                    <Link
                        :href="route('superadmin.tenants')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors',
                            route().current('superadmin.tenants*')
                                ? 'bg-violet-600/20 text-violet-400 border border-violet-500/30 font-bold'
                                : 'text-slate-400 hover:text-white hover:bg-slate-900'
                        ]"
                    >
                        <Building2 class="w-4 h-4 flex-shrink-0" />
                        <span>Tenant Agencies</span>
                    </Link>

                    <Link
                        :href="route('superadmin.plans')"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors',
                            route().current('superadmin.plans*')
                                ? 'bg-violet-600/20 text-violet-400 border border-violet-500/30 font-bold'
                                : 'text-slate-400 hover:text-white hover:bg-slate-900'
                        ]"
                    >
                        <Layers class="w-4 h-4 flex-shrink-0" />
                        <span>Subscription Plans</span>
                    </Link>

                    <div class="pt-4 px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                        Shortcuts
                    </div>

                    <Link
                        :href="route('admin.dashboard')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-indigo-400 hover:bg-slate-900 transition-colors"
                    >
                        <ArrowLeft class="w-4 h-4 flex-shrink-0" />
                        <span>Back to Agency CRM</span>
                    </Link>

                    <a
                        :href="route('home')"
                        target="_blank"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:text-emerald-400 hover:bg-slate-900 transition-colors"
                    >
                        <ExternalLink class="w-4 h-4 flex-shrink-0" />
                        <span>Public Website</span>
                    </a>
                </div>
            </div>

            <div class="p-3 border-t border-slate-800">
                <button
                    type="button"
                    @click="logout"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:bg-rose-950/40 transition-colors"
                >
                    <LogOut class="w-4 h-4 text-rose-400" />
                    <span>Log Out</span>
                </button>
            </div>
        </aside>

        <!-- Main Body -->
        <div class="flex-1 flex flex-col min-w-0 bg-slate-900">
            <header class="h-16 bg-slate-950/80 backdrop-blur-md border-b border-slate-800 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="mobileSidebarOpen = true" class="lg:hidden text-slate-400 hover:text-white p-2">
                        <Menu class="w-5 h-5" />
                    </button>
                    <span class="text-xs font-bold text-violet-400 bg-violet-950/80 px-2.5 py-1 rounded-md border border-violet-800/40">
                        SUPER ADMIN PANEL
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <div class="text-right hidden sm:block">
                        <span class="block font-bold text-white leading-tight">{{ authUser.name }}</span>
                        <span class="block text-[11px] text-slate-400">{{ authUser.email }}</span>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
