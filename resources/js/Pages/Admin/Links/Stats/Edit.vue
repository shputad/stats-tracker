<template>
    <Head title="Edit Link" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">
            <GoBack :href="`/admin/links/${link.id}/stats`"></GoBack> Edit Stats for "{{ link.name }}" link
        </h1>
        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="log" class="block text-gray-700">Logs</label>
                <input v-model="form.log" type="number" id="log" min="0" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.log" class="text-red-600 text-sm">{{ form.errors.log }}</span>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
            <Link :href="`/admin/links/${link.id}/stats`" class="px-4 py-2.5 bg-gray-600 text-white rounded-md ml-3" role="button">
                Cancel
            </Link>
        </form>
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
    log: stat.log
});

const submit = () => {
    form.put(route('admin.links.stats.update', {link: link.id, stat: stat.id}));
};
</script>
