<template>
    <div class="w-full">
        <div v-if="data && data.length > 0" class="w-full">
            <apexchart
                type="bar"
                height="350"
                :options="chartOptions"
                :series="series"
            ></apexchart>
        </div>
        <div v-else class="flex items-center justify-center h-64 text-slate-400">
            No data available
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import "apexcharts/dist/apexcharts.css";

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
            show: true,
            tools: {
                download: true,
                selection: true,
                zoom: true,
                zoomin: true,
                zoomout: true,
                pan: true,
                reset: true,
            },
            export: {
                csv: {
                    filename: 'occupancy-data.csv',
                },
                svg: {
                    filename: 'occupancy-chart.svg',
                },
                png: {
                    filename: 'occupancy-chart.png',
                },
            }
        },
        background: 'transparent',
        foreColor: '#cbd5e1',
        dropShadow: {
            enabled: true,
            top: 2,
            left: 2,
            blur: 4,
            opacity: 0.1,
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            borderRadius: 8,
            columnWidth: '65%',
            dataLabels: {
                total: {
                    enabled: false,
                },
            },
        },
    },
    colors: ['#06b6d4', '#8b5cf6'],
    xaxis: {
        categories: props.data.map(hall => hall.hall_name),
        labels: {
            style: {
                colors: '#cbd5e1',
                fontSize: '12px',
                fontWeight: 600,
            }
        },
        axisBorder: {
            color: '#475569',
        },
        axisTicks: {
            color: '#475569',
        }
    },
    yaxis: {
        title: {
            text: 'Number of Seats',
            style: {
                color: '#cbd5e1',
                fontWeight: 600,
            }
        },
        labels: {
            style: {
                colors: '#cbd5e1',
                fontSize: '12px',
                fontWeight: 600,
            }
        }
    },
    grid: {
        borderColor: '#334155',
        strokeDashArray: 4,
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: {
            colors: '#cbd5e1',
        },
        fontSize: 12,
        fontFamily: 'inherit',
        markers: {
            width: 12,
            height: 12,
            radius: 3,
            fillColor: '#06b6d4',
        }
    },
    fill: {
        opacity: 0.9,
    },
    dataLabels: {
        enabled: false,
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: (val: number) => `${val} seats`,
        },
        style: {
            fontSize: '12px',
            fontFamily: 'inherit',
        }
    },
    states: {
        hover: {
            filter: {
                type: 'darken',
                value: 0.15,
            }
        }
    }
}));
</script>
