<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">

        <div class="card mb-5 mb-xl-10">
            <!--begin::Card header-->
            <div class="card-header border-0 cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">{{ props.student?.id ? 'Edit Student' : 'Add Student' }}</h3>
                </div>
            </div>
            <!--end::Card header-->

            <div class="show">
                <VForm @submit="submit()" class="form">
                    <!--begin::Card body-->
                    <div class="card-body border-top p-9">
                        <!-- Name -->
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
                    <!--end::Card body-->

                    <!-- Submit Button-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <SubmitButton :id="props.student?.id" />
                    </div>
                </VForm>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Field, Form as VForm } from "vee-validate";
import ErrorMessage from "@/Components/Message/ErrorMessage.vue";
import SubmitButton from "@/Components/Button/SubmitButton.vue";
import Multiselect from '@vueform/multiselect';
import { ref } from 'vue';

const props = defineProps({
    student: Object,
    departments: Object,
    breadcrumbs: Array as() => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

const formData = useForm({
    id: props.student?.id || '',
    roll: props.student?.roll || '',
    registration: props.student?.registration || '',
    name: props.student?.name || '',
    department_id: props.student?.department_id || '',
    email: props.student?.email || '',
    father_name: props.student?.father_name || '',
    mother_name: props.student?.mother_name || '',
    address: props.student?.address || '',
    mobile_number: props.student?.mobile_number || '',
});

const allDepartments = ref<Array<any>>([]);
if (Array.isArray(props.departments) && props.departments.length > 0) {
    allDepartments.value = props.departments.map(dept => ({value: dept.id, name:dept.name}));
}

const submit = () => {
    if (props.student?.id) {
        // for updating permission
        formData.put(route('students.update', props.student?.id), {
            preserveScroll: true,
            onSuccess: () => {
                console.log("Student updated successfully!");
            },
            onError: (errors) => {
                console.error("Validation errors:", errors);
            },
        });
    } else {
        // for adding new permission
        formData.post(route('students.store'), {
            preserveScroll: true,
            onSuccess: () => {
                console.log("Student created successfully!");
            },
            onError: (errors) => {
                console.error("Validation errors:", errors);
            },
        });
    }
};
</script>
