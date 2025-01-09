<template>
    <Head title="Edit Network Profile" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Edit Network Profile</h1>
        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="user_id" class="block text-gray-700">User</label>
                <select v-model="form.user_id" id="user_id" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option v-for="user in users" :value="user.id" :key="user.id">{{ user.name }}</option>
                </select>
                <span v-if="form.errors.user_id" class="text-red-600 text-sm">{{ form.errors.user_id }}</span>
            </div>
            <div class="mb-4">
                <label for="channel_id" class="block text-gray-700">Network Channel</label>
                <select v-model="form.channel_id" id="channel_id" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option v-for="channel in channels" :value="channel.id" :key="channel.id">{{ channel.name }}</option>
                </select>
                <span v-if="form.errors.channel_id" class="text-red-600 text-sm">{{ form.errors.channel_id }}</span>
            </div>
            <div class="mb-4">
                <label for="link_id" class="block text-gray-700">Link</label>
                <select v-model="form.link_id" id="link_id" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option v-for="link in links" :value="link.id" :key="link.id">{{ link.name }}</option>
                </select>
                <span v-if="form.errors.link_id" class="text-red-600 text-sm">{{ form.errors.link_id }}</span>
            </div>
            <div class="mb-4">
                <label for="account_id" class="block text-gray-700">Account ID</label>
                <input v-model="form.account_id" type="text" id="account_id" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.account_id" class="text-red-600 text-sm">{{ form.errors.account_id }}</span>
            </div>
            <div class="mb-4">
                <label for="api_key" class="block text-gray-700">API Key</label>
                <input v-model="form.api_key" type="text" id="api_key" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.api_key" class="text-red-600 text-sm">{{ form.errors.api_key }}</span>
            </div>
            <div class="mb-4">
                <label for="status" class="block text-gray-700">Status</label>
                <select v-model="form.status" id="status" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <span v-if="form.errors.status" class="text-red-600 text-sm">{{ form.errors.status }}</span>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
            <Link href="/admin/network-profiles" class="px-4 py-2.5 bg-gray-600 text-white rounded-md ml-3">
                Cancel
            </Link>
        </form>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';

const { props } = usePage();
const profile = props.profile;
const users = props.users;
const channels = props.channels;
const links = props.links;

const form = useForm({
    user_id: profile.user_id,
    channel_id: profile.channel_id,
    link_id: profile.link_id,
    account_id: profile.account_id,
    api_key: profile.api_key,
    status: profile.status,
});

const submit = () => {
    form.put(route('admin.network-profiles.update', profile.id));
};
</script>
