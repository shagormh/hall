<template>
    <div class="modal fade" id="kt_modal_add_student" ref="addStudentModalRef" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header" id="kt_modal_add_student_header">
                    <!--begin::Modal title-->
                    <h2 class="fw-bold">Add Student</h2>
                    <!--end::Modal title-->

                    <!--begin::Close-->
                    <div id="kt_modal_add_student_close" data-bs-dismiss="modal" class="btn btn-icon btn-sm btn-active-icon-primary">
                        <KTIcon icon-name="cross" icon-class="fs-1" />
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->

                <!--begin::Form-->
                <VForm @submit="submit()" :model="formData" ref="formRef">
                    <!--begin::Modal body-->
                    <div class="modal-body py-10 px-lg-17">
                        <!--begin::Scroll-->
                        <div class="scroll-y me-n9 pe-5" id="kt_modal_add_student" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_student_header" data-kt-scroll-wrappers="#kt_modal_update_item_unit_scroll" data-kt-scroll-offset="300px">
                            <div class="row mb-2 g-4">
                                <div class="col-md-6 fv-row">
                                    <div class="d-flex flex-column mb-5 fv-row">
                                        <label class="required fs-5 fw-semibold mb-2">Roll</label>
                                        <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Student Roll" name="roll" v-model="formData.roll"/>
                                        <ErrorMessage :errorMessage="formData.errors.roll" />
                                    </div>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <div class="d-flex flex-column mb-5 fv-row">
                                        <label class="required fs-5 fw-semibold mb-2">Registration</label>
                                        <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Student Registration" name="registration" v-model="formData.registration"/>
                                        <ErrorMessage :errorMessage="formData.errors.registration" />
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-2 g-4">
                                <div class="col-md-6 fv-row">
                                    <div class="d-flex flex-column mb-5 fv-row">
                                        <label class="required fs-5 fw-semibold mb-2">Name</label>
                                        <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Student Name" name="name" v-model="formData.name"/>
                                        <ErrorMessage :errorMessage="formData.errors.name" />
                                    </div>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <div lass="mb-5 fv-row">
                                        <label class="required fs-5 fw-semibold mb-2">Department</label>
                                        <Multiselect
                                            placeholder="Select Department"
                                            v-model="formData.department_id"
                                            :searchable="true"
                                            :options="allDepartments"
                                            label="name"
                                            trackBy="value"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-2 g-4">
                                <div class="col-md-6 fv-row">
                                    <div class="d-flex flex-column mb-5 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">Father Name</label>
                                        <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Father Name" name="father_name" v-model="formData.father_name"/>
                                        <ErrorMessage :errorMessage="formData.errors.father_name" />
                                    </div>
                                </div>

                                <!-- Group Name -->
                                <div class="col-md-6 fv-row">
                                    <div class="d-flex flex-column mb-5 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">Mother Name</label>
                                        <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Mother Name" name="mother_name" v-model="formData.mother_name"/>
                                        <ErrorMessage :errorMessage="formData.errors.mother_name" />
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-2 g-4">
                                <div class="col-md-6 fv-row">
                                    <div class="d-flex flex-column mb-5 fv-row">
                                        <label class="fs-5 fw-semibold mb-2">Email</label>
                                        <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Student Email" name="email" v-model="formData.email"/>
                                        <ErrorMessage :errorMessage="formData.errors.email" />
                                    </div>
                                </div>

                                <div class="col-md-6 fv-row">
                                    <div class="d-flex flex-column mb-5 fv-row">
                                        <label class="required fs-5 fw-semibold mb-2">Mobile Number</label>
                                        <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Mobile Number" name="mobile_number" v-model="formData.mobile_number"/>
                                        <ErrorMessage :errorMessage="formData.errors.mobile_number" />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="fs-5 fw-semibold mb-2">Address</label>
                                    <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Address" name="address" v-model="formData.address"/>
                                    <ErrorMessage :errorMessage="formData.errors.address" />
                                </div>
                            </div>
                        </div>
                        <!--end::Scroll-->
                    </div>
                    <!--end::Modal body-->

                    <!--begin::Modal footer-->
                    <div class="modal-footer flex-center">
                        <!--Discard Button-->
                        <div data-bs-dismiss="modal" class="btn btn-light me-3">
                            {{ $t('buttonValue.discard') }}
                        </div>

                        <!-- Submit Button -->
                        <SubmitButton />
                    </div>
                    <!--end::Modal footer-->
                </VForm>
                <!--end::Form-->
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import ErrorMessage from "@/Components/Message/ErrorMessage.vue";
import SubmitButton from "@/Components/Button/SubmitButton.vue";
import { ref, onMounted, defineProps } from "vue";
import { useForm } from '@inertiajs/vue3';
import { hideModal } from "@/Core/helpers/Modal";
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import { Field, Form as VForm } from "vee-validate";
import Multiselect from '@vueform/multiselect';
import i18n from '@/Core/plugins/i18n';

const { t } = i18n.global;

const formRef = ref < null | HTMLFormElement > (null);
const addStudentModalRef = ref < null | HTMLElement > (null);

const props = defineProps({
  routeType: String,
  allDepartments: Array
});

const formData = useForm({
    id: '',
    roll: '',
    registration: '',
    name: '',
    department_id: '',
    email: '',
    father_name: '',
    mother_name: '',
    address: '',
    mobile_number: '',
});

const submit = () => {
    const url = route('hallAttachments.student.store');
    formData.post(url, {
        preserveScroll: true,
        onSuccess: (response: any) => {
            hideModal(addStudentModalRef.value);
            window.location.reload();
        },
    });
};
</script>
