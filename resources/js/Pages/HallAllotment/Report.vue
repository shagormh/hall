<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <!-- Filters Section -->
                <div class="card-title flex-column">
                    <div class="d-flex align-items-center gap-4 flex-wrap mb-4">
                        <!-- Filter Type -->
                        <div class="w-200px">
                            <label class="fs-6 fw-semibold mb-2">Filter Type</label>
                            <select v-model="filters.filter_type" @change="applyFilters" class="form-select form-select-solid">
                                <option value="">All Allotments</option>
                                <option value="today_created">Today's Allotments (Created)</option>
                                <option value="active_today">Active Today</option>
                                <option value="active_this_month">Active This Month</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2 p-4 bg-light-success rounded">
                            <!-- ID From -->
                            <div class="w-100px">
                                <label class="fs-7 fw-bold text-success mb-1">From ID</label>
                                <input type="number" v-model="filters.id_from" @input="applyFilters" class="form-control form-control-solid form-control-sm" placeholder="1" />
                            </div>

                            <!-- ID To -->
                            <div class="w-100px">
                                <label class="fs-7 fw-bold text-success mb-1">To ID</label>
                                <input type="number" v-model="filters.id_to" @input="applyFilters" class="form-control form-control-solid form-control-sm" placeholder="100" />
                            </div>
                        </div>

                        <!-- Month & Year Group -->
                        <div class="d-flex align-items-center gap-2 p-4 bg-light-success rounded ">
                            <div class="w-150px">
                                <label class="fs-7 fw-bold text-success mb-1">Select Month</label>
                                <select v-model="filters.month" @change="applyFilters" class="form-select form-select-solid form-select-sm">
                                    <option value="">Month</option>
                                    <option v-for="m in monthNames" :key="m.value" :value="m.value">
                                        {{ m.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="w-120px">
                                <label class="fs-7 fw-bold text-success mb-1">Select Year</label>
                                <select v-model="filters.year" @change="applyFilters" class="form-select form-select-solid form-select-sm">
                                    <option value="">Year</option>
                                    <option v-for="y in yearList" :key="y" :value="y">
                                        {{ y }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Custom Date Range -->
                        <div class="d-flex align-items-center gap-2 p-4 bg-light-success rounded">
                            <div class="w-300px">
                                <label class="fs-7 fw-bold text-success mb-1">Custom Date Range</label>
                                <div class="d-flex gap-2">
                                    <input type="date" v-model="filters.start_date" @change="applyFilters" class="form-control form-control-solid form-control-sm" />
                                    <input type="date" v-model="filters.end_date" @change="applyFilters" class="form-control form-control-solid form-control-sm" />
                                </div>
                            </div>
                        </div>
                        <div class="align-self-end">
                            <button @click="resetFilters" class="btn btn-light btn-active-light-primary me-2">Reset</button>
                            <button @click="printReport" class="btn btn-primary">Print Report</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fw-bold fs-2 mb-10 position-relative print" id="invoice-content">
                <div class="logo-wrapper position-absolute">
                    <img
                        alt="Logo"
                        :src="getAssetPath('/media/logos/jkkniu-logo.png')"
                        class="h-50px"
                    />
                </div>
                <div class="text-center">
                    <h1 class="mb-2">Hall Allotment Report</h1>
                    <h4 class="mb-2">JKKNIU, Trishal, Mymensingh</h4>
                    <p class="fs-6 text-muted">Generated on: {{ currentDateTime }}</p>
                </div>
                
            </div>

            <div class="card-body pt-0" id="invoice-content">
                <Datatable :data="tableData" :header="tableHeader" :enable-items-per-page-dropdown="true" :checkbox-enabled="false">
                    <template v-slot:serial_number="{ row: allotment }">
                        {{ allotment.serial_number }}
                    </template>
                    <template v-slot:id="{ row: allotment }">
                        {{ allotment.id }}
                    </template>
                    <template v-slot:student_roll="{ row: allotment }">
                        {{ allotment.student?.roll }}
                    </template>
                    <template v-slot:student_name="{ row: allotment }">
                        {{ allotment.student?.name }}
                    </template>
                    <template v-slot:hall_name="{ row: allotment }">
                        {{ allotment.hall?.name }}
                    </template>
                    <template v-slot:seat_code="{ row: allotment }">
                        {{ allotment.seat?.seat_code }}
                    </template>
                    <template v-slot:starting_month="{ row: allotment }">
                        {{ formatMonth(allotment.starting_month) }}
                    </template>
                    <template v-slot:allotment_date="{ row: allotment }">
                        {{ formatDate(allotment.allotment_date) }}
                    </template>
                    <template v-slot:status="{ row: allotment }">
                        <span :class="getStatusClass(allotment.status)" class="badge">
                            {{ allotment.status }}
                        </span>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Datatable from "@/Components/kt-datatable/KTDataTable.vue";
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { getAssetPath } from "@/Core/helpers/Assets";

const monthNames = [
    { name: 'January', value: '01' },
    { name: 'February', value: '02' },
    { name: 'March', value: '03' },
    { name: 'April', value: '04' },
    { name: 'May', value: '05' },
    { name: 'June', value: '06' },
    { name: 'July', value: '07' },
    { name: 'August', value: '08' },
    { name: 'September', value: '09' },
    { name: 'October', value: '10' },
    { name: 'November', value: '11' },
    { name: 'December', value: '12' },
];

const yearList = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear - 5; i <= currentYear + 1; i++) {
        years.push(i);
    }
    return years.reverse();
});

const props = defineProps<{
    hallAllotments: any[],
    filters: any,
    breadcrumbs: any[],
    pageTitle: string
}>();

const filters = ref({
    filter_type: props.filters.filter_type || '',
    id_from: props.filters.id_from || '',
    id_to: props.filters.id_to || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    year: props.filters.year || '',
    month: props.filters.month || '',
    status: props.filters.status || ''
});

const tableHeader = ref([
    { columnName: 'SL', columnLabel: "serial_number", sortEnabled: false, columnWidth: 50 },
    { columnName: 'ID', columnLabel: "id", sortEnabled: true, columnWidth: 70 },
    { columnName: 'Roll', columnLabel: "student_roll", sortEnabled: true, columnWidth: 100 },
    { columnName: 'Student Name', columnLabel: "student_name", sortEnabled: true, columnWidth: 200 },
    { columnName: 'Hall', columnLabel: "hall_name", sortEnabled: true, columnWidth: 150 },
    { columnName: 'Seat', columnLabel: "seat_code", sortEnabled: true, columnWidth: 100 },
    { columnName: 'Starting Month', columnLabel: "starting_month", sortEnabled: true, columnWidth: 150 },
    { columnName: 'Allotment Date', columnLabel: "allotment_date", sortEnabled: true, columnWidth: 120 },
    { columnName: 'Status', columnLabel: "status", sortEnabled: true, columnWidth: 100 },
]);

const tableData = computed(() => {
    return props.hallAllotments.map((allotment, index) => ({
        ...allotment,
        serial_number: index + 1
    }));
});

const applyFilters = () => {
    router.get(route('hall-allotments.report'), filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['hallAllotments', 'filters']
    });
};

const resetFilters = () => {
    filters.value = {
        filter_type: '',
        id_from: '',
        id_to: '',
        start_date: '',
        end_date: '',
        year: '',
        month: '',
        status: ''
    };
    applyFilters();
};

const formatMonth = (date: string) => {
    if (!date) return '-';
    const d = new Date(date);
    return d.toLocaleString('default', { month: 'short', year: 'numeric' });
};

const formatDate = (date: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB');
};

const getStatusClass = (status: string) => {
    const classes: any = {
        'active': 'badge-light-success',
        'cancelled': 'badge-light-danger',
        'cancel_requested': 'badge-light-warning'
    };
    return classes[status] || 'badge-light-secondary';
};

const currentDateTime = computed(() => {
    return new Date().toLocaleString();
});

const printReport = () => {
    window.print();
};
</script>

<style>
    .print{
        display: none !important;
    }

    .logo-wrapper {
        display: flex;
        align-items: center;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
    }

    .logo-wrapper img {
        object-fit: contain;
        height: 50px;
    }

    #invoice-content {
        min-height: 80px;
    }

    @media print {
        @page {
            margin-left: 0.5cm !important;
            margin-right: 0.5cm !important;
        }

        html,body * {
            visibility: hidden;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* For second page don't show the heading again */
        thead {
            display: table-row-group !important;
        }

        .card-body, .app-container, .app-content, #kt_app_content {
            margin-top: -45px !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: none !important;
        }

        #invoice-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        #invoice-content, #invoice-content * {
            visibility: visible !important;
        }

        .logo-wrapper img {
            visibility: visible !important;
            height: 50px !important;
            width: auto !important;
        }

        .no-print {
            display: none !important;
        }

        .print{
            display: block !important;
        }

        .table {
            page-break-inside: auto;
            border-collapse: collapse !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            table-layout: fixed !important;
        }

        .table th,
        .table td {
            border: 0.2px solid #9291914f !important;
            padding: 2px 10px !important;
            text-align: center !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            font-size: 11px !important;
        }

        .table:not(.table-bordered) tfoot tr:last-child,
        .table:not(.table-bordered) tbody tr:last-child {
            border-bottom: 0.2px solid #9291914f !important;
        }
    }
</style>
