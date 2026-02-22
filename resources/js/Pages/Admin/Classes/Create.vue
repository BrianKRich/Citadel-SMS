<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomFieldsSection from '@/Components/Admin/CustomFieldsSection.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';

const props = defineProps({
    courses: Array,
    employees: Array,
    academicYears: Array,
    customFields: { type: Array, default: () => [] },
});

const form = useForm({
    course_id: '',
    employee_id: '',
    academic_year_id: '',
    term_id: '',
    section_name: '',
    room: '',
    max_students: 30,
    status: 'open',
    schedule: [],
    custom_field_values: {},
});

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

const selectedYear = computed(() => props.academicYears.find(y => y.id == form.academic_year_id));
const availableTerms = computed(() => selectedYear.value?.terms ?? []);

watch(() => form.academic_year_id, () => {
    form.term_id = '';
});

function addScheduleSlot() {
    form.schedule.push({ day: 'Monday', start_time: '08:00', end_time: '09:00' });
}

function removeScheduleSlot(idx) {
    form.schedule.splice(idx, 1);
}

function submit() {
    form.post(route('admin.classes.store'));
}
</script>

<template>
    <Head title="Create Class" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Create Class</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <PageHeader title="New Class" description="Schedule a new class section for a course." />

                    <form @submit.prevent="submit" class="space-y-8 mt-6">

                        <!-- Section 1: Course & Instructor -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide mb-4">
                                Course &amp; Instructor
                            </h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Course -->
                                <div>
                                    <label for="course_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Course <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="course_id"
                                        v-model="form.course_id"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    >
                                        <option value="">— Select Course —</option>
                                        <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.course_code }} — {{ c.name }}</option>
                                    </select>
                                    <p v-if="form.errors.course_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.course_id }}</p>
                                </div>

                                <!-- Teacher -->
                                <div>
                                    <label for="employee_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Teacher <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="employee_id"
                                        v-model="form.employee_id"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    >
                                        <option value="">— Select Teacher —</option>
                                        <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.first_name }} {{ e.last_name }}</option>
                                    </select>
                                    <p v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.employee_id }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Academic Period -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide mb-4">
                                Academic Period
                            </h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Academic Year -->
                                <div>
                                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Academic Year <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="academic_year_id"
                                        v-model="form.academic_year_id"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    >
                                        <option value="">— Select Academic Year —</option>
                                        <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                                    </select>
                                    <p v-if="form.errors.academic_year_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.academic_year_id }}</p>
                                </div>

                                <!-- Term -->
                                <div>
                                    <label for="term_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Term <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="term_id"
                                        v-model="form.term_id"
                                        required
                                        :disabled="!form.academic_year_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <option value="">— Select Term —</option>
                                        <option v-for="t in availableTerms" :key="t.id" :value="t.id">{{ t.name }}</option>
                                    </select>
                                    <p v-if="!form.academic_year_id" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select an academic year first</p>
                                    <p v-if="form.errors.term_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.term_id }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Class Details -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide mb-4">
                                Class Details
                            </h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Section Name -->
                                <div>
                                    <label for="section_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Section Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="section_name"
                                        v-model="form.section_name"
                                        type="text"
                                        required
                                        placeholder="e.g. A, B, Morning, Period 1"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.section_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.section_name }}</p>
                                </div>

                                <!-- Room -->
                                <div>
                                    <label for="room" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Room
                                    </label>
                                    <input
                                        id="room"
                                        v-model="form.room"
                                        type="text"
                                        placeholder="e.g. Room 101"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.room" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.room }}</p>
                                </div>

                                <!-- Max Students -->
                                <div>
                                    <label for="max_students" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Max Students <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="max_students"
                                        v-model="form.max_students"
                                        type="number"
                                        required
                                        min="1"
                                        max="500"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.max_students" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.max_students }}</p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    >
                                        <option value="open">Open</option>
                                        <option value="closed">Closed</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                    <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.status }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Schedule -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide mb-4">
                                Schedule
                            </h3>
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Weekly Schedule</label>
                                    <button
                                        type="button"
                                        @click="addScheduleSlot"
                                        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
                                    >+ Add Time Slot</button>
                                </div>
                                <div v-if="form.schedule.length" class="space-y-3">
                                    <div
                                        v-for="(slot, idx) in form.schedule"
                                        :key="idx"
                                        class="flex items-center gap-3 border border-gray-200 dark:border-gray-700 rounded-lg p-3"
                                    >
                                        <select
                                            v-model="slot.day"
                                            class="mt-1 block w-36 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        >
                                            <option v-for="d in DAYS" :key="d" :value="d">{{ d }}</option>
                                        </select>
                                        <input
                                            v-model="slot.start_time"
                                            type="time"
                                            class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        />
                                        <span class="text-gray-500 dark:text-gray-400 text-sm">to</span>
                                        <input
                                            v-model="slot.end_time"
                                            type="time"
                                            class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        />
                                        <button
                                            type="button"
                                            @click="removeScheduleSlot(idx)"
                                            class="text-red-500 hover:text-red-700 text-sm ml-auto"
                                        >&#x2715;</button>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                                    No schedule set. Click "Add Time Slot" to add class meeting times.
                                </p>
                                <p v-if="form.errors.schedule" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.schedule }}</p>
                            </div>
                        </div>

                        <!-- Custom Fields -->
                        <CustomFieldsSection
                            v-if="customFields.length"
                            :fields="customFields"
                            v-model="form.custom_field_values"
                        />

                        <!-- Button Row -->
                        <div class="flex items-center gap-4 pt-2">
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Create Class' }}
                            </PrimaryButton>
                            <Link
                                :href="route('admin.classes.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >Cancel</Link>
                        </div>
                    </form>
                </Card>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <Link
                        :href="route('admin.classes.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >&#x2190; Back to Classes</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
