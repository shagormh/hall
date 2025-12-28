<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs" :pageTitle="pageTitle">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="fw-bold fw-black text-gray-800 font-bengali fs-2hx">ফি পরিশোধ ফরম</h3>
                </div>
            </div>

            <div class="card-body p-9">
                <form @submit.prevent="submit" class="form">
                    <div class="row g-9 mb-8">
                        <!-- Student Selection -->
                        <div class="col-md-6 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2 font-bengali">
                                <span class="required">শিক্ষার্থী নির্বাচন করুন</span>
                            </label>
                            
                            <div class="position-relative" ref="searchContainer">
                                <KTIcon icon-name="magnifier" icon-class="fs-2 position-absolute ms-4 mt-4" />
                                <input
                                    type="text"
                                    v-model="studentSearch"
                                    @focus="dropdownOpen = true"
                                    placeholder="আইডি বা নাম লিখুন..."
                                    class="form-control form-control-solid ps-12 h-45px fs-6 fw-bold"
                                    required
                                />

                                <!-- Dropdown -->
                                <div v-if="dropdownOpen && filteredStudents.length" 
                                     class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-100 show position-absolute mt-1 top-100 shadow-lg z-index-105"
                                     style="max-height: 300px; overflow-y: auto;">
                                    <div v-for="student in filteredStudents" :key="student.id" class="menu-item px-3">
                                        <a @mousedown.prevent="selectStudent(student)" class="menu-link px-3 py-3 d-flex justify-content-between align-items-center">
                                            <div class="d-flex flex-column">
                                                <span class="fs-6 fw-bold text-gray-800">{{ student.name }}</span>
                                                <span class="fs-8 text-muted mt-1">Roll: {{ student.roll }} | Hall: {{ student.hall?.name }}</span>
                                            </div>
                                            <KTIcon icon-name="plus-square" icon-class="fs-2 text-primary" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.student_id" class="fv-plugins-message-container invalid-feedback">{{ form.errors.student_id }}</div>
                        </div>

                        <!-- Hall Selection -->
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2 font-bengali">হল নির্বাচন করুন</label>
                            <select
                                v-model="form.hall_id"
                                class="form-select form-select-solid h-45px fs-6 fw-bold"
                                required
                            >
                                <option value="">হল নির্বাচন করুন</option>
                                <option v-for="hall in halls" :key="hall.id" :value="hall.id">
                                    {{ hall.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.hall_id" class="fv-plugins-message-container invalid-feedback">{{ form.errors.hall_id }}</div>
                        </div>
                    </div>

                    <!-- Voucher Upload -->
                    <div class="fv-row mb-8">
                        <label class="fs-6 fw-semibold mb-2 font-bengali">ভাউচার আপলোড (ছবি বা PDF)</label>
                        <div class="dropzone-styled p-10 text-center rounded-3 bg-light-primary border-primary border-dashed border-2 position-relative">
                            <input
                                type="file"
                                @change="handleVoucherUpload"
                                accept="image/*,application/pdf"
                                class="position-absolute w-100 h-100 top-0 start-0 opacity-0 cursor-pointer"
                                :disabled="scanning"
                            />
                            
                            <div v-if="!scanning" class="dz-message needsclick">
                                <KTIcon icon-name="cloud-upload" icon-class="fs-3hx text-primary mb-3" />
                                <div class="ms-4">
                                    <h3 class="fs-5 fw-bold text-gray-900 mb-1 font-bengali">এখানে ফাইল টেনে আনুন অথবা ক্লিক করুন</h3>
                                    <span class="fs-7 fw-semibold text-gray-500">JPG, PNG অথবা PDF সাপোর্ট করে (সর্বোচ্চ ৪ এমবি)</span>
                                </div>
                            </div>

                            <div v-else class="d-flex flex-column align-items-center">
                                <div class="spinner-border text-primary w-40px h-40px mb-3" role="status"></div>
                                <span class="fs-6 fw-bold text-primary font-bengali">ভাউচার প্রসেস হচ্ছে...</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-9 mb-8">
                        <!-- Transaction ID -->
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2 font-bengali">লেনদেন আইডি (Transaction ID)</label>
                            <div class="position-relative">
                                <KTIcon icon-name="tag" icon-class="fs-2 position-absolute ms-4 mt-4" />
                                <input
                                    v-model="form.transaction_id"
                                    type="text"
                                    placeholder="যেমন: AB0157733"
                                    class="form-control form-control-solid ps-12 h-45px fs-6 fw-bold"
                                    required
                                />
                            </div>
                            <div v-if="form.errors.transaction_id" class="fv-plugins-message-container invalid-feedback text-danger fs-7 mt-1">{{ form.errors.transaction_id }}</div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2 font-bengali">টাকার পরিমাণ (টাকা)</label>
                            <div class="position-relative">
                                <span class="position-absolute ms-4 mt-4 fs-4 fw-bold text-primary">৳</span>
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    placeholder="৯০০"
                                    class="form-control form-control-solid ps-12 h-45px fs-5 fw-black text-primary"
                                    required
                                />
                                <span class="badge badge-light-primary position-absolute end-0 top-50 translate-middle-y me-4 fw-bold">
                                    {{ monthsCovered }} মাস
                                </span>
                            </div>
                            <div v-if="form.errors.amount" class="fv-plugins-message-container invalid-feedback text-danger fs-7 mt-1">{{ form.errors.amount }}</div>
                        </div>
                    </div>

                    <div class="row g-9 mb-12">
                        <!-- Payment Date -->
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2 font-bengali">পরিশোধের তারিখ</label>
                            <div class="position-relative">
                                <KTIcon icon-name="calendar" icon-class="fs-2 position-absolute ms-4 mt-4" />
                                <input
                                    v-model="form.payment_date"
                                    type="date"
                                    class="form-control form-control-solid ps-12 h-45px fs-6 fw-bold"
                                    required
                                />
                            </div>
                            <div v-if="form.errors.payment_date" class="fv-plugins-message-container invalid-feedback text-danger fs-7 mt-1">{{ form.errors.payment_date }}</div>
                        </div>

                        <!-- Details -->
                        <div class="col-md-6 fv-row">
                            <label class="fs-6 fw-semibold mb-2 font-bengali">ফি এর বিবরণ</label>
                            <div class="position-relative">
                                <KTIcon icon-name="notepad" icon-class="fs-2 position-absolute ms-4 mt-4" />
                                <input
                                    v-model="form.fee_details"
                                    type="text"
                                    class="form-control form-control-solid ps-12 h-45px fs-6 fw-bold"
                                />
                            </div>
                            <div v-if="form.errors.fee_details" class="fv-plugins-message-container invalid-feedback text-danger fs-7 mt-1">{{ form.errors.fee_details }}</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="text-end">
                        <Link :href="route('student-fees.index')" class="btn btn-light me-3 fw-bold font-bengali">বাতিল</Link>
                        <button
                            type="submit"
                            class="btn btn-primary px-10 fw-black font-bengali"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                            ফি সাবমিট করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    students: Array,
    halls: Array,
    breadcrumbs: Array,
    pageTitle: String,
});

const form = useForm({
    student_id: '',
    hall_id: '',
    transaction_id: '',
    amount: '',
    payment_date: '',
    fee_details: 'Hall seat Rent',
    voucher_path: '',
});

const scanning = ref(false);
const studentSearch = ref('');
const dropdownOpen = ref(false);
const searchContainer = ref(null);

const filteredStudents = computed(() => {
    if (!studentSearch.value) return props.students;
    const query = studentSearch.value.toLowerCase();
    return props.students.filter(s =>
        s.roll.toLowerCase().includes(query) ||
        s.name.toLowerCase().includes(query)
    ).slice(0, 10);
});

const selectStudent = (student) => {
    form.student_id = student.id;
    form.hall_id = student.hall_id;
    studentSearch.value = `${student.name} (${student.roll})`;
    dropdownOpen.value = false;
};

// Click outside logic
if (typeof window !== 'undefined') {
    window.addEventListener('mousedown', (e) => {
        if (searchContainer.value && !searchContainer.value.contains(e.target)) {
            dropdownOpen.value = false;
        }
    });
}

const handleVoucherUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!form.student_id) {
        alert('অনুগ্রহ করে আগে শিক্ষার্থী নির্বাচন করুন।');
        event.target.value = '';
        return;
    }

    scanning.value = true;
    const formData = new FormData();
    formData.append('voucher', file);
    formData.append('student_id', form.student_id);

    try {
        const response = await axios.post(route('student-fees.parse-voucher'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        // Auto-fill form
        form.transaction_id = response.data.transaction_id;
        form.amount = response.data.amount;
        form.payment_date = response.data.payment_date;
        form.voucher_path = response.data.voucher_path;
        
    } catch (error) {
        console.error('Scanning failed', error);
        alert('ভাউচার স্ক্যান করা সম্ভব হয়নি। অনুগ্রহ করে ম্যানুয়ালি তথ্য প্রদান করুন।');
    } finally {
        scanning.value = false;
    }
};

const monthsCovered = computed(() => {
    if (!form.amount) return 0;
    return Math.floor(form.amount / 150);
});

const submit = () => {
    form.post(route('student-fees.store'));
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');

.font-bengali {
    font-family: 'Hind Siliguri', sans-serif;
}

.dropzone-styled {
    transition: all 0.3s ease;
}

.dropzone-styled:hover {
    background-color: var(--bs-light-primary) !important;
    border-color: var(--bs-primary) !important;
}

.invalid-feedback {
    display: block;
}
</style>
