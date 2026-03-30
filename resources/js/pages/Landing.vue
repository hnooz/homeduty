<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import { ref, onMounted } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const isVisible = ref(false);
const featuresVisible = ref(false);
const statsVisible = ref(false);

onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
    
    setTimeout(() => {
        featuresVisible.value = true;
    }, 400);
    
    setTimeout(() => {
        statsVisible.value = true;
    }, 700);
});

const features = [
    {
        icon: '📋',
        title: 'Easy Task Assignment',
        description: 'Assign household duties to family members with just a few clicks.',
    },
    {
        icon: '🔄',
        title: 'Rotating Schedules',
        description: 'Automatically rotate chores so everyone shares the workload fairly.',
    },
    {
        icon: '🔔',
        title: 'Smart Reminders',
        description: 'Get notified when duties are due, never miss a chore again.',
    },
    {
        icon: '👨‍👩‍👧‍👦',
        title: 'Family Groups',
        description: 'Create groups for your household and manage duties together.',
    },
    {
        icon: '📊',
        title: 'Progress Tracking',
        description: 'Track completed tasks and see who is contributing the most.',
    },
    {
        icon: '✨',
        title: 'Gamification',
        description: 'Earn points and badges for completing duties on time.',
    },
];

const stats = [
    { value: '10K+', label: 'Active Families' },
    { value: '50K+', label: 'Tasks Completed' },
    { value: '98%', label: 'Satisfaction Rate' },
    { value: '24/7', label: 'Available' },
];
</script>

<template>
    <Head title="HomeDuty - Simplify Your Household Chores">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-800">
        <!-- Navigation -->
        <header class="fixed top-0 left-0 right-0 z-50 backdrop-blur-md bg-white/70 dark:bg-slate-900/70 border-b border-slate-200/50 dark:border-slate-700/50">
            <nav class="mx-auto max-w-7xl px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center size-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/25">
                            <AppLogoIcon class="size-6 text-white" />
                        </div>
                        <span class="text-xl font-bold text-slate-900 dark:text-white">HomeDuty</span>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                        >
                            <Button variant="default">
                                Dashboard
                            </Button>
                        </Link>
                        <template v-else>
                            <Link :href="login()">
                                <Button variant="ghost" class="text-slate-700 dark:text-slate-300">
                                    Log in
                                </Button>
                            </Link>
                            <Link v-if="canRegister" :href="register()">
                                <Button variant="default" class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 border-0">
                                    Get Started
                                </Button>
                            </Link>
                        </template>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 px-6 overflow-hidden">
            <!-- Animated background elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-20 left-10 w-72 h-72 bg-purple-300 dark:bg-purple-900 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-30 animate-blob"></div>
                <div class="absolute top-40 right-10 w-72 h-72 bg-indigo-300 dark:bg-indigo-900 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-pink-300 dark:bg-pink-900 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
            </div>
            
            <div class="relative mx-auto max-w-5xl text-center">
                <div 
                    :class="[
                        'transition-all duration-1000 ease-out',
                        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'
                    ]"
                >
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-6 animate-bounce-slow">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        Now available for all families
                    </span>
                    
                    <h1 class="text-5xl md:text-7xl font-bold text-slate-900 dark:text-white leading-tight mb-6">
                        Household chores,
                        <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent animate-gradient">
                            simplified
                        </span>
                    </h1>
                    
                    <p class="text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                        HomeDuty helps your family stay organized by assigning, tracking, and rotating household duties. 
                        No more arguments about who does what!
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link v-if="canRegister && !$page.props.auth.user" :href="register()">
                            <Button size="lg" class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 border-0 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-0.5 text-base px-8">
                                Start Free Trial
                                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Button>
                        </Link>
                        <Link v-if="$page.props.auth.user" :href="dashboard()">
                            <Button size="lg" class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 border-0 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-0.5 text-base px-8">
                                Go to Dashboard
                            </Button>
                        </Link>
                        <Button size="lg" variant="outline" class="text-base px-8 hover:-translate-y-0.5 transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" />
                            </svg>
                            Watch Demo
                        </Button>
                    </div>
                </div>
                
                <!-- Hero Image/Mockup -->
                <div 
                    :class="[
                        'mt-16 relative transition-all duration-1000 delay-300 ease-out',
                        isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'
                    ]"
                >
                    <div class="relative mx-auto max-w-4xl">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur-2xl opacity-20 transform scale-95"></div>
                        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-4 md:p-8">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-xl p-4 transform hover:scale-105 transition-transform duration-300">
                                    <div class="text-3xl mb-2">🧹</div>
                                    <div class="font-semibold text-slate-900 dark:text-white">Vacuum Living Room</div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">Assigned to: Mom</div>
                                    <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 text-xs">
                                        ✓ Completed
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/30 dark:to-rose-900/30 rounded-xl p-4 transform hover:scale-105 transition-transform duration-300">
                                    <div class="text-3xl mb-2">🍽️</div>
                                    <div class="font-semibold text-slate-900 dark:text-white">Do the Dishes</div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">Assigned to: Dad</div>
                                    <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 text-xs">
                                        ⏳ In Progress
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-cyan-50 to-blue-50 dark:from-cyan-900/30 dark:to-blue-900/30 rounded-xl p-4 transform hover:scale-105 transition-transform duration-300">
                                    <div class="text-3xl mb-2">🛏️</div>
                                    <div class="font-semibold text-slate-900 dark:text-white">Make the Beds</div>
                                    <div class="text-sm text-slate-500 dark:text-slate-400">Assigned to: Kids</div>
                                    <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs">
                                        📅 Upcoming
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16 px-6">
            <div 
                :class="[
                    'mx-auto max-w-5xl transition-all duration-1000 ease-out',
                    statsVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'
                ]"
            >
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div 
                        v-for="(stat, index) in stats" 
                        :key="stat.label"
                        :style="{ transitionDelay: `${index * 100}ms` }"
                        class="text-center transform hover:scale-110 transition-all duration-300"
                    >
                        <div class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            {{ stat.value }}
                        </div>
                        <div class="text-slate-600 dark:text-slate-400 mt-2">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-6">
            <div class="mx-auto max-w-6xl">
                <div 
                    :class="[
                        'text-center mb-16 transition-all duration-1000 ease-out',
                        featuresVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'
                    ]"
                >
                    <h2 class="text-3xl md:text-5xl font-bold text-slate-900 dark:text-white mb-4">
                        Everything you need to
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">manage your home</span>
                    </h2>
                    <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                        Powerful features designed to make household management effortless for the whole family.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="(feature, index) in features" 
                        :key="feature.title"
                        :class="[
                            'group p-6 rounded-2xl bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-700 shadow-sm hover:shadow-xl transition-all duration-500 cursor-pointer',
                            featuresVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'
                        ]"
                        :style="{ transitionDelay: `${index * 100}ms` }"
                    >
                        <div class="text-4xl mb-4 transform group-hover:scale-125 group-hover:rotate-12 transition-transform duration-300">
                            {{ feature.icon }}
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ feature.title }}
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-6">
            <div class="mx-auto max-w-4xl">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-1">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 animate-gradient-x"></div>
                    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-[calc(1.5rem-4px)] p-12 text-center">
                        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                            Ready to get started?
                        </h2>
                        <p class="text-indigo-100 text-lg mb-8 max-w-xl mx-auto">
                            Join thousands of families who have transformed their household management with HomeDuty.
                        </p>
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <Link v-if="canRegister && !$page.props.auth.user" :href="register()">
                                <Button size="lg" class="bg-white text-indigo-600 hover:bg-indigo-50 border-0 shadow-lg shadow-indigo-900/25 text-base px-8 hover:-translate-y-0.5 transition-all duration-300">
                                    Create Free Account
                                </Button>
                            </Link>
                            <Link v-if="$page.props.auth.user" :href="dashboard()">
                                <Button size="lg" class="bg-white text-indigo-600 hover:bg-indigo-50 border-0 shadow-lg shadow-indigo-900/25 text-base px-8 hover:-translate-y-0.5 transition-all duration-300">
                                    Go to Dashboard
                                </Button>
                            </Link>
                            <Link v-else :href="login()">
                                <Button size="lg" variant="outline" class="border-white/30 text-white hover:bg-white/10 text-base px-8 hover:-translate-y-0.5 transition-all duration-300">
                                    Sign In
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-12 px-6 border-t border-slate-200 dark:border-slate-800">
            <div class="mx-auto max-w-6xl">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center size-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600">
                            <AppLogoIcon class="size-5 text-white" />
                        </div>
                        <span class="font-semibold text-slate-900 dark:text-white">HomeDuty</span>
                    </div>
                    <div class="flex items-center gap-6 text-sm text-slate-600 dark:text-slate-400">
                        <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Privacy</a>
                        <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Terms</a>
                        <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Contact</a>
                    </div>
                    <div class="text-sm text-slate-500 dark:text-slate-500">
                        © 2026 HomeDuty. All rights reserved.
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

@keyframes gradient {
    0%, 100% {
        background-size: 200% 200%;
        background-position: left center;
    }
    50% {
        background-size: 200% 200%;
        background-position: right center;
    }
}

@keyframes gradient-x {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.animate-gradient {
    animation: gradient 3s ease infinite;
    background-size: 200% 200%;
}

.animate-gradient-x {
    animation: gradient-x 3s ease infinite;
    background-size: 200% 100%;
}

.animate-bounce-slow {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
</style>
