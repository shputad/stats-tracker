<template>
    <Head title="Lander Builder" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">
            <GoBack :href="`/admin/tools`"></GoBack> Lander Builder
        </h1>

        <form @submit.prevent="submit">
            <!-- Buttons -->
            <div class="flex gap-4">
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Build
                </button>
                <button
                    type="button"
                    @click="previewLander"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                >
                    Preview
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import { Head, useForm, usePage } from '@inertiajs/vue3';

// Initialize toast
const toast = useToast();

// Props from server
const { props } = usePage();

// Form data
const form = useForm({
    
});

// Flash messages
const flash = props.flash || {};

/**
 * Submit form to generate the command.
 */
const submit = () => {
    
};

/**
 * Preview the lander.
 */
const previewLander = () => {

};
</script>
