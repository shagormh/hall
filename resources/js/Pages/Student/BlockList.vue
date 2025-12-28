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
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <!--begin::Back Button-->
                        <Link :href="route('students.index')" class="btn btn-secondary">
                            <KTIcon icon-name="arrow-left" icon-class="fs-2" />
                            Back to Students
                        </Link>
                        <!--end::Back Button-->
                    </div>
                    <!--end::Toolbar-->
                </div>
            </div>

            <div class="card-body pt-0">
                <Datatable @on-sort="sortData" :data="tableData" :header="tableHeader" :enable-items-per-page-dropdown="true" :checkbox-enabled="false">
                    <!-- Roll -->
                    <template v-slot:roll="{ row: student }">
                        {{ student.roll }}
                    </template>

                    <!-- Name -->
                    <template v-slot:name="{ row: student }">
                        {{ student.name }}
                    </template>

                    <!-- Department -->
                    <template v-slot:department="{ row: student }">
                        {{ student.department }}
                    </template>

                    <!-- Reason -->
                    <template v-slot:reason="{ row: student }">
                        <span :title="student.reason">{{ student.reason || 'N/A' }}</span>
                    </template>

                    <!-- <template v-slot:blocked_by="{ row: student }">
                        {{ student.blocked_by }}


                    </template> -->


                    <!-- Blocked At -->
                    <template v-slot:blocked_at="{ row: student }">
                        {{ formatDate(student.blocked_at) }}
                    </template>

                    <!-- Unblocked At -->
                    <template v-slot:unblocked_at="{ row: student }">
                        {{ student.unblocked_at ? formatDate(student.unblocked_at) : ' ' }}
                    </template>

                    <!-- Actions -->
                    <template v-slot:actions="{ row: student }">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <!-- Unblock Button -->
                            <button
                                :class="{
                                    'btn': true,
                                    'btn-success': student.unblocked_at === null,
                                    'btn-secondary': student.unblocked_at !== null,
                                    'btn-sm': true
                                }"
                                @click="student.unblocked_at === null ? unblockStudent(student) : null"
                                :disabled="student.unblocked_at !== null"
                                data-bs-toggle="tooltip"
                                :title="student.unblocked_at === null ? 'Unblock Student' : 'Already Unblocked'"
                            >
                                <KTIcon
                                    icon-name="check"
                                    icon-class="fs-3"
                                    :class="student.unblocked_at !== null ? 'text-muted' : ''"
                                />
                                {{ student.unblocked_at === null ? 'Unblock' : 'Unblocked' }}
                            </button>

                            <button
                                class="btn btn-danger btn-sm"
                                @click="deleteBlockEntry(student)"
                                data-bs-toggle="tooltip"
                                title="Delete Block Entry"
                            >
                                <KTIcon icon-name="trash" icon-class="fs-3" />
                                Delete
                            </button>
                        </div>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, defineProps } from 'vue';
import Datatable from "@/Components/kt-datatable/KTDataTable.vue";
import type { Sort } from "@/Components/kt-datatable/table-partials/Models";
import { MenuComponent } from "@/Assets/ts/components";
import arraySort from "array-sort";
import { Link, router } from '@inertiajs/vue3';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import i18n from '@/Core/plugins/i18n';
// import DeleteConfirmationButton from '@/Components/kt-confirmation-button/DeleteConfirmationButton.vue';

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
    block_id: number;
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
    reason?: string;
    blocked_at: string;
    unblocked_at?: string;

}

const tableHeader = ref([
    {
        columnName: 'Roll',
        columnLabel: "roll",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Name',
        columnLabel: "name",
        sortEnabled: true,
        columnWidth: 150
    },
    {
        columnName: 'Department',
        columnLabel: "department",
        sortEnabled: true,
        columnWidth: 120
    },
    {
        columnName: 'Reason',
        columnLabel: "reason",
        sortEnabled: true,
        columnWidth: 250
    },
    // {
    //     columnName: 'Blocked By',
    //     columnLabel: "blocked_by",
    //     sortEnabled: true,
    //     columnWidth: 150
    // },
    {
        columnName: 'Blocked At',
        columnLabel: "blocked_at",
        sortEnabled: true,
        columnWidth: 150
    },
    {
        columnName: 'Unblocked At',
        columnLabel: "unblocked_at",
        sortEnabled: true,
        columnWidth: 150
    },
    {
        columnName: t('buttonValue.actions'),
        columnLabel: "actions",
        sortEnabled: false, // Actions column এ সাধারণত sort enabled থাকে না
        columnWidth: 200
    },
]);

const tableData = ref < IStudent[] > ([]);
const initStudents = ref < IStudent[] > ([]);

onMounted(() => {
    if (props.students) {
        initStudents.value = props.students.map((student: any) => ({
            block_id: student.block_id,
            id: student.id,
            roll: student.roll,
            registration: student.registration,
            name: student.name,
            department: props.departments?.find((dept: any) => dept.id === student.department)?.name || student.department,
            email: student.email,
            father_name: student.father_name,
            mother_name: student.mother_name,
            hall_status: student.hall_status,
            address: student.address,
            mobile_number: student.mobile_number,
            is_active: student.is_active,
            blocked_at: student.blocked_at,
            unblocked_at: student.unblocked_at,
            reason: student.reason,
            blocked_by: student.blocked_by
        }));
        tableData.value = initStudents.value;
        console.log('Blocked students loaded:', initStudents.value);
    }
    MenuComponent.reinitialization();
});

const search = ref < string > ("");
const searchData = () => {
    tableData.value = [...initStudents.value];
    if (search.value !== "") {
        tableData.value = tableData.value.filter(item => searchingFunc(item, search.value));
    }
};

const searchingFunc = (obj: any, value: string): boolean => {
    for (let key in obj) {
        if (!Number.isInteger(obj[key]) && !(typeof obj[key] === "object") && obj[key] !== null && obj[key] !== undefined) {
            if (obj[key].toString().toLowerCase().includes(value.toLowerCase())) {
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

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        // hour: '2-digit',
        // minute: '2-digit'
    });
};

const unblockStudent = (student: IStudent) => {
    if (confirm(`Are you sure you want to unblock ${student.name}?`)) {
        router.patch(route('students.blockList.unblock', student.block_id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Update local data instead of removing
                const index = tableData.value.findIndex(s => s.block_id === student.block_id);
                if (index !== -1) {
                    tableData.value[index].unblocked_at = new Date().toISOString();
                    tableData.value[index].is_active = true;
                }

                // Also update initStudents for search functionality
                const initIndex = initStudents.value.findIndex(s => s.block_id === student.block_id);
                if (initIndex !== -1) {
                    initStudents.value[initIndex].unblocked_at = new Date().toISOString();
                    initStudents.value[initIndex].is_active = true;
                }

                console.log('Student unblocked successfully');
            },
            onError: (error) => {
                console.error('Error unblocking:', error);
                alert('Failed to unblock student.');
            }
        });
    }
};

const deleteBlockEntry = (student: IStudent) => {
    if (confirm(`Are you sure you want to delete block entry for ${student.name}?`)) {
        router.delete(route('students.blockList.destroy', student.block_id), {
            preserveScroll: true,
            onSuccess: () => {
                // Remove from table data
                tableData.value = tableData.value.filter(s => s.block_id !== student.block_id);
                initStudents.value = initStudents.value.filter(s => s.block_id !== student.block_id);
                console.log('Block entry deleted successfully');
            },
            onError: (errors) => {
                console.error('Error deleting block entry:', errors);
                alert('Failed to delete block entry.');
            }
        });
    }
};

</script>
