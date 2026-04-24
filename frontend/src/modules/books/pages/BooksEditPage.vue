<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import BookForm from '../components/BookForm.vue'
import { getBookById, updateBook } from '../services/booksApi'
import { getErrorList, mapBookDetail } from '../utils/booksHelpers'

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const isSaving = ref(false)
const message = ref('')
const formInitialValues = ref({
  name: '',
  harga: '',
  stock: '',
})
const errorMessages = ref([])

async function loadDetail() {
  isLoading.value = true
  message.value = ''
  errorMessages.value = []

  try {
    const payload = await getBookById(route.params.id)
    const book = mapBookDetail(payload)

    formInitialValues.value = {
      name: book?.name || '',
      harga: book?.harga || '',
      stock: book?.stock || '',
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
    const payload = new FormData()
    payload.append('name', form.name)
    payload.append('harga', form.harga)
    payload.append('stock', form.stock)
    if (form.image) {
      payload.append('image', form.image)
    }

    await updateBook(route.params.id, payload)
    router.push({
      name: 'books.index',
      query: {
        success: 'Book berhasil diperbarui.',
      },
    })
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isSaving.value = false
  }
}

function cancel() {
  router.push({ name: 'books.index' })
}

onMounted(loadDetail)
</script>

<template>
  <div>
    <div class="page-header">
      <h2 class="h4 mb-0 page-title">Edit Book</h2>
      <button class="btn btn-outline-secondary btn-sm" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

    <PageBanner v-if="message" variant="success" :message="message" class="mb-3" />

    <PageBanner v-if="isLoading" variant="secondary" message="Loading data book..." class="mb-3" :timeout="0" />

    <BookForm
      v-else
      :initial-values="formInitialValues"
      :loading="isSaving"
      :require-image="false"
      submit-label="Update"
      @submit="submit"
      @cancel="cancel"
    />
  </div>
</template>
