<template>
    <Head title="Lander Templates" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.tools.index')" /> Lander Templates
            </h1>

            <!-- Upload Form -->
            <form @submit.prevent="uploadTemplate" class="bg-white p-6 shadow rounded mb-6">
                <div class="mb-4">
                    <label for="templateName" class="block text-gray-700 font-medium mb-2">
                        Template Name
                    </label>
                    <input type="text" id="templateName" v-model="templateName" placeholder="Enter template name"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                </div>
                <div class="mb-4">
                    <label for="templateFile" class="block text-gray-700 font-medium mb-2">
                        Upload Template
                    </label>
                    <input type="file" id="templateFile" @change="handleFileChange" accept=".html"
                        class="block w-full" />
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Upload Template
                </button>
            </form>

            <!-- Templates List -->
            <div v-if="templates.length > 0">
                <h2 class="text-xl font-semibold mb-4">Stored Templates</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="template in templates" :key="template"
                        class="bg-white p-4 shadow rounded hover:shadow-xl transition-all duration-300">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold">{{ template }}</h3>
                            <button @click="deleteTemplate(template)" class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </div>
                        <button @click="previewTemplate(template)"
                            class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Preview
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="text-center text-gray-500 p-4">
                No templates uploaded.
            </div>

            <!-- Preview Modal -->
            <div v-if="showPreview" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <div class="bg-white p-6 rounded-lg max-w-5xl w-full overflow-hidden max-h-full">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Template Preview: {{ previewTemplateName }}</h3>
                        <button @click="closePreview"
                            class="text-gray-600 hover:text-gray-800 text-2xl">&times;</button>
                    </div>
                    <!-- Iframe for previewing the template -->
                    <iframe :srcdoc="previewContent" class="w-full h-[500px] border border-gray-300 rounded"
                        frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import GoBack from '@/Components/GoBack.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const toast = useToast();
const { props } = usePage();

const templates = ref([]);
const templateName = ref('');
const selectedFile = ref(null);
const showPreview = ref(false);
const previewContent = ref('');
const previewTemplateName = ref('');

// Load stored templates from the API
const loadTemplates = async () => {
    try {
        const response = await axios.get(route('admin.tools.landertemplates.json'));
        templates.value = response.data.templates || [];
    } catch (error) {
        toast.error('Failed to load templates.');
    }
};

loadTemplates();

const handleFileChange = (e) => {
    selectedFile.value = e.target.files[0];
};

const uploadTemplate = async () => {
    if (!templateName.value) {
        toast.error('Please enter a template name.');
        return;
    }
    if (!selectedFile.value) {
        toast.error('Please select a file.');
        return;
    }
    const file = selectedFile.value;
    const reader = new FileReader();
    reader.onload = async (e) => {
        const content = e.target.result;
        const formData = new FormData();
        formData.append('name', templateName.value);
        formData.append('content', content);
        try {
            await axios.post(route('admin.tools.landertemplates.store'), formData);
            toast.success('Template uploaded successfully!');
            loadTemplates();
        } catch (error) {
            if (error.response && error.response.data && error.response.data.message) {
                toast.error(error.response.data.message);
            } else {
                toast.error('Template upload failed.');
            }
        }
    };
    reader.onerror = () => {
        toast.error('Failed to read the file.');
    };
    reader.readAsText(file);
};

const deleteTemplate = async (filename) => {
    try {
        await axios.delete(route('admin.tools.landertemplates.destroy', { filename }));
        toast.success('Template deleted successfully!');
        loadTemplates();
    } catch (error) {
        toast.error('Failed to delete template.');
    }
};

const previewTemplate = async (filename) => {
    try {
        const response = await axios.get(route('admin.tools.landertemplates.preview', { filename }), {
            headers: { 'Accept': 'application/json' },
        });
        previewContent.value = response.data.content;
        previewTemplateName.value = filename;
        showPreview.value = true;
    } catch (error) {
        toast.error('Failed to load preview.');
    }
};

const closePreview = () => {
    showPreview.value = false;
};
</script>

<style scoped>
/* iOS-style mute toggle (if needed in this component, else remove) */
/* You can keep your existing styles if they are reused in other components */
</style>