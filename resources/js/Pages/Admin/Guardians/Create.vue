<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Alert from '@/Components/UI/Alert.vue';
import Breadcrumb from '@/Components/UI/Breadcrumb.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    preselectedStudentId: { type: Number, default: null },
    preselectedStudent:   { type: Object, default: null },
});

const form = useForm({
    first_name: '',
    last_name: '',
    relationship: '',
    email: '',
    phone_area_code: '',
    phone: '',
    address: '',
    occupation: '',
    students: [],
    from_student_id: props.preselectedStudentId ?? '',
});

// Student search
const studentSearch   = ref('');
const studentResults  = ref([]);
const selectedStudents = ref([]); // { id, student_id, first_name, last_name, is_primary }
let searchTimer = null;

onMounted(() => {
    if (props.preselectedStudent) {
        addStudent(props.preselectedStudent);
    }
});

async function searchStudents() {
    clearTimeout(searchTimer);
    const q = studentSearch.value.trim();
    if (!q) { studentResults.value = []; return; }
    searchTimer = setTimeout(async () => {
        const res = await fetch(route('api.students.search') + '?q=' + encodeURIComponent(q));
        const data = await res.json();
        studentResults.value = data.filter(s => !selectedStudents.value.some(sel => sel.id === s.id));
    }, 250);
}

function addStudent(student) {
    if (selectedStudents.value.some(s => s.id === student.id)) return;
    selectedStudents.value.push({ ...student, is_primary: false });
    syncFormStudents();
    studentSearch.value = '';
    studentResults.value = [];
}

function removeStudent(id) {
    selectedStudents.value = selectedStudents.value.filter(s => s.id !== id);
    syncFormStudents();
}

function syncFormStudents() {
    form.students = selectedStudents.value.map(s => ({ id: s.id, is_primary: s.is_primary }));
}

function toggleStudent(student) {
    const idx = form.students.findIndex(s => s.id === student.id);
    if (idx >= 0) {
        form.students.splice(idx, 1);
    } else {
        form.students.push({ id: student.id, is_primary: false });
    }
}

function isSelected(studentId) {
    return form.students.some(s => s.id === studentId);
}

function getStudentEntry(studentId) {
    return form.students.find(s => s.id === studentId);
}

function submit() {
    form.post(route('admin.guardians.store'));
}
</script>

<template>
    <Head title="Add Guardian" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Add Guardian</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <Breadcrumb :items="preselectedStudentId ? [
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Students', href: route('admin.students.index') },
                    { label: 'Student', href: route('admin.students.show', preselectedStudentId) },
                    { label: 'Add Guardian' },
                ] : [
                    { label: 'Dashboard', href: route('admin.dashboard') },
                    { label: 'Students', href: route('admin.students.index') },
                    { label: 'Guardians', href: route('admin.guardians.index') },
                    { label: 'Add Guardian' },
                ]" />

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <Card>
                    <PageHeader title="Add Guardian" />

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Section 1: Personal Info -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.first_name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.last_name }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Relationship <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.relationship"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    >
                                        <option value="" disabled>Select relationship</option>
                                        <option value="mother">Mother</option>
                                        <option value="father">Father</option>
                                        <option value="guardian">Guardian</option>
                                        <option value="grandparent">Grandparent</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <p v-if="form.errors.relationship" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.relationship }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Contact -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Contact Information</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
                                </div>

                                <!-- Phone: area code + number side-by-side -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                    <div class="grid grid-cols-4 gap-2 mt-1">
                                        <div class="col-span-1">
                                            <input
                                                v-model="form.phone_area_code"
                                                type="text"
                                                maxlength="3"
                                                placeholder="Area"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </div>
                                        <div class="col-span-3">
                                            <input
                                                v-model="form.phone"
                                                type="text"
                                                maxlength="7"
                                                placeholder="Number"
                                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </div>
                                    </div>
                                    <p v-if="form.errors.phone_area_code" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone_area_code }}</p>
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                    <textarea
                                        v-model="form.address"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.address" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.address }}</p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Occupation</label>
                                    <input
                                        v-model="form.occupation"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                    <p v-if="form.errors.occupation" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.occupation }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Linked Students -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-4">Linked Students</h3>

                            <!-- Search box -->
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Students</label>
                                <input
                                    v-model="studentSearch"
                                    @input="searchStudents"
                                    type="text"
                                    placeholder="Search by name or student ID…"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                />
                                <!-- Dropdown results -->
                                <ul v-if="studentResults.length" class="absolute z-10 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg max-h-48 overflow-y-auto">
                                    <li
                                        v-for="s in studentResults"
                                        :key="s.id"
                                        @click="addStudent(s)"
                                        class="cursor-pointer px-4 py-2 text-sm text-gray-900 dark:text-gray-100 hover:bg-primary-50 dark:hover:bg-primary-900/20"
                                    >
                                        {{ s.first_name }} {{ s.last_name }}
                                        <span class="ml-1 text-xs text-gray-400">({{ s.student_id }})</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Selected students list -->
                            <div v-if="selectedStudents.length" class="mt-3 space-y-2">
                                <div
                                    v-for="s in selectedStudents"
                                    :key="s.id"
                                    class="flex items-center justify-between rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-3 py-2"
                                >
                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ s.first_name }} {{ s.last_name }}
                                        <span class="ml-1 text-xs text-gray-400">({{ s.student_id }})</span>
                                    </span>
                                    <div class="flex items-center gap-3">
                                        <label class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                v-model="s.is_primary"
                                                @change="syncFormStudents"
                                                class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:ring-primary-500"
                                            />
                                            Primary
                                        </label>
                                        <button
                                            type="button"
                                            @click="removeStudent(s.id)"
                                            class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                        >Remove</button>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="mt-2 text-xs text-gray-500 dark:text-gray-400">No students linked yet.</p>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Optionally mark one linked student as the primary.
                            </p>
                            <p v-if="form.errors.students" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.students }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                :href="preselectedStudentId ? route('admin.students.show', preselectedStudentId) : route('admin.guardians.index')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                            >
                                &larr; {{ preselectedStudentId ? 'Back to Student' : 'Back to Guardians' }}
                            </Link>
                            <PrimaryButton type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Add Guardian' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
