<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import CourseForm from '../components/CourseForm.vue'
import { getCourseById, updateCourse } from '../services/coursesApi'
import { showToast } from '../../../services/toast'
import { getErrorList, mapCourseDetail } from '../utils/coursesHelpers'

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const isSaving = ref(false)
const message = ref('')
const formInitialValues = ref({
  title: '',
  instructor: '',
  duration: '',
  price: '',
  description: '',
})
const errorMessages = ref([])

async function loadDetail() {
  isLoading.value = true
  message.value = ''
  errorMessages.value = []

  try {
    const payload = await getCourseById(route.params.id)
    const course = mapCourseDetail(payload)

    formInitialValues.value = {
      title: course?.title || '',
      instructor: course?.instructor || '',
      duration: course?.duration || '',
      price: course?.price || '',
      description: course?.description || '',
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
    await updateCourse(route.params.id, form)
    showToast({
      variant: 'success',
      message: 'Course berhasil diperbarui.',
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

onMounted(loadDetail)
</script>

<template>
  <div>
    <div class="page-header">
      <h2 class="h4 mb-0 page-title">Edit Course</h2>
      <button class="btn btn-outline-secondary btn-sm" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

    <PageBanner v-if="message" variant="success" :message="message" class="mb-3" />

    <PageBanner v-if="isLoading" variant="secondary" message="Loading data course..." class="mb-3" :timeout="0" />

    <CourseForm
      v-else
      :initial-values="formInitialValues"
      :loading="isSaving"
      submit-label="Update"
      @submit="submit"
      @cancel="cancel"
    />
  </div>
</template>
