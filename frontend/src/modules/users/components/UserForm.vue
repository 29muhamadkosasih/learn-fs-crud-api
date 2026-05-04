<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  initialValues: {
    type: Object,
    default: () => ({
      name: '',
      email: '',
      role: 'user',
      password: '',
    }),
  },
  submitLabel: {
    type: String,
    default: 'Simpan',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  requirePassword: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const form = reactive({
  name: '',
  email: '',
  role: 'user',
  password: '',
})

watch(
  () => props.initialValues,
  (value) => {
    form.name = value?.name || ''
    form.email = value?.email || ''
    form.role = value?.role || 'user'
    form.password = ''
  },
  { immediate: true, deep: true },
)

function submit() {
  emit('submit', {
    name: form.name,
    email: form.email,
    role: form.role,
    password: form.password,
  })
}
</script>

<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-12 mb-3">
          <label>Name</label>
          <input v-model="form.name" type="text" class="form-control" placeholder="Nama user" />
        </div>

        <div class="col-12 mb-3">
          <label>Email</label>
          <input v-model="form.email" type="email" class="form-control" placeholder="email@example.com" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Role</label>
          <select v-model="form.role" class="form-control">
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Password {{ requirePassword ? '(wajib)' : '(opsional)' }}</label>
          <input v-model="form.password" type="password" class="form-control" placeholder="******" />
        </div>
      </div>

      <div class="d-flex mt-4 action-group">
        <button class="btn btn-primary" :disabled="loading" @click="submit">
          {{ loading ? 'Menyimpan...' : submitLabel }}
        </button>
        <button class="btn btn-outline-secondary" :disabled="loading" @click="emit('cancel')">Batal</button>
      </div>
    </div>
  </div>
</template>
