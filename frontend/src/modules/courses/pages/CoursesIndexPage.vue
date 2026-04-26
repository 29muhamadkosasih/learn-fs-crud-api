<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PageBanner from '../../../components/PageBanner.vue'
import Toast from '../../../components/Toast.vue'
import { deleteCourse, getCourses } from '../services/coursesApi'
import { getErrorList, mapPaginatedCourses } from '../utils/coursesHelpers'
import { getUserRole } from '../../../services/auth'
import { canPerformAction } from '../../../services/permissions'

const route = useRoute()
const router = useRouter()

const courses = ref([])
const isLoading = ref(false)
const message = ref('')
const errorMessages = ref([])
const searchQuery = ref('')
const showDeleteModal = ref(false)
const deleteItemId = ref(null)
const isDeleting = ref(false)
const userRole = ref('user')
const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  total: 0,
})

const toastMessage = ref('')
const toastVariant = ref('success')

const filteredCourses = computed(() => {
  const keyword = searchQuery.value.trim().toLowerCase()

  let filtered = courses.value

  if (keyword) {
    filtered = courses.value.filter((course) => {
      return [course.title, course.instructor, course.duration, course.price, course.description]
        .filter((value) => value !== null && value !== undefined)
        .some((value) => String(value).toLowerCase().includes(keyword))
    })
  }

  return filtered
})

const canCreate = computed(() => canPerformAction(userRole.value, 'courses', 'create'))
const canEdit = computed(() => canPerformAction(userRole.value, 'courses', 'edit'))
const canDelete = computed(() => canPerformAction(userRole.value, 'courses', 'delete'))

async function loadCourses(page = 1, options = {}) {
  const { preserveNotice = false } = options

  isLoading.value = true

  if (!preserveNotice) {
    message.value = ''
    errorMessages.value = []
  }

  try {
    const payload = await getCourses(page)
    const mapped = mapPaginatedCourses(payload)

    courses.value = mapped.items
    pagination.currentPage = mapped.currentPage
    pagination.lastPage = mapped.lastPage
    pagination.total = mapped.total
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isLoading.value = false
  }
}

function openDeleteModal(id) {
  deleteItemId.value = id
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  deleteItemId.value = null
}

async function confirmDelete() {
  if (!deleteItemId.value) return

  isDeleting.value = true
  toastMessage.value = ''
  errorMessages.value = []

  try {
    const payload = await deleteCourse(deleteItemId.value)
    toastMessage.value = payload?.message || 'Course berhasil dihapus.'
    toastVariant.value = 'success'
    errorMessages.value = []
    await loadCourses(pagination.currentPage, { preserveNotice: true })

    if (!courses.value.length && pagination.currentPage > 1) {
      await loadCourses(pagination.currentPage - 1, { preserveNotice: true })
    }
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isDeleting.value = false
    closeDeleteModal()
  }
}

async function removeCourse(id) {
  if (!confirm('Yakin hapus data course ini?')) {
    return
  }

  message.value = ''
  errorMessages.value = []

  try {
    const payload = await deleteCourse(id)
    message.value = payload?.message || 'Course berhasil dihapus.'
    errorMessages.value = []
    await loadCourses(pagination.currentPage, { preserveNotice: true })

    if (!courses.value.length && pagination.currentPage > 1) {
      await loadCourses(pagination.currentPage - 1, { preserveNotice: true })
    }
  } catch (error) {
    errorMessages.value = getErrorList(error)
  }
}

async function initializePage() {
  userRole.value = getUserRole()
  await loadCourses()

  const successMessage = route.query?.success
  if (typeof successMessage === 'string' && successMessage.trim()) {
    message.value = successMessage
    await router.replace({ name: route.name, query: {} })
  }
}

function goToCreate() {
  router.push({ name: 'courses.create' })
}

function goToEdit(id) {
  router.push({ name: 'courses.edit', params: { id } })
}

function searchCourses() {
  loadCourses(1, { preserveNotice: true })
}

onMounted(initializePage)
</script>

<template>
  <div>
    <Toast :message="toastMessage" :variant="toastVariant" @close="toastMessage = ''" />

    <div class="page-header flex-wrap mb-3">
      <h5 class="page-title mb-0">Kategori Course</h5>
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
            @keyup.enter="searchCourses"
          />
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" @click="searchCourses">
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
              <th>INSTRUCTOR</th>
              <th>DURATION</th>
              <th>PRICE</th>
              <th class="text-center">AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(course, index) in filteredCourses" :key="course.id">
              <td>{{ index + 1 }}</td>
              <td>{{ course.title }}</td>
              <td>{{ course.instructor }}</td>
              <td>{{ course.duration }}</td>
              <td>{{ course.price }}</td>
              <td>
                <div class="d-flex action-group">
                  <button v-if="canEdit" class="btn btn-outline-primary btn-sm mr-2" @click="goToEdit(course.id)">Edit</button>
                  <button v-if="canDelete" class="btn btn-danger btn-sm" @click="openDeleteModal(course.id)">Hapus</button>
                  <span v-if="!canEdit && !canDelete" class="text-muted small">No Actions</span>
                </div>
              </td>
            </tr>
            <tr v-if="!filteredCourses.length">
              <td colspan="6" class="text-center text-muted py-4">
                {{ searchQuery ? 'Data tidak ditemukan.' : 'Belum ada data courses.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-end mb-0">
          <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 || isLoading }">
            <a class="page-link" href="#" @click.prevent="loadCourses(pagination.currentPage - 1)">Prev</a>
          </li>
          <li class="page-item">
            <span class="page-link">
               {{ pagination.currentPage }} dari {{ pagination.lastPage }} (Total: {{ pagination.total }})
            </span>
          </li>
          <li class="page-item" :class="{ disabled: pagination.currentPage >= pagination.lastPage || isLoading }">
            <a class="page-link" href="#" @click.prevent="loadCourses(pagination.currentPage + 1)">Next</a>
          </li>
        </ul>
      </nav>
    </div>
    </div>
  </div>

  <div v-if="showDeleteModal" class="modal-backdrop-overlay">
    <div class="modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-bottom">
          <h5 class="modal-title">Konfirmasi Hapus</h5>
          <button type="button" class="close" @click="closeDeleteModal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="mb-0">
            Anda yakin ingin menghapus data course ini? Tindakan ini tidak dapat dibatalkan.
          </p>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-secondary" @click="closeDeleteModal" :disabled="isDeleting">
            Batal
          </button>
          <button type="button" class="btn btn-danger" @click="confirmDelete" :disabled="isDeleting">
            <span v-if="isDeleting" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
            {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-backdrop-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.modal-dialog-centered {
  width: 100%;
  max-width: 400px;
  margin: auto;
}

.modal-content {
  background: var(--white);
  border-radius: 0.75rem;
  border: none;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 1.5rem;
  background: #f8f9fa;
}

.modal-title {
  font-weight: 700;
  color: var(--gray-900);
  margin: 0;
}

.modal-body {
  padding: 1.5rem;
  color: var(--gray-700);
}

.modal-footer {
  padding: 1.5rem;
  background: #f8f9fa;
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
}

.modal-footer .btn {
  min-width: 100px;
}

.close {
  font-size: 1.5rem;
  line-height: 1;
  color: #000;
  opacity: 0.5;
  border: none;
  background: none;
  cursor: pointer;
  padding: 0;
}

.close:hover {
  opacity: 0.75;
}

.invalid-feedback {
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.text-danger {
  color: #dc3545;
}
</style>
