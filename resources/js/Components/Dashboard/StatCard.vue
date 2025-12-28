<template>
    <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow duration-300">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 uppercase tracking-wide">{{ title }}</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ value }}</p>
                <p v-if="subtitle" class="text-sm text-gray-500 mt-1">{{ subtitle }}</p>
            </div>
            <div :class="['w-12 h-12 rounded-full flex items-center justify-center', iconBgClass]">
                <i :class="['text-2xl', icon, iconColorClass]"></i>
            </div>
        </div>
        <div v-if="trend" class="mt-4 flex items-center text-sm">
            <span :class="trend.positive ? 'text-green-600' : 'text-red-600'" class="flex items-center">
                <i :class="trend.positive ? 'bi-arrow-up' : 'bi-arrow-down'" class="mr-1"></i>
                {{ trend.value }}
            </span>
            <span class="text-gray-500 ml-2">{{ trend.label }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Trend {
    positive: boolean;
    value: string;
    label: string;
}

interface Props {
    title: string;
    value: number | string;
    subtitle?: string;
    icon: string;
    color?: 'blue' | 'green' | 'yellow' | 'red' | 'purple';
    trend?: Trend;
}

const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
});

const iconBgClass = computed(() => {
    const colors = {
        blue: 'bg-blue-100',
        green: 'bg-green-100',
        yellow: 'bg-yellow-100',
        red: 'bg-red-100',
        purple: 'bg-purple-100',
    };
    return colors[props.color];
});

const iconColorClass = computed(() => {
    const colors = {
        blue: 'text-blue-600',
        green: 'text-green-600',
        yellow: 'text-yellow-600',
        red: 'text-red-600',
        purple: 'text-purple-600',
    };
    return colors[props.color];
});
</script>
