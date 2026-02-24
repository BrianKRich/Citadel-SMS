<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    record:           { type: Object, required: true },
    documentsEnabled: { type: Boolean, default: false },
    documents:        { type: Array, default: () => [] },
});

function destroy() {
    if (confirm('Delete this training record? This cannot be undone.')) {
        router.delete(route('admin.training-records.destroy', props.record.id));
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
    entity_type: 'TrainingRecord',
    entity_id:   props.record.id,
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
            docUploadForm.entity_type = 'TrainingRecord';
            docUploadForm.entity_id   = props.record.id;
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
    <Head title="Training Record" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Training Record
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-6">
                <Breadcrumb :items="[
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Staff Training', href: route('admin.training-records.index') },
                    { label: 'Record Detail' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <!-- Record Details Card -->
                <Card>
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ record.training_course?.name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ record.employee?.first_name }} {{ record.employee?.last_name }}
                                ({{ record.employee?.employee_id }})
                            </p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <Link
                                :href="route('admin.training-records.edit', record.id)"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700 transition-colors"
                            >Edit</Link>
                            <button
                                @click="destroy"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                            >Delete</button>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date Completed</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ record.date_completed?.substring(0, 10) || '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Trainer</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ record.trainer_name }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Employee</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                <Link
                                    :href="route('admin.employees.show', record.employee_id)"
                                    class="text-primary-600 dark:text-primary-400 hover:underline"
                                >
                                    {{ record.employee?.first_name }} {{ record.employee?.last_name }}
                                    ({{ record.employee?.employee_id }})
                                </Link>
                            </dd>
                        </div>
                        <div v-if="record.notes" class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ record.notes }}</dd>
                        </div>
                    </dl>
                </Card>

                <!-- Documents Card -->
                <Card v-if="documentsEnabled">
                    <div class="mb-4 flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Documents</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Certificates or sign-in sheets for this training</p>
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
                                <input v-model="docUploadForm.category" type="text" maxlength="100" placeholder="e.g. Certificate, Sign-in Sheet"
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
                            No documents uploaded for this training record.
                        </p>
                    </div>
                </Card>

                <!-- Back link -->
                <div class="mt-2">
                    <Link
                        :href="route('admin.training-records.index')"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                    >&larr; Back to Training Records</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
