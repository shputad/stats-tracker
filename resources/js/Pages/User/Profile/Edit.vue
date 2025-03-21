<template>
    <Head title="Edit Profile" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Profile</h1>

            <div class="space-y-10">
                <!-- Update Profile Info -->
                <div class="bg-white p-6 shadow-lg rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Profile Information</h2>
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Update Password -->
                <div class="bg-white p-6 shadow-lg rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Change Password</h2>
                    <UpdatePasswordForm />
                </div>

                <!-- Delete Account -->
                <div class="bg-white p-6 shadow-lg rounded-lg">
                    <h2 class="text-lg font-semibold text-red-700 mb-4">Danger Zone</h2>
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>