<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import ProductForm from '../components/ProductForm.vue'
import { createProduct } from '../services/productsApi'
import { showToast } from '../../../services/toast'
import { getErrorList } from '../utils/productsHelpers'

const router = useRouter()
const isSaving = ref(false)
const errorMessages = ref([])

async function submit(form) {
  isSaving.value = true
  errorMessages.value = []

  try {
    await createProduct(form)
    showToast({
      variant: 'success',
      message: 'Product berhasil ditambahkan.',
    })
    router.push({ name: 'products.index' })
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isSaving.value = false
  }
}

function cancel() {
  router.push({ name: 'products.index' })
}
</script>

<template>
  <div>
    <div class="page-header">
      <h5 class="page-title mb-0">Tambah Product</h5>
      <button class="btn btn-secondary" @click="cancel">Kembali</button>
    </div>

    <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

    <ProductForm :loading="isSaving" submit-label="Simpan" @submit="submit" @cancel="cancel" />
  </div>
</template>
