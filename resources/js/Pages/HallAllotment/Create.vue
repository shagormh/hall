<template>
    <AuthenticatedLayout :breadcrumbs="breadcrumbs" :pageTitle="pageTitle">
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0 cursor-pointer">
                <div class="card-title m-0">
                    <h3 class="fw-bold m-0">{{ hallAllotment?.id ? 'Edit Hall Allotment' : 'Add Hall Allotment' }}</h3>
                </div>
                <div class="d-flex justify-content-end p-4" data-kt-customer-table-toolbar="base">
                    <button v-if="checkPermission('can-create-student')" type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_add_student" style="width:150px">
                        <i class="fas fa-plus me-2"></i>
                        Add Student
                    </button>
                </div>
            </div>

            <div class="show">
                <form @submit.prevent="submit()" class="form">
                    <div class="card-body border-top p-9">
                        <!-- Student Roll & Name -->
                        <div class="row mb-2 g-4">
                            <div class="col-md-6 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="required fs-5 fw-semibold mb-2">Student Roll</label>

                                    <!-- Searchable Dropdown -->
                                    <div class="searchable-dropdown">
                                        <input
                                            type="text"
                                            v-model="studentSearch"
                                            @focus="dropdownOpen = true"
                                            @blur="handleBlur"
                                            placeholder="Select Student"
                                            class="form-control form-control-lg form-control-solid"
                                        />

                                        <ul v-show="dropdownOpen && filteredStudents.length" class="dropdown-list">
                                            <li
                                                v-for="student in filteredStudents"
                                                :key="student.value"
                                                @mousedown.prevent="selectStudent(student)"
                                            >
                                                {{ student.roll }} - {{ student.name }}
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="text-danger fs-7 mt-1" v-if="formData.errors.student_id">
                                        {{ formData.errors.student_id }}
                                    </div>

                                    <div class="text-info fs-7 mt-1">
                                        Available: {{ allStudents.length }} students
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="fs-5 fw-semibold mb-2">Student Name</label>
                                    <input type="text" class="form-control form-control-lg form-control-solid" placeholder="Student Name" v-model="formData.student_name" disabled/>
                                </div>
                            </div>
                        </div>

                        <!-- Starting Month & Allotment Date -->
                        <div class="row mb-2 g-4">
                            <div class="col-md-6 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="required fs-5 fw-semibold mb-2">Starting Month</label>
                                    <select
                                        class="form-control form-control-lg form-control-solid"
                                        v-model="formData.starting_month"
                                        @change="fetchAvailableSeats"
                                    >
                                        <option value="">Select Starting Month</option>
                                        <option v-for="month in allMonths" :key="month.value" :value="month.value">
                                            {{ month.name }}
                                        </option>
                                    </select>
                                    <div class="text-danger fs-7 mt-1" v-if="formData.errors.starting_month">
                                        {{ formData.errors.starting_month }}
                                    </div>
                                    <div class="text-info fs-7 mt-1">
                                        Available: {{ allMonths.length }} months
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="required fs-5 fw-semibold mb-2">Allotment Date</label>
                                    <input type="date" class="form-control form-control-lg form-control-solid" v-model="formData.allotment_date"/>
                                    <div class="text-danger fs-7 mt-1" v-if="formData.errors.allotment_date">
                                        {{ formData.errors.allotment_date }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seat Number & Room Type -->
                        <div class="row mb-2 g-4">
                            <div class="col-md-6 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="required fs-5 fw-semibold mb-2">Seat Number</label>
                                    <select
                                        class="form-control form-control-lg form-control-solid"
                                        v-model="formData.seat_id"
                                        @change="onSeatChange"
                                        :disabled="!formData.hall_id || !formData.starting_month"
                                    >
                                        <option value="">
                                            {{ !formData.hall_id ? 'Select Student First' : (!formData.starting_month ? 'Select Starting Month' : 'Select Seat Number') }}
                                        </option>
                                        <option v-for="seat in availableSeats" :key="seat.value" :value="seat.value">
                                            {{ seat.code }} - {{ seat.room_type }}
                                        </option>
                                    </select>
                                    <div class="text-danger fs-7 mt-1" v-if="formData.errors.seat_id">
                                        {{ formData.errors.seat_id }}
                                    </div>
                                    <div class="text-info fs-7 mt-1">
                                        Available: {{ availableSeats.length }} seats
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="fs-5 fw-semibold mb-2">Room Type</label>
                                    <input type="text" class="form-control form-control-lg form-control-solid" placeholder="Room Type" v-model="formData.room_type_name" disabled/>
                                </div>
                            </div>
                        </div>

                        <!-- Hall Name (Moved into its own full row or combined depending on preference, but keeping it simple for now) -->
                        <div class="row mb-2 g-4">
                             <div class="col-md-12 fv-row">
                                <div class="d-flex flex-column mb-5 fv-row">
                                    <label class="required fs-5 fw-semibold mb-2">Hall Name</label>
                                    <input type="text" class="form-control form-control-lg form-control-solid" placeholder="Hall Name" v-model="formData.hall_name" disabled/>
                                    <div class="text-info fs-7 mt-1">
                                        Hall ID: {{ formData.hall_id || 'Not selected' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="submit" class="btn btn-primary">
                            {{ hallAllotment?.id ? 'Update' : 'Submit' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { checkPermission } from "@/Core/helpers/Permission";
import axios from 'axios';
import Swal from 'sweetalert2';

// Props
const props = defineProps<{
    hallAllotment?: any;
    students?: { id: number; roll: string; name: string; hall_id: number }[];
    halls?: { id: number; name: string }[];
    seats?: { id: number; seat_code: string; room_id: number; room?: { hall_id: number; room_type?: { name: string } } }[];
    rooms?: { id: number; room_type: { name: string } }[];
    getMonths?: { name: string; value: string }[];
    breadcrumbs?: { url: string; title: string }[];
    pageTitle?: string;
}>();

const hallAllotment = ref(props.hallAllotment);
const breadcrumbs = ref(props.breadcrumbs || []);
const pageTitle = ref(props.pageTitle || 'Add Hall Allotment');

const today = new Date().toISOString().split('T')[0];

const formData = useForm({
    id: props.hallAllotment?.id || '',
    student_id: props.hallAllotment?.student_id || '',
    hall_id: props.hallAllotment?.hall_id || '',
    seat_id: props.hallAllotment?.seat_id || '',
    allotment_date: props.hallAllotment?.allotment_date || today,
    starting_month: props.hallAllotment?.starting_month || '',
    student_name: '',
    hall_name: '',
    room_type_name: '',
});

// All students
const allStudents = ref<{value:number, roll:string, name:string, hall_id:number}[]>([]);
if (props.students) {
    allStudents.value = props.students.map(s => ({
        value: s.id,
        roll: s.roll,
        name: s.name,
        hall_id: s.hall_id
    }));
}

// All seats
// All seats
const allSeats = ref<{value:number, code:string, hall_id:number, room_type:string}[]>([]);
if (props.seats) {
    allSeats.value = props.seats.map(s => {
        const room = props.rooms?.find(r => r.id == s.room_id);
        return {
            value: s.id,
            code: s.seat_code,
            hall_id: s.room?.hall_id || 0,
            room_type: room?.room_type?.name || ''
        };
    });
}

// All months
const allMonths = ref(props.getMonths || [
    { name: 'Dec - 2025', value: '2025-12-01' },
    { name: 'Jan - 2026', value: '2026-01-01' },
    { name: 'Feb - 2026', value: '2026-02-01' },
    { name: 'Mar - 2026', value: '2026-03-01' },
]);

// Dropdown logic
const studentSearch = ref('');
const dropdownOpen = ref(false);

const filteredStudents = computed(() => {
    if (!studentSearch.value) return allStudents.value;
    return allStudents.value.filter(s =>
        s.roll.toLowerCase().includes(studentSearch.value.toLowerCase()) ||
        s.name.toLowerCase().includes(studentSearch.value.toLowerCase())
    );
});

// Dynamic available seats
const availableSeats = ref<{value:number, code:string, hall_id:number, room_type:string}[]>([]);

const fetchAvailableSeats = async () => {
    availableSeats.value = [];
    formData.seat_id = ''; // Reset selection
    
    if (!formData.hall_id || !formData.starting_month) return;

    try {
        const response = await axios.get(route('hall-allotments.available-seats'), {
            params: {
                hall_id: formData.hall_id,
                starting_month: formData.starting_month
            }
        });

        // Map response to dropdown format
        // Backend returns raw Seat models with nested room/roomType
        availableSeats.value = response.data.seats.map((s: any) => ({
            value: s.id,
            code: s.seat_code,
            hall_id: s.room?.hall_id || 0,
            room_type: s.room?.room_type?.name || ''
        }));
        
    } catch (error) {
        console.error("Failed to fetch seats", error);
    }
};

// Also trigger if hall changes (via student selection)
watch(() => formData.hall_id, () => {
    if(formData.starting_month) fetchAvailableSeats();
});

// Check Student Eligibility
const checkEligibility = async () => {
    if (!formData.student_id || !formData.starting_month) return;

    try {
        const response = await axios.get(route('hall-allotments.check-student-eligibility'), {
            params: {
                student_id: formData.student_id,
                starting_month: formData.starting_month
            }
        });
        
        // If not eligible (response.data.eligible === false) is handled by catch usually if status 422/500
        // But here we return JSON 200 with eligible field.
        
        if (response.data.eligible === false) {
             Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: response.data.message || 'Student is not eligible for allotment in this month.',
                confirmButtonText: 'Ok'
            });
            // Optional: Clear selection?
            // formData.starting_month = '';
        }

    } catch (error) {
        console.error("Eligibility check failed", error);
    }
}

// Watchers for eligibility
watch(() => formData.student_id, checkEligibility);
watch(() => formData.starting_month, () => {
    checkEligibility();
    // Also fetch seats when month changes
});

// Initial load if editing
onMounted(() => {
    if (props.seats) {
         // If editing, use the provided seats (which handles current seat logic in backend)
         // But wait, the backend create() method provided 'seats' prop.
         // If we switch to dynamic, we might ignore initial prop UNLESS it's an edit with prepopulated data.
         // However, on Edit, we might want to refresh too? 
         // For now, let's map 'props.seats' to 'availableSeats' initially if present.
         
        availableSeats.value = props.seats.map(s => {
            const room = props.rooms?.find(r => r.id == s.room_id); // Or s.room if eager loaded as per my fix
            // The prop fix I did earlier: seats?: { ... room?: ... }
            // Let's rely on the prop structure I verified.
            return {
                value: s.id,
                code: s.seat_code,
                hall_id: s.room?.hall_id || 0,
                room_type: s.room?.room_type?.name || (room?.room_type?.name || '')
            };
        });
    }
});

const selectStudent = (student: any) => {
    formData.student_id = student.value;
    formData.student_name = student.name;
    formData.hall_id = student.hall_id;
    const selectedHall = props.halls?.find(h => h.id == student.hall_id);
    formData.hall_name = selectedHall?.name || '';
    studentSearch.value = `${student.roll} - ${student.name}`;
    dropdownOpen.value = false;
};

// Delay closing to allow click on list
const handleBlur = () => setTimeout(() => dropdownOpen.value = false, 150);

// Seat change handler
const onSeatChange = () => {
    const selectedSeat = availableSeats.value.find(s => s.value == formData.seat_id);
    if (selectedSeat) {
        formData.room_type_name = selectedSeat.room_type;
    }
};

// Submit form
const submit = () => {
    if (hallAllotment.value?.id) {
        formData.put(route('hall-allotments.update', hallAllotment.value.id));
    } else {
        formData.post(route('hall-allotments.store'));
    }
};
</script>

<style scoped>
.text-info { color: #17a2b8 !important; }
.text-danger { color: #dc3545 !important; }

.searchable-dropdown { position: relative; }
.dropdown-list {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #ddd;
  background: white;
  z-index: 10;
  padding: 0;
  margin: 0;
  list-style: none;
}
.dropdown-list li {
  padding: 8px 10px;
  cursor: pointer;
}
.dropdown-list li:hover {
  background: #f0f0f0;
}
</style>
