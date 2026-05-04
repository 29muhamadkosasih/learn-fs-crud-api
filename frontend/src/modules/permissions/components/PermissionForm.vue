<template>
    <div>
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ title }}</h5>
            <button 
                v-if="!isEditMode" 
                type="button" 
                class="btn btn-sm btn-primary"
                @click="$emit('close')"
            >
                <i class="fas fa-times"></i> Close
            </button>
        </div>
        <div class="card-body">
            <form @submit.prevent="handleSubmit">
                <div class="mb-3">
                    <label for="module" class="form-label">Module</label>
                    <select 
                        id="module"
                        v-model="form.module"
                        class="form-select"
                        :class="{ 'is-invalid': errors.module }"
                        :disabled="isEditMode"
                    >
                        <option value="">-- Pilih Module --</option>
                        <option v-for="module in modules" :key="module" :value="module">
                            {{ module }}
                        </option>
                    </select>
                    <div v-if="errors.module" class="invalid-feedback d-block">
                        {{ errors.module[0] }}
                    </div>
                </div>

                <div class="mb-3">
                    <label for="action" class="form-label">Action</label>
                    <select 
                        id="action"
                        v-model="form.action"
                        class="form-select"
                        :class="{ 'is-invalid': errors.action }"
                        :disabled="isEditMode"
                    >
                        <option value="">-- Pilih Action --</option>
                        <option v-for="act in actions" :key="act" :value="act">
                            {{ act }}
                        </option>
                    </select>
                    <div v-if="errors.action" class="invalid-feedback d-block">
                        {{ errors.action[0] }}
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea 
                        id="description"
                        v-model="form.description"
                        class="form-control"
                        :class="{ 'is-invalid': errors.description }"
                        rows="3"
                        placeholder="Enter permission description (optional)"
                    ></textarea>
                    <div v-if="errors.description" class="invalid-feedback d-block">
                        {{ errors.description[0] }}
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button 
                        type="submit" 
                        class="btn btn-primary"
                        :disabled="isLoading"
                    >
                        <i v-if="!isLoading" :class="isEditMode ? 'fas fa-save' : 'fas fa-plus'"></i>
                        <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                        {{ isEditMode ? 'Update' : 'Create' }}
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-secondary"
                        @click="$emit('close')"
                        :disabled="isLoading"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { createPermission, updatePermission } from '../services/permissionsApi'

const props = defineProps({
    permission: {
        type: Object,
        default: null,
    },
    modules: {
        type: Array,
        required: true,
    },
    actions: {
        type: Array,
        required: true,
    },
})

const emit = defineEmits(['close', 'create', 'update'])

const form = ref({
    module: '',
    action: '',
    description: '',
})

const errors = ref({})
const isLoading = ref(false)

const isEditMode = computed(() => !!props.permission)
const title = computed(() => isEditMode.value ? 'Edit Permission' : 'Create Permission')

watch(() => props.permission, (permission) => {
    if (permission) {
        form.value = {
            module: permission.module,
            action: permission.action,
            description: permission.description || '',
        }
    } else {
        form.value = {
            module: '',
            action: '',
            description: '',
        }
    }
    errors.value = {}
}, { immediate: true })

async function handleSubmit() {
    errors.value = {}
    isLoading.value = true

    try {
        if (isEditMode.value) {
            const data = {
                description: form.value.description,
            }
            const response = await updatePermission(props.permission.id, data)
            emit('update', response.data)
        } else {
            const response = await createPermission(form.value)
            emit('create', response.data)
        }

        // Reset form
        form.value = {
            module: '',
            action: '',
            description: '',
        }
    } catch (error) {
        if (error.errors) {
            errors.value = error.errors
        } else {
            console.error('Error:', error)
        }
    } finally {
        isLoading.value = false
    }
}
</script>
