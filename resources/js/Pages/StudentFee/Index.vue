<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs" :pageTitle="pageTitle">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <KTIcon
                            icon-name="magnifier"
                            icon-class="fs-1 position-absolute ms-6"
                        />
                        <input
                            v-model="search"
                            type="text"
                            class="form-control form-control-solid w-250px ps-15"
                            placeholder="Name, Roll, Reg..."
                        />
                    </div>
                    <!--end::Search-->
                </div>
                <!--begin::Card title-->

                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end gap-3 flex-wrap" data-kt-user-table-toolbar="base">
                        <!--begin::Hall Filter-->
                        <!-- <div class="w-200px">
                             <Multiselect
                                v-model="hallId"
                                :options="halls"
                                label="name"
                                value-prop="id"
                                placeholder="All Halls"
                                :searchable="true"
                                class="form-select-solid"
                            />
                        </div> -->
                        <!--end::Hall Filter-->

                        <!--begin::Status Filter-->
                        <div class="w-150px">
                             <Multiselect
                                v-model="status"
                                :options="['pending', 'approved', 'rejected']"
                                placeholder="All Status"
                                class="form-select-solid"
                            />
                        </div>
                        <!--end::Status Filter-->

                        <!--begin::Add User-->
                        <Link :href="route('student-fees.create')" class="btn btn-primary">
                            <KTIcon icon-name="plus" icon-class="fs-2" />
                            Pay Fee
                        </Link>
                        <!--end::Add User-->
                    </div>
                    <!--end::Toolbar-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px">Student Info</th>
                                <th class="min-width-150px">Transaction Details</th>
                                <th class="text-center min-w-100px">Amount (TK)</th>
                                <th class="text-center min-w-100px">Coverage</th>
                                <th class="text-center min-w-100px">Status</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <tr v-for="fee in fees.data" :key="fee.id">
                                <td class="d-flex align-items-center">
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 text-hover-primary mb-1 fw-bold">{{ fee.student?.name }}</span>
                                        <span class="fs-7 text-muted">{{ fee.student?.roll }}</span>
                                        <span class="fs-8 text-primary opacity-50">{{ fee.student?.registration }}</span>
                                        <div class="d-flex flex-column gap-1 mt-1">
                                            <span class="badge badge-light-info fw-bold fs-8 w-auto me-auto">{{ fee.hall?.name }}</span>
                                            <span v-if="fee.student?.active_allotment?.seat?.room && fee.student?.active_allotment?.seat?.seat_label" 
                                                class="fs-8 text-dark fw-bold badge badge-light-warning w-auto me-auto">
                                                Room: {{ fee.student.active_allotment.seat.room.room_number }}-{{ fee.student.active_allotment.seat.seat_label }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold fs-7 text-gray-800">{{ fee.transaction_id }}</span>
                                        <span class="fs-7 text-muted">{{ fee.fee_details }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-gray-800">৳{{ fee.amount }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="badge badge-light-primary fw-bold">{{ fee.months_count }} Months</div>
                                </td>
                                <td class="text-center">
                                    <div :class="['badge fw-bold', getStatusBadgeTheme(fee.status)]">
                                        {{ fee.status }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div v-if="fee.status === 'pending' && canApprove" class="d-flex justify-content-end gap-2">
                                        <button @click="approveFee(fee)" class="btn btn-icon btn-light-success btn-sm hover-elevate-up" title="Approve">
                                            <KTIcon icon-name="check" icon-class="fs-2" />
                                        </button>
                                        <button @click="openRejectionModal(fee)" class="btn btn-icon btn-light-danger btn-sm hover-elevate-up" title="Reject">
                                            <KTIcon icon-name="cross" icon-class="fs-2" />
                                        </button>
                                    </div>
                                    <span v-else-if="fee.status === 'pending'" class="text-warning fs-7 italic">Pending Approval</span>
                                    <span v-else class="text-muted fs-7 italic">Processed</span>
                                </td>
                            </tr>
                            <tr v-if="fees.data.length === 0">
                                <td colspan="6" class="text-center py-20 px-4">
                                    <div class="d-flex flex-column align-items-center justify-content-center mx-auto" style="max-width: 400px;">
                                        <KTIcon icon-name="search-list" icon-class="fs-5tx text-gray-200 mb-5" />
                                        <h3 class="fs-3 fw-bold text-gray-800 mb-2">No Records Found</h3>
                                        <p class="text-gray-400 fw-semibold fs-6">Try adjusting your filters or search keywords to find what you're looking for.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
            <div class="card-footer py-4" v-if="fees.total > 0">
                <div class="row">
                    <div class="col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start">
                        <div class="text-gray-600 fw-semibold fs-7">
                            Showing {{ fees.from }} to {{ fees.to }} of {{ fees.total }} entries
                        </div>
                    </div>
                    <TablePagination
                        :totalPages="fees.last_page"
                        :total="fees.total"
                        :perPage="fees.per_page"
                        :currentPage="fees.current_page"
                        @page-change="handlePageChange"
                    />
                </div>
            </div>
        </div>
        <!--end::Card-->

        <!--begin::Rejection Modal-->
        <div v-if="showRejectionModal" class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h3 class="modal-title fw-black fs-3">Reject Payment</h3>
                        <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" @click="showRejectionModal = false">
                             <KTIcon icon-name="cross" icon-class="fs-1" />
                        </div>
                    </div>

                    <div class="modal-body py-10">
                        <label class="form-label fw-bold fs-6 mb-3">Reason for rejection</label>
                        <textarea
                            v-model="form.rejection_reason"
                            class="form-control form-control-solid"
                            rows="4"
                            placeholder="Why is this payment being rejected?"
                        ></textarea>
                        <div v-if="form.errors.rejection_reason" class="text-danger fs-7 mt-2">{{ form.errors.rejection_reason }}</div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light fw-bold" @click="showRejectionModal = false">Cancel</button>
                        <button type="button" class="btn btn-danger fw-bold" @click="rejectFee" :disabled="form.processing">
                            Confirm Rejection
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Rejection Modal-->
        <div v-if="showRejectionModal" class="modal-backdrop fade show"></div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import TablePagination from "@/Components/kt-datatable/table-partials/table-content/table-footer/TablePagination.vue";
import { ref, watch, computed } from 'vue';
import Multiselect from '@vueform/multiselect';

const props = defineProps({
    fees: Object,
    halls: Array,
    filters: Object,
    breadcrumbs: Array,
    pageTitle: String,
    canApprove: Boolean,
});

const search = ref(props.filters.search || '');
const hallId = ref(props.filters.hall_id ? Number(props.filters.hall_id) : null);
const status = ref(props.filters.status || null);

// Simple native debounce function
function debounce(fn, delay) {
    let timeout;
    return (...args) => {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), delay);
    };
}

const updateFilters = debounce(() => {
    router.get(route('student-fees.index'), {
        page: 1,
        search: search.value,
        hall_id: hallId.value,
        status: status.value
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

const handlePageChange = (page) => {
    router.get(route('student-fees.index'), {
        page: page,
        search: search.value,
        hall_id: hallId.value,
        status: status.value
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([search, hallId, status], () => {
    updateFilters();
});

const form = useForm({
    status: '',
    rejection_reason: '',
});

const processingFee = ref(null);
const showRejectionModal = ref(false);

const approveFee = (fee) => {
    if (confirm('Are you sure you want to approve this fee?')) {
        form.status = 'approved';
        form.patch(route('student-fees.update-status', fee.id), {
            preserveScroll: true,
            onSuccess: () => {
                processingFee.value = null;
            }
        });
    }
};

const openRejectionModal = (fee) => {
    processingFee.value = fee;
    showRejectionModal.value = true;
};

const rejectFee = () => {
    form.status = 'rejected';
    form.patch(route('student-fees.update-status', processingFee.value.id), {
        onSuccess: () => {
            showRejectionModal.value = false;
            processingFee.value = null;
            form.rejection_reason = '';
        }
    });
};

const getStatusBadgeTheme = (status) => {
    switch (status) {
        case 'approved': return 'badge-light-success';
        case 'rejected': return 'badge-light-danger';
        default: return 'badge-light-warning';
    }
};
</script>
