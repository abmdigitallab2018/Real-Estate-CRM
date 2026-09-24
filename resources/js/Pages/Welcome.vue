<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTextarea from '@/Components/UI/AppTextarea.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppToast from '@/Components/UI/AppToast.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import {
    Terminal,
    Sparkles,
    Download,
    ArrowRight,
    ExternalLink,
    Github,
    Linkedin,
    Twitter,
    Facebook,
    Instagram,
    Mail,
    Layers,
    Cpu,
    CheckCircle2,
    Smartphone,
    Database,
    ShieldCheck,
    Menu,
    X,
    Clock,
    Copy,
    Check,
    Send,
    Zap,
    Newspaper,
    BookOpen,
    CreditCard
} from 'lucide-vue-next';

const props = defineProps({
    services: {
        type: Array,
        default: () => []
    },
    portfolios: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    posts: {
        type: Array,
        default: () => []
    },
    pricingPlans: {
        type: Array,
        default: () => []
    },
    about: {
        type: Object,
        default: () => null
    },
    siteSetting: {
        type: Object,
        default: () => null
    },
    canLogin: Boolean,
    canRegister: Boolean,
});

// Mobile Navigation Drawer State
const mobileNavOpen = ref(false);

// Active Category Filter for Portfolio
const activeCategory = ref('All');

// PWA Install Prompt State
const deferredPrompt = ref(null);
const isInstallable = ref(false);

onMounted(() => {
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt.value = e;
        isInstallable.value = true;
    });

    window.addEventListener('appinstalled', () => {
        isInstallable.value = false;
        deferredPrompt.value = null;
    });
});

const installPWA = async () => {
    if (!deferredPrompt.value) return;
    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    if (outcome === 'accepted') {
        isInstallable.value = false;
    }
    deferredPrompt.value = null;
};

// ==================== MASTER MENU SETTINGS ====================
const defaultNavLinks = [
    { key: 'home', label: 'Home' },
    { key: 'about', label: 'About' },
    { key: 'services', label: 'Services' },
    { key: 'portfolio', label: 'Work' },
    { key: 'pricing', label: 'Pricing' },
    { key: 'posts', label: 'Blog' },
    { key: 'contact', label: 'Contact' },
];

const isSectionEnabled = (key) => {
    const settingKey = `menu_${key}`;
    if (props.siteSetting && props.siteSetting[settingKey] !== undefined) {
        const val = props.siteSetting[settingKey];
        return val === 'enabled' || val === true || val === '1';
    }
    if (props.siteSetting?.menu_settings && Array.isArray(props.siteSetting.menu_settings)) {
        const item = props.siteSetting.menu_settings.find(i => i.key === key);
        if (item) return Boolean(item.is_enabled);
    }
    return true;
};

const enabledNavLinks = computed(() => {
    return defaultNavLinks.filter(item => isSectionEnabled(item.key));
});

// ==================== ORGANIZATION & BRANDING SETTINGS ====================
const branding = computed(() => ({
    siteTitle: props.siteSetting?.site_title || 'ABM Digital Lab',
    siteSubtitle: props.siteSetting?.site_subtitle || 'Software Architect',
    logoText: props.siteSetting?.logo_text || 'BM',
    logoImage: props.siteSetting?.logo_image
        ? (props.siteSetting.logo_image.startsWith('http') || props.siteSetting.logo_image.startsWith('/')
            ? props.siteSetting.logo_image
            : `/storage/${props.siteSetting.logo_image}`)
        : null,
    contactEmail: props.siteSetting?.contact_email || 'bhavesh@example.com',
    contactPhone: props.siteSetting?.contact_phone || '',
    location: props.siteSetting?.location || 'Remote / Worldwide',
    githubUrl: props.siteSetting?.github_url || 'https://github.com/bhavesh57',
    githubUsername: props.siteSetting?.github_username || 'bhavesh57',
    linkedinUrl: props.siteSetting?.linkedin_url || 'https://linkedin.com/in/bhavesh-methaniya',
    linkedinUsername: props.siteSetting?.linkedin_username || 'bhavesh-methaniya',
    stackoverflowUrl: props.siteSetting?.stackoverflow_url || 'https://stackoverflow.com',
    stackoverflowUsername: props.siteSetting?.stackoverflow_username || 'bhavesh-methaniya',
    facebookUrl: props.siteSetting?.facebook_url || 'https://facebook.com',
    facebookUsername: props.siteSetting?.facebook_username || 'bhavesh.methaniya',
    instagramUrl: props.siteSetting?.instagram_url || 'https://instagram.com',
    instagramUsername: props.siteSetting?.instagram_username || '@bhavesh_dev',
    twitterUrl: props.siteSetting?.twitter_url || 'https://twitter.com',
    twitterUsername: props.siteSetting?.twitter_username || '@bhavesh',
    footerText: props.siteSetting?.footer_text || 'Senior Full-Stack Software Engineer',
    copyrightText: props.siteSetting?.copyright_text || `© ${new Date().getFullYear()} ABM Digital Lab. All rights reserved.`,
}));

// ==================== FRONT HOME / HERO SETTINGS ====================
const heroSettings = computed(() => ({
    badge: props.siteSetting?.hero_badge || 'Senior Full-Stack Developer & Solutions Architect',
    title: props.siteSetting?.hero_title || 'Building Digital Products That Scale',
    highlight: props.siteSetting?.hero_highlight || 'Products That Scale',
    description: props.siteSetting?.hero_description || props.about?.hero_tagline || 'Specializing in high-performance digital products, modern cloud architecture, resilient data systems, offline-first platforms, and mission-critical enterprise solutions.',
    ctaPrimaryText: props.siteSetting?.cta_primary_text || 'Hire Me',
    ctaPrimaryLink: props.siteSetting?.cta_primary_link || '#contact',
    ctaSecondaryText: props.siteSetting?.cta_secondary_text || 'View Projects',
    ctaSecondaryLink: props.siteSetting?.cta_secondary_link || '#portfolio',
    availabilityStatus: props.siteSetting?.availability_status || 'Available for contract & consulting',
    resumeButtonEnabled: props.siteSetting?.resume_button_enabled === 'enabled' || props.siteSetting?.resume_button_enabled === true || props.siteSetting?.resume_button_enabled === '1' || props.siteSetting?.resume_button_enabled === undefined,
    resumeButtonText: props.siteSetting?.resume_button_text || 'Download CV',
}));

// ==================== FALLBACKS FOR SERVICES & PORTFOLIOS ====================
const fallbackServices = [
    {
        id: 's1',
        title: 'Full-Stack Web Development',
        icon: '💻',
        description: 'Architecting robust end-to-end web applications and distributed systems. Clean architecture, rock-solid security, and responsive UI.',
        is_active: true,
    },
    {
        id: 's2',
        title: 'Progressive Web Apps (PWA)',
        icon: '📱',
        description: 'Building installable, offline-capable mobile and desktop web applications with Service Workers, background sync, and app-like performance.',
        is_active: true,
    },
    {
        id: 's3',
        title: 'High-Throughput API Architecture',
        icon: '⚡',
        description: 'Designing scalable RESTful and GraphQL APIs with OAuth2/Sanctum authentication, rate limiting, and seamless third-party integrations.',
        is_active: true,
    },
    {
        id: 's4',
        title: 'Database Architecture & Tuning',
        icon: '🗄️',
        description: 'Relational schema design, advanced query indexing, multi-tier caching layers, and high-performance transactional scaling.',
        is_active: true,
    },
    {
        id: 's5',
        title: 'Cloud DevOps & Containerization',
        icon: '☁️',
        description: 'Automated CI/CD pipelines, container orchestration, cloud VPS provisioning, SSL hardening, and zero-downtime deployments.',
        is_active: true,
    },
    {
        id: 's6',
        title: 'SaaS Back-Office & Admin Portals',
        icon: '🛡️',
        description: 'Bespoke administrative control systems, role-based access control (RBAC), analytical telemetry charts, and automated data exports.',
        is_active: true,
    }
];

const fallbackPortfolios = [
    {
        id: 'p1',
        title: 'Enterprise ERP & Logistics Platform',
        category: 'SaaS',
        image_url: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80',
        project_url: 'https://github.com/bhavesh57',
        description: 'Comprehensive business resource planning suite managing multi-warehouse inventory, freight routes, and automated billing workflows.',
        is_active: true,
    },
    {
        id: 'p2',
        title: 'Multi-Tenant E-Commerce PWA',
        category: 'PWA',
        image_url: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
        project_url: 'https://github.com/bhavesh57',
        description: 'High-conversion storefront with offline catalog caching, instant search indexing, Stripe checkout, and push notification cart recovery.',
        is_active: true,
    },
    {
        id: 'p3',
        title: 'Fintech Real-Time Telemetry Dashboard',
        category: 'Full-Stack',
        image_url: 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?auto=format&fit=crop&w=800&q=80',
        project_url: 'https://github.com/bhavesh57',
        description: 'Real-time financial analytics portal ingesting high-frequency market data with WebSocket feeds, interactive charting, and ledger auditing.',
        is_active: true,
    },
    {
        id: 'p4',
        title: 'High-Concurrency Microservices API Gateway',
        category: 'API',
        image_url: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
        project_url: 'https://github.com/bhavesh57',
        description: 'Distributed authentication and reverse proxy gateway handling millions of requests with token buckets and circuit breakers.',
        is_active: true,
    }
];

const displayServices = computed(() => {
    return props.services && props.services.length > 0 ? props.services : fallbackServices;
});

const displayPortfolios = computed(() => {
    return props.portfolios && props.portfolios.length > 0 ? props.portfolios : fallbackPortfolios;
});

const categories = computed(() => {
    const list = ['All'];
    if (props.categories && props.categories.length > 0) {
        props.categories.forEach(c => {
            if (c.name && !list.includes(c.name)) {
                list.push(c.name);
            }
        });
    } else {
        displayPortfolios.value.forEach(p => {
            if (p.category && !list.includes(p.category)) {
                list.push(p.category);
            }
        });
    }
    return list;
});

const filteredPortfolios = computed(() => {
    if (activeCategory.value === 'All') return displayPortfolios.value;
    return displayPortfolios.value.filter(p => p.category === activeCategory.value);
});

// ==================== DAILY POSTS & UPDATES ====================
const fallbackPosts = [
    {
        id: 'p1',
        title: 'Multi-Tier Caching Strategies for High-Concurrency SaaS',
        category: 'SaaS',
        summary: 'How to coordinate in-memory cache, distributed stores, and database query layers to achieve sub-40ms latency under surge loads.',
        content: `In high-throughput SaaS applications, caching is not just an optimization—it is a critical reliability pillar.\n\nWhen scaling to tens of thousands of concurrent users, a single-tier cache often becomes a network bottleneck. By implementing a multi-tier caching architecture:\n\n1. In-Process L1 Cache: Storing hot configuration and immutable master items directly in application memory for instantaneous (<1ms) reads.\n2. Distributed L2 Cache: Coordinating multi-instance state, session validation, and rate-limiting token buckets with optimal TTL expiration policies.\n3. Proactive Cache Warming: Utilizing background workers to re-populate heavy aggregation datasets before expiration, eliminating cache stampedes and latency spikes.\n\nAdopting this pattern consistently cut our p99 response times from 420ms down to 28ms across mission-critical endpoints.`,
        image_url: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=800&q=80',
        read_time: '4 min read',
        published_at_formatted: 'Yesterday',
    },
    {
        id: 'p2',
        title: 'Architecting Resilient Offline-First PWAs with Background Sync',
        category: 'PWA',
        summary: 'Designing client-side IndexedDB pipelines and service workers to keep operations seamless when network conditions degrade.',
        content: `Modern users expect enterprise web applications to feel as dependable as native desktop software, even when network connectivity fluctuates.\n\nKey architectural pillars for offline-first resilience:\n\n- Service Worker Caching Strategies: Stale-While-Revalidate for application assets combined with Network-First fallbacks for live telemetry.\n- Optimistic Mutation Queuing: Capturing form updates and dispatch events into an IndexedDB transactional store, updating the UI immediately.\n- Background Sync Synchronization: When connection resumes, syncing queued mutations in chronological order with idempotent server endpoints.\n\nThis approach ensures field operators and logistics teams never lose data during intermittent connection drops.`,
        image_url: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80',
        read_time: '5 min read',
        published_at_formatted: '2 days ago',
    },
    {
        id: 'p3',
        title: 'Clean Domain Boundaries & Modular Architecture',
        category: 'Full-Stack',
        summary: 'Structuring large codebases into cohesive domain modules to enhance maintainability, test isolation, and team velocity.',
        content: `As products evolve, monoliths often become unwieldy without strict architectural boundaries.\n\nBy segregating core business logic into domain-driven modules, we decouple billing, inventory, and authentication into distinct layers. Each domain encapsulates its own models, repositories, and transactional services, making automated testing fast, isolated, and highly reliable.\n\nInvesting in clean boundaries early prevents technical debt from accumulating as feature complexity grows.`,
        image_url: 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?auto=format&fit=crop&w=800&q=80',
        read_time: '3 min read',
        published_at_formatted: '3 days ago',
    }
];

const displayPosts = computed(() => {
    return props.posts && props.posts.length > 0 ? props.posts : fallbackPosts;
});

const displayPricingPlans = computed(() => {
    return props.pricingPlans && props.pricingPlans.length > 0 ? props.pricingPlans : [];
});

const activePost = ref(null);
const postModalOpen = ref(false);

const openPostReader = (post) => {
    activePost.value = post;
    postModalOpen.value = true;
};

// ==================== NEWSLETTER SUBSCRIPTION FORM ====================
const newsletterForm = useForm({
    email: '',
    name: '',
});
const newsletterSuccessMessage = ref('');
const newsletterInfoMessage = ref('');

const submitNewsletter = () => {
    newsletterForm.post(route('newsletter.subscribe'), {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.newsletter_info) {
                newsletterInfoMessage.value = page.props.flash.newsletter_info;
            } else {
                newsletterSuccessMessage.value = 'Thank you for subscribing! You will receive our latest architectural updates.';
            }
            newsletterForm.reset();
        }
    });
};

// ==================== INTERACTIVE CONTACT FORM ====================
const contactForm = useForm({
    name: '',
    email: '',
    subject: '',
    message: ''
});

const formSubmittedSuccess = ref(false);
const submittedSenderName = ref('');

const subjectSuggestions = [
    '🚀 New SaaS Project',
    '💼 Contract Consulting',
    '⚡ Architecture Review',
    '🤝 Technical Partnership'
];

const setSubject = (subject) => {
    contactForm.subject = subject;
};

const emailCopied = ref(false);
const copyEmail = async () => {
    try {
        await navigator.clipboard.writeText(branding.value.contactEmail);
        emailCopied.value = true;
        setTimeout(() => {
            emailCopied.value = false;
        }, 2000);
    } catch {
        // Fallback
    }
};

const submitContact = () => {
    contactForm.post(route('contact.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            submittedSenderName.value = contactForm.name;
            formSubmittedSuccess.value = true;
            contactForm.reset();
        }
    });
};

const resetContactForm = () => {
    formSubmittedSuccess.value = false;
    contactForm.reset();
};
</script>

<template>
    <Head :title="`${branding.siteTitle} - ${heroSettings.badge}`" />

    <!-- Toast Notification System -->
    <AppToast />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-800 font-sans antialiased selection:bg-indigo-500 selection:text-white">
        <!-- ==================== STICKY HEADER ==================== -->
        <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200/80 transition-all duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-20">
                    <!-- Brand / Logo -->
                    <a href="#hero" class="flex items-center space-x-3 group">
                        <!-- Custom Image Logo or Monogram -->
                        <div
                            v-if="branding.logoImage"
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl overflow-hidden shadow-sm flex items-center justify-center bg-white border border-slate-200 group-hover:scale-105 transition-transform"
                        >
                            <img :src="branding.logoImage" :alt="branding.siteTitle" class="w-full h-full object-contain" />
                        </div>
                        <div
                            v-else
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white flex items-center justify-center font-extrabold text-sm tracking-wider shadow-sm shadow-indigo-500/25 group-hover:scale-105 transition-transform duration-200"
                        >
                            {{ branding.logoText }}
                        </div>

                        <div class="leading-none">
                            <span class="block font-bold text-slate-900 text-base sm:text-lg tracking-tight group-hover:text-indigo-600 transition-colors">
                                {{ branding.siteTitle }}
                            </span>
                            <span class="block text-[11px] font-medium text-slate-400 mt-0.5">{{ branding.siteSubtitle }}</span>
                        </div>
                    </a>

                    <!-- Desktop Nav Links (Dynamic from Master Menu Settings) -->
                    <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                        <a
                            v-for="link in enabledNavLinks"
                            :key="link.key"
                            :href="`#${link.key}`"
                            class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-xl hover:bg-slate-100/70 transition-colors"
                        >
                            {{ link.label }}
                        </a>
                    </nav>

                    <!-- Header Right: PWA Install (Admin Go button removed as requested) -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <button
                            v-if="isInstallable"
                            type="button"
                            @click="installPWA"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/80 rounded-xl transition-colors shadow-2xs"
                        >
                            <Smartphone class="w-3.5 h-3.5" />
                            <span>Install App</span>
                        </button>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <button
                        type="button"
                        @click="mobileNavOpen = !mobileNavOpen"
                        class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none"
                        aria-label="Toggle Navigation"
                    >
                        <Menu v-if="!mobileNavOpen" class="w-6 h-6" />
                        <X v-else class="w-6 h-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div
                v-if="mobileNavOpen"
                class="md:hidden bg-white border-b border-slate-200 px-4 pt-2 pb-6 space-y-2 shadow-lg"
            >
                <a
                    v-for="link in enabledNavLinks"
                    :key="link.key"
                    :href="`#${link.key}`"
                    @click="mobileNavOpen = false"
                    class="block px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    {{ link.label }}
                </a>

                <div v-if="isInstallable" class="pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="installPWA(); mobileNavOpen = false"
                        class="w-full flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-xl"
                    >
                        <Smartphone class="w-4 h-4" />
                        <span>Install Progressive Web App</span>
                    </button>
                </div>
            </div>
        </header>

        <main>
            <!-- ==================== HERO SECTION ==================== -->
            <section
                v-if="isSectionEnabled('home')"
                id="home"
                class="relative bg-[#0F172A] text-white pt-20 pb-16 lg:pt-28 lg:pb-20 overflow-hidden"
            >
                <!-- Subtle Gradient Glow & Grid Accent -->
                <div class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-25"></div>
                <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[350px] bg-gradient-to-tr from-indigo-600/20 to-violet-600/20 blur-3xl rounded-full pointer-events-none"></div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                        <!-- Left Column: Content -->
                        <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                            <!-- Role Badge -->
                            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-indigo-300 text-xs font-semibold shadow-inner">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400 -ml-3"></span>
                                <span>{{ heroSettings.badge }}</span>
                            </div>

                            <!-- Main Headline -->
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.1]">
                                <template v-if="heroSettings.highlight && heroSettings.title.includes(heroSettings.highlight)">
                                    {{ heroSettings.title.replace(heroSettings.highlight, '') }}
                                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-violet-300 to-indigo-200">
                                        {{ heroSettings.highlight }}
                                    </span>
                                </template>
                                <template v-else>
                                    {{ heroSettings.title }}
                                </template>
                            </h1>

                            <!-- Supporting Description -->
                            <p class="text-base sm:text-lg text-slate-300 max-w-2xl leading-relaxed mx-auto lg:mx-0 font-normal">
                                {{ heroSettings.description }}
                            </p>

                            <!-- Core Competencies & Capabilities -->
                            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2 pt-1">
                                <span class="px-2.5 py-1 text-xs font-mono rounded-lg bg-slate-800/90 text-slate-300 border border-slate-700/80">Enterprise SaaS</span>
                                <span class="px-2.5 py-1 text-xs font-mono rounded-lg bg-slate-800/90 text-slate-300 border border-slate-700/80">Full-Stack Architecture</span>
                                <span class="px-2.5 py-1 text-xs font-mono rounded-lg bg-slate-800/90 text-slate-300 border border-slate-700/80">High-Throughput APIs</span>
                                <span class="px-2.5 py-1 text-xs font-mono rounded-lg bg-slate-800/90 text-slate-300 border border-slate-700/80">Offline-First Apps</span>
                                <span class="px-2.5 py-1 text-xs font-mono rounded-lg bg-slate-800/90 text-slate-300 border border-slate-700/80">Scalable Databases</span>
                            </div>

                            <!-- Call-to-Actions -->
                            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-3">
                                <AppButton
                                    v-if="heroSettings.ctaPrimaryText"
                                    :href="heroSettings.ctaPrimaryLink"
                                    variant="gradient"
                                    size="lg"
                                >
                                    <span>{{ heroSettings.ctaPrimaryText }}</span>
                                    <ArrowRight class="w-4 h-4" />
                                </AppButton>

                                <AppButton
                                    v-if="heroSettings.ctaSecondaryText"
                                    :href="heroSettings.ctaSecondaryLink"
                                    variant="secondary"
                                    size="lg"
                                >
                                    <span>{{ heroSettings.ctaSecondaryText }}</span>
                                </AppButton>

                                <a
                                    v-if="heroSettings.resumeButtonEnabled && about?.resume_url"
                                    :href="about.resume_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-base font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition duration-150 shadow-xs"
                                >
                                    <Download class="w-4 h-4 text-indigo-400" />
                                    <span>{{ heroSettings.resumeButtonText }}</span>
                                </a>
                            </div>

                            <!-- Live Availability Status -->
                            <div class="flex items-center justify-center lg:justify-start pt-2">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-xs text-slate-300 shadow-2xs font-mono">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>{{ heroSettings.availabilityStatus || 'Available for contract & consulting' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Sleek Glassmorphic Tech Highlight -->
                        <div class="lg:col-span-5">
                            <div class="relative mx-auto max-w-lg lg:max-w-none">
                                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500/25 to-violet-500/25 rounded-3xl blur-xl opacity-50"></div>

                                <div class="relative rounded-3xl bg-slate-900/90 backdrop-blur-xl border border-slate-800 shadow-2xl p-6 sm:p-7 space-y-6 text-left">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                                        <div class="flex items-center space-x-2.5">
                                            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                                                <Layers class="w-4 h-4" />
                                            </div>
                                            <div>
                                                <span class="block text-xs font-bold text-white tracking-wide">Production Architecture</span>
                                                <span class="block text-[10px] text-slate-400 font-mono">High-Scale Web & API Systems</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-mono font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span>Active</span>
                                        </div>
                                    </div>

                                    <!-- 4 Key Metrics -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                                <span class="text-[10px] font-mono uppercase tracking-wider">Experience</span>
                                                <ShieldCheck class="w-3.5 h-3.5 text-indigo-400" />
                                            </div>
                                            <div class="text-xl font-black text-white font-mono">
                                                {{ about?.years_experience || 5 }}+ Years
                                            </div>
                                            <span class="text-[10px] text-slate-500">Enterprise & Cloud</span>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                                <span class="text-[10px] font-mono uppercase tracking-wider">Completed</span>
                                                <Sparkles class="w-3.5 h-3.5 text-amber-400" />
                                            </div>
                                            <div class="text-xl font-black text-white font-mono">
                                                {{ about?.completed_projects || 45 }}+ Projects
                                            </div>
                                            <span class="text-[10px] text-slate-500">Shipped to Production</span>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                                <span class="text-[10px] font-mono uppercase tracking-wider">API Latency</span>
                                                <Cpu class="w-3.5 h-3.5 text-emerald-400" />
                                            </div>
                                            <div class="text-xl font-black text-white font-mono">
                                                &lt; 35ms
                                            </div>
                                            <span class="text-[10px] text-slate-500">Multi-Tier Caching</span>
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80">
                                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                                <span class="text-[10px] font-mono uppercase tracking-wider">Reliability</span>
                                                <CheckCircle2 class="w-3.5 h-3.5 text-indigo-400" />
                                            </div>
                                            <div class="text-xl font-black text-white font-mono">
                                                99.9%
                                            </div>
                                            <span class="text-[10px] text-slate-500">Client Satisfaction</span>
                                        </div>
                                    </div>

                                    <!-- Stack Badges -->
                                    <div class="pt-3 border-t border-slate-800 space-y-2">
                                        <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400 block">Core Architecture Stack</span>
                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">Laravel 12</span>
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-violet-500/10 text-violet-300 border border-violet-500/20">Vue 3 / Inertia</span>
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">Tailwind CSS</span>
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-amber-500/10 text-amber-300 border border-amber-500/20">MySQL & Redis</span>
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">PWA & Offline</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== ABOUT SECTION ==================== -->
            <section
                v-if="isSectionEnabled('about')"
                id="about"
                class="py-16 sm:py-20 bg-white border-b border-slate-200/80"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                        <div class="lg:col-span-7 space-y-6">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">About Me</span>
                                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1.5">
                                    Engineering robust, scalable software with product-driven focus.
                                </h2>
                            </div>

                            <p class="text-base text-slate-600 leading-relaxed whitespace-pre-line font-normal">
                                {{ about?.bio || 'With over 5 years of professional engineering experience, I architect scalable web applications, API microservices, and modern SaaS products. I specialize in building reliable, high-performance systems, crafting clean, maintainable code that scales gracefully from initial MVP to high-volume production workloads.' }}
                            </p>

                            <div class="pt-2 flex flex-wrap items-center gap-4">
                                <a
                                    v-if="about?.resume_url"
                                    :href="about.resume_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-500/20 transition-all"
                                >
                                    <Download class="w-4 h-4" />
                                    <span>Download Resume</span>
                                </a>

                                <AppButton
                                    href="#contact"
                                    variant="secondary"
                                    size="md"
                                >
                                    <span>Get in Touch</span>
                                </AppButton>
                            </div>
                        </div>

                        <div class="lg:col-span-5 grid grid-cols-2 gap-4 sm:gap-5">
                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-2xs text-center sm:text-left">
                                <span class="text-3xl sm:text-4xl font-extrabold text-indigo-600 tracking-tight font-mono">
                                    {{ about?.years_experience || 5 }}+
                                </span>
                                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mt-2">
                                    Years Experience
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    Full-stack development & cloud architecture.
                                </p>
                            </div>

                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-2xs text-center sm:text-left">
                                <span class="text-3xl sm:text-4xl font-extrabold text-emerald-600 tracking-tight font-mono">
                                    {{ about?.completed_projects || 45 }}+
                                </span>
                                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mt-2">
                                    Completed Projects
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    SaaS apps, APIs & client platforms.
                                </p>
                            </div>

                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-2xs text-center sm:text-left">
                                <span class="text-3xl sm:text-4xl font-extrabold text-violet-600 tracking-tight font-mono">
                                    15+
                                </span>
                                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mt-2">
                                    Technologies
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    Cloud Architecture, APIs, Caching, Databases.
                                </p>
                            </div>

                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-2xs text-center sm:text-left">
                                <span class="text-3xl sm:text-4xl font-extrabold text-blue-600 tracking-tight font-mono">
                                    100%
                                </span>
                                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider mt-2">
                                    Client Focus
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    High reliability, security & on-time delivery.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== SERVICES SECTION ==================== -->
            <section
                v-if="isSectionEnabled('services')"
                id="services"
                class="py-16 sm:py-20 bg-[#F8FAFC] border-b border-slate-200/80"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-12">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Capabilities</span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1.5">
                            Specialized Technical Services
                        </h2>
                        <p class="text-base text-slate-600 mt-3 leading-relaxed">
                            Comprehensive engineering solutions designed to deliver high-performance, maintainable web systems and digital products.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="service in displayServices"
                            :key="service.id"
                            class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-xs hover:shadow-md hover:-translate-y-1 hover:border-indigo-200 transition-all duration-200 flex flex-col justify-between group"
                        >
                            <div>
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg mb-5 border border-indigo-100 shadow-2xs group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-200">
                                    {{ service.icon ? service.icon.substring(0, 4) : '⚡' }}
                                </div>

                                <h3 class="text-lg font-bold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors">
                                    {{ service.title }}
                                </h3>

                                <p class="text-xs sm:text-sm text-slate-600 mt-2.5 leading-relaxed font-normal">
                                    {{ service.description }}
                                </p>
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center text-xs font-semibold text-indigo-600 group-hover:text-indigo-800">
                                <span>Learn more</span>
                                <ArrowRight class="w-3.5 h-3.5 ml-1.5 group-hover:translate-x-1 transition-transform" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== PORTFOLIO / SELECTED WORK ==================== -->
            <section
                v-if="isSectionEnabled('portfolio')"
                id="portfolio"
                class="py-16 sm:py-20 bg-white border-b border-slate-200/80"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-8 sm:mb-10">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Showcase</span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1.5">
                            Selected Work
                        </h2>
                        <p class="text-base text-slate-600 mt-3 leading-relaxed">
                            A curated selection of production applications, APIs, SaaS platforms, and enterprise solutions.
                        </p>
                    </div>

                    <!-- Category Filter Buttons -->
                    <div class="flex items-center justify-center gap-2 flex-wrap mb-8 sm:mb-10">
                        <button
                            v-for="cat in categories"
                            :key="cat"
                            type="button"
                            @click="activeCategory = cat"
                            :class="[
                                'px-4 py-1.5 rounded-full text-xs font-semibold transition-all duration-150',
                                activeCategory === cat
                                    ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/25'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80'
                            ]"
                        >
                            {{ cat }}
                        </button>
                    </div>

                    <!-- Project Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div
                            v-for="project in filteredPortfolios"
                            :key="project.id"
                            class="bg-[#F8FAFC] rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs hover:shadow-md hover:border-slate-300 transition-all duration-200 flex flex-col justify-between group"
                        >
                            <div>
                                <div class="relative w-full aspect-16/9 overflow-hidden bg-slate-100 border-b border-slate-200/80">
                                    <img
                                        v-if="project.image_url"
                                        :src="project.image_url"
                                        :alt="project.title"
                                        class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-300"
                                        loading="lazy"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-medium">
                                        No image preview available
                                    </div>

                                    <div class="absolute top-3 left-3">
                                        <AppBadge variant="indigo" size="sm">
                                            {{ project.category || 'Engineering' }}
                                        </AppBadge>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors">
                                        {{ project.title }}
                                    </h3>

                                    <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                                        {{ project.description }}
                                    </p>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-white/80 border-t border-slate-200/80 flex items-center justify-between">
                                <a
                                    v-if="project.project_url"
                                    :href="project.project_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition"
                                >
                                    <span>Explore Project</span>
                                    <ExternalLink class="w-3.5 h-3.5" />
                                </a>
                                <span v-else class="text-xs text-slate-400">Internal Enterprise Suite</span>

                                <a
                                    :href="branding.githubUrl"
                                    target="_blank"
                                    class="text-xs font-semibold text-slate-500 hover:text-slate-900 inline-flex items-center gap-1 transition"
                                >
                                    <Github class="w-3.5 h-3.5" />
                                    <span>Source</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== PRICING & INVESTMENT SECTION ==================== -->
            <section
                v-if="isSectionEnabled('pricing') && displayPricingPlans.length > 0"
                id="pricing"
                class="py-16 sm:py-20 bg-slate-50 border-b border-slate-200/80"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 sm:mb-12 gap-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Investment Tiers</span>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1.5">
                                Transparent Pricing & Service Packages
                            </h2>
                            <p class="text-base text-slate-600 mt-3 max-w-2xl leading-relaxed">
                                Predictable budgets with comprehensive architectural deliverables, from startup MVPs to distributed systems.
                            </p>
                        </div>
                        <Link
                            :href="'/pricing'"
                            class="inline-flex items-center gap-1.5 text-sm font-bold text-indigo-600 hover:text-indigo-700 transition"
                        >
                            <span>View Full Comparison</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
                        <div
                            v-for="plan in displayPricingPlans"
                            :key="plan.id"
                            :class="[
                                'rounded-3xl flex flex-col justify-between transition-all duration-300 relative',
                                plan.is_featured
                                    ? 'bg-white border-2 border-indigo-600 shadow-2xl shadow-indigo-600/15 ring-4 ring-indigo-600/10 lg:-translate-y-2'
                                    : 'bg-white border border-slate-200 shadow-sm hover:shadow-md'
                            ]"
                        >
                            <div
                                v-if="plan.badge"
                                class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full text-xs font-bold tracking-wide uppercase shadow-md flex items-center gap-1.5"
                                :class="plan.is_featured ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white' : 'bg-slate-900 text-white'"
                            >
                                <Sparkles class="w-3 h-3 text-amber-300" />
                                <span>{{ plan.badge }}</span>
                            </div>

                            <div class="p-8 space-y-6 flex-1">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-900">{{ plan.name }}</h3>
                                    <p v-if="plan.tagline" class="text-xs text-slate-500 mt-1 min-h-[32px]">{{ plan.tagline }}</p>
                                </div>

                                <div class="pt-2 border-t border-slate-100">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-4xl font-black text-slate-900">{{ plan.formatted_price || (plan.currency + plan.price) }}</span>
                                        <span class="text-xs font-semibold text-slate-500 capitalize">/ {{ plan.billing_period }}</span>
                                    </div>
                                    <p v-if="plan.description" class="text-xs text-slate-500 mt-2 leading-relaxed">{{ plan.description }}</p>
                                </div>

                                <div class="space-y-2.5 pt-4 border-t border-slate-100">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Included Features</span>
                                    <ul class="space-y-2">
                                        <li
                                            v-for="(f, fi) in (plan.features || [])"
                                            :key="fi"
                                            class="flex items-start gap-2.5 text-xs text-slate-700 font-medium"
                                        >
                                            <CheckCircle2 class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" />
                                            <span>{{ f }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="p-8 pt-0">
                                <a
                                    :href="plan.cta_url || '#contact'"
                                    :class="[
                                        'w-full py-3 px-5 rounded-2xl text-center text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2 shadow-xs cursor-pointer',
                                        plan.is_featured
                                            ? 'bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.01]'
                                            : 'bg-slate-900 hover:bg-slate-800 text-white'
                                    ]"
                                >
                                    <span>{{ plan.cta_text || 'Get Started' }}</span>
                                    <ArrowRight class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== DAILY POSTS & ARCHITECTURE UPDATES SECTION ==================== -->
            <section
                v-if="isSectionEnabled('posts') && displayPosts.length > 0"
                id="posts"
                class="py-16 sm:py-20 bg-white border-b border-slate-200/80"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 sm:mb-12 gap-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Daily Updates & Knowledge</span>
                            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1.5">
                                Engineering Posts & Architecture Insights
                            </h2>
                            <p class="text-base text-slate-600 mt-3 max-w-2xl leading-relaxed">
                                Practical lessons from production workloads, multi-tier system designs, and day-to-day software engineering patterns.
                            </p>
                        </div>
                    </div>

                    <!-- Post Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <article
                            v-for="post in displayPosts"
                            :key="post.id"
                            class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md hover:border-indigo-200 hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden group"
                        >
                            <div>
                                <!-- Post Thumbnail -->
                                <div class="relative h-48 bg-slate-100 overflow-hidden">
                                    <img
                                        v-if="post.image_url"
                                        :src="post.image_url"
                                        :alt="post.title"
                                        class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-300"
                                        loading="lazy"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-900 to-slate-900 text-slate-400">
                                        <Newspaper class="w-10 h-10 text-indigo-400/60" />
                                    </div>

                                    <div class="absolute top-3 left-3">
                                        <AppBadge variant="indigo" size="sm">
                                            {{ post.category || 'Architecture' }}
                                        </AppBadge>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <div class="flex items-center gap-3 text-xs text-slate-400 font-mono mb-2.5">
                                        <span class="flex items-center gap-1">
                                            <Clock class="w-3.5 h-3.5 text-indigo-500" />
                                            {{ post.read_time || '3 min read' }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ post.published_at_formatted || 'Recent' }}</span>
                                    </div>

                                    <h3 class="text-lg font-bold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors line-clamp-2">
                                        {{ post.title }}
                                    </h3>

                                    <p class="text-xs sm:text-sm text-slate-600 mt-2.5 leading-relaxed line-clamp-3">
                                        {{ post.summary || post.content }}
                                    </p>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                                <button
                                    type="button"
                                    @click="openPostReader(post)"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition cursor-pointer"
                                >
                                    <BookOpen class="w-3.5 h-3.5" />
                                    <span>Read Full Post</span>
                                </button>
                                <span class="text-[11px] text-slate-400 font-mono">Technical Note</span>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <!-- ==================== NEWSLETTER SUBSCRIPTION SECTION ==================== -->
            <section
                v-if="isSectionEnabled('newsletter')"
                id="newsletter"
                class="py-14 sm:py-16 bg-slate-900 text-white relative overflow-hidden"
            >
                <!-- Background Accent Glow -->
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-violet-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="p-8 sm:p-12 rounded-3xl bg-slate-950/70 border border-slate-800 shadow-2xl backdrop-blur-md">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <div class="lg:col-span-7 space-y-3 text-center lg:text-left">
                                <span class="px-3 py-1 rounded-full text-[11px] font-mono font-bold tracking-wider uppercase bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 inline-block">
                                    Weekly Engineering Digest
                                </span>
                                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                                    Stay Ahead with Production Insights
                                </h2>
                                <p class="text-sm text-slate-400 max-w-xl leading-relaxed">
                                    Direct breakdowns of multi-tier caching architectures, database performance tuning, and resilient offline-first systems delivered straight to your inbox.
                                </p>
                            </div>

                            <div class="lg:col-span-5">
                                <form @submit.prevent="submitNewsletter" class="space-y-3">
                                    <div class="space-y-2">
                                        <input
                                            v-model="newsletterForm.email"
                                            type="email"
                                            placeholder="Enter your email address..."
                                            required
                                            class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-hidden focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition"
                                        />
                                        <input
                                            v-model="newsletterForm.name"
                                            type="text"
                                            placeholder="Your name (optional)"
                                            class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-hidden focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition"
                                        />
                                    </div>

                                    <AppButton
                                        type="submit"
                                        variant="gradient"
                                        size="md"
                                        class="w-full justify-center"
                                        :loading="newsletterForm.processing"
                                    >
                                        <Send class="w-4 h-4 mr-1.5" />
                                        <span>Subscribe to Newsletter</span>
                                    </AppButton>

                                    <div v-if="newsletterSuccessMessage" class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
                                        <CheckCircle2 class="w-4 h-4 flex-shrink-0" />
                                        <span>{{ newsletterSuccessMessage }}</span>
                                    </div>

                                    <div v-if="newsletterInfoMessage" class="p-3 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs flex items-center gap-2">
                                        <Sparkles class="w-4 h-4 flex-shrink-0" />
                                        <span>{{ newsletterInfoMessage }}</span>
                                    </div>

                                    <p class="text-[11px] text-slate-500 text-center lg:text-left">
                                        No spam. One-click unsubscribe at any time.
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== INTERACTIVE CONTACT SECTION ==================== -->
            <section
                v-if="isSectionEnabled('contact')"
                id="contact"
                class="py-16 sm:py-20 bg-[#F8FAFC]"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
                        <!-- Left Column: Contact Info & Availability -->
                        <div class="lg:col-span-5 space-y-6">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Get In Touch</span>
                                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mt-1.5">
                                    Let's build something great together.
                                </h2>
                                <p class="text-base text-slate-600 mt-3 leading-relaxed">
                                    Whether you need to architect a new SaaS application, scale an existing enterprise platform, or hire a senior engineer for contract consulting, I'm here to help.
                                </p>
                            </div>

                            <div class="space-y-4 pt-2">
                                <!-- Email with Interactive Copy Action -->
                                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 border border-indigo-100 shadow-2xs">
                                            <Mail class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Direct Email</p>
                                            <a :href="`mailto:${branding.contactEmail}`" class="text-sm font-semibold text-slate-900 hover:text-indigo-600 transition">
                                                {{ branding.contactEmail }}
                                            </a>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="copyEmail"
                                        class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition border border-slate-200/60"
                                        :title="emailCopied ? 'Copied!' : 'Copy email address'"
                                    >
                                        <Check v-if="emailCopied" class="w-4 h-4 text-emerald-600" />
                                        <Copy v-else class="w-4 h-4" />
                                    </button>
                                </div>

                                <!-- Availability Notice -->
                                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100 shadow-2xs">
                                        <Clock class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Availability</p>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ heroSettings.availabilityStatus }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Location & Response Time -->
                                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center flex-shrink-0 border border-violet-100 shadow-2xs">
                                        <CheckCircle2 class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Location & Turnaround</p>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ branding.location }} &bull; Replies within 24 hours
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Interactive Contact Form -->
                        <div class="lg:col-span-7 bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-sm transition-all duration-300">
                            <!-- Interactive Success View -->
                            <div
                                v-if="formSubmittedSuccess"
                                class="py-10 text-center space-y-4 animate-in fade-in zoom-in-95 duration-300"
                            >
                                <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto border-2 border-emerald-200 shadow-sm">
                                    <Check class="w-8 h-8 stroke-[2.5]" />
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">
                                        Message Sent Successfully!
                                    </h3>
                                    <p class="text-sm text-slate-600 max-w-md mx-auto leading-relaxed">
                                        Thank you<span v-if="submittedSenderName">, {{ submittedSenderName }}</span>! Your inquiry has been routed directly to my dashboard inbox. I will review and reply within 24 business hours.
                                    </p>
                                </div>
                                <div class="pt-3">
                                    <AppButton
                                        variant="secondary"
                                        size="md"
                                        @click="resetContactForm"
                                    >
                                        <Send class="w-4 h-4 mr-1.5" />
                                        <span>Send Another Message</span>
                                    </AppButton>
                                </div>
                            </div>

                            <!-- Interactive Form View -->
                            <div v-else>
                                <h3 class="text-xl font-bold text-slate-900 tracking-tight mb-1">
                                    Send a Message
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-500 mb-6">
                                    Fill out the form below or pick a quick topic to kick off our conversation.
                                </p>

                                <form @submit.prevent="submitContact" class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <AppInput
                                            v-model="contactForm.name"
                                            label="Your Name"
                                            placeholder="Jane Doe"
                                            :error="contactForm.errors.name"
                                            required
                                        />

                                        <AppInput
                                            v-model="contactForm.email"
                                            type="email"
                                            label="Your Email"
                                            placeholder="jane@company.com"
                                            :error="contactForm.errors.email"
                                            required
                                        />
                                    </div>

                                    <!-- Interactive Subject Suggestions -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label class="block text-xs font-semibold text-slate-700">Subject</label>
                                            <span class="text-[11px] text-slate-400">Click a quick topic or type below</span>
                                        </div>

                                        <div class="flex flex-wrap gap-1.5 mb-2.5">
                                            <button
                                                v-for="s in subjectSuggestions"
                                                :key="s"
                                                type="button"
                                                @click="setSubject(s)"
                                                :class="[
                                                    'px-2.5 py-1 rounded-lg text-xs font-medium border transition-colors',
                                                    contactForm.subject === s
                                                        ? 'bg-indigo-50 border-indigo-300 text-indigo-700 font-semibold'
                                                        : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'
                                                ]"
                                            >
                                                {{ s }}
                                            </button>
                                        </div>

                                        <AppInput
                                            v-model="contactForm.subject"
                                            placeholder="Project inquiry / Consultation"
                                            :error="contactForm.errors.subject"
                                        />
                                    </div>

                                    <!-- Message with Live Character Count -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label class="block text-xs font-semibold text-slate-700">
                                                Project Overview / Message <span class="text-red-500">*</span>
                                            </label>
                                            <span class="text-[11px] font-mono text-slate-400">
                                                {{ contactForm.message.length }} / 5000
                                            </span>
                                        </div>

                                        <AppTextarea
                                            v-model="contactForm.message"
                                            rows="5"
                                            placeholder="Tell me about your project, timeline, deliverables, or technical challenges..."
                                            :error="contactForm.errors.message"
                                            required
                                        />
                                    </div>

                                    <div class="pt-2 flex items-center justify-between">
                                        <span class="text-xs text-slate-400 hidden sm:inline">
                                            🔒 Your details are kept strictly confidential.
                                        </span>
                                        <AppButton
                                            type="submit"
                                            variant="primary"
                                            size="md"
                                            :loading="contactForm.processing"
                                        >
                                            <span>Send Message</span>
                                            <Send class="w-4 h-4 ml-1" />
                                        </AppButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- ==================== CLEAN DARK FOOTER ==================== -->
        <footer class="bg-[#0F172A] text-slate-400 py-12 sm:py-14 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8 pb-8 sm:pb-10 border-b border-slate-800">
                    <!-- Brand -->
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-extrabold text-sm shadow-sm shadow-indigo-500/25">
                            {{ branding.logoText }}
                        </div>
                        <div>
                            <span class="block font-bold text-white text-base">{{ branding.siteTitle }}</span>
                            <span class="block text-xs text-slate-400">{{ branding.footerText }}</span>
                        </div>
                    </div>

                    <!-- Navigation Links (Dynamic from Master Menu Settings) -->
                    <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-semibold text-slate-300">
                        <a
                            v-for="link in enabledNavLinks"
                            :key="link.key"
                            :href="`#${link.key}`"
                            class="hover:text-white transition"
                        >
                            {{ link.label }}
                        </a>
                    </div>

                    <!-- Social Icons (Exclusive to Footer) -->
                    <div class="flex items-center gap-2">
                        <a
                            v-if="branding.githubUrl"
                            :href="branding.githubUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center border border-slate-700/60 hover:border-slate-500 transition-all shadow-xs"
                            title="GitHub"
                            aria-label="GitHub"
                        >
                            <Github class="w-4 h-4" />
                        </a>
                        <a
                            v-if="branding.linkedinUrl"
                            :href="branding.linkedinUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-[#0077B5] text-slate-400 hover:text-white flex items-center justify-center border border-slate-700/60 hover:border-[#0077B5] transition-all shadow-xs"
                            title="LinkedIn"
                            aria-label="LinkedIn"
                        >
                            <Linkedin class="w-4 h-4" />
                        </a>
                        <a
                            v-if="branding.stackoverflowUrl"
                            :href="branding.stackoverflowUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-[#F48024] text-slate-400 hover:text-white flex items-center justify-center border border-slate-700/60 hover:border-[#F48024] transition-all shadow-xs"
                            title="Stack Overflow"
                            aria-label="Stack Overflow"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15.725 0l-1.72 1.277 6.39 8.588 1.716-1.277L15.725 0zm-3.94 3.818l-1.513 1.514 8.032 8.032 1.514-1.514-8.033-8.032zm-2.73 4.887l-1.077 1.854 10.02 5.82 1.077-1.854-10.02-5.82zm-1.18 5.617l-.427 2.103 11.238 2.302.427-2.103-11.238-2.302zM3 14.288v9.712h18v-9.712h-2.182v7.53H5.182v-7.53H3zm4.5 4.5v2.182h10.909v-2.182H7.5z"/>
                            </svg>
                        </a>
                        <a
                            v-if="branding.twitterUrl"
                            :href="branding.twitterUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center border border-slate-700/60 hover:border-slate-500 transition-all shadow-xs"
                            title="Twitter / X"
                            aria-label="Twitter"
                        >
                            <Twitter class="w-4 h-4" />
                        </a>
                        <a
                            v-if="branding.facebookUrl"
                            :href="branding.facebookUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-[#1877F2] text-slate-400 hover:text-white flex items-center justify-center border border-slate-700/60 hover:border-[#1877F2] transition-all shadow-xs"
                            title="Facebook"
                            aria-label="Facebook"
                        >
                            <Facebook class="w-4 h-4" />
                        </a>
                        <a
                            v-if="branding.instagramUrl"
                            :href="branding.instagramUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-xl bg-slate-800/90 hover:bg-gradient-to-tr hover:from-[#833AB4] hover:via-[#FD1D1D] hover:to-[#FCAF45] text-slate-400 hover:text-white flex items-center justify-center border border-slate-700/60 hover:border-transparent transition-all shadow-xs"
                            title="Instagram"
                            aria-label="Instagram"
                        >
                            <Instagram class="w-4 h-4" />
                        </a>
                    </div>
                </div>

                <!-- Bottom Copyright -->
                <div class="pt-6 sm:pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                    <p>{{ branding.copyrightText }}</p>
                    <p class="text-[11px] text-slate-500">{{ branding.location }}</p>
                </div>
            </div>
        </footer>

        <!-- Interactive Post Reader Modal -->
        <AppModal
            :show="postModalOpen"
            :title="activePost?.title || 'Daily Engineering Post'"
            maxWidth="2xl"
            @close="postModalOpen = false"
        >
            <div v-if="activePost" class="space-y-5">
                <div class="flex items-center gap-3 text-xs text-slate-500 font-mono">
                    <AppBadge variant="indigo" size="sm">
                        {{ activePost.category || 'Architecture' }}
                    </AppBadge>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <Clock class="w-3.5 h-3.5 text-slate-400" />
                        {{ activePost.read_time || '3 min read' }}
                    </span>
                    <span>•</span>
                    <span>{{ activePost.published_at_formatted || 'Recent' }}</span>
                </div>

                <div v-if="activePost.image_url" class="w-full h-64 rounded-2xl overflow-hidden border border-slate-200 shadow-xs">
                    <img :src="activePost.image_url" :alt="activePost.title" class="w-full h-full object-cover" />
                </div>

                <div class="prose prose-slate max-w-none text-slate-700 text-sm leading-relaxed whitespace-pre-line font-normal">
                    {{ activePost.content }}
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-mono">Technical Notes</span>
                    <AppButton
                        type="button"
                        @click="postModalOpen = false"
                        variant="secondary"
                        size="sm"
                    >
                        Close Reader
                    </AppButton>
                </div>
            </div>
        </AppModal>
    </div>
</template>
