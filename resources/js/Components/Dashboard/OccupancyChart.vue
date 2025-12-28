<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Hall Occupancy</h3>
        <div v-if="data && data.length > 0">
            <apexchart
                type="bar"
                height="300"
                :options="chartOptions"
                :series="series"
            ></apexchart>
        </div>
        <div v-else class="flex items-center justify-center h-64 text-gray-500">
            No data available
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const apexchart = VueApexCharts;

interface HallOccupancy {
    hall_name: string;
    total_seats: number;
    allotted_seats: number;
    empty_seats: number;
    occupancy_rate: number;
}

interface Props {
    data: HallOccupancy[];
}

const props = defineProps<Props>();

const series = computed(() => [
    {
        name: 'Allotted',
        data: props.data.map(hall => hall.allotted_seats),
    },
    {
        name: 'Available',
        data: props.data.map(hall => hall.empty_seats),
    },
]);

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 300,
        stacked: true,
        toolbar: {
            show: false,
        },
    },
    plotOptions: {
        bar: {
            horizontal: false,
            borderRadius: 4,
            dataLabels: {
                total: {
                    enabled: true,
                    style: {
                        fontSize: '12px',
                        fontWeight: 600,
                    },
                },
            },
        },
    },
    colors: ['#3B82F6', '#E5E7EB'],
    xaxis: {
        categories: props.data.map(hall => hall.hall_name),
    },
    yaxis: {
        title: {
            text: 'Number of Seats',
        },
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
    },
    fill: {
        opacity: 1,
    },
    dataLabels: {
        enabled: false,
    },
    tooltip: {
        y: {
            formatter: (val: number) => `${val} seats`,
        },
    },
}));
</script>
