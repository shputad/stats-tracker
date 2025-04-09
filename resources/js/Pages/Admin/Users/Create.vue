<template>
    <Head title="Create User" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <GoBack :href="route('admin.users.index')" class="mr-2" />
                Create User
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

                <!-- Password & Confirm Password -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input v-model="form.password" id="password" type="password"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex-1">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input v-model="form.password_confirmation" id="password_confirmation" type="password"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.password_confirmation" class="text-red-600 text-sm mt-1">{{ form.errors.password_confirmation }}</p>
                    </div>
                </div>

                <!-- Role & Status -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select v-model="form.role" id="role"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                        <p v-if="form.errors.role" class="text-red-600 text-sm mt-1">{{ form.errors.role }}</p>
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

                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Link -->
                    <div class="flex-1">
                        <label for="link_id" class="block text-sm font-medium text-gray-700">Link</label>
                        <select v-model="form.link_id" id="link_id"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option disabled value="">Select Link</option>
                            <option v-for="link in links" :key="link.id" :value="link.id">{{ link.name }}</option>
                        </select>
                        <p v-if="form.errors.link_id" class="text-red-600 text-sm mt-1">{{ form.errors.link_id }}</p>
                    </div>

                    <!-- Profit Percentage -->
                    <div class="flex-1">
                        <label for="profit_percentage" class="block text-sm font-medium text-gray-700">Profit Percentage</label>
                        <input v-model="form.profit_percentage" id="profit_percentage" type="number"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.profit_percentage" class="text-red-600 text-sm mt-1">{{ form.errors.profit_percentage }}</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Min Daily Profit Cap -->
                    <div class="flex-1">
                        <label for="min_daily_profit_cap" class="block text-sm font-medium text-gray-700">
                            Min Daily Profit Cap (optional)
                        </label>
                        <input v-model="form.min_daily_profit_cap" id="min_daily_profit_cap" type="number"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.min_daily_profit_cap" class="text-red-600 text-sm mt-1">
                            {{ form.errors.min_daily_profit_cap }}
                        </p>
                    </div>

                    <!-- Special Profit Percentage (shown only if cap is set) -->
                    <div v-if="form.min_daily_profit_cap" class="flex-1">
                        <label for="special_profit_percentage" class="block text-sm font-medium text-gray-700">
                            Special Profit % (if cap not met)
                        </label>
                        <input v-model="form.special_profit_percentage" id="special_profit_percentage" type="number"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.special_profit_percentage" class="text-red-600 text-sm mt-1">
                            {{ form.errors.special_profit_percentage }}
                        </p>
                    </div>
                    <div v-else class="flex-1"></div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition">
                        Create
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
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({ links: Array });

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
    status: '',
    link_id: '',
    profit_percentage: '',
    min_daily_profit_cap: '',
    special_profit_percentage: '',
});

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>