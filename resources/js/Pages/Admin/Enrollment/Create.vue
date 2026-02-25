<template>
    <Head title="Enroll Student" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Breadcrumb :items="[
                { label: 'Dashboard', href: route('admin.dashboard') },
                { label: 'Enrollment', href: route('admin.enrollment.index') },
                { label: 'Enroll Student' },
            ]" />

            <div v-if="$page.props.flash?.success" class="mb-4">
                <Alert type="success" :message="$page.props.flash.success" />
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4">
                <Alert type="error" :message="$page.props.flash.error" />
            </div>

            <Card>
                <div class="p-6">
                    <PageHeader title="Enroll Student">
                        <template #actions>
                            <Link
                                :href="route('admin.enrollment.index')"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                &larr; Back to Enrollments
                            </Link>
                        </template>
                    </PageHeader>

                    <!-- Server-side enrollment error -->
                    <div v-if="form.errors.error" class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 mb-4">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.error }}</p>
                    </div>

                    <form @submit.prevent="submit" class="mt-6 space-y-6">
                        <!-- Student -->
                        <div>
                            <label for="student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Student <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="student_id"
                                v-model="form.student_id"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">— Select Student —</option>
                                <option v-for="s in students" :key="s.id" :value="s.id">
                                    {{ s.first_name }} {{ s.last_name }} ({{ s.student_id }})
                                </option>
                            </select>
                            <p v-if="form.errors.student_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.student_id }}
                            </p>
                        </div>

                        <!-- Filter by Class (not part of form submission) -->
                        <div>
                            <label for="class_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Filter by Class
                            </label>
                            <select
                                id="class_filter"
                                v-model="classFilter"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">All Classes</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                    Class {{ cls.class_number }}{{ cls.name ? ` — ${cls.name}` : '' }}
                                </option>
                            </select>
                        </div>

                        <!-- Select Course Assignment -->
                        <div>
                            <label for="class_course_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Select Course Assignment <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="class_course_id"
                                v-model="form.class_course_id"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            >
                                <option value="">— Select Course Assignment —</option>
                                <option v-for="cc in filteredClassCourses" :key="cc.id" :value="cc.id">
                                    Class {{ cc.class?.class_number }}
                                    / {{ cc.course?.course_code }} — {{ cc.course?.name }}
                                    <template v-if="cc.employee">
                                        ({{ cc.employee.first_name }} {{ cc.employee.last_name }})
                                    </template>
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Showing available course assignments.
                            </p>
                            <p v-if="form.errors.class_course_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.class_course_id }}
                            </p>
                        </div>

                        <!-- Enrollment Date -->
                        <div>
                            <label for="enrollment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enrollment Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="enrollment_date"
                                v-model="form.enrollment_date"
                                type="date"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                            />
                            <p v-if="form.errors.enrollment_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.enrollment_date }}
                            </p>
                        </div>

                        <!-- Custom Fields -->
                        <CustomFieldsSection
                            v-if="customFields.length"
                            :fields="customFields"
                            v-model="form.custom_field_values"
                        />

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-4 pt-2">
                            <Link
                                :href="route('admin.enrollment.index')"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Enrolling...' : 'Enroll Student' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomFieldsSection from '@/Components/Admin/CustomFieldsSection.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    students:              Array,
    classes:               Array,
    classCourses:          Array,
    selectedClassId:       [Number, String, null],
    selectedClassCourseId: [Number, String, null],
    customFields:          { type: Array, default: () => [] },
});

// Resolve preselected class course to also pre-populate class filter
const preselectedCC = props.selectedClassCourseId
    ? (props.classCourses ?? []).find(cc => cc.id === Number(props.selectedClassCourseId))
    : null;

const form = useForm({
    student_id:          '',
    class_course_id:     preselectedCC ? preselectedCC.id : '',
    enrollment_date:     new Date().toISOString().substring(0, 10),
    custom_field_values: {},
});

const classFilter = ref(preselectedCC?.class?.id ?? props.selectedClassId ?? '');

// Reset course filter when class changes
watch(classFilter, () => {
    form.class_course_id = '';
});

// Filter class courses by selected class
const filteredClassCourses = computed(() => {
    let list = props.classCourses ?? [];

    if (classFilter.value) {
        list = list.filter(cc => cc.class?.id == classFilter.value);
    }

    return list;
});

function submit() {
    form.post(route('admin.enrollment.enroll'));
}
</script>
