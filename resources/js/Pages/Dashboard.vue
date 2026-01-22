<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OccupancyChart from '@/Components/Dashboard/OccupancyChart.vue';
import TrendsChart from '@/Components/Dashboard/TrendsChart.vue';
import PendingAlerts from '@/Components/Dashboard/PendingAlerts.vue';
import QuickActions from '@/Components/Dashboard/QuickActions.vue';
import RecentActivities from '@/Components/Dashboard/RecentActivities.vue';
import { usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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
    hall_occupancy: any[];
    monthly_trends: any[];
    recent_activities: any[];
}

const props = defineProps<{
    breadcrumbs: any[];
    pageTitle: string;
    dashboardData: DashboardData;
    halls: Array<{ id: number; name: string }>;
    selectedHallId: string | null;
}>();

const currentHallId = ref(props.selectedHallId || '');
const route: any = (window as any).route;

const filterByHall = () => {
    router.get(route('dashboard'), { hall_id: currentHallId.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const stats = computed(() => props.dashboardData.statistics);
const pendingActions = computed(() => props.dashboardData.pending_actions);
const hallOccupancy = computed(() => props.dashboardData.hall_occupancy);
const monthlyTrends = computed(() => props.dashboardData.monthly_trends);
const recentActivities = computed(() => props.dashboardData.recent_activities);

// Greeting
const hour = new Date().getHours();
const greeting = computed(() => hour < 12 ? 'Good Morning' : hour < 18 ? 'Good Afternoon' : 'Good Evening');
// const emoji = computed(() => hour < 12 ? '🌅' : hour < 18 ? '☀️' : '🌙');

const permissions = usePage().props.permissions as string[];
</script>

<template>
<AuthenticatedLayout :breadcrumbs="breadcrumbs" :pageTitle="pageTitle">
<div class="py-12">
<div class="max-w-7xl mx-auto px-6 lg:px-8">

<!-- HEADER -->
<div class="flex flex-col xl:flex-row xl:items-center justify-between gap-8 mb-10">
    <div>
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 rounded-full border border-emerald-100 text-xs font-black uppercase">
            Live Dashboard
        </span>
        <h1 class="text-5xl font-black mt-4">
          {{ greeting }}, {{ usePage().props.auth.user.name }}
        </h1>
        <p class="text-xl font-bold mt-2">
            Real-time overview of hall occupancy and student activity
        </p>
    </div>

    <!-- Hall Filter -->
    <div v-if="halls && halls.length > 1"
         class="bg-white p-4 rounded-2xl shadow border min-w-[280px]">
        <label class="text-xs font-black uppercase">Filter by Hall</label>
        <select v-model="currentHallId"
                @change="filterByHall"
                class="w-full mt-1 text-sm font-bold border-0 focus:ring-0">
            <option value="">All Halls</option>
            <option v-for="hall in halls" :key="hall.id" :value="hall.id">
                {{ hall.name }}
            </option>
        </select>
    </div>
</div>

<!-- QUICK OVERVIEW -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    <!-- Occupancy Rate -->
    <div class="bg-white rounded-3xl p-6 shadow border flex justify-between">
        <div>
            <p class="text-sm font-black uppercase">Occupancy Rate</p>
            <h3 class="text-4xl font-black">{{ stats.seats.occupancy_rate }}%</h3>
            <span class="text-xs font-bold bg-emerald-100 px-2 py-1 rounded-full">
                Current usage
            </span>
        </div>
        <i class="bi-pie-chart-fill text-3xl"></i>
    </div>

    <!-- Active Students -->
    <div class="bg-white rounded-3xl p-6 shadow border flex justify-between">
        <div>
            <p class="text-sm font-black uppercase">Active Students</p>
            <h3 class="text-4xl font-black">{{ stats.students.allotted }}</h3>
            <span class="text-xs font-bold bg-blue-100 px-2 py-1 rounded-full">
                Currently staying
            </span>
        </div>
        <i class="bi-people-fill text-3xl"></i>
    </div>

    <!-- Pending Applications -->
    <div class="bg-white rounded-3xl p-6 shadow border flex justify-between">
        <div>
            <p class="text-sm font-black uppercase">Pending Applications</p>
            <h3 class="text-4xl font-black">{{ stats.students.attachment }}</h3>
            <span class="text-xs font-bold bg-amber-100 px-2 py-1 rounded-full">
                Review required
            </span>
        </div>
        <i class="bi-clock-history text-3xl"></i>
    </div>

</div>

<!-- PRIMARY STATS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">

    <!-- Total Students -->
    <div class="bg-white rounded-3xl p-6 shadow border">
        <p class="text-sm font-black uppercase">Total Students</p>
        <h3 class="text-5xl font-black">{{ stats.students.total }}</h3>
        <span class="text-xs font-bold bg-blue-100 px-2 py-1 rounded-full">
            Registered
        </span>
    </div>

    <!-- Allotted Seats -->
    <div class="bg-white rounded-3xl p-6 shadow border">
        <p class="text-sm font-black uppercase">Allotted Seats</p>
        <h3 class="text-5xl font-black">{{ stats.seats.allotted }}</h3>
        <span class="text-xs font-bold bg-emerald-100 px-2 py-1 rounded-full">
            Occupied
        </span>
    </div>

    <!-- Available Seats -->
    <div class="bg-white rounded-3xl p-6 shadow border">
        <p class="text-sm font-black uppercase">Available Seats</p>
        <h3 class="text-5xl font-black">{{ stats.seats.empty }}</h3>
        <span class="text-xs font-bold bg-purple-100 px-2 py-1 rounded-full">
            Available
        </span>
    </div>

    <!-- 🔴 Blocked Students -->
    <div class="bg-white rounded-3xl p-6 shadow border">
        <p class="text-sm font-black uppercase">Blocked Students</p>
        <h3 class="text-5xl font-black">{{ stats.students.blocked }}</h3>
        <span
            class="text-xs font-bold bg-rose-100 px-2 py-1 rounded-full"
            :class="stats.students.blocked > 0 ? 'animate-pulse' : ''"
        >
            Restricted
        </span>
        <p class="text-xs font-bold mt-2">
            Students blocked due to security or policy violations
        </p>
    </div>

</div>

<!-- CHARTS + SIDEBAR -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    <div class="xl:col-span-2 space-y-8">
        <OccupancyChart :data="hallOccupancy" />
        <TrendsChart :data="monthlyTrends" />
    </div>

    <div class="space-y-8">
        <PendingAlerts :pending-actions="pendingActions" />
        <!-- <RecentActivities :activities="recentActivities" /> -->
        <QuickActions :permissions="permissions" />
    </div>
</div>

</div>
</div>
</AuthenticatedLayout>
</template>
