<template>
    <!-- Premium Variant (Modern, Dark-themed with Glow) -->
    <div v-if="variant === 'premium' || variant === 'primary'"
        class="group relative overflow-hidden rounded-2xl transition-all duration-500 hover:scale-105"
    >
        <!-- Animated Gradient Background -->
        <div :class="[
            'absolute inset-0 bg-gradient-to-br transition-all duration-500',
            colorMap[color].premiumGradient
        ]"></div>

        <!-- Glossy Shine Effect -->
        <div class="absolute -right-20 -top-20 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:bg-white/20 transition-all duration-500"></div>
        <div class="absolute -bottom-16 -left-16 w-32 h-32 bg-black/20 rounded-full blur-2xl group-hover:bg-black/10 transition-all duration-500"></div>

        <!-- Border Glow Effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

        <div class="relative z-10 p-8 h-full flex flex-col justify-between border border-white/10 rounded-2xl backdrop-blur-xl bg-white/5 group-hover:bg-white/10 transition-all duration-500">
            <div class="flex items-start justify-between mb-6">
                <div :class="[
                    'w-16 h-16 rounded-2xl flex items-center justify-center transition-all duration-500 group-hover:scale-110',
                    'bg-white/15 backdrop-blur-md border border-white/20 group-hover:bg-white/20 group-hover:border-white/40',
                    'shadow-lg group-hover:shadow-xl'
                ]">
                    <i :class="['text-3xl text-white', icon]"></i>
                </div>
                <div v-if="badge" class="px-4 py-2 bg-white/20 backdrop-blur-md rounded-full border border-white/30 group-hover:bg-white/30 transition-all">
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">{{ badge }}</span>
                </div>
            </div>

            <div class="mt-auto">
                <p class="text-white/70 text-sm font-semibold tracking-wide mb-2 uppercase">{{ title }}</p>
                <h3 class="text-5xl font-black text-white tracking-tight leading-none mb-4">{{ value }}</h3>

                <!-- Progress Bar (Optional) -->
                <div v-if="progress !== undefined" class="w-full bg-white/20 rounded-full h-2 overflow-hidden border border-white/10">
                    <div class="bg-gradient-to-r from-white via-white/80 to-white/60 h-full transition-all duration-1000 rounded-full shadow-lg" :style="{ width: progress + '%' }"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Variant (Compact, Modern Dark) -->
    <div v-else
        class="group relative p-6 bg-slate-800/40 dark:bg-slate-900/40 backdrop-blur-xl rounded-xl border border-slate-700/50 hover:border-slate-600/80 transition-all duration-300 hover:bg-slate-800/60 shadow-lg hover:shadow-xl"
    >
        <div class="flex items-center gap-4">
            <div :class="[
                'flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-all duration-300 group-hover:scale-110',
                colorMap[color].secondaryBg,
                'border border-white/10'
            ]">
                <i :class="['text-2xl text-white', icon]"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">{{ title }}</p>
                <h4 class="text-2xl font-black text-white leading-none">{{ value }}</h4>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    title: string;
    value: number | string;
    icon: string;
    variant?: 'premium' | 'primary' | 'secondary';
    color?: 'blue' | 'green' | 'yellow' | 'red' | 'purple';
    badge?: string;
    progress?: number;
}

const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
    variant: 'secondary',
});

const colorMap = {
    blue: {
        premiumGradient: 'from-blue-600 via-blue-500 to-cyan-500',
        secondaryBg: 'bg-blue-500/20 dark:bg-blue-900/30',
        text: 'text-blue-400'
    },
    green: {
        premiumGradient: 'from-emerald-600 via-emerald-500 to-teal-500',
        secondaryBg: 'bg-emerald-500/20 dark:bg-emerald-900/30',
        text: 'text-emerald-400'
    },
    yellow: {
        premiumGradient: 'from-amber-600 via-amber-500 to-yellow-500',
        secondaryBg: 'bg-amber-500/20 dark:bg-amber-900/30',
        text: 'text-amber-400'
    },
    red: {
        premiumGradient: 'from-rose-600 via-rose-500 to-pink-500',
        secondaryBg: 'bg-rose-500/20 dark:bg-rose-900/30',
        text: 'text-rose-400'
    },
    purple: {
        premiumGradient: 'from-violet-600 via-purple-500 to-fuchsia-500',
        secondaryBg: 'bg-purple-500/20 dark:bg-purple-900/30',
        text: 'text-purple-400'
    },
};
</script>
