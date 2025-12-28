<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">

        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <KTIcon icon-name="magnifier" icon-class="fs-1 position-absolute ms-6" />
                        <input type="text" v-model="search" @input="searchData()" class="form-control form-control-solid w-250px ps-15" placeholder="Search Room" />
                    </div>
                    <!--end::Search-->
                </div>

                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <!--begin::Add Permission-->
                        <Link v-if="checkPermission('can-create-room')" :href="route('rooms.create')" class="btn btn-primary">
                            <KTIcon icon-name="plus" icon-class="fs-2" />
                            Add Room
                        </Link>
                        <!--end::Add Permission-->
                    </div>
                    <!--end::Toolbar-->
                </div>
            </div>

            <div class="card-body pt-0">
                <Datatable @on-sort="sortData" :data="tableData" :header="tableHeader" :enable-items-per-page-dropdown="true" :checkbox-enabled="false">
                    <!-- Room Number -->
                    <template v-slot:room_number="{ row: room }">
                        {{ room.room_number }}
                    </template>

                    <template v-slot:seat_label="{ row: room }">
                        {{ room.seat_label }}
                    </template>

                    <template v-slot:seat_code="{ row: room }">
                        {{ room.seat_code }}
                    </template>

                    <template v-slot:hall_name="{ row: room }">
                        {{ room.hall_name }}
                    </template>

                    <template v-slot:room_type="{ row: room }">
                        {{ room.room_type }}
                    </template>

                    <template v-slot:status="{ row: room }">
                        <span :class="['badge',room.status === 'empty' ? 'badge-success' : 'badge-danger']">
                            {{ room.status }}
                        </span>
                    </template>

                    <template v-slot:actions="{ row: room }">
                        <div class="d-flex align-items-center justify-content-end">
                            <Link v-if="checkPermission('can-edit-room')" :href="route('rooms.edit', room.id)" class="btn btn-icon btn-flex btn-active-light-primary w-30px h-30px" data-bs-toggle="tooltip" :title="$t('tooltip.title.edit')">
                                <KTIcon icon-name="pencil" icon-class="fs-3 text-primary" />
                            </Link>
                            <!-- Delete -->
                            <DeleteConfirmationButton v-if="checkPermission('can-delete-room')" iconClass="fs-2" :obj="room" confirmRoute="rooms.destroy" title="Delete room" :messageTitle="`${room.name} ?`"/>
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
    rooms: Object as() => IRoom[] | undefined,
    breadcrumbs: Array as() => Breadcrumb[],
    pageTitle: String,
});
console.log(props.rooms);
interface Breadcrumb {
    url: string;
    title: string;
}

interface IRoom {
    id: number;
    room_number: string;
    seat_label: string;
    seat_code: string;
    status: string;
}

const tableHeader = ref([
    {
        columnName: 'Room Number',
        columnLabel: "room_number",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Seat Label',
        columnLabel: "seat_label",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Seat Code',
        columnLabel: "seat_code",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Hall Name',
        columnLabel: "hall_name",
        sortEnabled: true,
        columnWidth: 100
    },
    {
        columnName: 'Room Type',
        columnLabel: "room_type",
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
        sortEnabled: true,
        columnWidth: 100
    },
]);

const tableData = ref < IRoom[] > ([]);
const initRooms = ref < IRoom[] > ([]);

onMounted(() => {
    if (props.rooms) {
        initRooms.value = props.rooms.flatMap((room: any) =>
            room.seats.map((seat: any) => ({
                id: room.id,
                room_number: room.room_number,
                seat_id: seat.id,
                seat_label: seat.seat_label,
                seat_code: seat.seat_code,
                hall_name: room.hall?.name,
                room_type: room.room_type?.name,
                status: seat.status,
            }))
        );
        tableData.value = initRooms.value;
    }
});

const search = ref < string > ("");
const searchData = () => {
    tableData.value = [...initRooms.value];
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
