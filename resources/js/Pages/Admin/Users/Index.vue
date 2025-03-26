<template>
    <Head title="Users" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold mb-4 sm:mb-0 text-gray-800">Manage Users</h1>
                <Link :href="route('admin.users.create')"
                      class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md shadow hover:bg-blue-700 transition">
                    + Create User
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-center">Role</th>
                            <th class="p-3 text-center">Profit %</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-t hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800">{{ user.name }}</td>
                            <td class="p-3 text-gray-700">{{ user.email }}</td>
                            <td class="p-3 text-center">
                                <span :class="[
                                    'px-2 py-1 text-xs font-semibold rounded',
                                    user.roles[0]?.name === 'admin'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-gray-100 text-gray-700'
                                ]">
                                    {{ user.roles[0] ? user.roles[0].name.charAt(0).toUpperCase() + user.roles[0].name.slice(1) : 'No Role' }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span v-if="user.min_daily_profit_cap"
                                    class="relative group cursor-pointer">
                                    {{ user.profit_percentage }}%
                                    <i class="fas fa-info-circle text-gray-400 ml-1" :title="`If profit &lt; ${user.min_daily_profit_cap}, apply ${user.special_profit_percentage}%`"></i>
                                </span>
                                <span v-else class="text-gray-800 font-semibold">
                                    {{ user.profit_percentage }}%
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="[
                                    'px-2 py-1 rounded text-xs font-bold',
                                    user.status === 'active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                ]">
                                    {{ user.status.charAt(0).toUpperCase() + user.status.slice(1) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex justify-center space-x-3">
                                    <Link :href="route('admin.users.edit', user.id)"
                                          class="text-blue-600 hover:text-blue-800" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </Link>

                                    <button @click="impersonateUser(user.id)"
                                            class="text-orange-500 hover:text-orange-700" title="Login as user">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>

                                    <button @click="deleteUser(user.id)"
                                            :class="[
                                                'hover:text-red-800',
                                                user.id === props.auth.user.id ? 'text-red-200 cursor-not-allowed' : 'text-red-600'
                                            ]"
                                            title="Delete user"
                                            :disabled="user.id === props.auth.user.id">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td colspan="5" class="p-4 text-center text-gray-500">No users found.</td>
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

const impersonateUser = async (id) => {
    const result = await Swal.fire({
        title: 'Login as this user?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb',
    });

    if (result.isConfirmed) {
        await router.post(route('admin.users.impersonate', id));
    }
};

const deleteUser = async (id) => {
    if (id === props.auth.user.id) {
        Swal.fire({
            title: 'Action Not Allowed',
            text: 'You cannot delete your own account.',
            icon: 'warning',
            confirmButtonColor: '#2563eb',
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this user?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
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