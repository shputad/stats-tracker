<template>
    <Head title="Edit Channel" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <GoBack :href="route('admin.network-channels.index')" class="mr-2" />
                Edit Network Channel
            </h1>

            <form @submit.prevent="submit" class="bg-white p-6 shadow-lg rounded-lg space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input v-model="form.name" type="text" id="name"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700">Website</label>
                    <input v-model="form.website" type="url" id="website"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.website" class="text-red-600 text-sm mt-1">{{ form.errors.website }}</p>
                </div>

                <!-- Has API & Status -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Has API -->
                    <div class="flex-1">
                        <label for="has_api" class="block text-sm font-medium text-gray-700">Has API</label>
                        <select v-model="form.has_api" id="has_api"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <p v-if="form.errors.has_api" class="text-red-600 text-sm mt-1">{{ form.errors.has_api }}</p>
                    </div>

                    <!-- Status -->
                    <div class="flex-1">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select v-model="form.status" id="status"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <p v-if="form.errors.status" class="text-red-600 text-sm mt-1">{{ form.errors.status }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md hover:bg-blue-700 transition">
                        Save Changes
                    </button>
                    <Link :href="route('admin.network-channels.index')"
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
const channel = props.channel;

const form = useForm({
    name: channel.name,
    website: channel.website,
    has_api: channel.has_api.toString(),
    status: channel.status,
});

const submit = () => {
    form.put(route('admin.network-channels.update', channel.id));
};
</script>