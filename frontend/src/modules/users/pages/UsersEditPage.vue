<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import UserForm from '../components/UserForm.vue'
import { getUserById, updateUser } from '../services/usersApi'
import { getErrorList } from '../utils/usersHelpers'

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const isSaving = ref(false)
const message = ref('')
const formInitialValues = ref({
  name: '',
  email: '',
  role: 'user',
  password: '',
})
const errorMessages = ref([])

function mapUserDetail(payload) {
  return payload?.data || null
}

async function loadDetail() {
  isLoading.value = true
  message.value = ''
  errorMessages.value = []

  try {
    const payload = await getUserById(route.params.id)
    const user = mapUserDetail(payload)

    formInitialValues.value = {
      name: user?.name || '',
      email: user?.email || '',
      role: user?.role || 'user',
      password: '',
    }
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isLoading.value = false
  }
}

async function submit(form) {
  isSaving.value = true
  message.value = ''
  errorMessages.value = []

  try {
    const payload = {
      name: form.name,
      email: form.email,
      role: form.role,
    }

    if (form.password) {
      payload.password = form.password
    }

    await updateUser(route.params.id, payload)
    router.push({
      name: 'users.index',
      query: {
        success: 'User berhasil diperbarui.',
      },
    })
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isSaving.value = false
  }
}

function cancel() {
  router.push({ name: 'users.index' })
}

onMounted(loadDetail)
</script>

<template>
  <div>
    <div class="page-header">
      <h2 class="h4 mb-0 page-title">Edit User</h2>
      <button class="btn btn-outline-secondary btn-sm" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

    <PageBanner v-if="message" variant="success" :message="message" class="mb-3" />

    <PageBanner v-if="isLoading" variant="secondary" message="Loading data user..." class="mb-3" :timeout="0" />

    <UserForm
      v-else
      :initial-values="formInitialValues"
      :loading="isSaving"
      :require-password="false"
      submit-label="Update"
      @submit="submit"
      @cancel="cancel"
    />
  </div>
</template>
