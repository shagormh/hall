<template>
    <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100">
        <div class="flex items-center gap-3 mb-6">
            <i class="bi-activity text-gray-600" style="font-size: 1.5rem;"></i>
            <h3 class="text-2xl font-black text-gray-900">Recent Activities</h3>
        </div>
            <div v-if="activities && activities.length > 0" class="space-y-4">
                <div v-for="(activity, index) in activities" :key="index"
                     class="p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                    <p class="font-bold text-gray-900 text-sm line-clamp-2">{{ activity.description }}</p>
                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-600">
                        <span>{{ activity.causer_name }}</span>
                        <span>•</span>
                        <span>{{ activity.created_at }}</span>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-8">
                <i class="bi-clock-history text-gray-300" style="font-size: 2rem;"></i>
                <p class="text-gray-500 font-semibold text-sm mt-2">No recent activities to show.</p>
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
