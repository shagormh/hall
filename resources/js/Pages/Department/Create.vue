<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">

        <div class="card mb-5 mb-xl-10">
            <!--begin::Card header-->
            <div class="card-header border-0 cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">{{ props.department?.id ? 'Edit Department' : 'Add Department' }}</h3>
                </div>
            </div>
            <!--end::Card header-->

            <div class="show">
                <VForm @submit="submit()" class="form">
                    <!--begin::Card body-->
                    <div class="card-body border-top p-9">
                        <!-- Name -->
                        <div class="row mb-2 g-4">
                            <div class="col-md-12 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="required fs-5 fw-semibold mb-2">Name</label>
                                    <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Department Name" name="name" v-model="formData.name"/>
                                    <ErrorMessage :errorMessage="formData.errors.name" />
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5 g-4">
                            <div class="col-md-12 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="required fs-5 fw-semibold mb-2">Code</label>
                                    <Field type="text" class="form-control form-control-lg form-control-solid" placeholder="Department Code" name="code" v-model="formData.code"/>
                                    <ErrorMessage :errorMessage="formData.errors.code" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->

                    <!-- Submit Button-->
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <SubmitButton :id="props.department?.id" />
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
    department: Object,
    breadcrumbs: Array as() => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

const formData = useForm({
    id: props.department?.id || '',
    name: props.department?.name || '',
    code: props.department?.code || '',
});

const submit = () => {
    if (props.department?.id) {
        // for updating permission
        formData.put(route('departments.update', props.department?.id), {
            preserveScroll: true,
        });
    } else {
        // for adding new permission
        formData.post(route('departments.store'), {
            preserveScroll: true,
        });
    }
};
</script>
