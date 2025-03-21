<template>
    <form @submit.prevent="updatePassword" class="space-y-6">
        <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
            <input v-model="form.current_password" ref="currentPasswordInput" id="current_password" type="password"
                autocomplete="current-password"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
            <p v-if="form.errors.current_password" class="text-sm text-red-600 mt-1">{{ form.errors.current_password }}
            </p>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input v-model="form.password" ref="passwordInput" id="password" type="password" autocomplete="new-password"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
            <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input v-model="form.password_confirmation" id="password_confirmation" type="password"
                autocomplete="new-password"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
            <p v-if="form.errors.password_confirmation" class="text-sm text-red-600 mt-1">{{
                form.errors.password_confirmation }}</p>
        </div>

        <div class="pt-2">
            <button type="submit" :disabled="form.processing"
                class="px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md hover:bg-blue-700 transition">
                Update
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Password updated successfully');
        },
        onError: () => {
            toast.error('There was a problem updating the password');
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>