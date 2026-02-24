<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    notes: { type: Object, default: null },
    filters: Object,
    departments: { type: Array, default: () => [] },
    isAdmin: { type: Boolean, default: false },
    searched: { type: Boolean, default: false },
});

const filterForm = ref({
    search:        props.filters?.search        ?? '',
    department_id: props.filters?.department_id ?? '',
    date_from:     props.filters?.date_from     ?? '',
    date_to:       props.filters?.date_to       ?? '',
});

function applyFilters() {
    const params = {};
    if (filterForm.value.search)        params.search        = filterForm.value.search;
    if (filterForm.value.department_id) params.department_id = filterForm.value.department_id;
    if (filterForm.value.date_from)     params.date_from     = filterForm.value.date_from;
    if (filterForm.value.date_to)       params.date_to       = filterForm.value.date_to;
    router.get(route('admin.student-notes.index'), params, { preserveState: true });
}

function clearFilters() {
    filterForm.value = { search: '', department_id: '', date_from: '', date_to: '' };
    router.get(route('admin.student-notes.index'));
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString();
}

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

// Expand body inline
const expandedNoteId = ref(null);
function toggleExpand(id) {
    expandedNoteId.value = expandedNoteId.value === id ? null : id;
}

// Edit form per note
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
    editForms.value[note.id].patch(
        route('admin.students.notes.update', [note.student_id, note.id]),
        { onSuccess: () => { editingNoteId.value = null; } }
    );
}
function deleteNote(note) {
    if (confirm('Delete this note? This cannot be undone.')) {
        router.delete(route('admin.students.notes.destroy', [note.student_id, note.id]));
    }
}
</script>

<template>
    <Head title="Student Notes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Student Notes</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Student Notes' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>

                <!-- Filters -->
                <Card>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search Student</label>
                            <input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Name or ID…"
                                @keyup.enter="applyFilters"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <div v-if="isAdmin">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                            <select
                                v-model="filterForm.department_id"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">All Departments</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                    {{ dept.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date From</label>
                            <input
                                v-model="filterForm.date_from"
                                type="date"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date To</label>
                            <input
                                v-model="filterForm.date_to"
                                type="date"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button
                            @click="applyFilters"
                            class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700"
                        >Apply Filters</button>
                        <button
                            @click="clearFilters"
                            class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                        >Clear</button>
                    </div>
                </Card>

                <!-- Prompt to search -->
                <div v-if="!searched" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-10 text-center">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Enter search criteria above and click <strong>Apply Filters</strong> to find notes.</p>
                </div>

                <!-- Notes Table -->
                <Card v-if="searched">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Dept</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Author</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                <template v-for="note in notes?.data" :key="note.id">
                                    <!-- Main row -->
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <Link
                                                v-if="note.student"
                                                :href="route('admin.students.show', note.student_id)"
                                                class="text-primary-600 dark:text-primary-400 hover:underline"
                                            >
                                                {{ note.student.first_name }} {{ note.student.last_name }}
                                            </Link>
                                            <span v-else class="text-gray-400">—</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                :class="deptBadgeClass(note.department_id)"
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                            >{{ note.department?.name || '—' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <button
                                                @click="toggleExpand(note.id)"
                                                class="text-left font-medium hover:text-primary-600 dark:hover:text-primary-400"
                                            >
                                                {{ note.title }}
                                                <span class="ml-1 text-xs text-gray-400">{{ expandedNoteId === note.id ? '▲' : '▼' }}</span>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ note.employee ? `${note.employee.first_name} ${note.employee.last_name}` : '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(note.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm">
                                            <div class="flex justify-end gap-3">
                                                <button
                                                    @click="startEdit(note)"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline"
                                                >Edit</button>
                                                <button
                                                    @click="deleteNote(note)"
                                                    class="text-red-600 dark:text-red-400 hover:underline"
                                                >Delete</button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Expanded body row -->
                                    <tr v-if="expandedNoteId === note.id && editingNoteId !== note.id" class="bg-gray-50 dark:bg-gray-800/50">
                                        <td colspan="6" class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                            {{ note.body }}
                                        </td>
                                    </tr>

                                    <!-- Edit form row -->
                                    <tr v-if="editingNoteId === note.id" class="bg-blue-50 dark:bg-blue-900/20">
                                        <td colspan="6" class="px-6 py-4">
                                            <div class="space-y-3 max-w-2xl">
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
                                                <div class="flex gap-2">
                                                    <button
                                                        @click="submitEdit(note)"
                                                        :disabled="editForms[note.id].processing"
                                                        class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                                                    >Save</button>
                                                    <button
                                                        @click="cancelEdit"
                                                        type="button"
                                                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100"
                                                    >Cancel</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <tr v-if="!notes?.data?.length">
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No notes found matching your search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="notes?.last_page > 1" class="mt-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Showing {{ notes.from }}–{{ notes.to }} of {{ notes.total }} notes
                        </p>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in notes.links"
                                :key="link.label"
                                :href="link.url ?? '#'"
                                :class="[
                                    'px-3 py-1 text-sm rounded',
                                    link.active
                                        ? 'bg-primary-600 text-white'
                                        : link.url
                                            ? 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                                            : 'text-gray-400 dark:text-gray-600 cursor-not-allowed',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
