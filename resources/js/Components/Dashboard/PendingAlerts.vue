<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="bi-exclamation-triangle-fill text-yellow-500 mr-2"></i>
            Attention Required
        </h3>
        
        <div class="space-y-3">
            <div v-if="pendingActions.cancellation_requests > 0"
                 class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200 hover:bg-red-100 transition-colors cursor-pointer"
                 @click="navigateTo('hall-allotments.index')">
                <div class="flex items-center">
                    <i class="bi-x-circle-fill text-red-600 text-2xl mr-3"></i>
                    <span class="text-gray-900 font-medium">Cancellation Requests</span>
                </div>
                <div class="flex items-center">
                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                        {{ pendingActions.cancellation_requests }}
                    </span>
                    <i class="bi-chevron-right text-gray-400 ml-2"></i>
                </div>
            </div>

            <div v-if="pendingActions.attachment_students > 0"
                 class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border border-yellow-200 hover:bg-yellow-100 transition-colors cursor-pointer"
                 @click="navigateTo('students.index')">
                <div class="flex items-center">
                    <i class="bi-person-plus-fill text-yellow-600 text-2xl mr-3"></i>
                    <span class="text-gray-900 font-medium">Students Pending Allotment</span>
                </div>
                <div class="flex items-center">
                    <span class="bg-yellow-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                        {{ pendingActions.attachment_students }}
                    </span>
                    <i class="bi-chevron-right text-gray-400 ml-2"></i>
                </div>
            </div>

            <div v-if="pendingActions.blocked_students > 0"
                 class="flex items-center justify-between p-4 bg-orange-50 rounded-lg border border-orange-200 hover:bg-orange-100 transition-colors cursor-pointer"
                 @click="navigateTo('students.blockList')">
                <div class="flex items-center">
                    <i class="bi-shield-fill-exclamation text-orange-600 text-2xl mr-3"></i>
                    <span class="text-gray-900 font-medium">Blocked Students</span>
                </div>
                <div class="flex items-center">
                    <span class="bg-orange-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                        {{ pendingActions.blocked_students }}
                    </span>
                    <i class="bi-chevron-right text-gray-400 ml-2"></i>
                </div>
            </div>

            <div v-if="!hasAnyPendingActions" class="text-center py-8 text-gray-500">
                <i class="bi-check-circle-fill text-green-500 text-4xl mb-2"></i>
                <p class="font-medium">All caught up!</p>
                <p class="text-sm">No pending actions at the moment.</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

interface PendingActions {
    cancellation_requests: number;
    attachment_students: number;
    blocked_students: number;
}

interface Props {
    pendingActions: PendingActions;
}

const props = defineProps<Props>();

const hasAnyPendingActions = computed(() => {
    return props.pendingActions.cancellation_requests > 0 ||
           props.pendingActions.attachment_students > 0 ||
           props.pendingActions.blocked_students > 0;
});

const navigateTo = (routeName: string) => {
    router.visit(route(routeName));
};
</script>
