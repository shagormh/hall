<template>
    <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
        <div class="flex items-center gap-3 mb-6">
            <i class="bi-lightning-charge text-gray-600" style="font-size: 1.5rem;"></i>
            <div class="flex flex-col">
                <h3 class="text-2xl font-black text-gray-900 leading-none">Quick Actions</h3>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Common tasks</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button
                v-if="hasPermission('students.create')"
                @click="navigateTo('students.create')"
                class="flex flex-col items-center justify-center p-6 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
                style="background: linear-gradient(to bottom right, #3b82f6, #0ea5e9);">
                <i class="bi-person-plus text-3xl mb-2 text-white group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-black uppercase tracking-wider text-center text-white">Add Student</span>
            </button>

            <button
                v-if="hasPermission('hall-allotments.create')"
                @click="navigateTo('hall-allotments.create')"
                class="flex flex-col items-center justify-center p-6 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
                style="background: linear-gradient(to bottom right, #10b981, #059669);">
                <i class="bi-house-add text-3xl mb-2 text-white group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-black uppercase tracking-wider text-center text-white">Allocate Seat</span>
            </button>

            <button
                v-if="hasPermission('hall-allotments.index')"
                @click="navigateTo('hall-allotments.index')"
                class="flex flex-col items-center justify-center p-6 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
                style="background: linear-gradient(to bottom right, #8b5cf6, #7c3aed);">
                <i class="bi-clipboard-check text-3xl mb-2 text-white group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-black uppercase tracking-wider text-center text-white">Manage</span>
            </button>

            <button
                v-if="hasPermission('reports.index')"
                @click="navigateTo('reports.index')"
                class="flex flex-col items-center justify-center p-6 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group"
                style="background: linear-gradient(to bottom right, #f97316, #ea580c);">
                <i class="bi-file-earmark-bar-graph text-3xl mb-2 text-white group-hover:scale-110 transition-transform"></i>
                <span class="text-xs font-black uppercase tracking-wider text-center text-white">Reports</span>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';

interface Props {
    permissions?: string[];
}

const props = defineProps<Props>();

const hasPermission = (permission: string) => {
    if (!props.permissions) return true; // If no permissions provided, show all
    return props.permissions.includes(permission);
};

const navigateTo = (routeName: string) => {
    router.visit(route(routeName));
};
</script>
