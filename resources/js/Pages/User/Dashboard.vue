<template>
    <div>
        <h1 class="text-2xl font-bold mb-4">User Dashboard</h1>
        <div>
            <h2 class="text-xl font-semibold mb-2">Your Network Profiles</h2>
            <ul>
                <li v-for="profile in profiles" :key="profile.id">
                    <strong>Channel:</strong> {{ profile.network_channel.name }} |
                    <strong>Link:</strong> {{ profile.link.name }} |
                    <strong>Account ID:</strong> {{ profile.account_id }}
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        profiles: Array,
    },
};
</script>
