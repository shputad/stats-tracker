<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link :href="href">
        <i class="fas fa-left-long text-gray-600 mr-2"></i>
    </Link>
</template>
