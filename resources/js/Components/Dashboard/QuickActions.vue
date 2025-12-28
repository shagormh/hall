<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        
        <div class="grid grid-cols-1 gap-3">
            <button
                v-if="hasPermission('students.create')"
                @click="navigateTo('students.create')"
                class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="bi-person-plus-fill text-2xl mr-3"></i>
                <span class="font-medium">Add New Student</span>
            </button>

            <button
                v-if="hasPermission('hall-allotments.create')"
                @click="navigateTo('hall-allotments.create')"
                class="flex items-center p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="bi-house-add-fill text-2xl mr-3"></i>
                <span class="font-medium">Allot Seat</span>
            </button>

            <button
                v-if="hasPermission('hall-allotments.index')"
                @click="navigateTo('hall-allotments.index')"
                class="flex items-center p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="bi-list-ul text-2xl mr-3"></i>
                <span class="font-medium">View All Allotments</span>
            </button>

            <button
                v-if="hasPermission('students.index')"
                @click="navigateTo('students.blockList')"
                class="flex items-center p-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="bi-shield-fill-exclamation text-2xl mr-3"></i>
                <span class="font-medium">Blocked Students</span>
            </button>

            <button
                v-if="hasPermission('rooms.index')"
                @click="navigateTo('rooms.index')"
                class="flex items-center p-4 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="bi-door-open-fill text-2xl mr-3"></i>
                <span class="font-medium">Manage Rooms</span>
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
