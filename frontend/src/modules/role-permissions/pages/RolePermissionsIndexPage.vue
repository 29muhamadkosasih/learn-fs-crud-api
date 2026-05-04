<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import Swal from 'sweetalert2'
import PageBanner from '../../../components/PageBanner.vue'
import { showToast } from '../../../services/toast'
import { getPermissions } from '../../permissions/services/permissionsApi'
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
const permissions = ref([])

const roles = ['admin', 'manager', 'user']
const form = reactive({
  id: null,
  role: 'admin',
  permission: '',
  allowed: true,
})

const permissionOptions = computed(() => {
  return permissions.value.map((perm) => ({
    value: perm.name,
    label: perm.name,
  }))
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
  form.permission = permissionOptions.value[0]?.value || ''
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

async function loadPermissions() {
  try {
    const payload = await getPermissions()
    permissions.value = payload?.data || []
    // Set default form permission to first available
    if (permissions.value.length > 0 && !form.permission) {
      form.permission = permissions.value[0].name
    }
  } catch (error) {
    console.error('Failed to load permissions:', error)
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

onMounted(() => {
  loadPermissions()
  loadRolePermissions()
})
</script>

<template>
  <div>
    <div class="page-header">
      <h5 class="page-title mb-0">Role Permissions</h5>
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
          <div class="col-12 col-lg-4 mb-3 mb-lg-0">
            <label class="mb-1 font-weight-bold">Role</label>
            <select v-model="form.role" class="form-control">
              <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
            </select>
          </div>

          <div class="col-12 col-lg-4 mb-3 mb-lg-0">
            <label class="mb-1 font-weight-bold">Permission</label>
            <select v-model="form.permission" class="form-control">
              <option v-for="permission in permissionOptions" :key="permission.value" :value="permission.value">
                {{ permission.label }}
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
              placeholder="Cari role, permission, status..."
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
                <th>ROLE</th>
                <th>PERMISSION</th>
                <th>ALLOW</th>
                <th width="150px" class="text-center">AKSI</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in filteredItems" :key="item.id">
                <td class="text-center">{{ index + 1 }}</td>
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