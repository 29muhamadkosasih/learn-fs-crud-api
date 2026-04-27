<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import UserForm from '../components/UserForm.vue'
import { createUser } from '../services/usersApi'
import { showToast } from '../../../services/toast'
import { getErrorList } from '../utils/usersHelpers'

const router = useRouter()
const isSaving = ref(false)
const message = ref('')
const errorMessages = ref([])

async function submit(form) {
  isSaving.value = true
  message.value = ''
  errorMessages.value = []

  try {
    await createUser(form)
    showToast({
      variant: 'success',
      message: 'User berhasil ditambahkan.',
    })
    router.push({
      name: 'users.index',
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
</script>

<template>
  <div>
    <div class="page-header">
      <h2 class="h4 mb-0 page-title">Create User</h2>
      <button class="btn btn-outline-secondary btn-sm" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

    <PageBanner v-if="message" variant="success" :message="message" class="mb-3" />

    <UserForm :loading="isSaving" :require-password="true" submit-label="Simpan" @submit="submit" @cancel="cancel" />
  </div>
</template>
