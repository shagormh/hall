<template>
    <div class="w-full">
        <div v-if="data && data.length > 0" class="w-full">
            <apexchart
                type="line"
                height="350"
                :options="chartOptions"
                :series="series"
            ></apexchart>
        </div>
        <div v-else class="flex items-center justify-center h-64 text-slate-400">
            No trend data available
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import "apexcharts/dist/apexcharts.css";

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
                    filename: 'trends-data.csv',
                },
                svg: {
                    filename: 'trends-chart.svg',
                },
                png: {
                    filename: 'trends-chart.png',
                },
            }
        },
        zoom: {
            enabled: true,
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
    stroke: {
        curve: 'smooth',
        width: 3,
        lineCap: 'round',
    },
    colors: ['#06b6d4'],
    markers: {
        size: 6,
        colors: ['#06b6d4'],
        strokeColors: '#1e293b',
        strokeWidth: 3,
        hover: {
            size: 8,
        },
        shape: 'circle',
    },
    xaxis: {
        categories: props.data.map(item => item.month),
        labels: {
            rotate: -45,
            rotateAlways: false,
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
            text: 'Number of Allotments',
            style: {
                color: '#cbd5e1',
                fontSize: '13px',
                fontWeight: 600,
            }
        },
        labels: {
            formatter: (val: number) => Math.floor(val).toString(),
            style: {
                colors: '#cbd5e1',
                fontSize: '12px',
                fontWeight: 600,
            }
        },
    },
    grid: {
        borderColor: '#334155',
        strokeDashArray: 4,
        xaxis: {
            lines: {
                show: true,
            }
        }
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: (val: number) => `${val} allotments`,
        },
        style: {
            fontSize: '12px',
            fontFamily: 'inherit',
        }
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'dark',
            type: 'vertical',
            shadeIntensity: 0.2,
            gradientToColors: ['#0891b2'],
            inverseColors: false,
            opacityFrom: 0.6,
            opacityTo: 0.1,
            stops: [0, 100],
        },
    },
    states: {
        hover: {
            filter: {
                type: 'darken',
                value: 0.2,
            }
        },
        active: {
            filter: {
                type: 'darken',
                value: 0.3,
            }
        }
    }
}));
</script>
