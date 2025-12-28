<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import OccupancyChart from '@/Components/Dashboard/OccupancyChart.vue';
import TrendsChart from '@/Components/Dashboard/TrendsChart.vue';
import PendingAlerts from '@/Components/Dashboard/PendingAlerts.vue';
import QuickActions from '@/Components/Dashboard/QuickActions.vue';
import RecentActivities from '@/Components/Dashboard/RecentActivities.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Breadcrumb {
    url: string;
    title: string;
}

interface DashboardData {
    statistics: {
        students: {
            total: number;
            allotted: number;
            attachment: number;
            blocked: number;
        };
        seats: {
            total: number;
            allotted: number;
            empty: number;
            occupancy_rate: number;
        };
        halls: {
            total: number;
            active: number;
        } | null;
        rooms: {
            total: number;
        };
    };
    pending_actions: {
        cancellation_requests: number;
        attachment_students: number;
        blocked_students: number;
    };
    hall_occupancy: Array<{
        hall_name: string;
        total_seats: number;
        allotted_seats: number;
        empty_seats: number;
        occupancy_rate: number;
    }>;
    monthly_trends: Array<{
        month: string;
        count: number;
    }>;
    recent_activities: Array<{
        description: string;
        subject_type: string;
        causer_name: string;
        created_at: string;
        created_at_formatted: string;
    }>;
}

const props = defineProps<{
    breadcrumbs: Breadcrumb[];
    pageTitle: string;
    dashboardData: DashboardData;
}>();

const permissions = usePage().props.permissions;
if (permissions) {
    localStorage.setItem('permissions', JSON.stringify(permissions));
}

const stats = computed(() => props.dashboardData.statistics);
const pendingActions = computed(() => props.dashboardData.pending_actions);
const hallOccupancy = computed(() => props.dashboardData.hall_occupancy);
const monthlyTrends = computed(() => props.dashboardData.monthly_trends);
const recentActivities = computed(() => props.dashboardData.recent_activities);
</script>

<template>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs" :pageTitle="pageTitle">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <StatCard
                        title="Total Students"
                        :value="stats.students.total"
                        icon="bi-people-fill"
                        color="blue"
                    />
                    <StatCard
                        title="Allotted Seats"
                        :value="stats.seats.allotted"
                        :subtitle="`${stats.seats.occupancy_rate}% occupied`"
                        icon="bi-house-check-fill"
                        color="green"
                    />
                    <StatCard
                        title="Available Seats"
                        :value="stats.seats.empty"
                        icon="bi-house-fill"
                        color="purple"
                    />
                    <StatCard
                        title="Blocked Students"
                        :value="stats.students.blocked"
                        icon="bi-shield-fill-exclamation"
                        color="red"
                    />
                </div>

                <!-- Second row of stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <StatCard
                        title="Allotted Students"
                        :value="stats.students.allotted"
                        icon="bi-person-check-fill"
                        color="green"
                    />
                    <StatCard
                        title="Pending Allotment"
                        :value="stats.students.attachment"
                        icon="bi-person-plus-fill"
                        color="yellow"
                    />
                    <StatCard
                        title="Total Rooms"
                        :value="stats.rooms.total"
                        icon="bi-door-closed-fill"
                        color="blue"
                    />
                    <StatCard
                        v-if="stats.halls"
                        title="Total Halls"
                        :value="stats.halls.total"
                        icon="bi-building"
                        color="purple"
                    />
                    <StatCard
                        v-else
                        title="Total Seats"
                        :value="stats.seats.total"
                        icon="bi-grid-3x3-gap-fill"
                        color="purple"
                    />
                </div>

                <!-- Charts and Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Hall Occupancy Chart - 2 columns -->
                    <div class="lg:col-span-2">
                        <OccupancyChart :data="hallOccupancy" />
                    </div>
                    
                    <!-- Quick Actions - 1 column -->
                    <div>
                        <QuickActions :permissions="permissions as string[]" />
                    </div>
                </div>

                <!-- Monthly Trends Chart -->
                <div class="mb-6">
                    <TrendsChart :data="monthlyTrends" />
                </div>

                <!-- Pending Actions and Recent Activities -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <PendingAlerts :pending-actions="pendingActions" />
                    <RecentActivities :activities="recentActivities" />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
