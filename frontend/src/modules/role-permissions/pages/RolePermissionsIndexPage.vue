<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import Swal from 'sweetalert2'
import PageBanner from '../../../components/PageBanner.vue'
import { showToast } from '../../../services/toast'
import { ACTIONS, MODULES } from '../../../services/permissionCatalog'
import {
  createRolePermission,
  deleteRolePermission,
  getRolePermissions,
  updateRolePermission,
} from '../services/rolePermissionsApi'
import { getErrorList } from '../../books/utils/booksHelpers'

const items = ref([])
const isLoading = ref(false)
const errorMessages = ref([])
const searchQuery = ref('')

const roles = ['admin', 'manager', 'user']
const form = reactive({
  id: null,
  role: 'admin',
  permission: 'books.view',
  allowed: true,
})

const permissionOptions = computed(() => {
  return MODULES.flatMap((module) => ACTIONS.map((action) => `${module}.${action}`))
})

const filteredItems = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase()

  if (!keyword) {
    return items.value
  }

  return items.value.filter((item) => {
    return [item.role, item.permission, item.allowed ? 'allowed' : 'denied']
      .some((value) => String(value).toLowerCase().includes(keyword))
  })
})

const isEditing = computed(() => form.id !== null)

function resetForm() {
  form.id = null
  form.role = 'admin'
  form.permission = 'books.view'
  form.allowed = true
}

function fillForm(item) {
  form.id = item.id
  form.role = item.role
  form.permission = item.permission
  form.allowed = Boolean(item.allowed)
}

async function loadRolePermissions() {
  isLoading.value = true
  errorMessages.value = []

  try {
    const payload = await getRolePermissions()
    items.value = payload?.data || []
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isLoading.value = false
  }
}

async function submitForm() {
  errorMessages.value = []

  const payload = {
    role: form.role,
    permission: form.permission,
    allowed: form.allowed,
  }

  try {
    if (isEditing.value) {
      await updateRolePermission(form.id, payload)
      showToast({ variant: 'success', message: 'Permission berhasil diubah.' })
    } else {
      await createRolePermission(payload)
      showToast({ variant: 'success', message: 'Permission berhasil ditambahkan.' })
    }

    resetForm()
    await loadRolePermissions()
  } catch (error) {
    errorMessages.value = getErrorList(error)
  }
}

async function confirmDelete(item) {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: `Hapus permission ${item.permission} untuk role ${item.role}?`,
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
    await deleteRolePermission(item.id)
    showToast({ variant: 'success', message: 'Permission berhasil dihapus.' })
    if (form.id === item.id) {
      resetForm()
    }
    await loadRolePermissions()
  } catch (error) {
    errorMessages.value = getErrorList(error)
  }
}

onMounted(loadRolePermissions)
</script>

<template>
  <div>
    <div class="page-header flex-wrap mb-3">
      <h5 class="page-title mb-0">Role Permissions</h5>
    </div>

    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <PageBanner
          v-if="errorMessages.length"
          variant="danger"
          :items="errorMessages"
          class="mb-3"
          @close="errorMessages = []"
        />

        <div class="row">
          <div class="col-12 col-lg-4 mb-3 mb-lg-0">
            <label class="font-weight-bold">Role</label>
            <select v-model="form.role" class="form-control">
              <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
            </select>
          </div>

          <div class="col-12 col-lg-4 mb-3 mb-lg-0">
            <label class="font-weight-bold">Permission</label>
            <select v-model="form.permission" class="form-control">
              <option v-for="permission in permissionOptions" :key="permission" :value="permission">
                {{ permission }}
              </option>
            </select>
          </div>

          <div class="col-12 col-lg-2 mb-3 mb-lg-0">
            <label class="font-weight-bold d-block">Allowed</label>
            <div class="custom-control custom-switch mt-2">
              <input
                id="allowed"
                v-model="form.allowed"
                type="checkbox"
                class="custom-control-input"
              />
              <label class="custom-control-label" for="allowed">
                {{ form.allowed ? 'Yes' : 'No' }}
              </label>
            </div>
          </div>

          <div class="col-12 col-lg-2 d-flex align-items-end">
            <button class="btn btn-primary btn-block" :disabled="isLoading" @click="submitForm">
              {{ isEditing ? 'Update' : 'Simpan' }}
            </button>
          </div>
        </div>

        <div v-if="isEditing" class="mt-3">
          <button class="btn btn-link p-0" type="button" @click="resetForm">Batal edit</button>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-3">
        <div class="d-flex justify-content-end align-items-center mb-3">
          <div class="input-group" style="max-width: 320px;">
            <input
              v-model="searchQuery"
              type="text"
              class="form-control"
              placeholder="Cari role, permission, status..."
            />
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" @click="searchQuery = searchQuery.trim()">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <thead class="thead-primary">
              <tr>
                <th class="text-center">NO.</th>
                <th>ROLE</th>
                <th>PERMISSION</th>
                <th>ALLOW</th>
                <th class="text-center">AKSI</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in filteredItems" :key="item.id">
                <td>{{ index + 1 }}</td>
                <td>
                  <span class="badge badge-info">{{ item.role }}</span>
                </td>
                <td>{{ item.permission }}</td>
                <td>
                  <span class="badge" :class="item.allowed ? 'badge-success' : 'badge-danger'">
                    {{ item.allowed ? 'Allowed' : 'Denied' }}
                  </span>
                </td>
                <td>
                  <div class="d-flex action-group">
                    <button class="btn btn-outline-primary btn-sm mr-2" @click="fillForm(item)">Edit</button>
                    <button class="btn btn-danger btn-sm" @click="confirmDelete(item)">Hapus</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!filteredItems.length && !isLoading">
                <td colspan="5" class="text-center text-muted py-4">
                  Tidak ada data permission.
                </td>
              </tr>
              <tr v-if="isLoading">
                <td colspan="5" class="text-center text-muted py-4">
                  Memuat data...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>