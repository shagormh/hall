<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout :breadcrumbs="breadcrumbs" :pageTitle="pageTitle">
        <div class="card shadow-sm mb-5">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1" ref="searchContainer" style="overflow: visible;">
                        <KTIcon
                            icon-name="magnifier"
                            icon-class="fs-1 position-absolute ms-6"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="form-control form-control-solid w-250px ps-15 h-45px text-gray-800"
                            @focus="showResults = searchResults.length > 0"
                            placeholder="আইডি বা নাম লিখুন..."
                        />
                        
                        <!-- Dropdown Menu (Metronic Style) -->
                        <div v-if="showResults && searchResults.length" 
                             class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-400px show position-absolute mt-1 top-100 shadow-lg z-index-105"
                             style="max-height: 400px; overflow-y: auto;">
                            <div v-for="student in searchResults" :key="student.id" class="menu-item px-3">
                                <a @click.prevent="selectStudent(student)" class="menu-link px-3 py-4 d-flex justify-content-between align-items-center">
                                    <div class="d-flex flex-column">
                                        <span class="fs-6 fw-bold text-gray-800">{{ student.name }}</span>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge badge-light-primary fw-bold fs-8">Roll: {{ student.roll }}</span>
                                            <span v-if="student.registration" class="badge badge-light-info fw-bold fs-8">Reg: {{ student.registration }}</span>
                                        </div>
                                        <span class="fs-8 text-muted mt-1">{{ student.hall?.name }}</span>
                                    </div>
                                    <KTIcon icon-name="right-arrow" icon-class="fs-2 text-primary" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Details Section (Metronic Style) -->
        <transition name="fade">
            <div v-if="feeSummary" class="card shadow-sm">
                <!-- Card Header -->
                <div class="card-header ribbon ribbon-top ribbon-vertical border-0 pt-10 pb-5 h-150px min-h-150px bg-primary rounded-top">
                    <div class="ribbon-label bg-warning fw-bold fs-7">
                        {{ feeSummary.due_fee <= 0 ? 'ভরা' : 'বকেয়া' }}
                    </div>
                    <div class="card-title d-flex justify-content-between align-items-center w-100">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-70px symbol-circle me-5">
                                <div class="symbol-label fs-2 fw-bold bg-white text-primary text-uppercase">
                                    {{ feeSummary.student.name.charAt(0) }}
                                </div>
                            </div>
                            <div class="d-flex flex-column text-white">
                                <span class="fs-2 fw-bold">{{ feeSummary.student.name }}</span>
                                <span class="opacity-75 fs-6">{{ feeSummary.student.roll }}</span>
                                <span v-if="feeSummary.student.registration" class="opacity-50 fs-7">REG: {{ feeSummary.student.registration }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-9">
                    <!-- Info Grid -->
                    <div class="row g-5 mb-10">
                        <div class="col-md-6">
                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-5 px-6 mb-3 bg-light-primary border-primary border-opacity-25">
                                <div class="fs-8 fw-bold text-gray-400 uppercase">হল এর নাম</div>
                                <div class="fs-5 fw-bold text-gray-800">{{ feeSummary.student.hall?.name || 'বরাদ্দ করা হয়নি' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-5 px-6 mb-3 bg-light-info border-info border-opacity-25">
                                <div class="fs-8 fw-bold text-gray-400 uppercase">সেমিস্টার</div>
                                <div class="fs-5 fw-bold text-gray-800">বর্ষপঞ্জি ২০২৫</div>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Summary Table -->
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8">
                                    <th class="min-w-150px">বিবরণ</th>
                                    <th class="min-w-100px text-end text-primary">পরিমাণ (টাকা)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-3">
                                                <span class="symbol-label bg-light-secondary">
                                                    <KTIcon icon-name="bill" icon-class="fs-2 text-gray-600" />
                                                </span>
                                            </div>
                                            <span class="text-gray-900 fw-bold fs-6">মোট ফি</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-black fs-5">৳ {{ formatCurrency(feeSummary.total_fee) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-3">
                                                <span class="symbol-label bg-light-success">
                                                    <KTIcon icon-name="check-circle" icon-class="fs-2 text-success" />
                                                </span>
                                            </div>
                                            <span class="text-gray-900 fw-bold fs-6">পরিশোধিত</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-black fs-5 text-success">৳ {{ formatCurrency(feeSummary.paid_fee) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-3">
                                                <span class="symbol-label bg-light-danger">
                                                    <KTIcon icon-name="information-5" icon-class="fs-2 text-danger" />
                                                </span>
                                            </div>
                                            <span class="text-gray-900 fw-bold fs-6 text-danger">বকেয়া</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-black fs-5 text-danger">৳ {{ formatCurrency(feeSummary.due_fee) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row g-4 mt-5">
                        <div class="col-md-4">
                            <div class="bg-light-info rounded p-5 border border-info border-opacity-10">
                                <span class="text-info fw-bold d-block fs-8 uppercase mb-1">লেনদেন আইডি</span>
                                <span class="text-gray-800 fw-bold fs-7 font-mono">ALLOT-{{ feeSummary.allotment?.id || 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light-primary rounded p-5 border border-primary border-opacity-10">
                                <span class="text-primary fw-bold d-block fs-8 uppercase mb-1">বরাদ্দের তারিখ</span>
                                <span class="text-gray-800 fw-bold fs-7">{{ feeSummary.allotment?.allotment_date || 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light-success rounded p-5 border border-success border-opacity-10">
                                <span class="text-success fw-bold d-block fs-8 uppercase mb-1">মোট মাস</span>
                                <span class="text-gray-800 fw-bold fs-7 font-black">{{ feeSummary.total_months }} মাস</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end mt-10">
                        <Link :href="route('student-fees.create')" class="btn btn-primary fw-bold px-8 py-4">
                            <KTIcon icon-name="send" icon-class="fs-2 me-2" />
                            ফি পরিশোধ করুন
                        </Link>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Initial/Empty State -->
        <div v-if="!feeSummary && !loadingSummary" class="card shadow-sm h-400px d-flex align-items-center justify-content-center">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <div class="symbol symbol-100px mb-10">
                    <div class="symbol-label bg-light-primary">
                        <KTIcon icon-name="search-list" icon-class="fs-5x text-primary" />
                    </div>
                </div>
                <h3 class="fs-2 fw-bold text-gray-800 mb-2 font-bengali">শিক্ষার্থী খুঁজুন</h3>
                <p class="text-gray-400 fw-semibold fs-5 font-bengali">উপরে আইডি বা নাম লিখে ফি স্ট্যাটাস দেখুন</p>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loadingSummary" class="card shadow-sm h-400px d-flex align-items-center justify-content-center">
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div class="spinner-border text-primary mb-4 w-50px h-50px" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="text-muted fw-bold font-bengali">তথ্য লোড হচ্ছে...</span>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    breadcrumbs: Array,
    pageTitle: String,
});

const searchQuery = ref('');
const searchResults = ref([]);
const feeSummary = ref(null);
const loadingSummary = ref(false);
const showResults = ref(false);
const searchContainer = ref(null);

// Search students as user types with debounce
let searchTimeout;
watch(searchQuery, (newQuery) => {
    clearTimeout(searchTimeout);
    if (newQuery.length < 2) {
        searchResults.value = [];
        showResults.value = false;
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get(route('student-fees.search-checker'), {
                params: { q: newQuery }
            });
            searchResults.value = response.data;
            showResults.value = searchResults.value.length > 0;
        } catch (error) {
            console.error('Error searching students:', error);
        }
    }, 300);
});

// Click outside logic
if (typeof window !== 'undefined') {
    window.addEventListener('click', (e) => {
        if (searchContainer.value && !searchContainer.value.contains(e.target)) {
            showResults.value = false;
        }
    });
}

const selectStudent = async (student) => {
    searchQuery.value = student.name;
    showResults.value = false;
    
    loadingSummary.value = true;
    feeSummary.value = null;
    try {
        const response = await axios.get(route('student-fees.get-summary', student.id));
        feeSummary.value = response.data;
    } catch (error) {
        console.error('Error fetching fee summary:', error);
    } finally {
        loadingSummary.value = false;
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('bn-BD').format(amount);
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');

.font-bengali {
    font-family: 'Hind Siliguri', sans-serif;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

.font-mono {
    font-family: monospace;
}
</style>
