<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    cohortCourse:  { type: Object, required: true },
    classes:       { type: Array, default: () => [] },
    courses:       { type: Array, default: () => [] },
    employees:     { type: Array, default: () => [] },
    institutions:  { type: Array, default: () => [] },
});

const inputClass = 'mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';

// Resolve initial class from the cohort
const initialClassId = (() => {
    for (const cls of props.classes) {
        if ((cls.cohorts ?? []).some(c => c.id === props.cohortCourse.cohort_id)) return cls.id;
    }
    return '';
})();

const selectedClassId = ref(initialClassId);

const form = useForm({
    cohort_id:       props.cohortCourse.cohort_id ?? '',
    course_id:       props.cohortCourse.course_id ?? '',
    instructor_type: props.cohortCourse.instructor_type ?? 'staff',
    employee_id:     props.cohortCourse.employee_id ?? '',
    institution_id:  props.cohortCourse.institution_id ?? '',
    room:            props.cohortCourse.room ?? '',
    schedule:        Array.isArray(props.cohortCourse.schedule) ? [...props.cohortCourse.schedule] : [],
    max_students:    props.cohortCourse.max_students ?? 30,
    status:          props.cohortCourse.status ?? 'open',
});

// Selected employee display
const selectedEmployeeName = ref(
    props.cohortCourse.employee
        ? `${props.cohortCourse.employee.first_name} ${props.cohortCourse.employee.last_name}`
        : ''
);

const availableCohorts = computed(() => {
    const cls = props.classes.find(c => c.id === Number(selectedClassId.value));
    return cls?.cohorts ?? [];
});

watch(selectedClassId, () => {
    form.cohort_id = '';
    const cohorts = availableCohorts.value;
    if (cohorts.length === 1) form.cohort_id = cohorts[0].id;
});

const filteredInstitutions = computed(() => {
    if (form.instructor_type === 'technical_college') {
        return props.institutions.filter(i => i.type === 'technical_college');
    }
    if (form.instructor_type === 'university') {
        return props.institutions.filter(i => i.type === 'university');
    }
    return [];
});

watch(() => form.instructor_type, () => {
    form.employee_id    = '';
    form.institution_id = '';
    employeeSearch.value = '';
    employeeResults.value = [];
    selectedEmployeeName.value = '';
});

// Employee live search
const employeeSearch       = ref('');
const employeeResults      = ref([]);
let searchDebounce = null;

async function onEmployeeSearch() {
    const q = employeeSearch.value.trim();
    if (!q) { employeeResults.value = []; return; }
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(async () => {
        try {
            const res = await fetch(`/api/employees/search?q=${encodeURIComponent(q)}`);
            employeeResults.value = await res.json();
        } catch {
            employeeResults.value = [];
        }
    }, 300);
}

function selectEmployee(emp) {
    form.employee_id         = emp.id;
    selectedEmployeeName.value = `${emp.first_name} ${emp.last_name}`;
    employeeSearch.value     = '';
    employeeResults.value    = [];
}

// Schedule builder
const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

function addScheduleSlot() {
    form.schedule.push({ day: 'Monday', start_time: '', end_time: '' });
}

function removeScheduleSlot(index) {
    form.schedule.splice(index, 1);
}

const courseName = computed(() => props.cohortCourse.course?.name ?? 'Course Assignment');

function submit() {
    form.patch(route('admin.cohort-courses.update', props.cohortCourse.id));
}
</script>

<template>
    <Head :title="`Edit ${courseName}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Edit Course Assignment</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Course Assignments', href: route('admin.cohort-courses.index') },
                    { label: courseName, href: route('admin.cohort-courses.show', cohortCourse.id) },
                    { label: 'Edit' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <PageHeader :title="`Edit: ${courseName}`" description="Update this course assignment." />

                    <form @submit.prevent="submit" class="mt-6 space-y-6">
                        <!-- Class -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Class <span class="text-red-500">*</span>
                            </label>
                            <select v-model="selectedClassId" required :class="inputClass">
                                <option value="">Select a class...</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    Class {{ cls.class_number }} {{ cls.academic_year ? `(${cls.academic_year.name})` : '' }}
                                </option>
                            </select>
                        </div>

                        <!-- Cohort -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Cohort <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.cohort_id" required :class="inputClass" :disabled="!selectedClassId">
                                <option value="">Select a cohort...</option>
                                <option v-for="cohort in availableCohorts" :key="cohort.id" :value="cohort.id">
                                    {{ cohort.name === 'alpha' ? 'Cohort Alpha' : 'Cohort Bravo' }}
                                </option>
                            </select>
                            <p v-if="form.errors.cohort_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.cohort_id }}</p>
                        </div>

                        <!-- Course -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Course <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.course_id" required :class="inputClass">
                                <option value="">Select a course...</option>
                                <option v-for="course in courses" :key="course.id" :value="course.id">
                                    {{ course.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.course_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.course_id }}</p>
                        </div>

                        <!-- Instructor Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Instructor Type <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.instructor_type" :class="inputClass">
                                <option value="staff">Staff Instructor</option>
                                <option value="technical_college">Technical College</option>
                                <option value="university">University</option>
                            </select>
                            <p v-if="form.errors.instructor_type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.instructor_type }}</p>
                        </div>

                        <!-- Staff instructor -->
                        <div v-if="form.instructor_type === 'staff'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Staff Instructor <span class="text-red-500">*</span>
                            </label>
                            <div v-if="selectedEmployeeName" class="mt-1 flex items-center gap-2">
                                <span class="text-sm text-gray-900 dark:text-gray-100 font-medium">{{ selectedEmployeeName }}</span>
                                <button
                                    type="button"
                                    @click="selectedEmployeeName = ''; form.employee_id = ''"
                                    class="text-xs text-gray-400 hover:text-red-500"
                                >Change</button>
                            </div>
                            <div v-else class="relative">
                                <input
                                    v-model="employeeSearch"
                                    type="text"
                                    placeholder="Search employees by name..."
                                    @input="onEmployeeSearch"
                                    :class="inputClass"
                                />
                                <ul v-if="employeeResults.length > 0" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                    <li
                                        v-for="emp in employeeResults"
                                        :key="emp.id"
                                        @click="selectEmployee(emp)"
                                        class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 hover:bg-primary-50 dark:hover:bg-primary-900/30 cursor-pointer"
                                    >
                                        {{ emp.first_name }} {{ emp.last_name }}
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ emp.employee_id }})</span>
                                    </li>
                                </ul>
                            </div>
                            <p v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.employee_id }}</p>
                        </div>

                        <!-- Institution -->
                        <div v-if="form.instructor_type === 'technical_college' || form.instructor_type === 'university'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Institution <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.institution_id" :class="inputClass">
                                <option value="">Select institution...</option>
                                <option v-for="inst in filteredInstitutions" :key="inst.id" :value="inst.id">
                                    {{ inst.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.institution_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.institution_id }}</p>
                        </div>

                        <!-- Room -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Room</label>
                            <input v-model="form.room" type="text" placeholder="e.g. Building A, Room 101" :class="inputClass" />
                            <p v-if="form.errors.room" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.room }}</p>
                        </div>

                        <!-- Schedule Builder -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Schedule</label>
                                <button type="button" @click="addScheduleSlot" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">+ Add Time Slot</button>
                            </div>
                            <div v-if="form.schedule.length === 0" class="text-sm text-gray-400 dark:text-gray-500 italic">No schedule slots added.</div>
                            <div
                                v-for="(slot, index) in form.schedule"
                                :key="index"
                                class="mb-3 flex flex-col sm:flex-row sm:items-center gap-2 bg-gray-50 dark:bg-gray-800/50 rounded-md p-3"
                            >
                                <select v-model="slot.day" :class="['flex-1', inputClass]">
                                    <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
                                </select>
                                <input v-model="slot.start_time" type="time" :class="['flex-1', inputClass]" />
                                <input v-model="slot.end_time" type="time" :class="['flex-1', inputClass]" />
                                <button type="button" @click="removeScheduleSlot(index)" class="text-red-500 hover:text-red-700 text-sm shrink-0">Remove</button>
                            </div>
                            <p v-if="form.errors.schedule" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.schedule }}</p>
                        </div>

                        <!-- Max Students -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Students</label>
                            <input v-model.number="form.max_students" type="number" min="1" :class="inputClass" />
                            <p v-if="form.errors.max_students" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.max_students }}</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select v-model="form.status" :class="inputClass">
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.status }}</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-4">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.cohort-courses.show', cohortCourse.id)"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</Link>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
