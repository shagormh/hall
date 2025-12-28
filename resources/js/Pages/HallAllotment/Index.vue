<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">

        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <KTIcon icon-name="magnifier" icon-class="fs-1 position-absolute ms-6" />
                        <input type="text" v-model="search" @input="searchData()" class="form-control form-control-solid w-250px ps-15" placeholder="Search Hall Allotment" />
                    </div>
                    <!--end::Search-->
                </div>

                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <button type="button" class="btn btn-primary my-1 me-3" @click="printInvoice">Print </button>
                        <!--begin::Add Permission-->
                        <Link v-if="checkPermission('can-create-hall')" :href="route('hall-allotments.create')" class="btn btn-primary my-1">
                            <KTIcon icon-name="plus" icon-class="fs-2" />
                            Add Hall Allotment
                        </Link>
                        <!--end::Add Permission-->
                    </div>
                    <!--end::Toolbar-->
                </div>
            </div>

            <div class="fw-bold fs-2 mb-6 position-relative print" id="invoice-content">
                <div class="logo-wrapper position-absolute">
                    <img
                        alt="Logo"
                        :src="getAssetPath('/media/logos/jkkniu-logo.png')"
                        class="h-50px"
                    />
                </div>
                <div class="text-center">
                    <h1 class="mb-2">{{ props.hallAllotments && props.hallAllotments.length > 0 ? props.hallAllotments[0].hall?.name : '' }}</h1>
                    <h4 class="mb-2">JKKNIU, Trishal, Mymensingh</h4>
                </div>
            </div>

            <div class="card-body pt-0" id="invoice-content">
                <Datatable @on-sort="sortData" :data="tableData" :header="tableHeader" :enable-items-per-page-dropdown="true" :checkbox-enabled="false">
                    <!-- Serial Number -->
                    <template v-slot:serial_number="{ row: hallAllotment }">
                        {{ hallAllotment.serial_number }}
                    </template>

                     <!-- Allotment Id -->
                    <template v-slot:id="{ row: hallAllotment }">
                        {{ hallAllotment.id }}
                    </template>

                    <!-- Student ROLL -->
                    <template v-slot:student_roll="{ row: hallAllotment }">
                        {{ hallAllotment.student_roll }}
                    </template>

                    <!-- Student Name -->
                    <template v-slot:student_name="{ row: hallAllotment }">
                        {{ hallAllotment.student_name }}
                    </template>

                    <!--hall name -->
                    <template v-slot:hall_name="{ row: hallAllotment }">
                        {{ hallAllotment.hall_name }}
                    </template>

                    <!-- Seat Code -->
                    <template v-slot:seat_code="{ row: hallAllotment }">
                        {{ hallAllotment.seat_code }}
                    </template>

                    <!-- Starting Month -->
                    <template v-slot:starting_month="{ row: hallAllotment }">
                        {{ formatStartingMonth(hallAllotment.starting_month) }}
                    </template>

                     <!-- Allotment Date -->
                    <template v-slot:allotment_date="{ row: hallAllotment }">
                        {{ formatDate(hallAllotment.allotment_date) }}
                    </template>

                    <!-- Status -->
                    <template v-slot:status="{ row: hallAllotment }">
                        <span :class="getStatusClass(hallAllotment.status)" class="badge">
                            {{ formatStatus(hallAllotment.status) }}
                        </span>
                    </template>

                    <!-- Ending Month -->
                    <template v-slot:ending_month="{ row: hallAllotment }">
                        {{ formatStartingMonth(hallAllotment.ending_month) }}
                    </template>

                    <template v-slot:actions="{ row: hallAllotment }">
                        <div class="d-flex align-items-center justify-content-end no-print">
                            <!-- For Active Allotments -->
                            <template v-if="hallAllotment.status === 'active'">
                                <Link v-if="checkPermission('can-edit-hall-allotment')"
                                    :href="route('hall-allotments.edit', hallAllotment.id)"
                                    class="btn btn-icon btn-flex btn-active-light-primary w-30px h-30px me-2"
                                    title="Edit">
                                    <i class="fas fa-pencil text-primary"></i>
                                </Link>

                                <!-- Cancel Request Button -->
                                <button v-if="checkPermission('can-delete-hall-allotment')"
                                        @click="requestCancel(hallAllotment.id, hallAllotment.starting_month, hallAllotment.student_name)"
                                        class="btn btn-icon btn-flex btn-active-light-warning w-30px h-30px me-2"
                                        title="Request Cancel">
                                    <i class="fas fa-times text-warning"></i>
                                </button>

                                <!-- Delete Button -->
                                <button v-if="checkPermission('can-delete-hall-allotment')"
                                        @click="deleteAllotment(hallAllotment.id, hallAllotment.student_name)"
                                        class="btn btn-icon btn-flex btn-active-light-danger w-30px h-30px"
                                        title="Permanent Delete">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </template>

                            <!-- For Cancel Requested Allotments -->
                            <template v-else-if="hallAllotment.status === 'cancel_requested'">
                                <span class="badge badge-light-warning me-2">Cancel Requested</span>
                                <button v-if="checkPermission('can-approve-cancel')"
                                        @click="approveCancel(hallAllotment.id)"
                                        class="btn btn-icon btn-flex btn-active-light-success w-30px h-30px me-2"
                                        title="Approve Cancel">
                                    <i class="fas fa-check text-success"></i>
                                </button>
                            </template>

                            <!-- For Cancelled Allotments -->
                            <span v-else-if="hallAllotment.status === 'cancelled'" class="text-muted fs-7">Cancelled</span>

                            <!-- For Other Status -->
                            <span v-else class="text-muted fs-7">
                                {{ formatStatus(hallAllotment.status) }}
                            </span>
                        </div>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted,defineProps } from 'vue';
import Datatable from "@/Components/kt-datatable/KTDataTable.vue";
import type { Sort } from "@/Components/kt-datatable/table-partials/Models";
import { MenuComponent } from "@/Assets/ts/components";
import arraySort from "array-sort";
import { Link, router } from '@inertiajs/vue3';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import { getAssetPath } from "@/Core/helpers/Assets";
import { checkPermission } from "@/Core/helpers/Permission";
import i18n from '@/Core/plugins/i18n';
import Swal from 'sweetalert2';

const { t } = i18n.global;

const props = defineProps<{
    hallAllotments?: IHallAllotment[],
    breadcrumbs?: Breadcrumb[],
    pageTitle?: string
}>();

interface Breadcrumb {
    url: string;
    title: string;
}

interface IHallAllotment {
    id: number;
    student_roll: number;
    student_name: string;
    hall_name: string;
    student?: IStudent;
    hall?: IHall;
    seat_code?: string;
    starting_month?: string;
    allotment_date?: string;
    ending_month?: string;
    status?: string;
    cancel_request_date?: string;
}

interface IStudent {
    id: number;
    name: string;
    roll: string;
}

interface IHall {
    id: number;
    name: string;
}

const tableHeader = ref([
    {
        columnName: 'SL',
        columnLabel: "serial_number",
        sortEnabled: false,
        columnWidth: 50
    },
    {
        columnName: 'Allotment ID',
        columnLabel: "id",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Student Roll',
        columnLabel: "student_roll",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Student Name',
        columnLabel: "student_name",
        sortEnabled: true,
        columnWidth: 200
    },
    {
        columnName: 'Hall Name',
        columnLabel: "hall_name",
        sortEnabled: true,
        columnWidth: 200
    },
    {
        columnName: 'Seat Number',
        columnLabel: "seat_code",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Starting Month',
        columnLabel: "starting_month",
        sortEnabled: true,
        columnWidth: 150
    },
    {
        columnName: 'Ending Month',
        columnLabel: "ending_month",
        sortEnabled: true,
        columnWidth: 150
    },
    {
        columnName: 'Allotment Date',
        columnLabel: "allotment_date",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Status',
        columnLabel: "status",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Action',
        columnLabel: "actions",
        sortEnabled: false,
        columnWidth: 120
    },
]);

const tableData = ref < IHallAllotment[] > ([]);
const initHallAllotments = ref < IHallAllotment[] > ([]);

onMounted(() => {
    if (props.hallAllotments) {
        let serial_number = 0;
        initHallAllotments.value = props.hallAllotments.map((allotment: any) => ({
            id: allotment.id,
            serial_number: ++serial_number,
            student_roll: allotment.student?.roll || '',
            student_name: allotment.student?.name || '',
            hall_name: allotment.hall?.name || '',
            seat_code: allotment.seat?.seat_code || '',
            starting_month: allotment.starting_month || '',
            allotment_date: allotment.allotment_date || '',
            ending_month: allotment.ending_month || '',
            status: allotment.status || 'active',
            cancel_request_date: allotment.cancel_request_date || ''
        }));

        initHallAllotments.value.reverse(); // Show latest allotments first
        tableData.value = initHallAllotments.value;
    }
});

const search = ref < string > ("");
const searchData = () => {
    tableData.value = [...initHallAllotments.value];
    if (search.value !== "") {
        tableData.value = tableData.value.filter(item => searchingFunc(item, search.value));
    }
    MenuComponent.reinitialization();
};

const searchingFunc = (obj: any, value: string): boolean => {
    for (let key in obj) {
        if (!Number.isInteger(obj[key]) && !(typeof obj[key] === "object")) {
            if (obj[key] && obj[key].includes && obj[key].includes(value)) {
                return true;
            }
        }
    }
    return false;
};

const sortData = (sort: Sort) => {
    const reverse: boolean = sort.order === "asc";
    if (sort.label) {
        arraySort(tableData.value, sort.label, {
            reverse
        });
    }
};

const formatStartingMonth = (date: string) => {
    if (!date) return '-';
    const [year, month] = date.split('-');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthIndex = parseInt(month) - 1;
    return `${monthNames[monthIndex]}/${year}`;
};

const formatDate = (date: string) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-GB');
};

const formatStatus = (status: string) => {
    const statusMap: { [key: string]: string } = {
        'active': 'Active',
        'pending': 'Pending',
        'cancelled': 'Cancelled',
        'pending_cancel': 'Pending Cancel'
    };
    return statusMap[status] || status;
};

const getStatusClass = (status: string) => {
    const statusClasses: { [key: string]: string } = {
        'active': 'badge-light-success',
        'pending': 'badge-light-warning',
        'cancelled': 'badge-light-danger',
        'pending_cancel': 'badge-light-warning'
    };
    return statusClasses[status] || 'badge-light-secondary';
};

const cancelAllotment = (allotmentId: number) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to cancel this hall allotment?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('hall-allotments.cancel', allotmentId), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire(
                        'Cancelled!',
                        'Hall allotment has been cancelled.',
                        'success'
                    );
                    // Refresh the page to update the table
                    router.reload();
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Failed to cancel hall allotment.',
                        'error'
                    );
                }
            });
        }
    });
};

const approveCancel = (allotmentId: number) => {
    Swal.fire({
        title: 'Approve Cancel Request',
        text: 'Are you sure you want to approve this cancellation request?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('hall-allotments.approve-cancel', allotmentId), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire(
                        'Approved!',
                        'Cancellation request has been approved.',
                        'success'
                    );
                    // Refresh the page to update the table
                    router.reload();
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Failed to approve cancellation request.',
                        'error'
                    );
                }
            });
        }
    });
};

const requestCancel = async (allotmentId: number, startingMonth: string, studentName: string) => {
    const today = new Date();
    const baseDate = new Date(today.getFullYear(), today.getMonth(), 1); // current month

    // Generate next 12 months starting from current month
    const months: { name: string; value: string }[] = [];
    for (let i = 0; i <= 12; i++) {
        const date = new Date(baseDate.getFullYear(), baseDate.getMonth() + i, 1);
        const monthName = date.toLocaleString('en-US', { month: 'long', year: 'numeric' });
        const monthValue = new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split('T')[0];
        months.push({ name: monthName, value: monthValue });
    }

    const { value: selectedMonth } = await Swal.fire({
        title: 'Request Cancellation',
        html: `
            <div class="text-start">
                <p><strong>Student:</strong> ${studentName}</p>
                <p><strong>Current Starting Month:</strong> ${new Date(startingMonth).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</p>
                <p>Select the ending month for this allotment:</p>
                <select id="ending-month" class="form-select">
                    <option value="">Select Ending Month</option>
                    ${months.map(m => `<option value="${m.value}">${m.name}</option>`).join('')}
                </select>
                <p class="text-muted mt-2 small">
                    The allotment will stay active until the end of the selected month.
                </p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Request Cancel',
        cancelButtonText: 'Keep Active',
        preConfirm: () => {
            const select = document.getElementById('ending-month') as HTMLSelectElement;
            if (!select.value) {
                Swal.showValidationMessage('Please select an ending month');
                return false;
            }
            return select.value;
        }
    });

    if (selectedMonth) {
        router.post(route('hall-allotments.request-cancel', allotmentId), {
            ending_month: selectedMonth
        }, {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire('Request Sent!', 'Cancellation request has been submitted.', 'success');
                router.reload();
            },
            onError: (error: any) => {
                Swal.fire('Error!', error.response?.data?.message || 'Failed to submit cancellation request.', 'error');
            }
        });
    }
};




// Add this method in your script section
const deleteAllotment = (allotmentId: number, studentName: string) => {
    Swal.fire({
        title: 'Are you sure?',
        html: `You want to <strong class="text-danger">permanently delete</strong> the allotment for <strong>${studentName}</strong>?<br><br>
              <span class="text-warning">⚠️ This action cannot be undone!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete permanently!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('hall-allotments.destroy', allotmentId), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Hall allotment has been permanently deleted.',
                        'success'
                    );
                },
                onError: () => {
                    Swal.fire(
                        'Error!',
                        'Failed to delete hall allotment.',
                        'error'
                    );
                }
            });
        }
    });
};

const printInvoice = () => {
    document.title = `Hall Allotment`;
    window.print();
};
</script>

<style>
    #print-only{
        display: none;
    }

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
            /* size: landscape; */
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

        .card-header{
            margin-top: -3rem;
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

        #no-print {
            display: none !important;
        }

        .no-print {
            display: none !important;
        }

        .print{
            display: block !important;
        }

        #print-only{
            display: block;
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
            font-size: 12px !important;
        }

        .table:not(.table-bordered) tfoot tr:last-child,
        .table:not(.table-bordered) tbody tr:last-child {
            border-bottom: 0.2px solid #9291914f !important;
        }

        table th:last-child,
        table td:last-child {
            display: none !important;
        }

        .ledgerTable table th:last-child,
        .ledgerTable table td:last-child {
            display: block !important;
        }
    }
</style>
