<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">

        <div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
            <div class="content flex-row-fluid" id="kt_content">
                <div class="d-flex flex-column flex-lg-row">
                    <!-- User Information Section -->
                    <div class="flex-column flex-lg-row-auto w-lg-250px w-xl-350px mb-10">
                        <div class="card mb-5 mb-xl-8">
                            <div class="card-body">
                                <!--begin::Summary-->
                                <div class="d-flex flex-center flex-column py-5">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-100px symbol-circle mb-7">
                                        <div class="symbol-label fs-2 bg-light-danger text-danger">{{ getInitials(props?.user) }}</div>
                                    </div>
                                    <!--end::Avatar-->

                                    <!--begin::Name-->
                                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-3">{{ props.user?.name }}</a>
                                    <!--end::Name-->

                                    <!--begin::Position-->
                                    <div class="mb-9">
                                        <div v-for="role in props.user?.roles" :key="role.id" class="badge badge-lg badge-light-primary d-inline me-3">{{ role.name }}</div>
                                    </div>
                                    <!--end::Position-->
                                </div>
                                <!--end::Summary-->

                                <!--begin::Details toggle-->
                                <div class="d-flex flex-stack fs-4 py-3">
                                    <div class="fw-bold rotate collapsible" data-bs-toggle="collapse" href="#kt_user_view_details" role="button" aria-expanded="false" aria-controls="kt_user_view_details" @click="toggleDetails">Details
                                    <span class="ms-2 rotate-180">
                                        <i class="ki-duotone ki-down fs-3"></i>
                                    </span></div>
                                </div>
                                <!--end::Details toggle-->
                                <div class="separator"></div>

                                <!--begin::Details content-->
                                <div id="kt_user_view_details" class="collapse show" v-show="isDetailsVisible">
                                    <div class="pb-5 fs-6">
                                        <!--begin::Details item-->
                                        <div class="fw-bold mt-5">Email</div>
                                        <div class="text-gray-600">
                                            <a href="#" class="text-gray-600 text-hover-primary">{{ props.user?.email }}</a>
                                        </div>
                                        <!--begin::Details item-->
                                    </div>
                                </div>
                                <!--end::Details content-->
                            </div>
                        </div>
                    </div>

                    <!-- Tab Sections -->
                    <div class="flex-lg-row-fluid ms-lg-15">
                        <!--begin::Nav Tab-->
                        <div class="card-toolbar m-0">
                            <ul class="nav nav-stretch fs-5 fw-semibold nav-line-tabs nav-line-tabs-2x border-transparent" role="tablist">
                                <!-- Student Info Tab -->
                                <li class="nav-item" role="presentation" v-if="props.studentDetails">
                                    <a class="nav-link text-active-primary" data-bs-toggle="tab" role="tab" href="javascript:void(0)" @click="activeTab = 1"
                                    :class="{ 'active': activeTab === 1 }">
                                        Student Info
                                    </a>
                                </li>

                                <!-- Assigned Halls Tab (Provost/Tutor) -->
                                <li class="nav-item" role="presentation" v-if="props.assignedHalls">
                                    <a class="nav-link text-active-primary" data-bs-toggle="tab" role="tab" href="javascript:void(0)" @click="activeTab = 2"
                                    :class="{ 'active': activeTab === 2 }">
                                        Assigned Halls
                                    </a>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <a class="nav-link text-active-primary" data-bs-toggle="tab" role="tab" href="javascript:void(0)" @click="activeTab = 3"
                                    :class="{ 'active': activeTab === 3 }">
                                        Account Settings
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!--end::Nav Tab-->

                        <!-- Start: Student Details Section -->
                        <div id="kt_referred_student_details_tab_content" class="tab-content" v-if="props.studentDetails">
                            <div id="student_details" class="py-4 tab-pane fade active show" role="tabpanel" v-show="activeTab === 1">
                                <div class="card card-flush mb-6 mb-xl-9">
                                    <div class="card-header mt-6">
                                        <div class="card-title flex-column">
                                            <h2 class="mb-1">Student Information</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pb-5 pt-0">
                                        
                                        <!-- Student Basic Info -->
                                        <div class="row mb-7">
                                            <div class="col-md-6 mb-7">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="fw-bold text-muted">Roll Number</div>
                                                    <div class="fs-5 fw-bold text-gray-800">{{ props.studentDetails.student.roll }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-7">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="fw-bold text-muted">Registration Number</div>
                                                    <div class="fs-5 fw-bold text-gray-800">{{ props.studentDetails.student.registration || 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-7">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="fw-bold text-muted">Department</div>
                                                    <div class="fs-5 fw-bold text-gray-800">{{ props.studentDetails.student.department?.name || 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-7">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="fw-bold text-muted">Email</div>
                                                    <div class="fs-5 fw-bold text-gray-800">{{ props.studentDetails.student.email }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-7" v-if="props.studentDetails.student.mobile_number">
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="fw-bold text-muted">Mobile Number</div>
                                                    <div class="fs-5 fw-bold text-gray-800">{{ props.studentDetails.student.mobile_number }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="separator my-7"></div>

                                        <!-- Hall Status -->
                                        <h3 class="fs-4 fw-bold text-dark mb-5">Hall Information</h3>
                                        <div class="row mb-10">
                                            <div class="col-md-6">
                                                <div class="d-flex flex-column gap-3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <KTIcon icon-name="home" icon-class="fs-2 text-primary" />
                                                        <span class="fw-bold text-muted">Hall:</span>
                                                        <span class="fw-bold text-gray-800">{{ props.studentDetails.student.hall?.name || 'No Hall Assigned' }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <KTIcon icon-name="information-5" icon-class="fs-2 text-primary" />
                                                        <span class="fw-bold text-muted">Status:</span>
                                                        <span class="badge badge-light-primary fw-bold text-uppercase">{{ props.studentDetails.student.hall_status || 'N/A' }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2" v-if="props.studentDetails.student.active_allotment">
                                                        <KTIcon icon-name="calendar" icon-class="fs-2 text-primary" />
                                                        <span class="fw-bold text-muted">Allotment Date:</span>
                                                        <span class="fw-bold text-gray-800">{{ new Date(props.studentDetails.student.active_allotment.allotment_date).toLocaleDateString() }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2" v-if="props.studentDetails.student.active_allotment?.seat">
                                                        <KTIcon icon-name="home-2" icon-class="fs-2 text-primary" />
                                                        <span class="fw-bold text-muted">Room & Seat:</span>
                                                        <span class="badge badge-light-warning fw-bold fs-6">
                                                            {{ props.studentDetails.student.active_allotment.seat.room?.room_number }}-{{ props.studentDetails.student.active_allotment.seat.seat_label }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="separator my-7"></div>

                                        <!-- Fee History -->
                                        <h3 class="fs-4 fw-bold text-dark mb-4">Fee Payment History</h3>
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                <thead>
                                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                        <th>Transaction ID</th>
                                                        <th>Date</th>
                                                        <th>Details</th>
                                                        <th>Amount</th>
                                                        <th>Months</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="fw-semibold text-gray-600">
                                                    <tr v-for="fee in props.studentDetails.fees" :key="fee.id">
                                                        <td class="font-mono">{{ fee.transaction_id }}</td>
                                                        <td>{{ new Date(fee.payment_date).toLocaleDateString() }}</td>
                                                        <td>{{ fee.fee_details }}</td>
                                                        <td class="fw-bold text-primary">{{ fee.amount }} Tk</td>
                                                        <td>{{ fee.months_count }} months</td>
                                                        <td>
                                                            <span :class="{'badge badge-light-success': fee.status === 'approved', 'badge badge-light-warning': fee.status === 'pending', 'badge badge-light-danger': fee.status === 'rejected'}">
                                                                {{ fee.status }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="props.studentDetails.fees.length === 0">
                                                        <td colspan="6" class="text-center text-gray-500 py-10">No fee records found.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End: Student Details Section -->

                        <!-- Start: Assigned Halls Section -->
                        <div id="kt_referred_assigned_halls_tab_content" class="tab-content" v-if="props.assignedHalls">
                            <div id="assigned_halls" class="py-4 tab-pane fade active show" role="tabpanel" v-show="activeTab === 2">
                                <div class="card card-flush mb-6 mb-xl-9">
                                    <div class="card-header mt-6">
                                        <div class="card-title flex-column">
                                            <h2 class="mb-1">Assigned Halls</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pb-5 pt-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                                <thead>
                                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                        <th>Hall Name</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="fw-semibold text-gray-600">
                                                    <tr v-for="hall in props.assignedHalls" :key="hall.id">
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="symbol symbol-45px me-5">
                                                                    <span class="symbol-label bg-light-primary">
                                                                        <KTIcon icon-name="home" icon-class="fs-2 text-primary" />
                                                                    </span>
                                                                </div>
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-gray-800 fw-bold mb-1">{{ hall.name }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span :class="{'badge badge-light-success': hall.is_active, 'badge badge-light-danger': !hall.is_active}">
                                                                {{ hall.is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="props.assignedHalls.length === 0">
                                                        <td colspan="2" class="text-center text-gray-500">No halls assigned.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End: Assigned Halls Section -->

                        <!-- Start: Account Settings Section -->
                        <div id="kt_account_settings_tab_content" class="tab-content">
                            <div id="account_settings" class="py-4 tab-pane fade active show" role="tabpanel" v-show="activeTab === 3">
                                <UpdateProfileInformationForm
                                    :must-verify-email="mustVerifyEmail"
                                    :status="status"
                                    class="mb-6"
                                />

                                <UpdatePasswordForm />
                            </div>
                        </div>
                        <!-- End: Account Settings Section -->

                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { ref } from 'vue';
import { getInitials } from '@/Core/helpers/Helper';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import i18n from '@/Core/plugins/i18n';

const { t } = i18n.global;

interface Hall {
    id: number;
    name: string;
    is_active: boolean;
}

interface Breadcrumb {
    url: string;
    title: string;
}

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
    breadcrumbs: Array as() => Breadcrumb[],
    pageTitle: String,
    studentDetails: Object,
    assignedHalls: Array as () => Hall[],
});

const isDetailsVisible = ref(true);
const toggleDetails = () => {
  isDetailsVisible.value = !isDetailsVisible.value;
};

// Set default active tab based on role
const activeTab = ref(props.studentDetails ? 1 : (props.assignedHalls ? 2 : 3));
</script>

<style scoped>
.font-mono {
    font-family: monospace;
}
</style>
