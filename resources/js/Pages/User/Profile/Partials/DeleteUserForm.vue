<template>
    <section>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Delete Account</h2>
        <p class="text-sm text-gray-600 mb-4">
            Once your account is deleted, all resources and data will be permanently deleted.
            Please download any information you want to keep before proceeding.
        </p>

        <button
            type="button"
            @click="confirmDeletion"
            class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700 transition"
        >
            Delete Account
        </button>

        <!-- Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="confirming"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
            >
                <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        Are you sure you want to delete your account?
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Please enter your password to confirm this action.
                    </p>

                    <input
                        type="password"
                        ref="passwordInput"
                        v-model="form.password"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-red-500 focus:border-red-500"
                    />
                    <p v-if="form.errors.password" class="text-red-600 text-sm mt-1">
                        {{ form.errors.password }}
                    </p>

                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            class="px-4 py-2 text-sm bg-gray-200 rounded-md hover:bg-gray-300"
                            @click="closeModal"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="px-4 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700"
                            :disabled="form.processing"
                            @click="deleteUser"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </section>
</template>
<script setup>
import { ref, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const confirming = ref(false);
const passwordInput = ref(null);
const toast = useToast();

const form = useForm({
    password: '',
});

const confirmDeletion = () => {
    confirming.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const closeModal = () => {
    confirming.value = false;
    form.reset();
    form.clearErrors();
};

const deleteUser = async () => {
    form.delete(route('user.profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            toast.success('Your account has been deleted.');
        },
        onError: () => {
            toast.error('Please enter a valid password.');
            passwordInput.value?.focus();
        },
        onFinish: () => form.reset(),
    });
};
</script>