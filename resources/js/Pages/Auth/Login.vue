<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Mail, Lock, Eye, EyeOff, LogIn, Building2, Shield, User, Briefcase, Key } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
        default: null,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: 'admin@skylinerealty.com',
    password: 'password',
    remember: true,
});

const fillDemo = (email, password = 'password') => {
    form.email = email;
    form.password = password;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign In - Real Estate CRM & Property Management" />

        <div class="mb-6 text-center">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600/20 text-indigo-400 mx-auto flex items-center justify-center mb-3">
                <Building2 class="w-6 h-6" />
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white">Real Estate CRM Portal</h1>
            <p class="mt-1 text-xs text-slate-400">
                Sign in to manage property listings, buyer leads, deals & commissions
            </p>
        </div>

        <!-- Status message -->
        <div v-if="status" class="mb-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 p-3.5 text-xs font-medium text-emerald-400">
            {{ status }}
        </div>

        <!-- General / Throttle error -->
        <div v-if="form.errors.email && !form.errors.password" class="mb-5 rounded-2xl bg-rose-500/10 border border-rose-500/30 p-3.5 text-xs text-rose-400 flex items-start gap-2">
            <span class="inline-block mt-0.5 w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0"></span>
            <span>{{ form.errors.email }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <Mail class="w-4 h-4" />
                    </div>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="agent@agency.com"
                        class="block w-full pl-10 pr-4 py-2.5 bg-slate-900/60 border border-slate-700/80 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        :class="{ 'border-rose-500/70 focus:ring-rose-500': form.errors.email }"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-400">
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- Password Input -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-slate-300">
                        Password
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[11px] font-medium text-indigo-400 hover:text-indigo-300 transition"
                    >
                        Forgot password?
                    </Link>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <Lock class="w-4 h-4" />
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="block w-full pl-10 pr-10 py-2.5 bg-slate-900/60 border border-slate-700/80 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        :class="{ 'border-rose-500/70 focus:ring-rose-500': form.errors.password }"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition"
                    >
                        <EyeOff v-if="showPassword" class="w-4 h-4" />
                        <Eye v-else class="w-4 h-4" />
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="w-4 h-4 rounded border-slate-700 bg-slate-900/60 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900"
                    />
                    <span class="text-xs text-slate-400 select-none">Remember this device</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 shadow-lg shadow-indigo-600/30 transition disabled:opacity-50 disabled:cursor-not-allowed mt-2"
            >
                <LogIn class="w-4 h-4" />
                <span>{{ form.processing ? 'Signing In...' : 'Sign In to Dashboard' }}</span>
            </button>
        </form>

        <!-- Quick Demo Role Selector -->
        <div class="mt-6 pt-5 border-t border-slate-800">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-2.5">
                Quick One-Click Demo Logins
            </p>
            <div class="grid grid-cols-2 gap-2 text-[11px]">
                <button
                    type="button"
                    @click="fillDemo('admin@skylinerealty.com')"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-indigo-900/40 text-left border border-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5"
                >
                    <Building2 class="w-3.5 h-3.5 text-indigo-400 flex-shrink-0" />
                    <span class="truncate font-semibold">Agency Admin</span>
                </button>

                <button
                    type="button"
                    @click="fillDemo('agent@skylinerealty.com')"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-indigo-900/40 text-left border border-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5"
                >
                    <Briefcase class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" />
                    <span class="truncate font-semibold">Real Estate Agent</span>
                </button>

                <button
                    type="button"
                    @click="fillDemo('manager@skylinerealty.com')"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-indigo-900/40 text-left border border-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5"
                >
                    <User class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" />
                    <span class="truncate font-semibold">Sales Manager</span>
                </button>

                <button
                    type="button"
                    @click="fillDemo('superadmin@recrm.com')"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-violet-900/40 text-left border border-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5"
                >
                    <Shield class="w-3.5 h-3.5 text-violet-400 flex-shrink-0" />
                    <span class="truncate font-semibold">Super Admin SaaS</span>
                </button>

                <button
                    type="button"
                    @click="fillDemo('buyer@client.com')"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-sky-900/40 text-left border border-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5"
                >
                    <Key class="w-3.5 h-3.5 text-sky-400 flex-shrink-0" />
                    <span class="truncate font-semibold">Customer Portal</span>
                </button>

                <button
                    type="button"
                    @click="fillDemo('accountant@skylinerealty.com')"
                    class="px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-indigo-900/40 text-left border border-slate-700 text-slate-300 hover:text-white transition flex items-center gap-1.5"
                >
                    <User class="w-3.5 h-3.5 text-teal-400 flex-shrink-0" />
                    <span class="truncate font-semibold">Accountant</span>
                </button>
            </div>
            <p class="text-[10px] text-slate-500 text-center mt-2">Default demo password for all accounts: <code class="text-indigo-400">password</code></p>
        </div>
    </GuestLayout>
</template>
