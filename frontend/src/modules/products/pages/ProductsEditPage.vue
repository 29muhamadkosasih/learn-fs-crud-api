<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import ProductForm from '../components/ProductForm.vue'
import { getProductById, updateProduct } from '../services/productsApi'
import { showToast } from '../../../services/toast'
import { getErrorList, mapProductDetail } from '../utils/productsHelpers'

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const isSaving = ref(false)
const message = ref('')
const formInitialValues = ref({
  name: '',
  sku: '',
  stock: '',
  price: '',
  description: '',
})
const errorMessages = ref([])

async function loadDetail() {
  isLoading.value = true
  message.value = ''
  errorMessages.value = []

  try {
    const payload = await getProductById(route.params.id)
    const product = mapProductDetail(payload)

    formInitialValues.value = {
      name: product?.name || '',
      sku: product?.sku || '',
      stock: product?.stock || '',
      price: product?.price || '',
      description: product?.description || '',
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
    await updateProduct(route.params.id, form)
    showToast({
      variant: 'success',
      message: 'Product berhasil diperbarui.',
    })
    router.push({
      name: 'products.index',
    })
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isSaving.value = false
  }
}

function cancel() {
  router.push({ name: 'products.index' })
}

onMounted(loadDetail)
</script>

<template>
  <div>
    <div class="page-header">
      <h2 class="h4 mb-0 page-title">Edit Product</h2>
      <button class="btn btn-outline-secondary btn-sm" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

    <PageBanner v-if="message" variant="success" :message="message" class="mb-3" />

    <PageBanner v-if="isLoading" variant="secondary" message="Loading data product..." class="mb-3" :timeout="0" />

    <ProductForm
      v-else
      :initial-values="formInitialValues"
      :loading="isSaving"
      submit-label="Update"
      @submit="submit"
      @cancel="cancel"
    />
  </div>
</template>
