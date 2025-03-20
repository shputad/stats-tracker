<template>
    <Head title="Edit Link" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.links.stats.index', link.id)" />
                Edit Stats for "{{ link.name }}" link
            </h1>
            <form @submit.prevent="submit" class="bg-white p-6 shadow rounded">
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="flex-1">
                        <label for="log" class="block text-gray-700 font-medium">Logs</label>
                        <input v-model="form.log" type="number" id="log" min="0"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                        <span v-if="form.errors.log" class="text-red-600 text-sm">
                            {{ form.errors.log }}
                        </span>
                    </div>
                    <div class="flex-1" v-if="link.type === 'rhadamanthys'">
                        <label for="detailed_log" class="block text-gray-700 font-medium">Detailed Logs</label>
                        <input v-model="form.detailed_log" type="number" id="detailed_log" min="0"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                        <span v-if="form.errors.detailed_log" class="text-red-600 text-sm">
                            {{ form.errors.detailed_log }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save Changes
                    </button>
                    <Link :href="route('admin.links.stats.index', link.id)"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700" role="button">
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
const stat = props.stat;

const form = useForm({
    link_id: link.id,
    log: stat.log,
    detailed_log: stat.detailed_log
});

const submit = () => {
    form.put(route('admin.links.stats.update', { link: link.id, stat: stat.id }));
};
</script>