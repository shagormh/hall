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
                            placeholder="Search Fee Config..."
                        />
                    </div>
                    <!--end::Search-->
                </div>
                <!--begin::Card title-->

                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end gap-3 flex-wrap">
                        <!--begin::Hall Filter-->
                        <div class="w-150px">
                            <select
                                v-model="filters.hall_id"
                                class="form-select form-select-solid"
                                @change="updateFilters"
                            >
                                <option :value="null">All Halls</option>
                                <option v-for="hall in halls" :key="hall.id" :value="hall.id">{{ hall.name }}</option>
                            </select>
                        </div>

                        <!--begin::Type Filter-->
                        <div class="w-150px">
                            <select
                                v-model="filters.fee_type"
                                class="form-select form-select-solid"
                                @change="updateFilters"
                            >
                                <option :value="null">All Types</option>
                                <option v-for="type in feeTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                            </select>
                        </div>

                        <!--begin::Add Button-->
                        <button @click="openCreateModal" class="btn btn-primary">
                            <KTIcon icon-name="plus" icon-class="fs-2" />
                            Add Config
                        </button>
                    </div>
                    <!--end::Toolbar-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px">Fee Type</th>
                                <th class="min-w-125px">Hall</th>
                                <th class="min-w-100px">Amount</th>
                                <th class="min-w-100px">Frequency</th>
                                <th class="min-w-100px">Status</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <tr v-for="config in feeConfigurations.data" :key="config.id">
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold mb-1">{{ formatFeeType(config.fee_type) }}</span>
                                        <span class="text-muted fs-7">{{ config.description }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span v-if="config.hall" class="badge badge-light-info fw-bold fs-7">{{ config.hall.name }}</span>
                                    <span v-else class="badge badge-light-primary fw-bold fs-7">Global / Default</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-bold">৳{{ config.amount }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light fw-bold">{{ formatPeriod(config.period) }}</span>
                                </td>
                                <td>
                                    <div :class="['badge fw-bold', config.is_active ? 'badge-light-success' : 'badge-light-danger']">
                                        {{ config.is_active ? 'Active' : 'Inactive' }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button @click="openEditModal(config)" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3">
                                        <KTIcon icon-name="pencil" icon-class="fs-3" />
                                    </button>
                                    <button @click="confirmDelete(config)" class="btn btn-icon btn-active-light-danger w-30px h-30px">
                                        <KTIcon icon-name="trash" icon-class="fs-3" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="feeConfigurations.data.length === 0">
                                <td colspan="6" class="text-center py-10 text-muted">No fee configurations found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                 <div v-if="feeConfigurations.links && feeConfigurations.links.length > 3" class="d-flex justify-content-end mt-4">
                    <ul class="pagination">
                        <li v-for="(link, index) in feeConfigurations.links" :key="index" :class="['page-item', { active: link.active, disabled: !link.url }]">
                            <Link class="page-link" :href="link.url || '#'" v-html="link.label" />
                        </li>
                    </ul>
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->

        <FeeConfigurationModal
            :show="showModal"
            :fee-configuration="selectedConfig"
            :halls="halls"
            :fee-types="feeTypes"
            @close="showModal = false"
            @submitted="refresh"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import FeeConfigurationModal from './Partials/FeeConfigurationModal.vue';

const props = defineProps({
    feeConfigurations: Object,
    halls: Array,
    feeTypes: Array,
    filters: Object,
    breadcrumbs: Array,
    pageTitle: String,
});

const search = ref(props.filters.search || '');
const filters = reactive({
    hall_id: props.filters.hall_id || null,
    fee_type: props.filters.fee_type || null,
});

const showModal = ref(false);
const selectedConfig = ref(null);

const formatFeeType = (type) => {
    const found = props.feeTypes.find(t => t.value === type);
    return found ? found.label : type;
};

const formatPeriod = (period) => {
    const map = {
        monthly: 'Monthly',
        semester: 'Per Semester',
        yearly: 'Yearly',
        one_time: 'One Time'
    };
    return map[period] || period;
};

// Debounce search
let timeout = null;
watch(search, (newSearch) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        updateFilters();
    }, 300);
});

const updateFilters = () => {
    router.get(route('fee-configurations.index'), {
        search: search.value,
        hall_id: filters.hall_id,
        fee_type: filters.fee_type
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const openCreateModal = () => {
    selectedConfig.value = null;
    showModal.value = true;
};

const openEditModal = (config) => {
    selectedConfig.value = config;
    showModal.value = true;
};

const confirmDelete = (config) => {
    if (confirm('Are you sure you want to delete this configuration?')) {
        router.delete(route('fee-configurations.destroy', config.id));
    }
};

const refresh = () => {
    // Optional: Actions after modal submit
};
</script>
