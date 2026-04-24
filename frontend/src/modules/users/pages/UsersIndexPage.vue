<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import { deleteUser, getUsers } from '../services/usersApi'
import { getErrorList } from '../utils/usersHelpers'

const route = useRoute()
const router = useRouter()

const users = ref([])
const isLoading = ref(false)
const message = ref('')
const errorMessages = ref([])
const searchQuery = ref('')
const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  total: 0,
})

const filteredUsers = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase()

  if (!keyword) {
    return users.value
  }

  return users.value.filter((user) => {
    return [user.name, user.email, user.role]
      .filter((value) => value !== null && value !== undefined)
      .some((value) => String(value).toLowerCase().includes(keyword))
  })
})

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
    message.value = ''
    errorMessages.value = []
  }

  try {
    const payload = await getUsers(page)
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

async function removeUser(id) {
  if (!confirm('Yakin hapus data user ini?')) {
    return
  }

  message.value = ''
  errorMessages.value = []

  try {
    const payload = await deleteUser(id)
    message.value = payload?.message || 'User berhasil dihapus.'
    errorMessages.value = []
    await loadUsers(pagination.currentPage, { preserveNotice: true })

    if (!users.value.length && pagination.currentPage > 1) {
      await loadUsers(pagination.currentPage - 1, { preserveNotice: true })
    }
  } catch (error) {
    errorMessages.value = getErrorList(error)
  }
}

async function initializePage() {
  await loadUsers()

  const successMessage = route.query?.success
  if (typeof successMessage === 'string' && successMessage.trim()) {
    message.value = successMessage
    await router.replace({ name: route.name, query: {} })
  }
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

onMounted(initializePage)
</script>

<template>
  <div>
    <div class="page-header flex-wrap mb-3">
      <h5 class="page-title mb-0">Kategori User</h5>
    </div>

    <div class="row align-items-center mb-3">
      <div class="col-12 col-md-3 mb-3 mb-md-0">
        <button class="btn btn-primary btn-block rounded-lg shadow-sm" @click="goToCreate">
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
      <PageBanner v-if="message" variant="success" :message="message" class="mb-3" @close="message = ''" />

      <PageBanner
        v-if="errorMessages.length"
        variant="danger"
        :items="errorMessages"
        class="mb-3"
        @close="errorMessages = []"
      />

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
                  <button class="btn btn-outline-primary btn-sm mr-2" @click="goToEdit(item.id)">Edit</button>
                  <button class="btn btn-danger btn-sm" @click="removeUser(item.id)">Hapus</button>
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
