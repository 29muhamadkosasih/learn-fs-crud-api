<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  initialValues: {
    type: Object,
    default: () => ({
      name: '',
      harga: '',
      stock: '',
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
  requireImage: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const form = reactive({
  name: '',
  harga: '',
  stock: '',
  image: null,
})

watch(
  () => props.initialValues,
  (value) => {
    form.name = value?.name || ''
    form.harga = value?.harga || ''
    form.stock = value?.stock || ''
    form.image = null
  },
  { immediate: true, deep: true },
)

function onImageChange(event) {
  form.image = event.target.files?.[0] || null
}

function submit() {
  emit('submit', {
    name: form.name,
    harga: form.harga,
    stock: form.stock,
    image: form.image,
  })
}
</script>

<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-12 mb-3">
          <label>Nama Book</label>
          <input v-model="form.name" type="text" class="form-control" placeholder="Nama book" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Harga</label>
          <input v-model="form.harga" type="number" class="form-control" placeholder="10000" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Stock</label>
          <input v-model="form.stock" type="number" class="form-control" placeholder="10" />
        </div>

        <div class="col-12 mb-3">
          <label>Image {{ requireImage ? '(wajib)' : '(opsional)' }}</label>
          <input type="file" class="form-control" accept="image/*" @change="onImageChange" />
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
