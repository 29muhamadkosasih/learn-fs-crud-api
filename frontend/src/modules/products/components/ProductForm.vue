<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  initialValues: {
    type: Object,
    default: () => ({
      name: '',
      sku: '',
      stock: '',
      price: '',
      description: '',
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
})

const emit = defineEmits(['submit', 'cancel'])

const form = reactive({
  name: '',
  sku: '',
  stock: '',
  price: '',
  description: '',
})

watch(
  () => props.initialValues,
  (value) => {
    form.name = value?.name || ''
    form.sku = value?.sku || ''
    form.stock = value?.stock || ''
    form.price = value?.price || ''
    form.description = value?.description || ''
  },
  { immediate: true, deep: true },
)

function submit() {
  emit('submit', {
    name: form.name,
    sku: form.sku,
    stock: form.stock,
    price: form.price,
    description: form.description,
  })
}
</script>

<template>
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Form Product</h6>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12 mb-3">
          <label>Nama Product</label>
          <input v-model="form.name" type="text" class="form-control" placeholder="Nama product" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>SKU</label>
          <input v-model="form.sku" type="text" class="form-control" placeholder="SKU-001" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Stock</label>
          <input v-model="form.stock" type="number" class="form-control" placeholder="10" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Price</label>
          <input v-model="form.price" type="number" class="form-control" placeholder="10000" />
        </div>

        <div class="col-12 mb-3">
          <label>Description</label>
          <textarea v-model="form.description" rows="3" class="form-control" placeholder="Deskripsi product"></textarea>
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
