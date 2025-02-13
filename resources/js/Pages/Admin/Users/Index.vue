<template>
    <Head title="Users" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Responsive Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Users</h1>
                <Link :href="route('admin.users.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Create User
                </Link>
            </div>

            <!-- Table Container for Responsive Scrolling -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 p-2 text-left">Name</th>
                            <th class="border border-gray-300 p-2 text-left">Email</th>
                            <th class="border border-gray-300 p-2 text-center">Role</th>
                            <th class="border border-gray-300 p-2 text-center">Status</th>
                            <th class="border border-gray-300 p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                            <td class="border border-gray-300 p-2">{{ user.name }}</td>
                            <td class="border border-gray-300 p-2">{{ user.email }}</td>
                            <td class="border border-gray-300 p-2 text-center">
                                <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${user.roles[0] && user.roles[0].name == 'admin' ? 'bg-blue-600' : 'bg-gray-600'
                                    }`">
                                    {{ user.roles[0] ? (String(user.roles[0].name).charAt(0).toUpperCase() +
                                        String(user.roles[0].name).slice(1)) : 'No Role' }}
                                </span>
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${user.status == 'active' ? 'bg-green-600' : 'bg-red-600'
                                    }`">
                                    {{ String(user.status).charAt(0).toUpperCase() + String(user.status).slice(1) }}
                                </span>
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                <Link :href="route('admin.users.edit', user.id)" class="text-blue-600 hover:underline">
                                <i class="fas fa-pencil text-sm"></i>
                                </Link>
                                <button @click="deleteUser(user.id)" class="text-red-600 hover:underline ml-2">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const { props } = usePage();
const users = ref(props.users);

const deleteUser = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this user?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb', // Tailwind blue-600
        cancelButtonColor: '#6b7280',  // Tailwind gray-600
    });

    if (result.isConfirmed) {
        await router.delete(route('admin.users.destroy', id), {
            onSuccess: () => {
                users.value = users.value.filter(user => user.id !== id);
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The user has been deleted.',
                    icon: 'success',
                    confirmButtonColor: '#2563eb',
                });
            },
        });
    }
};
</script>