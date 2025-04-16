<template>
    <Head title="Payload Builder" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.tools.index')" /> Payload Builder
            </h1>
            <form @submit.prevent="generatePayload" class="bg-white p-6 shadow rounded mb-6">
                <div class="mb-4">
                    <label for="payloadUrl" class="block text-gray-700 font-medium mb-2">
                        Payload URL
                    </label>
                    <input type="url" id="payloadUrl" v-model="form.payloadUrl" placeholder="Enter payload url"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <p class="text-sm text-yellow-700 mt-1">
                        ⚠️ Only direct `.exe` file URLs are supported. Archives like `.zip` are not supported here.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="flex-1">
                        <label for="payloadName" class="block text-gray-700 font-medium mb-2">
                            Payload Name
                        </label>
                        <input type="text" id="payloadName" v-model="form.payloadName" placeholder="Enter payload name"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    </div>
                    <div class="flex-1">
                        <label for="campaignId" class="block text-gray-700 font-medium mb-2">
                            Campaign ID
                        </label>
                        <input type="text" id="campaignId" v-model="form.campaignId" placeholder="Enter campaign id"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    </div>
                </div>
                <div class="mb-4">
                    <label for="buildTag" class="block text-gray-700 font-medium mb-2">
                        Build Tag
                    </label>
                    <input type="text" id="buildTag" v-model="form.buildTag" placeholder="Enter build tag"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Generate Payload
                    </button>
                    <button type="button" v-if="payloadGenerated" @click="exportPayload"
                        class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        Export Payload
                    </button>
                </div>
            </form>

            <!-- Payloads List -->
            <div v-if="payloads.length > 0">
                <h2 class="text-xl font-semibold mb-4">Stored Payloads</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="payload in payloads" :key="payload.filename"
                        class="relative bg-white p-4 shadow rounded hover:shadow-xl transition-all duration-300">
                        <span
                            class="absolute -top-2.5 -left-2 text-center bg-blue-600 text-white text-xs font-semibold px-2 py-0.5 rounded shadow">
                            {{ payload.build_tag }}
                        </span>

                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-base font-semibold">{{ payload.filename }}</h4>
                            <button @click="deletePayload(payload.build_tag, payload.filename)" class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </div>
                        <button @click="exportExistingPayload(payload.build_tag, payload.filename)"
                            class="px-3 py-1 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                            Export
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
                        @click="loadPayloads(link.url)"
                        v-html="link.label">
                    </button>
                </div>
            </div>
            <div v-else class="text-center text-gray-500 p-4">
                No payloads generated.
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const toast = useToast();
const { props } = usePage();

// Form
const form = useForm({
    payloadUrl: '',
    payloadName: '',
    campaignId: '',
    buildTag: ''
});

const payloads = ref([]);
const pagination = ref({ links: [] });
const filename = ref(props.flash.filename || '');
const buildTag = ref(props.flash.build_tag || '');
const payloadGenerated = ref(!!filename.value);

// Load stored payloads from the API
const loadPayloads = async (url = route('admin.tools.payloads.json')) => {
    try {
        const response = await axios.get(url);
        payloads.value = response.data.data || [];
        pagination.value = response.data;
    } catch (error) {
        toast.error('Failed to load payloads.');
    }
};

loadPayloads();

/**
 * Handle payload generation
 */
const generatePayload = () => {
    form.post(route('admin.tools.payloadbuilder.generate'), {
        preserveScroll: true,
        onSuccess: (page) => {
            toast.success('Payload generated successfully!');
            filename.value = page.props.flash.filename;
            buildTag.value = page.props.flash.build_tag;
            payloadGenerated.value = true;

            loadPayloads();
        },
        onError: (errors) => {
            if (errors.payloadName) {
                toast.error(errors.payloadName);
            } else if (errors.payloadError) {
                toast.error(errors.payloadError);
            } else {
                toast.error('Failed to generate payload.');
            }
        }
    });
};

/**
 * Download the generated payload file
 */
const exportPayload = async () => {
    if (!filename.value) {
        toast.error("No payload generated yet.");
        return;
    }

    try {
        const response = await axios.get(
            route('admin.tools.payloadbuilder.export') + `?build_tag=${buildTag.value}&filename=${filename.value}`,
            { responseType: 'blob' }
        );
        const blobUrl = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = blobUrl;
        link.setAttribute('download', filename.value);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        toast.success("Payload downloaded!");
    } catch (error) {
        console.error(error);
        toast.error("Error downloading the payload.");
    }
};

const exportExistingPayload = async (buildTag, filename) => {
    try {
        const response = await axios.get(
            route('admin.tools.payloadbuilder.export', { 'build_tag' : buildTag, filename }),
            { responseType: 'blob' }
        );
        const blobUrl = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = blobUrl;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        toast.success("Payload downloaded!");
    } catch (error) {
        toast.error("Download failed.");
    }
};

const deletePayload = async (buildTag, filename) => {
    const confirm = await Swal.fire({
        title: 'Delete Payload?',
        text: `Are you sure you want to delete "${filename}" (tag: ${buildTag})?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it'
    });

    if (confirm.isConfirmed) {
        try {
            await axios.delete(route('admin.tools.payloads.destroy', { build_tag: buildTag, filename }));
            toast.success('Payload deleted successfully!');
            loadPayloads();
        } catch (error) {
            toast.error('Failed to delete payload.');
        }
    }
};
</script>
