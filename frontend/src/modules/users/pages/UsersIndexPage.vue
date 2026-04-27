<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import PageBanner from '../../../components/PageBanner.vue'
import { deleteUser, getUsers } from '../services/usersApi'
import { getErrorList } from '../utils/usersHelpers'
import { showToast } from '../../../services/toast'
import { getUserRole } from '../../../services/auth'
import { canPerformAction } from '../../../services/permissions'

const router = useRouter()

const users = ref([])
const isLoading = ref(false)
const errorMessages = ref([])
const searchQuery = ref('')
const userRole = ref('user')
const perPage = ref('10')
const perPageOptions = ['10', '25', '50', '100', 'all']
const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  total: 0,
})

const filteredUsers = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase()

  let filtered = users.value

  if (keyword) {
    filtered = users.value.filter((user) => {
      return [user.name, user.email, user.role]
        .filter((value) => value !== null && value !== undefined)
        .some((value) => String(value).toLowerCase().includes(keyword))
    })
  }

  return filtered
})

const canCreate = computed(() => canPerformAction(userRole.value, 'users', 'create'))
const canEdit = computed(() => canPerformAction(userRole.value, 'users', 'edit'))
const canDelete = computed(() => canPerformAction(userRole.value, 'users', 'delete'))

function mapPaginatedUsers(payload) {
  const paginator = payload?.data

  if (paginator && Array.isArray(paginator.data)) {
    return {
      items: paginator.data,
      currentPage: paginator.current_page || 1,
      lastPage: paginator.last_page || 1,
      total: paginator.total || paginator.data.length,
      message: payload?.message || '',
    }
  }

  return {
    items: [],
    currentPage: 1,
    lastPage: 1,
    total: 0,
    message: payload?.message || '',
  }
}

async function loadUsers(page = 1, options = {}) {
  const { preserveNotice = false } = options

  isLoading.value = true

  if (!preserveNotice) {
    errorMessages.value = []
  }

  try {
    const payload = await getUsers(page, perPage.value)
    const mapped = mapPaginatedUsers(payload)

    users.value = mapped.items
    pagination.currentPage = mapped.currentPage
    pagination.lastPage = mapped.lastPage
    pagination.total = mapped.total
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isLoading.value = false
  }
}

async function confirmDelete(id) {
  errorMessages.value = []

  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: 'Anda yakin ingin menghapus data user ini? Tindakan ini tidak dapat dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#dc3545',
    reverseButtons: true,
    showLoaderOnConfirm: true,
    preConfirm: async () => {
      try {
        return await deleteUser(id)
      } catch (error) {
        Swal.showValidationMessage(getErrorList(error).join(', ') || 'Gagal menghapus data.')
        return null
      }
    },
    allowOutsideClick: () => !Swal.isLoading(),
  })

  if (!result.isConfirmed || !result.value) {
    return
  }

  showToast({
    variant: 'success',
    message: result.value?.message || 'User berhasil dihapus.',
  })
  errorMessages.value = []
  await loadUsers(pagination.currentPage, { preserveNotice: true })

  if (!users.value.length && pagination.currentPage > 1) {
    await loadUsers(pagination.currentPage - 1, { preserveNotice: true })
  }
}

async function initializePage() {
  userRole.value = getUserRole()
  await loadUsers()
}

function goToCreate() {
  router.push({ name: 'users.create' })
}

function goToEdit(id) {
  router.push({ name: 'users.edit', params: { id } })
}

function searchUsers() {
  loadUsers(1, { preserveNotice: true })
}

function onPerPageChange() {
  loadUsers(1, { preserveNotice: true })
}

onMounted(initializePage)
</script>

<template>
  <div>
    <div class="page-header flex-wrap mb-3">
      <h5 class="page-title mb-0">Kategori User</h5>
    </div>

    <div class="row align-items-center mb-3">
      <div class="col-12 col-md-3 mb-3 mb-md-0">
        <button v-if="canCreate" class="btn btn-primary btn-block rounded-lg shadow-sm" @click="goToCreate">
          <i class="fas fa-plus-circle mr-1"></i>
          Tambah
        </button>
      </div>

      <div class="col-12 col-md-9">
        <div class="input-group shadow-sm">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control"
            placeholder="masukkan kata kunci dan enter..."
            @keyup.enter="searchUsers"
          />
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" @click="searchUsers">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-3">
      <PageBanner
        v-if="errorMessages.length"
        variant="danger"
        :items="errorMessages"
        class="mb-3"
        @close="errorMessages = []"
      />

      <div class="d-flex justify-content-end align-items-center mb-3">
        <label class="mb-0 mr-2">Show entries</label>
        <select v-model="perPage" class="form-control" style="width: auto;" @change="onPerPageChange">
          <option v-for="option in perPageOptions" :key="option" :value="option">
            {{ option === 'all' ? 'All' : option }}
          </option>
        </select>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead class="thead-primary">
            <tr>
              <th class="text-center">NO.</th>
              <th>NAMA</th>
              <th>EMAIL</th>
              <th>ROLE</th>
              <th class="text-center">AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in filteredUsers" :key="item.id">
              <td>{{ index + 1 }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.email }}</td>
              <td>
                <span class="badge" :class="item.role === 'admin' ? 'badge-primary' : 'badge-success'">
                  {{ item.role }}
                </span>
              </td>
              <td>
                <div class="d-flex action-group">
                  <button v-if="canEdit" class="btn btn-outline-primary btn-sm mr-2" @click="goToEdit(item.id)">Edit</button>
                  <button v-if="canDelete" class="btn btn-danger btn-sm" @click="confirmDelete(item.id)">Hapus</button>
                  <span v-if="!canEdit && !canDelete" class="text-muted small">No Actions</span>
                </div>
              </td>
            </tr>
            <tr v-if="!filteredUsers.length">
              <td colspan="5" class="text-center text-muted py-4">
                {{ searchQuery ? 'Data tidak ditemukan.' : 'Belum ada data users.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-end mb-0">
          <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 || isLoading }">
            <a class="page-link" href="#" @click.prevent="loadUsers(pagination.currentPage - 1)">Prev</a>
          </li>
          <li class="page-item">
            <span class="page-link">
               {{ pagination.currentPage }} dari {{ pagination.lastPage }} (Total: {{ pagination.total }})
            </span>
          </li>
          <li class="page-item" :class="{ disabled: pagination.currentPage >= pagination.lastPage || isLoading }">
            <a class="page-link" href="#" @click.prevent="loadUsers(pagination.currentPage + 1)">Next</a>
          </li>
        </ul>
      </nav>
    </div>
    </div>
  </div>

</template>

<style scoped src="../../../styles/pages-shared.css"></style>
