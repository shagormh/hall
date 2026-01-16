<template>
    <div v-if="show" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header pb-0 border-0 justify-content-end">
                    <div class="btn btn-sm btn-icon btn-active-color-primary" @click="$emit('close')">
                        <KTIcon icon-name="cross" icon-class="fs-1" />
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form @submit.prevent="submit" class="form">
                        <div class="mb-13 text-center">
                            <h1 class="mb-3">{{ feeConfiguration ? 'Edit' : 'Create' }} Fee Configuration</h1>
                            <div class="text-muted fw-semibold fs-5">
                                Configure fees for halls or global defaults.
                            </div>
                        </div>

                        <!-- Hall -->
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                <span class="required">Hall</span>
                            </label>
                            <select v-model="form.hall_id" class="form-select form-select-solid">
                                <option :value="null">All Halls (Default)</option>
                                <option v-for="hall in halls" :key="hall.id" :value="hall.id">{{ hall.name }}</option>
                            </select>
                            <div v-if="form.errors.hall_id" class="text-danger fs-7 mt-2">{{ form.errors.hall_id }}</div>
                        </div>

                        <!-- Fee Type -->
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Fee Type</label>
                            <select v-model="form.fee_type" class="form-select form-select-solid" :disabled="!!feeConfiguration">
                                <option v-for="type in feeTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                            </select>
                            <div v-if="form.errors.fee_type" class="text-danger fs-7 mt-2">{{ form.errors.fee_type }}</div>
                        </div>

                        <!-- Amount -->
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Amount</label>
                            <input v-model="form.amount" type="number" step="0.01" class="form-control form-control-solid" placeholder="Enter amount" />
                            <div v-if="form.errors.amount" class="text-danger fs-7 mt-2">{{ form.errors.amount }}</div>
                        </div>

                        <!-- Period -->
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="required fs-6 fw-semibold mb-2">Frequency</label>
                            <select v-model="form.period" class="form-select form-select-solid">
                                <option value="monthly">Monthly</option>
                                <option value="semester">Per Semester</option>
                                <option value="yearly">Yearly</option>
                                <option value="one_time">One Time</option>
                            </select>
                            <div v-if="form.errors.period" class="text-danger fs-7 mt-2">{{ form.errors.period }}</div>
                        </div>

                        <!-- Description -->
                        <div class="d-flex flex-column mb-8">
                            <label class="fs-6 fw-semibold mb-2">Description</label>
                            <textarea v-model="form.description" class="form-control form-control-solid" rows="3" placeholder="Additional details..."></textarea>
                            <div v-if="form.errors.description" class="text-danger fs-7 mt-2">{{ form.errors.description }}</div>
                        </div>

                        <!-- Is Active -->
                        <div class="d-flex flex-stack mb-8">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold">Active Status</label>
                                <div class="fs-7 fw-semibold text-muted">Set weather this fee is currently active</div>
                            </div>
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input v-model="form.is_active" class="form-check-input" type="checkbox" />
                                <span class="form-check-label fw-semibold text-muted">{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                            </label>
                        </div>

                        <div class="text-center">
                            <button type="reset" class="btn btn-light me-3" @click="$emit('close')">Cancel</button>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <span v-if="!form.processing" class="indicator-label">Submit</span>
                                <span v-else class="indicator-progress">
                                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div v-if="show" class="modal-backdrop fade show"></div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, ref } from 'vue';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";

const props = defineProps({
    show: Boolean,
    feeConfiguration: Object,
    halls: Array,
    feeTypes: Array,
});

const emit = defineEmits(['close', 'submitted']);

const form = useForm({
    hall_id: null,
    fee_type: 'hall_rent',
    amount: '',
    period: 'monthly',
    description: '',
    is_active: true,
});

watch(() => props.feeConfiguration, (newVal) => {
    if (newVal) {
        form.hall_id = newVal.hall_id;
        form.fee_type = newVal.fee_type;
        form.amount = newVal.amount;
        form.period = newVal.period;
        form.description = newVal.description;
        form.is_active = !!newVal.is_active;
    } else {
        form.reset();
        // Set defaults
        form.hall_id = null;
        form.fee_type = 'hall_rent';
        form.period = 'monthly';
        form.is_active = true;
    }
}, { immediate: true });

const submit = () => {
    if (props.feeConfiguration) {
        form.put(route('fee-configurations.update', props.feeConfiguration.id), {
            onSuccess: () => {
                form.reset();
                emit('close');
                emit('submitted');
            }
        });
    } else {
        form.post(route('fee-configurations.store'), {
            onSuccess: () => {
                form.reset();
                emit('close');
                emit('submitted');
            }
        });
    }
};
</script>
