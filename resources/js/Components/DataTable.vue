<template>
    <div class="w-full">
        <!-- Filters Section (optional) -->
        <div v-if="filters && filters.length" class="mb-4">
            <div class="flex flex-wrap gap-4">
                <div v-for="(filter, index) in filters" :key="index" class="flex-1 min-w-[150px]">
                    <label :for="filter.model" class="block text-gray-700 font-medium mb-1">
                        {{ filter.label }}
                    </label>
                    <!-- Text filter -->
                    <input v-if="filter.type === 'text'" type="text" :id="filter.model"
                        v-model="localFilters[filter.model]"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                        :placeholder="filter.placeholder || 'Search...'" />
                    <!-- Number filter -->
                    <input v-else-if="filter.type === 'number'" type="number" :id="filter.model"
                        v-model.number="localFilters[filter.model]"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                        :placeholder="filter.placeholder || 'Enter number...'" />
                    <!-- Select filter -->
                    <select v-else-if="filter.type === 'select'" :id="filter.model" v-model="localFilters[filter.model]"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option value="">{{ filter.placeholder || 'Select' }}</option>
                        <option v-for="option in filter.options" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                    <!-- Date filter -->
                    <input v-else-if="filter.type === 'date'" type="date" :id="filter.model"
                        v-model="localFilters[filter.model]"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <!-- Checkbox filter -->
                    <div v-else-if="filter.type === 'checkbox'" class="flex items-center mt-1">
                        <input type="checkbox" :id="filter.model" v-model="localFilters[filter.model]"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring focus:ring-blue-200" />
                    </div>
                    <!-- Default fallback: text input -->
                    <input v-else type="text" :id="filter.model" v-model="localFilters[filter.model]"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                        :placeholder="filter.placeholder || 'Search...'" />
                </div>
            </div>
            <div class="mt-4 flex gap-4">
                <button @click="applyFilters" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Apply Filters
                </button>
                <button @click="resetFilters" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th v-for="column in columns" :key="column.key" class="border border-gray-300 p-2 text-left">
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, rowIndex) in filteredData" :key="rowIndex" class="hover:bg-gray-50">
                        <td v-for="column in columns" :key="column.key" class="border border-gray-300 p-2">
                            <!-- Use a slot for custom cell rendering if provided -->
                            <slot :name="column.key" :row="row">
                                {{ row[column.key] }}
                            </slot>
                        </td>
                    </tr>
                    <tr v-if="filteredData.length === 0">
                        <td colspan="100" class="text-center p-4 text-gray-500">
                            No data available.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Slot (optional) -->
        <div class="mt-4">
            <slot name="pagination"></slot>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
    filters: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['filter']);

// Initialize local filters based on filters prop
const localFilters = reactive({});
if (props.filters.length) {
    props.filters.forEach(filter => {
        localFilters[filter.model] = filter.default || (filter.type === 'checkbox' ? false : '');
    });
}

// Computed filtered data based on localFilters
const filteredData = computed(() => {
    let tempData = props.data;
    for (const key in localFilters) {
        if (localFilters[key] !== '' && localFilters[key] !== false && localFilters[key] !== null) {
            tempData = tempData.filter(item => {
                const value = item[key] ? item[key].toString().toLowerCase() : '';
                return value.includes(localFilters[key].toString().toLowerCase());
            });
        }
    }
    return tempData;
});

// Methods to apply and reset filters
const applyFilters = () => {
    emit('filter', { ...localFilters });
};

const resetFilters = () => {
    for (const key in localFilters) {
        localFilters[key] = typeof localFilters[key] === 'boolean' ? false : '';
    }
    emit('filter', { ...localFilters });
};
</script>

<style scoped>
/* Additional styling if needed */
</style>