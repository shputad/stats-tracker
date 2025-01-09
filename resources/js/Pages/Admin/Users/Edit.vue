<template>
    <Head title="Edit User" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Edit User</h1>
        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Name</label>
                <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</span>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input v-model="form.email" type="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.email" class="text-red-600 text-sm">{{ form.errors.email }}</span>
            </div>
            <div class="mb-4">
                <label for="role" class="block text-gray-700">Role</label>
                <select v-model="form.role" id="role" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <span v-if="form.errors.role" class="text-red-600 text-sm">{{ form.errors.role }}</span>
            </div>
            <div class="mb-4">
                <label for="status" class="block text-gray-700">Status</label>
                <select v-model="form.status" id="status" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <span v-if="form.errors.status" class="text-red-600 text-sm">{{ form.errors.status }}</span>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
            <Link href="/admin/users" class="px-4 py-2.5 bg-gray-600 text-white rounded-md ml-3" role="button">
                Cancel
            </Link>
        </form>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';

const { props } = usePage();
const user = props.user;

const form = useForm({
    name: user.name,
    email: user.email,
    role: user.roles[0]?.name || 'user',
    status: user.status
});

const submit = () => {
    form.put(route('admin.users.update', user.id));
};
</script>
