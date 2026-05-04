<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import UserForm from '../components/UserForm.vue'
import { getUserById, updateUser } from '../services/usersApi'
import { showToast } from '../../../services/toast'
import { getErrorList } from '../utils/usersHelpers'

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const isSaving = ref(false)
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
    showToast({
      variant: 'success',
      message: 'User berhasil diperbarui.',
    })
    router.push({ name: 'users.index' })
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
      <h5 class="page-title mb-0">Edit User</h5>
      <button class="btn btn-secondary" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />
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
