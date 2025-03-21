<template>
    <Head title="Edit User" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <GoBack :href="route('admin.users.index')" class="mr-2" />
                Edit User
            </h1>

            <form @submit.prevent="submit" class="bg-white p-6 shadow-lg rounded-lg space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input v-model="form.name" id="name" type="text"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="form.email" id="email" type="email"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <!-- Role & Status -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select v-model="form.role" id="role"
                            :disabled="user.id === authUser.id"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                        <p v-if="form.errors.role" class="text-red-600 text-sm mt-1">{{ form.errors.role }}</p>
                        <p v-if="user.id === authUser.id" class="text-sm text-gray-500 mt-1">You cannot change your own role.</p>
                    </div>

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
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition">
                        Save Changes
                    </button>
                    <Link :href="route('admin.users.index')"
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
const user = props.user;

const authUser = props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    role: user.roles[0]?.name || 'user',
    status: user.status,
});

const submit = () => {
    form.put(route('admin.users.update', user.id));
};
</script>