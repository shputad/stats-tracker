<template>

    <Head title="Email Verification" />

    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 px-4">
        <div class="w-full max-w-md bg-white p-6 rounded-lg shadow">
            <h1 class="text-xl font-bold mb-4 text-center text-gray-800">Verify Your Email</h1>

            <p class="text-gray-700 text-sm mb-6 text-center">
                Thanks for signing up! Before getting started, please verify your email address by clicking the link we
                just emailed to you. If you didn’t receive the email, we’ll gladly send you another.
            </p>

            <div class="flex flex-col gap-4">
                <button @click="resendVerification"
                    class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-2 px-4 rounded-md"
                    :disabled="loading" :class="{ 'opacity-50': loading }">
                    Resend Verification Email
                </button>

                <button @click="logout"
                    class="w-full bg-gray-600 hover:bg-gray-700 transition text-white font-semibold py-2 px-4 rounded-md">
                    Log Out
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
const loading = ref(false);

const resendVerification = () => {
    loading.value = true;

    router.post(route('verification.send'), {}, {
        onSuccess: () => toast.success('Verification email sent successfully!'),
        onError: () => toast.error('Could not send verification email.'),
        onFinish: () => loading.value = false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>