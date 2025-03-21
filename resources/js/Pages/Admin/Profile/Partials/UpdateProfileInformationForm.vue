<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input v-model="form.name" type="text" id="name"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
            <p v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input v-model="form.email" type="email" id="email"
                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
            <p v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</p>
        </div>

        <div v-if="props.mustVerifyEmail && user.email_verified_at === null">
            <p class="text-sm text-gray-600">
                Your email address is unverified.
                <Link :href="route('verification.send')" method="post" as="button"
                      class="underline text-sm text-blue-600 hover:text-blue-800 ml-1">
                    Click here to re-send verification email.
                </Link>
            </p>
            <p v-if="props.status === 'verification-link-sent'"
               class="mt-2 text-sm text-green-600">
                A new verification link has been sent to your email address.
            </p>
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
import { useForm, usePage, Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();
const user = usePage().props.auth.user;

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('admin.profile.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Profile updated successfully!'),
        onError: () => toast.error('Failed to update profile.'),
    });
};
</script>