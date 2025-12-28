<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activities</h3>
        
        <div v-if="activities && activities.length > 0" class="space-y-4">
            <div v-for="(activity, index) in activities" :key="index"
                 class="flex items-start pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm text-gray-900 font-medium">{{ activity.description }}</p>
                    <div class="flex items-center mt-1 text-xs text-gray-500">
                        <span>{{ activity.causer_name }}</span>
                        <span class="mx-2">•</span>
                        <span>{{ activity.created_at }}</span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                        {{ activity.subject_type }}
                    </span>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
            <i class="bi-clock-history text-4xl mb-2"></i>
            <p class="font-medium">No recent activities</p>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Activity {
    description: string;
    subject_type: string;
    causer_name: string;
    created_at: string;
    created_at_formatted: string;
}

interface Props {
    activities: Activity[];
}

defineProps<Props>();
</script>
