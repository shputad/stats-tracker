<template>
    <Head title="Command Builder" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">
            <GoBack :href="`/admin/tools`"></GoBack> Command Builder
        </h1>

        <form @submit.prevent="submit">
            <!-- Custom Command -->
            <div class="mb-4">
                <label for="custom_command" class="block text-gray-700">Custom Command</label>
                <textarea
                    id="custom_command"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                    v-model="form.custom_command"
                ></textarea>
            </div>

            <!-- File URL -->
            <div class="mb-4">
                <label for="file_url" class="block text-gray-700">File URL</label>
                <input
                    v-model="form.file_url"
                    type="text"
                    id="file_url"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                    placeholder="Enter the file URL"
                />
                <span v-if="form.errors.file_url" class="text-red-600 text-sm">
                    {{ form.errors.file_url }}
                </span>
            </div>

            <!-- Obfuscation Technique -->
            <div class="mb-4">
                <label for="obfuscation_technique" class="block text-gray-700">Obfuscation Technique</label>
                <select
                    id="obfuscation_technique"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                    v-model="form.obfuscation_technique"
                >
                    <option value="">Select a technique</option>
                    <option value="base64">Base64</option>
                    <option value="rot13">ROT13</option>
                </select>
                <span v-if="form.errors.obfuscation_technique" class="text-red-600 text-sm">
                    {{ form.errors.obfuscation_technique }}
                </span>
            </div>

            <!-- Additional Options -->
            <div class="mb-4">
                <label class="block text-gray-700">Options</label>
                <div>
                    <label class="inline-flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.obfuscate_url"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200"
                        />
                        <span class="ml-2">Obfuscate URL</span>
                    </label>
                </div>
                <div>
                    <label class="inline-flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.copy_to_clipboard"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200"
                        />
                        <span class="ml-2">Copy to Clipboard</span>
                    </label>
                </div>
            </div>

            <!-- Generated Command -->
            <div class="mb-4">
                <label for="generated_command" class="block text-gray-700">Generated Command</label>
                <textarea
                    id="generated_command"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                    readonly
                    :value="generatedCommand"
                ></textarea>
            </div>

            <!-- Test Output -->
            <div v-if="testOutput" class="mb-4 p-4 bg-blue-100 text-blue-700 rounded">
                <h2 class="font-bold mb-2">Test Output:</h2>
                <pre class="max-w-full whitespace-pre-wrap break-all" style="word-break: break-all; overflow-wrap: anywhere;">{{ testOutput }}</pre>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Build
                </button>
                <button
                    type="button"
                    @click="exportCommand"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                >
                    Export to File
                </button>
                <button
                    type="button"
                    @click="testCommand"
                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
                >
                    Test Command
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';

// Initialize toast
const toast = useToast();

// Props from server
const { props } = usePage();

// Form data
const form = useForm({
    custom_command: props.custom_command || '',
    file_url: props.file_url || '',
    obfuscation_technique: props.obfuscation_technique || '',
    obfuscate_url: props.obfuscate_url || false,
    copy_to_clipboard: props.copy_to_clipboard !== undefined ? props.copy_to_clipboard : true
});

// Flash messages
const flash = props.flash || {};

// We use refs to store the generated command and test output
const generatedCommand = ref(flash.command || '');
const testOutput = ref(flash.test_output || '');

/**
 * Submit form to generate the command.
 */
const submit = () => {
    form.post(route('admin.tools.commandbuilder.generate'), {
        preserveScroll: true,
        onSuccess: (page) => {
            generatedCommand.value = page.props.flash.command || '';

            toast.success("Command generated!");

            // Copy the command to clipboard if the option is enabled
            if (form.copy_to_clipboard && generatedCommand.value) {
                navigator.clipboard.writeText(generatedCommand.value);

                toast.success("Command copied to clipboard!");
            }
        },
        onError: () => {
            toast.error("Error generating the command.");
        }
    });
};

/**
 * Export command to a file using axios (to handle the blob response)
 */
 const exportCommand = async () => {
    if (!generatedCommand.value) {
        toast.error('No command generated.');
        return;
    }
    try {
        const response = await axios.post(
            route('admin.tools.commandbuilder.export'),
            { command: generatedCommand.value },
            { responseType: 'blob' }
        );
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'command.txt');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        toast.success("Command exported successfully!");
    } catch (error) {
        console.error(error);
        toast.error("Error exporting the command.");
    }
};

/**
 * Test the generated command in simulation mode.
 */
const testCommand = () => {
    if (!generatedCommand.value) {
        toast.error('No command generated to test.');
        return;
    }
    Inertia.post(
        route('admin.tools.commandbuilder.test'),
        { command: generatedCommand.value, technique: form.obfuscation_technique },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                testOutput.value = page.props.flash.test_output || '';
                console.log(testOutput)
                toast.success("Command tested successfully!");
            },
            onError: () => {
                toast.error("Error testing the command.");
            }
        }
    );
};
</script>
