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
    student: Object,
    customFields: { type: Array, default: () => [] },
    notes: { type: Array, default: () => [] },
    canAddNote: { type: Boolean, default: false },
    userDeptId: { type: Number, default: null },
    isAdmin: { type: Boolean, default: false },
    departments: { type: Array, default: () => [] },
});

function getStatusBadgeClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        graduated: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        withdrawn: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        suspended: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

function getEnrollmentStatusBadgeClass(status) {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        dropped: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        withdrawn: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString();
}

function formatAddress() {
    const parts = [props.student.city, props.student.state, props.student.postal_code].filter(Boolean);
    return parts.length ? parts.join(', ') : '—';
}

function destroy() {
    if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
        router.delete(route('admin.students.destroy', props.student.id));
    }
}

// Department badge colors (cycle through a set)
const deptColors = [
    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300',
    'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
    'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
];
function deptBadgeClass(deptId) {
    return deptColors[(deptId ?? 0) % deptColors.length];
}

function canManageNote(note) {
    return props.isAdmin || note.department_id === props.userDeptId;
}

// Add note form
const showAddNote = ref(false);
const addNoteForm = useForm({ title: '', body: '', department_id: '' });
function submitAddNote() {
    addNoteForm.post(route('admin.students.notes.store', props.student.id), {
        onSuccess: () => {
            addNoteForm.reset();
            showAddNote.value = false;
        },
    });
}

// Edit note
const editingNoteId = ref(null);
const editForms = ref({});
function startEdit(note) {
    editingNoteId.value = note.id;
    editForms.value[note.id] = useForm({ title: note.title, body: note.body });
}
function cancelEdit() {
    editingNoteId.value = null;
}
function submitEdit(note) {
    editForms.value[note.id].patch(route('admin.students.notes.update', [props.student.id, note.id]), {
        onSuccess: () => { editingNoteId.value = null; },
    });
}

function deleteNote(note) {
    if (confirm('Delete this note? This cannot be undone.')) {
        router.delete(route('admin.students.notes.destroy', [props.student.id, note.id]));
    }
}
</script>

<template>
    <Head :title="`Student: ${student.first_name} ${student.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Student Details</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Students', href: route('admin.students.index') },
                    { label: `${student.first_name} ${student.last_name}` },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Soft-deleted warning -->
                <div v-if="student.deleted_at" class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-4">
                    <p class="text-sm text-red-800 dark:text-red-300 font-medium">
                        This student record has been deleted and is no longer active.
                        Deleted on {{ formatDate(student.deleted_at) }}.
                    </p>
                </div>

                <!-- Card 1: Student Profile -->
                <Card>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-6 gap-4">
                        <div class="flex items-start gap-4">
                            <!-- Photo or Initials Avatar -->
                            <div class="flex-shrink-0">
                                <img
                                    v-if="student.photo"
                                    :src="'/storage/' + student.photo"
                                    class="h-24 w-24 rounded-full object-cover"
                                    :alt="`${student.first_name} ${student.last_name}`"
                                />
                                <div
                                    v-else
                                    class="h-24 w-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                                >
                                    <span class="text-2xl text-gray-500 dark:text-gray-400">{{ student.first_name?.[0] }}</span>
                                </div>
                            </div>

                            <div>
                                <PageHeader
                                    :title="`${student.first_name}${student.middle_name ? ' ' + student.middle_name : ''} ${student.last_name}`"
                                    :description="student.student_id"
                                />
                                <div class="mt-2">
                                    <span
                                        :class="getStatusBadgeClass(student.status)"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize"
                                    >
                                        {{ student.status }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 flex-shrink-0 flex-wrap">
                            <Link
                                v-if="$page.props.features?.attendance_enabled"
                                :href="route('admin.attendance.student', student.id)"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                View Attendance
                            </Link>
                            <Link
                                :href="route('admin.students.edit', student.id)"
                                class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Edit
                            </Link>
                            <button
                                @click="destroy"
                                class="inline-flex items-center rounded-md border border-red-300 dark:border-red-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 shadow-sm hover:bg-red-50 dark:hover:bg-red-900/20"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date of Birth</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(student.date_of_birth) }}</dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Gender</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ student.gender || '—' }}</dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <a v-if="student.email" :href="`mailto:${student.email}`" class="text-primary-600 dark:text-primary-400 hover:underline">
                                    {{ student.email }}
                                </a>
                                <span v-else>—</span>
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Enrollment Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatDate(student.enrollment_date) }}</dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <div v-if="student.address">{{ student.address }}</div>
                                <div>{{ formatAddress() }}</div>
                                <span v-if="!student.address && !student.city && !student.state && !student.postal_code">—</span>
                            </dd>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Emergency Contact</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <div v-if="student.emergency_contact_name">{{ student.emergency_contact_name }}</div>
                                <div v-if="student.emergency_contact_phone" class="text-gray-500 dark:text-gray-400">
                                    {{ student.emergency_contact_phone }}
                                </div>
                                <span v-if="!student.emergency_contact_name && !student.emergency_contact_phone">—</span>
                            </dd>
                        </div>
                        <div v-if="student.notes" class="border-t border-gray-200 dark:border-gray-700 pt-4 sm:col-span-2 lg:col-span-3">
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ student.notes }}</dd>
                        </div>
                    </dl>
                </Card>

                <!-- Card 2: Guardians -->
                <Card>
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Guardians</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Parent and guardian contact information</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Relationship</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Primary</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="guardian in student.guardians" :key="guardian.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ guardian.first_name }} {{ guardian.last_name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 capitalize">
                                        {{ guardian.relationship || '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ guardian.phone || '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            v-if="guardian.is_primary"
                                            class="inline-flex rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 px-2 text-xs font-semibold leading-5"
                                        >
                                            Primary
                                        </span>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </td>
                                </tr>
                                <tr v-if="!student.guardians?.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No guardians found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Card 3: Enrollment History -->
                <Card>
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Enrollment History</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Courses this student has been enrolled in</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Course Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Course Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Term</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <tr v-for="enrollment in student.enrollments" :key="enrollment.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ enrollment.class?.course?.course_code || '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ enrollment.class?.course?.name || '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ enrollment.class?.term?.name || '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="getEnrollmentStatusBadgeClass(enrollment.status)"
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 capitalize"
                                        >
                                            {{ enrollment.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ enrollment.final_letter_grade || '—' }}
                                    </td>
                                </tr>
                                <tr v-if="!student.enrollments?.length">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No enrollments found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Card 4: Department Notes -->
                <Card>
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Department Notes</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Staff notes about this student</p>
                        </div>
                        <button
                            v-if="canAddNote"
                            @click="showAddNote = !showAddNote"
                            class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            {{ showAddNote ? 'Cancel' : '+ Add Note' }}
                        </button>
                    </div>

                    <!-- Add Note Form -->
                    <div v-if="showAddNote" class="mb-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">New Note</h4>
                        <div class="space-y-3">
                            <!-- Department picker: only shown for admins with no employee record -->
                            <div v-if="isAdmin && !userDeptId && departments.length">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                                <select
                                    v-model="addNoteForm.department_id"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="" disabled>Select a department</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </option>
                                </select>
                                <p v-if="addNoteForm.errors.department_id" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ addNoteForm.errors.department_id }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                <input
                                    v-model="addNoteForm.title"
                                    type="text"
                                    maxlength="255"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Note title"
                                />
                                <p v-if="addNoteForm.errors.title" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ addNoteForm.errors.title }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Body</label>
                                <textarea
                                    v-model="addNoteForm.body"
                                    rows="4"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Write your note here…"
                                ></textarea>
                                <p v-if="addNoteForm.errors.body" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ addNoteForm.errors.body }}</p>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button
                                    @click="showAddNote = false"
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100"
                                >Cancel</button>
                                <button
                                    @click="submitAddNote"
                                    :disabled="addNoteForm.processing"
                                    class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                                >Save Note</button>
                            </div>
                        </div>
                    </div>

                    <!-- Notes List -->
                    <div class="space-y-3">
                        <div
                            v-for="note in notes"
                            :key="note.id"
                            class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4"
                        >
                            <!-- View mode -->
                            <template v-if="editingNoteId !== note.id">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <span
                                                :class="deptBadgeClass(note.department_id)"
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                            >{{ note.department?.name || 'Unknown Dept' }}</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ note.title }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ note.body }}</p>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ note.employee ? `${note.employee.first_name} ${note.employee.last_name}` : 'Unknown' }}
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(note.created_at) }}</p>
                                        <div v-if="canManageNote(note)" class="flex gap-2 mt-2 justify-end">
                                            <button
                                                @click="startEdit(note)"
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                            >Edit</button>
                                            <button
                                                @click="deleteNote(note)"
                                                class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                            >Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Edit mode -->
                            <template v-else>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                        <input
                                            v-model="editForms[note.id].title"
                                            type="text"
                                            maxlength="255"
                                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        />
                                        <p v-if="editForms[note.id].errors.title" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ editForms[note.id].errors.title }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Body</label>
                                        <textarea
                                            v-model="editForms[note.id].body"
                                            rows="4"
                                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        ></textarea>
                                        <p v-if="editForms[note.id].errors.body" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ editForms[note.id].errors.body }}</p>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="cancelEdit"
                                            type="button"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100"
                                        >Cancel</button>
                                        <button
                                            @click="submitEdit(note)"
                                            :disabled="editForms[note.id].processing"
                                            class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                                        >Save</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <p v-if="!notes.length" class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No notes recorded for this student.
                        </p>
                    </div>
                </Card>

                <!-- Custom Fields -->
                <Card v-if="customFields.length">
                    <CustomFieldsSection :fields="customFields" :readonly="true" />
                </Card>

                <!-- Back Link -->
                <div class="px-4 sm:px-0">
                    <Link
                        :href="route('admin.students.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >
                        ← Back to Students
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
