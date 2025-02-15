<template>
    <Head title="Lander Builder" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.tools.index')" /> Lander Builder
            </h1>
            <form @submit.prevent="buildLander" class="bg-white p-6 shadow rounded">
                <!-- Template Selection -->
                <div class="mb-4">
                    <label for="templateSelect" class="block text-gray-700 font-medium">
                        Select Template
                    </label>
                    <select id="templateSelect" v-model="selectedTemplate"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option value="">-- Choose Existing Template --</option>
                        <option v-for="template in templates" :key="template" :value="template">
                            {{ template }}
                        </option>
                        <option value="custom">Custom Template</option>
                    </select>
                </div>

                <!-- Custom Template Editor (if 'custom' selected) -->
                <div v-if="selectedTemplate === 'custom'" class="mb-4">
                    <label for="customTemplateName" class="block text-gray-700 font-medium">
                        Template Name
                    </label>
                    <input id="customTemplateName" v-model="customTemplateName" type="text"
                        placeholder="Enter template name"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <label for="customTemplateContent" class="block text-gray-700 font-medium mt-4">
                        Template Content (HTML with placeholder <code v-pre>{commandPlaceholder}</code>)
                    </label>
                    <textarea id="customTemplateContent" v-model="customTemplateContent" rows="10"
                        placeholder="Enter HTML template"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 font-mono"></textarea>
                    <button type="button" @click="saveCustomTemplate"
                        class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save Template
                    </button>
                </div>

                <!-- Command Input -->
                <div class="mb-4">
                    <label for="command" class="block text-gray-700 font-medium">
                        Command
                    </label>
                    <textarea id="command" v-model="command" rows="3" placeholder="Enter command"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
                </div>

                <!-- Buttons: Preview & Download -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="button" @click="previewLander"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Preview
                    </button>
                    <button type="button" @click="downloadLander"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        Download
                    </button>
                </div>
            </form>

            <!-- Preview Modal -->
            <div v-if="showPreview" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white p-6 rounded-lg max-w-5xl w-full overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Preview</h3>
                        <button @click="closePreview" class="text-gray-600 hover:text-gray-800 text-2xl">
                            &times;
                        </button>
                    </div>
                    <iframe :srcdoc="builtLander" class="w-full h-[500px] border border-gray-300 rounded"
                        frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const toast = useToast();
const { props } = usePage();

// Load stored templates from API endpoint
const templates = ref([]);
const loadTemplates = async () => {
    try {
        const response = await axios.get(route('admin.tools.landertemplates.json'), {
            headers: { 'Accept': 'application/json' },
        });
        templates.value = response.data.templates || [];
    } catch (error) {
        toast.error('Failed to load templates.');
    }
};
loadTemplates();

// Form fields
const selectedTemplate = ref('');
const customTemplateName = ref('');
const customTemplateContent = ref('');
const command = ref('');
const builtLander = ref('');
const showPreview = ref(false);

// Save custom template to storage via API endpoint
const saveCustomTemplate = async () => {
    if (!customTemplateName.value || !customTemplateContent.value) {
        toast.error('Please provide both template name and content.');
        return;
    }
    try {
        await axios.post(route('admin.tools.landertemplates.store'), {
            name: customTemplateName.value,
            content: customTemplateContent.value,
        });
        toast.success('Template saved successfully!');
        loadTemplates();
        selectedTemplate.value = customTemplateName.value;
    } catch (error) {
        toast.error('Failed to save template.');
    }
};

// Build the lander content by replacing the placeholder {commandPlaceholder} with the command
const buildLanderContent = async () => {
    let templateContent = '';
    if (selectedTemplate.value && selectedTemplate.value !== 'custom') {
        try {
            const response = await axios.get(route('admin.tools.landertemplates.preview', { filename: selectedTemplate.value }), {
                headers: { 'Accept': 'application/json' },
            });
            templateContent = response.data.content;
        } catch (error) {
            toast.error('Failed to load template content.');
            return '';
        }
    } else if (selectedTemplate.value === 'custom') {
        templateContent = customTemplateContent.value;
    } else {
        toast.error('Please select a template.');
        return '';
    }
    // Replace the placeholder {commandPlaceholder} with the command value
    return templateContent.replace('{commandPlaceholder}', command.value);
};

const previewLander = async () => {
    const content = await buildLanderContent();
    if (content) {
        builtLander.value = content;
        showPreview.value = true;
    }
};

const downloadLander = async () => {
    const content = await buildLanderContent();
    if (!content) return;
    const result = await Swal.fire({
        title: 'Enter filename for download',
        input: 'text',
        inputLabel: 'Filename',
        inputValue: 'lander.html',
        showCancelButton: true,
        confirmButtonText: 'Download',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'Please enter a filename.';
            }
        },
    });
    if (result.isConfirmed && result.value) {
        const filename = result.value;
        const blob = new Blob([content], { type: 'text/html' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
};

const closePreview = () => {
    showPreview.value = false;
};

const buildLander = async () => {
    await previewLander();
};
</script>