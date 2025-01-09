<template>
    <Head title="Users" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Users</h1>
        <Link :href="route('admin.users.create')" class="mb-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add User</Link>
        <table class="min-w-full border-collapse border border-gray-200 mt-4">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">Name</th>
                    <th class="border border-gray-300 p-2">Email</th>
                    <th class="border border-gray-300 p-2">Role</th>
                    <th class="border border-gray-300 p-2">Status</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id">
                    <td class="border border-gray-300 p-2">{{ user.name }}</td>
                    <td class="border border-gray-300 p-2">{{ user.email }}</td>
                    <td class="border border-gray-300 p-2 text-center">
                        <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${user.roles[0].name == 'admin' ? 'bg-blue-600' : 'bg-gray-600'}`">{{ user.roles[0] ? String(user.roles[0].name).charAt(0).toUpperCase() + String(user.roles[0].name).slice(1) : 'No Role' }}</span>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${user.status == 'active' ? 'bg-green-600' : 'bg-red-600'}`">{{ String(user.status).charAt(0).toUpperCase() + String(user.status).slice(1) }}</span>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <Link :href="route('admin.users.edit', user.id)" class="text-blue-600 underline">
                            <i class="fas fa-pencil text-sm"></i>
                        </Link>
                        <button @click="deleteUser(user.id)" class="text-red-600 underline ml-2">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

const { props } = usePage();
const users = props.users;

const deleteUser = async (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        await router.delete(route('admin.users.destroy', id), {
            onSuccess: () => {
                users.value = users.value.filter(user => user.id !== id);
            },
        });
    }
};
</script>
