<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import Card from '@/Components/UI/Card.vue';
import Alert from '@/Components/UI/Alert.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    fields: Array,
});

const entityOrder = ['Student', 'Employee', 'Course', 'Class', 'Enrollment'];

const grouped = computed(() => {
    const groups = {};
    for (const type of entityOrder) {
        const items = props.fields.filter(f => f.entity_type === type);
        if (items.length) groups[type] = items;
    }
    return groups;
});

const fieldTypeBadgeClass = {
    text:     'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    textarea: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    number:   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    date:     'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    boolean:  'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    select:   'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300',
};

function toggle(field) {
    router.post(route('admin.custom-fields.toggle', field.id), {}, { preserveScroll: true });
}

function destroy(field) {
    if (confirm(`Delete the "${field.label}" field? All saved values for this field will also be removed.`)) {
        router.delete(route('admin.custom-fields.destroy', field.id));
    }
}
</script>

<template>
    <Head title="Custom Fields" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Custom Fields</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">

                <div v-if="$page.props.flash?.success" class="mb-4">
                    <Alert type="success" :message="$page.props.flash.success" />
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4">
                    <Alert type="error" :message="$page.props.flash.error" />
                </div>

                <div class="flex items-center justify-between">
                    <PageHeader
                        title="Custom Fields"
                        description="Define additional fields for students, employees, courses, classes, and enrollments"
                    />
                    <Link
                        :href="route('admin.custom-fields.create')"
                        class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        + Add Custom Field
                    </Link>
                </div>

                <!-- Empty state -->
                <Card v-if="!fields.length">
                    <div class="py-12 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No custom fields defined yet.</p>
                        <Link
                            :href="route('admin.custom-fields.create')"
                            class="mt-4 inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-500"
                        >
                            Create your first custom field →
                        </Link>
                    </div>
                </Card>

                <!-- Grouped by entity -->
                <template v-for="(entityFields, entityType) in grouped" :key="entityType">
                    <Card>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            {{ entityType }} Fields
                        </h3>
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div
                                v-for="field in entityFields"
                                :key="field.id"
                                class="flex items-center justify-between py-3 gap-4"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="font-medium text-sm text-gray-900 dark:text-gray-100 truncate">
                                        {{ field.label }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ field.name }}</span>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <!-- Type badge -->
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', fieldTypeBadgeClass[field.field_type]]">
                                        {{ field.field_type }}
                                    </span>

                                    <!-- Active/Disabled badge + toggle -->
                                    <button
                                        type="button"
                                        @click="toggle(field)"
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium cursor-pointer transition-colors',
                                            field.is_active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 hover:bg-green-200'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ field.is_active ? 'Enabled' : 'Disabled' }}
                                    </button>

                                    <!-- Edit -->
                                    <Link
                                        :href="route('admin.custom-fields.edit', field.id)"
                                        class="text-sm text-primary-600 hover:text-primary-500 dark:text-primary-400"
                                    >
                                        Edit
                                    </Link>

                                    <!-- Delete -->
                                    <button
                                        type="button"
                                        @click="destroy(field)"
                                        class="text-sm text-red-600 hover:text-red-500 dark:text-red-400"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Card>
                </template>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
