<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import CourseForm from '../components/CourseForm.vue'
import { createCourse } from '../services/coursesApi'
import { showToast } from '../../../services/toast'
import { getErrorList } from '../utils/coursesHelpers'

const router = useRouter()
const isSaving = ref(false)
const message = ref('')
const errorMessages = ref([])

async function submit(form) {
  isSaving.value = true
  message.value = ''
  errorMessages.value = []

  try {
    await createCourse(form)
    showToast({
      variant: 'success',
      message: 'Course berhasil ditambahkan.',
    })
    router.push({
      name: 'courses.index',
    })
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isSaving.value = false
  }
}

function cancel() {
  router.push({ name: 'courses.index' })
}
</script>

<template>
  <div>
    <div class="page-header">
      <h2 class="h4 mb-0 page-title">Create Course</h2>
      <button class="btn btn-outline-secondary btn-sm" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

    <PageBanner v-if="message" variant="success" :message="message" class="mb-3" />

    <CourseForm :loading="isSaving" submit-label="Simpan" @submit="submit" @cancel="cancel" />
  </div>
</template>
