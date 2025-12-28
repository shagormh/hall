<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">

        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <KTIcon icon-name="magnifier" icon-class="fs-1 position-absolute ms-6" />
                        <input type="text" v-model="search" @input="searchData()" class="form-control form-control-solid w-250px ps-15" placeholder="Search Hall Attachment" />
                    </div>
                    <!--end::Search-->
                </div>

                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <!--begin::Add Permission-->
                        <Link v-if="checkPermission('can-create-hall')" :href="route('hall-attachments.create')" class="btn btn-primary">
                            <KTIcon icon-name="plus" icon-class="fs-2" />
                            Add Hall Attachment
                        </Link>
                        <!--end::Add Permission-->
                    </div>
                    <!--end::Toolbar-->
                </div>
            </div>

            <div class="card-body pt-0">
                <Datatable @on-sort="sortData" :data="tableData" :header="tableHeader" :enable-items-per-page-dropdown="true" :checkbox-enabled="false">
                    <!-- Student ROLL -->
                     <template v-slot:student_roll="{ row: hallAttachment }">
                        {{ hallAttachment.student_roll }}
                    </template>

                    <!-- Student Name -->
                    <template v-slot:student_name="{ row: hallAttachment }">
                        {{ hallAttachment.student_name }}
                    </template>

                    <!-- Hall Name -->
                    <template v-slot:hall_name="{ row: hallAttachment }">
                        {{ hallAttachment.hall_name }}
                    </template>

                    <!-- Hall Attachment Status -->
                    <!-- <template v-slot:is_active="{ row: hallAttachment }" v-if="checkPermission('can-edit-hall-attachment')">
                        <div class="form-check form-check-solid form-switch">
                            <ChangeStatusButton :obj="hallAttachment" confirmRoute="hall-attachments.changeStatus" cancelRoute="hall-attachments.index" />
                        </div>
                    </template> -->

                    <template v-slot:actions="{ row: hallAttachment }">
                        <div class="d-flex align-items-center justify-content-end">
                            <Link v-if="checkPermission('can-edit-hall-attachment')" :href="route('hall-attachments.edit', hallAttachment.id)" class="btn btn-icon btn-flex btn-active-light-primary w-30px h-30px" data-bs-toggle="tooltip" :title="$t('tooltip.title.edit')">
                                <KTIcon icon-name="pencil" icon-class="fs-3 text-primary" />
                            </Link>
                            <!-- Delete -->
                            <DeleteConfirmationButton v-if="checkPermission('can-delete-hall-attachment')" iconClass="fs-2" :obj="hallAttachment" confirmRoute="hall-attachments.destroy" title="Delete hall" :messageTitle="`${hallAttachment.student_name} hall attachment?`"/>
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
import { Link } from '@inertiajs/vue3';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import { checkPermission } from "@/Core/helpers/Permission";
import i18n from '@/Core/plugins/i18n';
import ChangeStatusButton from '@/Components/Button/ChangeStatusButton.vue';
import DeleteConfirmationButton from '@/Components/Button/DeleteConfirmationButton.vue';

const { t } = i18n.global;

const props = defineProps({
    hallAttachments: Object as() => IHallAttachment[] | undefined,
    breadcrumbs: Array as() => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

interface IHallAttachment {
    id: number;
    student_roll: number;
    student_name: string;
    hall_name: string;
    is_approved: boolean;
    is_active: boolean;
    student?: IStudent;
    hall?: IHall;
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
        columnName: 'Student Roll',
        columnLabel: "student_roll",
        sortEnabled: true,
        columnWidth: 150
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
    // {
    //     columnName: 'Status',
    //     columnLabel: "is_active",
    //     sortEnabled: true,
    //     columnWidth: 100
    // },
    {
        columnName: 'Action',
        columnLabel: "actions",
        sortEnabled: true,
        columnWidth: 100
    },
]);

const tableData = ref < IHallAttachment[] > ([]);
const initHallAttachments = ref < IHallAttachment[] > ([]);

onMounted(() => {
    if (props.hallAttachments) {
        initHallAttachments.value = props.hallAttachments.map((hallAttachment: any) => ({
            id: hallAttachment.id,
            student_roll: hallAttachment.student?.roll,
            student_name: hallAttachment.student?.name,
            hall_name: hallAttachment.hall?.name,
            is_approved: hallAttachment.is_approved,
            is_active: hallAttachment.is_active
        }));
        tableData.value = initHallAttachments.value;
    }
});

const search = ref < string > ("");
const searchData = () => {
    tableData.value = [...initHallAttachments.value];
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
</script>
