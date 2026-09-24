<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Building2,
    Users,
    UserCheck,
    Sparkles,
    Kanban,
    CalendarCheck,
    CheckSquare,
    BookmarkCheck,
    CreditCard,
    DollarSign,
    UserCog,
    GitBranch,
    FolderLock,
    BarChart3,
    Settings,
    Shield,
    ExternalLink,
    LogOut,
    Menu,
    X,
    ChevronDown,
    PanelLeftClose,
    PanelLeftOpen,
    Building
} from 'lucide-vue-next';
import AppToast from '@/Components/UI/AppToast.vue';

const page = usePage();
const mobileSidebarOpen = ref(false);
const userMenuOpen = ref(false);

const isSidebarCollapsed = ref(
    typeof window !== 'undefined' ? localStorage.getItem('admin_sidebar_collapsed') === 'true' : false
);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    if (typeof window !== 'undefined') {
        localStorage.setItem('admin_sidebar_collapsed', isSidebarCollapsed.value);
    }
};

const authUser = computed(() => page.props.auth?.user || { name: 'Agent', email: '', role: 'agent' });
const currentTenant = computed(() => page.props.currentTenant || null);
const isSuperAdmin = computed(() => authUser.value.role === 'super_admin');

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] text-slate-800 antialiased font-sans flex">
        <AppToast />

        <!-- Mobile Sidebar Backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-2xs z-40 lg:hidden"
                @click="mobileSidebarOpen = false"
            ></div>
        </Transition>

        <!-- Sidebar (Desktop + Mobile Drawer) -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col justify-between transition-all duration-200 ease-in-out lg:static',
                isSidebarCollapsed ? 'lg:w-20' : 'lg:w-64 xl:w-72',
                'w-64 sm:w-72',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <div>
                <!-- Brand / Logo Header -->
                <div :class="['h-16 border-b border-slate-100 flex items-center', isSidebarCollapsed ? 'justify-center px-2' : 'justify-between px-6']">
                    <Link
                        :href="route('admin.dashboard')"
                        class="flex items-center space-x-3 font-bold text-base text-slate-900 group"
                        :title="isSidebarCollapsed ? 'Real Estate CRM' : undefined"
                    >
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-700 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 font-extrabold text-sm flex-shrink-0">
                            <Building2 class="w-5 h-5 text-white" />
                        </div>
                        <div v-if="!isSidebarCollapsed" class="leading-tight truncate">
                            <span class="block font-extrabold text-slate-900 tracking-tight text-sm">
                                {{ currentTenant ? currentTenant.name : 'RealEstate CRM' }}
                            </span>
                            <span class="block text-[10px] font-bold text-indigo-600 tracking-wider uppercase">
                                {{ authUser.role ? authUser.role.replace('_', ' ') : 'CRM Portal' }}
                            </span>
                        </div>
                    </Link>

                    <button
                        type="button"
                        @click="mobileSidebarOpen = false"
                        class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100"
                        aria-label="Close sidebar"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Navigation Links -->
                <div class="px-3 py-4 space-y-5 overflow-y-auto max-h-[calc(100vh-140px)] text-xs">
                    <!-- SECTION 1: CORE CRM -->
                    <div>
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Core CRM
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('admin.dashboard')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.dashboard')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Dashboard' : undefined"
                            >
                                <LayoutDashboard class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Dashboard</span>
                            </Link>

                            <Link
                                :href="route('admin.properties.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.properties.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Properties' : undefined"
                            >
                                <Building class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Property Listings</span>
                            </Link>

                            <Link
                                :href="route('admin.leads.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.leads.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Leads' : undefined"
                            >
                                <Users class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Buyer & Seller Leads</span>
                            </Link>

                            <Link
                                :href="route('admin.customers.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.customers.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Customers & Owners' : undefined"
                            >
                                <UserCheck class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Customers & Owners</span>
                            </Link>

                            <Link
                                :href="route('admin.matching.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.matching.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Property Matcher' : undefined"
                            >
                                <Sparkles class="w-4 h-4 text-amber-500 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Property Matching</span>
                            </Link>
                        </nav>
                    </div>

                    <!-- SECTION 2: SALES & PIPELINE -->
                    <div>
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Sales & Visits
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('admin.deals.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.deals.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Sales Pipeline' : undefined"
                            >
                                <Kanban class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Deals Pipeline</span>
                            </Link>

                            <Link
                                :href="route('admin.site-visits.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.site-visits.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Site Visits' : undefined"
                            >
                                <CalendarCheck class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Site Visits Calendar</span>
                            </Link>

                            <Link
                                :href="route('admin.tasks.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.tasks.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Follow-ups & Tasks' : undefined"
                            >
                                <CheckSquare class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Follow-ups & Tasks</span>
                            </Link>
                        </nav>
                    </div>

                    <!-- SECTION 3: FINANCE & TRANSACTIONS -->
                    <div>
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Finance & Deals
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('admin.bookings.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.bookings.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Bookings & Reservations' : undefined"
                            >
                                <BookmarkCheck class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Bookings & Reservations</span>
                            </Link>

                            <Link
                                :href="route('admin.payments.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.payments.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Payments & Receipts' : undefined"
                            >
                                <CreditCard class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Payments & Receipts</span>
                            </Link>

                            <Link
                                :href="route('admin.commissions.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.commissions.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Commissions' : undefined"
                            >
                                <DollarSign class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Commissions & Brokerage</span>
                            </Link>
                        </nav>
                    </div>

                    <!-- SECTION 4: AGENCY & OPERATIONS -->
                    <div>
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                            Agency & Team
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('admin.agents.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.agents.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Team & Agents' : undefined"
                            >
                                <UserCog class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Team & Agents</span>
                            </Link>

                            <Link
                                :href="route('admin.branches.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.branches.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Branch Offices' : undefined"
                            >
                                <GitBranch class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Branch Offices</span>
                            </Link>

                            <Link
                                :href="route('admin.documents.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.documents.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Document Vault' : undefined"
                            >
                                <FolderLock class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Document Vault</span>
                            </Link>

                            <Link
                                :href="route('admin.reports.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.reports.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Reports & Analytics' : undefined"
                            >
                                <BarChart3 class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Reports & Analytics</span>
                            </Link>

                            <Link
                                :href="route('admin.settings.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition-colors relative',
                                    isSidebarCollapsed ? 'justify-center' : '',
                                    route().current('admin.settings.*')
                                        ? 'bg-indigo-50 text-indigo-700 font-bold before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1 before:bg-indigo-600 before:rounded-r'
                                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80'
                                ]"
                                :title="isSidebarCollapsed ? 'Agency Settings' : undefined"
                            >
                                <Settings class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Agency Settings</span>
                            </Link>
                        </nav>
                    </div>

                    <!-- SUPER ADMIN SECTION (if applicable) -->
                    <div v-if="isSuperAdmin">
                        <div v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-bold text-violet-600 uppercase tracking-wider mb-1.5">
                            SaaS Platform
                        </div>
                        <nav class="space-y-0.5">
                            <Link
                                :href="route('superadmin.dashboard')"
                                class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold text-violet-700 bg-violet-50 hover:bg-violet-100/80 transition-colors"
                                :title="isSidebarCollapsed ? 'SaaS Super Admin' : undefined"
                            >
                                <Shield class="w-4 h-4 flex-shrink-0" />
                                <span v-if="!isSidebarCollapsed">Super Admin SaaS</span>
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Sidebar Footer -->
            <div class="p-3 border-t border-slate-100 space-y-1">
                <a
                    :href="route('home')"
                    target="_blank"
                    :class="[
                        'flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:bg-slate-50 transition-colors',
                        isSidebarCollapsed ? 'justify-center' : 'justify-between'
                    ]"
                    :title="isSidebarCollapsed ? 'Public Listings Site' : undefined"
                >
                    <span class="flex items-center gap-2">
                        <ExternalLink class="w-4 h-4 flex-shrink-0 text-slate-400" />
                        <span v-if="!isSidebarCollapsed">Public Website</span>
                    </span>
                    <span v-if="!isSidebarCollapsed" class="text-[9px] bg-slate-100 text-slate-500 font-bold px-1.5 py-0.5 rounded">Live</span>
                </a>

                <button
                    type="button"
                    @click="logout"
                    :class="[
                        'w-full flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors text-left',
                        isSidebarCollapsed ? 'justify-center' : 'gap-2.5'
                    ]"
                    :title="isSidebarCollapsed ? 'Log Out' : undefined"
                >
                    <LogOut class="w-4 h-4 text-rose-500 flex-shrink-0" />
                    <span v-if="!isSidebarCollapsed">Log Out</span>
                </button>
            </div>
        </aside>

        <!-- Main Layout Area: Header + Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-xs">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="toggleSidebar"
                        class="hidden lg:flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                        :title="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    >
                        <PanelLeftOpen v-if="isSidebarCollapsed" class="w-5 h-5 text-indigo-600" />
                        <PanelLeftClose v-else class="w-5 h-5" />
                    </button>

                    <button
                        type="button"
                        @click="mobileSidebarOpen = true"
                        class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition"
                    >
                        <Menu class="w-5 h-5" />
                    </button>

                    <!-- Agency & Branch Indicator -->
                    <div class="hidden sm:flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            <Building2 class="w-3.5 h-3.5" />
                            {{ currentTenant ? currentTenant.name : 'RealEstate CRM' }}
                        </span>
                        <span v-if="authUser.branch" class="text-xs text-slate-400 font-medium">
                            / {{ authUser.branch.name }}
                        </span>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-3">
                    <a
                        :href="route('home')"
                        target="_blank"
                        class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-700 transition border border-slate-200"
                    >
                        <span>Public Listings</span>
                        <ExternalLink class="w-3.5 h-3.5" />
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 transition focus:outline-none"
                        >
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-700 text-white font-extrabold text-xs flex items-center justify-center shadow-xs">
                                {{ authUser.name ? authUser.name.substring(0, 2).toUpperCase() : 'AD' }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <span class="block text-xs font-bold text-slate-900 leading-tight">{{ authUser.name }}</span>
                                <span class="block text-[10px] text-slate-400 capitalize font-medium">{{ authUser.role ? authUser.role.replace('_', ' ') : 'Agent' }}</span>
                            </div>
                            <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
                        </button>

                        <div
                            v-if="userMenuOpen"
                            @click.away="userMenuOpen = false"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 py-1.5 z-50 animate-in fade-in slide-in-from-top-1"
                        >
                            <div class="px-4 py-2.5 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-900">{{ authUser.name }}</p>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ authUser.email }}</p>
                                <div class="mt-1.5">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        {{ authUser.role ? authUser.role.replace('_', ' ') : 'User' }}
                                    </span>
                                </div>
                            </div>

                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition"
                                @click="userMenuOpen = false"
                            >
                                <Settings class="w-3.5 h-3.5 text-slate-400" />
                                <span>Profile Settings</span>
                            </Link>

                            <div v-if="isSuperAdmin" class="border-t border-slate-100">
                                <Link
                                    :href="route('superadmin.dashboard')"
                                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-violet-700 hover:bg-violet-50 transition"
                                    @click="userMenuOpen = false"
                                >
                                    <Shield class="w-3.5 h-3.5 text-violet-600" />
                                    <span>Super Admin Platform</span>
                                </Link>
                            </div>

                            <div class="border-t border-slate-100 mt-1 pt-1">
                                <button
                                    type="button"
                                    @click="logout"
                                    class="w-full flex items-center gap-2 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition text-left"
                                >
                                    <LogOut class="w-3.5 h-3.5 text-rose-500" />
                                    <span>Log Out</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="py-4 px-6 sm:px-8 border-t border-slate-200 bg-white/70 text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>
                    &copy; {{ new Date().getFullYear() }} {{ currentTenant ? currentTenant.name : 'Real Estate Pro CRM' }} &bull; Enterprise SaaS
                </div>
                <div class="text-[11px] text-slate-400">
                    Laravel 12 &bull; Inertia.js v2 &bull; Vue 3 &bull; Tailwind CSS
                </div>
            </footer>
        </div>
    </div>
</template>
