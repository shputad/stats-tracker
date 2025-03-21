<template>
    <Head title="Create Stats" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <GoBack :href="route('admin.links.stats.index', link.id)" class="mr-2" />
                Create Stats for "{{ link.name }}" link
            </h1>

            <form @submit.prevent="submit" class="bg-white p-6 shadow-lg rounded-lg space-y-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Logs -->
                    <div class="flex-1">
                        <label for="log" class="block text-sm font-medium text-gray-700">Logs</label>
                        <input v-model="form.log" type="number" id="log" min="0"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.log" class="text-red-600 text-sm mt-1">{{ form.errors.log }}</p>
                    </div>

                    <!-- Detailed Logs -->
                    <div class="flex-1" v-if="link.type === 'rhadamanthys'">
                        <label for="detailed_log" class="block text-sm font-medium text-gray-700">Detailed Logs</label>
                        <input v-model="form.detailed_log" type="number" id="detailed_log" min="0"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.detailed_log" class="text-red-600 text-sm mt-1">{{ form.errors.detailed_log }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md hover:bg-blue-700 transition">
                        Create
                    </button>
                    <Link :href="route('admin.links.stats.index', link.id)"
                        class="px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 text-center transition">
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage, useForm, Link } from '@inertiajs/vue3';

const { props } = usePage();
const link = props.link;

const form = useForm({
    link_id: link.id,
    log: 0,
    detailed_log: 0
});

const submit = () => {
    form.post(route('admin.links.stats.store', link.id));
};
</script>