<template>
    <Head title="Create User" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.users.index')" />
                Create User
            </h1>
            <form @submit.prevent="submit" class="bg-white p-6 shadow rounded">
                <!-- Name Field -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium">Name</label>
                    <input v-model="form.name" type="text" id="name"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <span v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</span>
                </div>
                <!-- Email Field -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium">Email</label>
                    <input v-model="form.email" type="email" id="email"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <span v-if="form.errors.email" class="text-red-600 text-sm">{{ form.errors.email }}</span>
                </div>
                <!-- Password and Confirm Password Fields in One Row -->
                <div class="mb-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label for="password" class="block text-gray-700 font-medium">Password</label>
                            <input v-model="form.password" type="password" id="password"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                            <span v-if="form.errors.password" class="text-red-600 text-sm">{{ form.errors.password
                                }}</span>
                        </div>
                        <div class="flex-1">
                            <label for="password_confirmation" class="block text-gray-700 font-medium">Confirm
                                Password</label>
                            <input v-model="form.password_confirmation" type="password" id="password_confirmation"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                            <span v-if="form.errors.password_confirmation" class="text-red-600 text-sm">{{
                                form.errors.password_confirmation }}</span>
                        </div>
                    </div>
                </div>
                <!-- Role and Status Fields in One Row -->
                <div class="mb-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label for="role" class="block text-gray-700 font-medium">Role</label>
                            <select v-model="form.role" id="role"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                            <span v-if="form.errors.role" class="text-red-600 text-sm">{{ form.errors.role }}</span>
                        </div>
                        <div class="flex-1">
                            <label for="status" class="block text-gray-700 font-medium">Status</label>
                            <select v-model="form.status" id="status"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <span v-if="form.errors.status" class="text-red-600 text-sm">{{ form.errors.status }}</span>
                        </div>
                    </div>
                </div>
                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Create
                    </button>
                    <Link :href="route('admin.users.index')"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700" role="button">
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
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
    status: '',
});

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>