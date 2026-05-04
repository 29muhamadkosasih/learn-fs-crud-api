<template>
  <div>
    <div class="page-header">
      <h5 class="page-title mb-0">Permissions</h5>
      <button class="btn btn-secondary" type="button" @click="resetForm">Reset</button>
    </div>

    <div class="card">
      <div class="card-body p-3">
        <PageBanner
          v-if="errorMessages.length"
          variant="danger"
          :items="errorMessages"
          class="mb-3"
          @close="errorMessages = []"
        />

        <div class="row align-items-end">
          <div class="col-12 col-lg-5 mb-3 mb-lg-0">
            <label class="mb-1 font-weight-bold">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="form-control"
              placeholder="e.g., Create Book"
            />
          </div>

          <div class="col-12 col-lg-5 mb-3 mb-lg-0">
            <label class="mb-1 font-weight-bold">Model Route</label>
            <input
              v-model="form.model_route"
              type="text"
              class="form-control"
              placeholder="e.g., books.create"
            />
          </div>

          <div class="col-12 col-lg-2 d-flex align-items-end">
            <button class="btn btn-primary btn-block" :disabled="isLoading" @click="submitForm">
              {{ isEditing ? 'Update' : 'Simpan' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-3">
        <div class="d-flex justify-content-end align-items-center mb-3">
          <div class="input-group" style="max-width: 350px; width:100%;">
            <input
              v-model="searchQuery"
              type="text"
              class="form-control"
              placeholder="Cari name, model_route..."
            />
            <button class="btn btn-outline-secondary" type="button" @click="searchQuery = searchQuery.trim()">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <thead class="thead-primary">
              <tr>
                <th width="1px" class="text-center">NO.</th>
                <th>NAME</th>
                <th>MODEL ROUTE</th>
                <th width="150px" class="text-center">AKSI</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!isLoading && filteredItems.length === 0">
                <td colspan="4" class="text-center text-muted py-4">
                  No permissions found
                </td>
              </tr>
              <tr v-for="(item, index) in filteredItems" v-else :key="item.id">
                <td class="text-center">{{ index + 1 }}</td>
                <td>{{ item.name }}</td>
                <td><code>{{ item.model_route }}</code></td>
                <td>
                  <div class="d-flex action-group">
                    <button class="btn btn-outline-primary btn-sm mr-2" @click="fillForm(item)">Edit</button>
                    <button class="btn btn-danger btn-sm" @click="confirmDelete(item)">Hapus</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import Swal from 'sweetalert2'
import PageBanner from '../../../components/PageBanner.vue'
import { showToast } from '../../../services/toast'
import {
  createPermission,
  deletePermission,
  getPermissions,
  updatePermission,
} from '../services/permissionsApi'
import { getErrorList } from '../../books/utils/booksHelpers'

const items = ref([])
const isLoading = ref(false)
const errorMessages = ref([])
const searchQuery = ref('')

const form = reactive({
  id: null,
  name: '',
  model_route: '',
})

const filteredItems = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase()

  if (!keyword) {
    return items.value
  }

  return items.value.filter((item) => {
    return [item.name, item.model_route].some((value) =>
      String(value).toLowerCase().includes(keyword)
    )
  })
})

const isEditing = computed(() => form.id !== null)

function resetForm() {
  form.id = null
  form.name = ''
  form.model_route = ''
}

function fillForm(item) {
  form.id = item.id
  form.name = item.name
  form.model_route = item.model_route
}

async function loadPermissions() {
  isLoading.value = true
  errorMessages.value = []

  try {
    const payload = await getPermissions()
    items.value = payload?.data || []
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isLoading.value = false
  }
}

async function submitForm() {
  errorMessages.value = []

  if (!form.name.trim() || !form.model_route.trim()) {
    errorMessages.value = ['Name dan Model Route harus diisi.']
    return
  }

  const payload = {
    name: form.name,
    model_route: form.model_route,
  }

  try {
    if (isEditing.value) {
      await updatePermission(form.id, payload)
      showToast({ variant: 'success', message: 'Permission berhasil diubah.' })
    } else {
      await createPermission(payload)
      showToast({ variant: 'success', message: 'Permission berhasil ditambahkan.' })
    }

    resetForm()
    await loadPermissions()
  } catch (error) {
    errorMessages.value = getErrorList(error)
  }
}

async function confirmDelete(item) {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: `Hapus permission "${item.name}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#dc3545',
    reverseButtons: true,
  })

  if (!result.isConfirmed) {
    return
  }

  try {
    await deletePermission(item.id)
    showToast({ variant: 'success', message: 'Permission berhasil dihapus.' })
    if (form.id === item.id) {
      resetForm()
    }
    await loadPermissions()
  } catch (error) {
    errorMessages.value = getErrorList(error)
  }
}

onMounted(loadPermissions)
</script>
