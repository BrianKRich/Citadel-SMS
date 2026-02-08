<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import ColorPicker from '@/Components/Form/ColorPicker.vue';
import ThemePreview from '@/Components/Theme/ThemePreview.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    theme: Object,
});

const form = useForm({
    primary_color: props.theme.primary_color,
    secondary_color: props.theme.secondary_color,
    accent_color: props.theme.accent_color,
    background_color: props.theme.background_color,
    text_color: props.theme.text_color,
});

const submit = () => {
    form.post(route('admin.theme.update'), {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
        },
    });
};
</script>

<template>
    <Head title="Theme Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Theme Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card>
                    <PageHeader
                        title="Customize Your Theme"
                        description="Adjust the color scheme to match your brand. Changes will apply globally across the application."
                    />

                    <form @submit.prevent="submit" class="mt-6">
                        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                            <!-- Left Column - Color Pickers -->
                            <div class="space-y-6">
                                <h4 class="text-base font-semibold text-gray-900">Color Settings</h4>

                                <ColorPicker
                                    v-model="form.primary_color"
                                    label="Primary Color"
                                    :error="form.errors.primary_color"
                                />

                                <ColorPicker
                                    v-model="form.secondary_color"
                                    label="Secondary Color"
                                    :error="form.errors.secondary_color"
                                />

                                <ColorPicker
                                    v-model="form.accent_color"
                                    label="Accent Color"
                                    :error="form.errors.accent_color"
                                />

                                <ColorPicker
                                    v-model="form.background_color"
                                    label="Background Color"
                                    :error="form.errors.background_color"
                                />

                                <ColorPicker
                                    v-model="form.text_color"
                                    label="Text Color"
                                    :error="form.errors.text_color"
                                />
                            </div>

                            <!-- Right Column - Preview -->
                            <div class="space-y-6">
                                <h4 class="text-base font-semibold text-gray-900">Live Preview</h4>

                                <ThemePreview
                                    :primary-color="form.primary_color"
                                    :secondary-color="form.secondary_color"
                                    :accent-color="form.accent_color"
                                    :background-color="form.background_color"
                                    :text-color="form.text_color"
                                />
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6">
                            <div>
                                <p v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                    ✓ Theme saved successfully!
                                </p>
                            </div>

                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Theme' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
