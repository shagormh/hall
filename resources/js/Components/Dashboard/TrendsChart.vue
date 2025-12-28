<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Allotment Trends</h3>
        <div v-if="data && data.length > 0">
            <apexchart
                type="line"
                height="300"
                :options="chartOptions"
                :series="series"
            ></apexchart>
        </div>
        <div v-else class="flex items-center justify-center h-64 text-gray-500">
            No trend data available
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const apexchart = VueApexCharts;

interface MonthlyTrend {
    month: string;
    count: number;
}

interface Props {
    data: MonthlyTrend[];
}

const props = defineProps<Props>();

const series = computed(() => [
    {
        name: 'Allotments',
        data: props.data.map(item => item.count),
    },
]);

const chartOptions = computed(() => ({
    chart: {
        type: 'line',
        height: 300,
        toolbar: {
            show: false,
        },
        zoom: {
            enabled: false,
        },
    },
    stroke: {
        curve: 'smooth',
        width: 3,
    },
    colors: ['#3B82F6'],
    markers: {
        size: 5,
        colors: ['#3B82F6'],
        strokeColors: '#fff',
        strokeWidth: 2,
        hover: {
            size: 7,
        },
    },
    xaxis: {
        categories: props.data.map(item => item.month),
        labels: {
            rotate: -45,
            rotateAlways: false,
        },
    },
    yaxis: {
        title: {
            text: 'Number of Allotments',
        },
        labels: {
            formatter: (val: number) => Math.floor(val).toString(),
        },
    },
    grid: {
        borderColor: '#f1f1f1',
    },
    tooltip: {
        y: {
            formatter: (val: number) => `${val} allotments`,
        },
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'light',
            type: 'vertical',
            shadeIntensity: 0.3,
            gradientToColors: ['#60A5FA'],
            inverseColors: false,
            opacityFrom: 0.8,
            opacityTo: 0.3,
            stops: [0, 100],
        },
    },
}));
</script>
