<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomFieldsSection from '@/Components/Admin/CustomFieldsSection.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    employee: Object,
    customFields: { type: Array, default: () => [] },
    documentsEnabled: { type: Boolean, default: false },
    documents: { type: Array, default: () => [] },
    trainingEnabled: { type: Boolean, default: false },
    trainingRecords: { type: Array, default: () => [] },
});

function getStatusBadgeClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        on_leave: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function removeClass(classId) {
    if (confirm('Remove this employee from the class? The class will remain but have no assigned teacher.')) {
        router.delete(route('admin.employees.remove-class', { employee: props.employee.id, class: classId }), {
            preserveScroll: true,
        });
    }
}

function destroy() {
    if (confirm('Are you sure? This will soft-delete the employee.')) {
        router.delete(route('admin.employees.destroy', props.employee.id));
    }
}

function formatDate(str) {
    if (!str) return '—';
    return new Date(str).toLocaleDateString();
}

// Documents
const showDocUpload = ref(false);
const docSelectedFileName = ref('');
const docUploadForm = useForm({
    entity_type: 'Employee',
    entity_id:   props.employee.id,
    file:        null,
    category:    '',
    description: '',
});

function setDocFile(e) {
    docUploadForm.file = e.target.files[0] ?? null;
    docSelectedFileName.value = e.target.files[0]?.name ?? '';
}

function submitDocUpload() {
    docUploadForm.post(route('admin.documents.store'), {
        forceFormData: true,
        onSuccess: () => {
            docUploadForm.reset();
            docUploadForm.entity_type = 'Employee';
            docUploadForm.entity_id   = props.employee.id;
            showDocUpload.value = false;
        },
    });
}

function deleteDocument(doc) {
    if (confirm(`Delete "${doc.original_name}"? This cannot be undone.`)) {
        router.delete(route('admin.documents.destroy', doc.id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head :title="`${employee.first_name} ${employee.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Employee Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Employees', href: route('admin.employees.index') },
                    { label: `${employee.first_name} ${employee.last_name}` },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Card 1: Employee Profile -->
                <Card class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <!-- Photo or Initials -->
                            <div class="flex-shrink-0">
                                <img
                                    v-if="employee.photo"
                                    :src="`/storage/${employee.photo}`"
                                    :alt="`${employee.first_name} ${employee.last_name}`"
                                    class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                                />
                                <div
                                    v-else
                                    class="h-20 w-20 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center ring-2 ring-gray-200 dark:ring-gray-700"
                                >
                                    <span class="text-2xl font-bold text-primary-700 dark:text-primary-300">
                                        {{ employee.first_name.charAt(0) }}{{ employee.last_name.charAt(0) }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ employee.first_name }} {{ employee.last_name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ employee.employee_id }}
                                </p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span
                                        :class="getStatusBadgeClass(employee.status)"
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    >
                                        {{ employee.status.replace('_', ' ') }}
                                    </span>
                                    <span
                                        v-if="employee.user"
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                    >
                                        Has Login Account
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 flex-shrink-0">
                            <Link
                                :href="route('admin.employees.edit', employee.id)"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700 transition-colors"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroy"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Definition List -->
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <a :href="`mailto:${employee.email}`" class="text-primary-600 dark:text-primary-400 hover:underline">
                                    {{ employee.email }}
                                </a>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <span v-if="employee.phone_numbers && employee.phone_numbers.length">
                                    ({{ employee.phone_numbers[0].area_code }}) {{ employee.phone_numbers[0].number }}
                                </span>
                                <span v-else class="text-gray-400 dark:text-gray-500">Not provided</span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Hire Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ employee.hire_date ? employee.hire_date.substring(0, 10) : 'Not recorded' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date of Birth</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ employee.date_of_birth ? employee.date_of_birth.substring(0, 10) : 'Not recorded' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Department</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ employee.department?.name ?? 'Not assigned' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ employee.role?.name ?? 'Not assigned' }}
                            </dd>
                        </div>
                    </dl>
                </Card>

                <!-- Card 2: Qualifications -->
                <Card class="mb-6">
                    <PageHeader title="Qualifications" />
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap" v-if="employee.qualifications">
                        {{ employee.qualifications }}
                    </p>
                    <p v-else class="text-sm text-gray-400 dark:text-gray-500 italic">
                        No qualifications recorded.
                    </p>
                </Card>

                <!-- Card 3: Teaching Schedule (only shown when classes are assigned) -->
                <Card v-if="employee.classes && employee.classes.length > 0" class="mb-6">
                    <PageHeader title="Teaching Schedule" description="Classes currently assigned to this employee." />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Course Code
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Course Name
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Section
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Term
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Enrolled / Capacity
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        Status
                                    </th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr
                                    v-for="cls in employee.classes"
                                    :key="cls.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                                >
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ cls.course?.course_code ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ cls.course?.name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.section_name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.term?.name ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ cls.enrolled_count }} / {{ cls.max_students }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span
                                            :class="getStatusBadgeClass(cls.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize"
                                        >
                                            {{ cls.status?.replace('_', ' ') ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <button
                                            @click="removeClass(cls.id)"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </Card>

                <!-- Custom Fields -->
                <Card v-if="customFields.length" class="mb-6">
                    <CustomFieldsSection :fields="customFields" :readonly="true" />
                </Card>

                <!-- Documents -->
                <Card v-if="documentsEnabled" class="mb-6">
                    <div class="mb-4 flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Documents</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Files attached to this employee</p>
                        </div>
                        <button
                            v-if="$page.props.auth.user?.role && ['admin','site_admin'].includes($page.props.auth.user.role)"
                            @click="showDocUpload = !showDocUpload"
                            class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            {{ showDocUpload ? 'Cancel' : '+ Upload' }}
                        </button>
                    </div>

                    <!-- Upload form -->
                    <div v-if="showDocUpload" class="mb-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File <span class="text-red-500">*</span></label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <span class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 whitespace-nowrap">
                                    Select File
                                </span>
                                <span class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                    {{ docSelectedFileName || 'No file chosen' }}
                                </span>
                                <input
                                    type="file"
                                    @change="setDocFile"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.webp"
                                    class="sr-only"
                                />
                            </label>
                            <p v-if="docUploadForm.errors.file" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ docUploadForm.errors.file }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max 10 MB. Allowed: PDF, Word, Excel, images.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                                <input v-model="docUploadForm.category" type="text" maxlength="100" placeholder="e.g. Contract, Certification"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                <p v-if="docUploadForm.errors.category" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ docUploadForm.errors.category }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <input v-model="docUploadForm.description" type="text" placeholder="Optional"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="showDocUpload = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900">Cancel</button>
                            <button @click="submitDocUpload" :disabled="docUploadForm.processing || !docUploadForm.file"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50">
                                {{ docUploadForm.processing ? 'Uploading…' : 'Upload' }}
                            </button>
                        </div>
                    </div>

                    <!-- Documents list -->
                    <div class="space-y-2">
                        <div v-for="doc in documents" :key="doc.id"
                            class="flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 gap-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ doc.original_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    <span v-if="doc.category">{{ doc.category }} · </span>{{ doc.formatted_size }} · {{ formatDate(doc.created_at) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <a :href="route('admin.documents.download', doc.id)"
                                    class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Download</a>
                                <button
                                    v-if="$page.props.auth.user?.role && ['admin','site_admin'].includes($page.props.auth.user.role)"
                                    @click="deleteDocument(doc)"
                                    class="text-sm text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </div>
                        </div>
                        <p v-if="!documents.length" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No documents uploaded for this employee.
                        </p>
                    </div>
                </Card>

                <!-- Staff Training -->
                <Card v-if="trainingEnabled" class="mb-6">
                    <div class="mb-4 flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Staff Training</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Recent training completions for this employee</p>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                :href="route('admin.training-records.create', { employee_id: employee.id })"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >+ Log Training</Link>
                            <Link
                                :href="route('admin.training-records.index', { employee_id: employee.id })"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >View All</Link>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Course Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Date Completed</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Trainer</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="record in trainingRecords" :key="record.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ record.training_course?.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ record.date_completed?.substring(0, 10) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ record.trainer_name }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <Link
                                            :href="route('admin.training-records.show', record.id)"
                                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
                                        >View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!trainingRecords.length">
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No training completions recorded for this employee.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Back Link -->
                <div class="mt-2">
                    <Link
                        :href="route('admin.employees.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        &larr; Back to Employees
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
