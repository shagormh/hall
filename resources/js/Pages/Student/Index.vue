<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">

        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <KTIcon icon-name="magnifier" icon-class="fs-1 position-absolute ms-6" />
                        <input type="text" v-model="search" @input="searchData()" class="form-control form-control-solid w-250px ps-15" placeholder="Search Student" />
                    </div>
                    <!--end::Search-->
                </div>

                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end gap-2" data-kt-customer-table-toolbar="base">
                        <!--begin::Block List Button-->
                        <Link :href="route('students.blockList')" class="btn btn-danger">
                            <KTIcon icon-name="ban" icon-class="fs-2" />
                            View Blocked Students
                        </Link>
                        <!--end::Block List Button-->
                        <!--begin::Add Permission-->
                        <Link v-if="checkPermission('can-create-student')" :href="route('students.create')" class="btn btn-primary">
                            <KTIcon icon-name="plus" icon-class="fs-2" />
                            Add Student
                        </Link>
                        <!--end::Add Permission-->
                    </div>
                    <!--end::Toolbar-->
                </div>
            </div>

            <div class="card-body pt-0">
                <Datatable @on-sort="sortData" :data="tableData" :header="tableHeader" :enable-items-per-page-dropdown="true" :checkbox-enabled="false">
                    <!-- Permission Name -->
                     <template v-slot:roll="{ row: student }">
                        {{ student.roll }}
                    </template>

                     <template v-slot:registration="{ row: student }">
                        {{ student.registration }}
                    </template>

                    <template v-slot:name="{ row: student }">
                        {{ student.name }}
                    </template>

                    <template v-slot:department="{ row: student }">
                        {{ student.department }}
                    </template>

                    <template v-slot:mobile_number="{ row: student }">
                        {{ student.mobile_number }}
                    </template>

                    <!-- Group -->
                    <template v-slot:father_name="{ row: student }">
                        {{ student.father_name }}
                    </template>

                    <!-- Display Name -->
                    <template v-slot:mother_name="{ row: student }">
                        {{ student.mother_name }}
                    </template>

                    <!-- Attach/Allot -->
                    <template v-slot:hall_status="{ row: student }">
                        <span :class="{
                                'badge': true,
                                'badge-warning': student.hall_status === 'attachment',
                                'badge-success': student.hall_status === 'alloted',
                                'badge-danger': student.hall_status === 'cancel'
                            }" style="text-transform: capitalize">
                        {{ student.hall_status || 'N/A' }}
                        </span>
                    </template>


                    <template v-slot:is_active="{ row: student }">
                        <div class="form-check form-check-solid form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                :checked="student.is_active"
                                @change="toggleStudentStatus(student)"
                            >
                        </div>
                    </template>


                    <template v-slot:email="{ row: student }">
                        {{ student.email }}
                    </template>

                    <template v-slot:address="{ row: student }">
                        {{ student.address }}
                    </template>

                    <template v-slot:actions="{ row: student }">
                        <div class="d-flex align-items-center justify-content-end">
                            <Link v-if="checkPermission('can-edit-student')" :href="route('students.edit', student.id)" class="btn btn-icon btn-flex btn-active-light-primary w-30px h-30px" data-bs-toggle="tooltip" :title="$t('tooltip.title.edit')">
                                <KTIcon icon-name="pencil" icon-class="fs-3 text-primary" />
                            </Link>
                            <!-- Delete -->
                            <DeleteConfirmationButton v-if="checkPermission('can-delete-student')" iconClass="fs-2" :obj="student" confirmRoute="students.destroy" title="Delete Student" :messageTitle="`${student.name} ?`"/>
                        </div>
                    </template>
                </Datatable>
            </div>
        </div>

        <!-- Block Reason Modal -->
        <div v-if="showBlockModal" class="modal fade show" id="blockReasonModal" tabindex="-1" role="dialog" aria-labelledby="blockReasonModalLabel" aria-hidden="false" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="blockReasonModalLabel">Block Student: {{ selectedStudent?.name }}</h5>
                        <button type="button" class="btn-close" @click="showBlockModal = false" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="blockReason" class="form-label">Reason for blocking (optional)</label>
                            <textarea v-model="blockReason" id="blockReason" class="form-control" rows="4" placeholder="Enter reason for blocking this student..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showBlockModal = false">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="confirmBlockStudent">Block Student</button>
                    </div>
                </div>
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
import { checkPermission } from "@/Core/helpers/Permission";
import i18n from '@/Core/plugins/i18n';
import ChangeStatusButton from '@/Components/Button/ChangeStatusButton.vue';
import DeleteConfirmationButton from '@/Components/Button/DeleteConfirmationButton.vue';

const { t } = i18n.global;

const props = defineProps({
    students: Object as() => IStudent[] | undefined,
    departments: Object,
    breadcrumbs: Array as() => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

interface IStudent {
    id: number;
    roll: string;
    registration: string;
    name: string;
    department: string | number;
    father_name: string;
    mother_name: string;
    hall_status: string;
    email: string;
    address: string;
    mobile_number: string;
    is_active: boolean;
}

const tableHeader = ref([
    {
        columnName: 'Roll',
        columnLabel: "roll",
        sortEnabled: true,
        columnWidth: 80
    },
    {
        columnName: 'Registration',
        columnLabel: "registration",
        sortEnabled: true,
        columnWidth: 80
    },
    {
        columnName: 'Name',
        columnLabel: "name",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Department',
        columnLabel: "department",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Father Name',
        columnLabel: "father_name",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Mother Name',
        columnLabel: "mother_name",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Attach/Allot',
        columnLabel: "hall_status",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Is Active',
        columnLabel: "is_active",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Email',
        columnLabel: "email",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Mobile Number',
        columnLabel: "mobile_number",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: t('buttonValue.actions'),
        columnLabel: "actions",
        sortEnabled: true,
        columnWidth: 100
    },
]);

const tableData = ref < IStudent[] > ([]);
const initStudents = ref < IStudent[] > ([]);
const selectedStudent = ref < any > (null);
const blockReason = ref < string > ('');
const showBlockModal = ref < boolean > (false);

onMounted(() => {
    if (props.students) {
        initStudents.value = props.students.map((student: any) => ({
            id: student.id,
            roll: student.roll,
            registration: student.registration,
            name: student.name,
            department: props.departments?.find((dept: any) => dept.id === student.department_id)?.name,
            email: student.email,
            father_name: student.father_name,
            mother_name: student.mother_name,
            hall_status: student.hall_status,
            address: student.address,
            mobile_number: student.mobile_number,
            is_active: student.is_active,
        }));
        tableData.value = initStudents.value;
    }
});const search = ref < string > ("");
const searchData = () => {
    tableData.value = [...initStudents.value];
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

const toggleStudentStatus = (student: IStudent) => {
    if (student.is_active) {
        // If turning off, open modal to get reason
        selectedStudent.value = student;
        blockReason.value = '';
        showBlockModal.value = true;
    } else {
        // If turning on, use the new status endpoint
        router.patch(route('students.status', student.id), {
            is_active: true
        });
        router.get(route('students.blockList'), {
            is_active: true
        }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                // Success message automatically handled by Inertia flash
                const index = tableData.value.findIndex(s => s.id === student.id);
                if (index !== -1) {
                    tableData.value[index].is_active = true;
                }
                console.log('Student activated successfully');
            },
            onError: (errors) => {
                console.error('Error activating student:', errors);
                alert('Failed to activate student.');
            }
        });

    }
};


const confirmBlockStudent = () => {
    if (!selectedStudent.value) return;
    

    router.patch(route('students.status', selectedStudent.value.id), {
        is_active: false,
        block_reason: blockReason.value
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            // Success message automatically handled by Inertia flash
            showBlockModal.value = false;

            // Update table data locally
            const index = tableData.value.findIndex(s => s.id === selectedStudent.value.id);
            if (index !== -1) {
                tableData.value[index].is_active = false;

                 if (tableData.value[index].hall_status === 'alloted') {
                    tableData.value[index].hall_status = 'attachment';
                }
            }

            // Clear selection
            selectedStudent.value = null;
            blockReason.value = '';
        },
        onError: (errors) => {
            console.error('Error blocking student:', errors);
            alert('Failed to block student. Please try again.');
        }
    });

};
</script>
