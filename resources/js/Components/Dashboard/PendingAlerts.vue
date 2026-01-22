<template>
    <div class="rounded-3xl p-8 shadow-2xl text-white" style="background: linear-gradient(to bottom right, #f97316, #f43f5e);">
        <div class="flex items-center gap-3 mb-6">
            <i class="bi-bell" style="font-size: 1.5rem;"></i>
            <h3 class="text-2xl font-black">Pending Actions</h3>
        </div>

            <div class="space-y-4">
                <div v-if="pendingActions.cancellation_requests > 0"
                     class="flex items-center justify-between p-4 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300 cursor-pointer"
                     @click="navigateTo('hall-allotments.index')">
                    <div class="flex items-center flex-1 min-w-0 gap-3">
                        <span class="block text-sm font-bold">Cancellation Requests</span>
                    </div>
                    <span class="text-2xl font-black">{{ pendingActions.cancellation_requests }}</span>
                </div>

                <div v-if="pendingActions.attachment_students > 0"
                     class="flex items-center justify-between p-4 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300 cursor-pointer"
                     @click="navigateTo('students.index')">
                    <div class="flex items-center flex-1 min-w-0 gap-3">
                        <span class="block text-sm font-bold">Attachment Students</span>
                    </div>
                    <span class="text-2xl font-black">{{ pendingActions.attachment_students }}</span>
                </div>

                <div v-if="pendingActions.blocked_students > 0"
                     class="flex items-center justify-between p-4 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300 cursor-pointer"
                     @click="navigateTo('students.blockList')">
                    <div class="flex items-center flex-1 min-w-0 gap-3">
                        <span class="block text-sm font-bold">Blocked Students</span>
                    </div>
                    <span class="text-2xl font-black">{{ pendingActions.blocked_students }}</span>
                </div>

                <div v-if="!hasAnyPendingActions" class="text-center py-8">
                    <p class="text-white/80 font-semibold text-sm">All caught up!</p>
                    <p class="text-xs text-white/60 mt-1">No pending actions.</p>
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
