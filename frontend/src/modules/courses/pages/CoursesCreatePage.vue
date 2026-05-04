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
const errorMessages = ref([])

async function submit(form) {
  isSaving.value = true
  errorMessages.value = []

  try {
    await createCourse(form)
    showToast({
      variant: 'success',
      message: 'Course berhasil ditambahkan.',
    })
    router.push({ name: 'courses.index' })
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
      <h5 class="page-title mb-0">Tambah Course</h5>
      <button class="btn btn-secondary" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

    <CourseForm :loading="isSaving" submit-label="Simpan" @submit="submit" @cancel="cancel" />
  </div>
</template>
