<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  initialValues: {
    type: Object,
    default: () => ({
      title: '',
      instructor: '',
      duration: '',
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
  title: '',
  instructor: '',
  duration: '',
  price: '',
  description: '',
})

watch(
  () => props.initialValues,
  (value) => {
    form.title = value?.title || ''
    form.instructor = value?.instructor || ''
    form.duration = value?.duration || ''
    form.price = value?.price || ''
    form.description = value?.description || ''
  },
  { immediate: true, deep: true },
)

function submit() {
  emit('submit', {
    title: form.title,
    instructor: form.instructor,
    duration: form.duration,
    price: form.price,
    description: form.description,
  })
}
</script>

<template>
  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-12 mb-3">
          <label>Judul Course</label>
          <input v-model="form.title" type="text" class="form-control" placeholder="Judul course" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Instructor</label>
          <input v-model="form.instructor" type="text" class="form-control" placeholder="Nama instructor" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Duration (jam)</label>
          <input v-model="form.duration" type="number" class="form-control" placeholder="12" />
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label>Price</label>
          <input v-model="form.price" type="number" class="form-control" placeholder="150000" />
        </div>

        <div class="col-12 mb-3">
          <label>Description</label>
          <textarea v-model="form.description" rows="3" class="form-control" placeholder="Deskripsi course"></textarea>
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
