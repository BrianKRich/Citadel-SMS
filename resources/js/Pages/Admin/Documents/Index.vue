<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    documents:  { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
    searched:   { type: Boolean, default: false },
});

const entityTypes = [
    { value: 'Student',     label: 'Student' },
    { value: 'Employee',    label: 'Employee' },
    { value: 'Institution', label: 'Organization' },
];

function entityLabel(type) {
    return entityTypes.find(e => e.value === type)?.label ?? type;
}

const entityBadgeClass = {
    Student:     'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    Employee:    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    Institution: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
};

const showUploadForm = ref(false);
const selectedFileName = ref('');

const uploadForm = useForm({
    entity_type: 'Institution',
    entity_id:   0,
    file:        null,
    category:    '',
    description: '',
});

function submitUpload() {
    uploadForm.post(route('admin.documents.store'), {
        forceFormData: true,
        onSuccess: () => {
            uploadForm.reset();
            uploadForm.entity_type = 'Institution';
            uploadForm.entity_id   = 0;
            selectedFileName.value = '';
            showUploadForm.value   = false;
        },
    });
}

function setFile(e) {
    uploadForm.file = e.target.files[0] ?? null;
    selectedFileName.value = e.target.files[0]?.name ?? '';
}

function deleteDocument(doc) {
    if (confirm(`Delete "${doc.original_name}"? This cannot be undone.`)) {
        router.delete(route('admin.documents.destroy', doc.id), { preserveScroll: true });
    }
}

function formatDate(str) {
    if (!str) return '—';
    return new Date(str).toLocaleDateString();
}

// Filter form
const filterForm = useForm({
    search:      props.filters.search      ?? '',
    entity_type: props.filters.entity_type ?? '',
    category:    props.filters.category    ?? '',
});

function applyFilters() {
    filterForm.get(route('admin.documents.index'), { preserveScroll: true });
}

const hasFilters = props.filters.search || props.filters.entity_type || props.filters.category;
</script>

<template>
    <Head title="Document Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Document Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Document Management' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <!-- Header row -->
                    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
                        <PageHeader
                            title="Document Management"
                            description="All documents across students, employees, and the organization"
                        />
                        <button
                            @click="showUploadForm = !showUploadForm"
                            class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700"
                        >
                            {{ showUploadForm ? 'Cancel' : '+ Upload Document' }}
                        </button>
                    </div>

                    <!-- Upload form -->
                    <div v-if="showUploadForm" class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 space-y-4">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Upload Document</h4>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">File <span class="text-red-500">*</span></label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <span class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 whitespace-nowrap">
                                    Select File
                                </span>
                                <span class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                    {{ selectedFileName || 'No file chosen' }}
                                </span>
                                <input
                                    type="file"
                                    @change="setFile"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.webp"
                                    class="sr-only"
                                />
                            </label>
                            <p v-if="uploadForm.errors.file" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ uploadForm.errors.file }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max 10 MB. Allowed: PDF, Word, Excel, images.</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Belongs To <span class="text-red-500">*</span></label>
                                <select
                                    v-model="uploadForm.entity_type"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option v-for="et in entityTypes" :key="et.value" :value="et.value">{{ et.label }}</option>
                                </select>
                                <p v-if="uploadForm.errors.entity_type" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ uploadForm.errors.entity_type }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                                <input
                                    v-model="uploadForm.category"
                                    type="text"
                                    maxlength="100"
                                    placeholder="e.g. Policy, Contract"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="uploadForm.errors.category" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ uploadForm.errors.category }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <input
                                    v-model="uploadForm.description"
                                    type="text"
                                    placeholder="Optional description"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="uploadForm.errors.description" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ uploadForm.errors.description }}</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                @click="showUploadForm = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100"
                            >Cancel</button>
                            <button
                                @click="submitUpload"
                                :disabled="uploadForm.processing || !uploadForm.file"
                                class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 disabled:opacity-50"
                            >
                                {{ uploadForm.processing ? 'Uploading…' : 'Upload' }}
                            </button>
                        </div>
                    </div>

                    <!-- Filter bar -->
                    <div class="mb-4 flex flex-wrap items-end gap-3">
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                            <input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Name, category, description…"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <div class="min-w-[160px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Document Type</label>
                            <select
                                v-model="filterForm.entity_type"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">All types</option>
                                <option v-for="et in entityTypes" :key="et.value" :value="et.value">{{ et.label }}</option>
                            </select>
                        </div>
                        <div v-if="categories.length" class="min-w-[160px]">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <select
                                v-model="filterForm.category"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">All categories</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                        <button
                            @click="applyFilters"
                            class="rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
                        >Filter</button>
                        <Link
                            v-if="hasFilters"
                            :href="route('admin.documents.index')"
                            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                        >Clear</Link>
                    </div>

                    <!-- Results table -->
                    <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Size</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Uploaded By</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    <tr v-for="doc in documents?.data ?? []" :key="doc.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ doc.original_name }}</p>
                                            <p v-if="doc.description" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ doc.description }}</p>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span
                                                :class="entityBadgeClass[doc.entity_type] ?? 'bg-gray-100 text-gray-700'"
                                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            >
                                                {{ entityLabel(doc.entity_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ doc.category || '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ doc.formatted_size }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ doc.uploader?.name || '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ formatDate(doc.created_at) }}</td>
                                        <td class="px-4 py-3 text-right whitespace-nowrap">
                                            <a
                                                :href="route('admin.documents.download', doc.id)"
                                                class="text-sm text-primary-600 dark:text-primary-400 hover:underline mr-3"
                                            >Download</a>
                                            <button
                                                @click="deleteDocument(doc)"
                                                class="text-sm text-red-600 dark:text-red-400 hover:underline"
                                            >Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="!documents?.data?.length">
                                        <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                            {{ searched ? 'No documents match your search.' : 'Use the filters above to search for documents.' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="documents?.last_page > 1" class="mt-4 flex items-center justify-between">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Showing {{ documents.from }}–{{ documents.to }} of {{ documents.total }}
                            </p>
                            <div class="flex gap-2">
                                <Link
                                    v-if="documents.prev_page_url"
                                    :href="documents.prev_page_url"
                                    class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >Previous</Link>
                                <Link
                                    v-if="documents.next_page_url"
                                    :href="documents.next_page_url"
                                    class="rounded-md border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >Next</Link>
                            </div>
                        </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
