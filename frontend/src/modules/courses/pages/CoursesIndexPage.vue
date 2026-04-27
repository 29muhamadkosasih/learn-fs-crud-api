<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import PageBanner from '../../../components/PageBanner.vue'
import { deleteCourse, getCourses } from '../services/coursesApi'
import { getErrorList, mapPaginatedCourses } from '../utils/coursesHelpers'
import { showToast } from '../../../services/toast'
import { getUserRole } from '../../../services/auth'
import { canPerformAction } from '../../../services/permissions'

const router = useRouter()

const courses = ref([])
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
    errorMessages.value = []
  }

  try {
    const payload = await getCourses(page, perPage.value)
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

async function confirmDelete(id) {
  errorMessages.value = []

  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: 'Anda yakin ingin menghapus data course ini? Tindakan ini tidak dapat dibatalkan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#dc3545',
    reverseButtons: true,
    showLoaderOnConfirm: true,
    preConfirm: async () => {
      try {
        return await deleteCourse(id)
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
    message: result.value?.message || 'Course berhasil dihapus.',
  })
  errorMessages.value = []
  await loadCourses(pagination.currentPage, { preserveNotice: true })

  if (!courses.value.length && pagination.currentPage > 1) {
    await loadCourses(pagination.currentPage - 1, { preserveNotice: true })
  }
}

async function initializePage() {
  userRole.value = getUserRole()
  await loadCourses()
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

function onPerPageChange() {
  loadCourses(1, { preserveNotice: true })
}

onMounted(initializePage)
</script>

<template>
  <div>
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
                  <button v-if="canDelete" class="btn btn-danger btn-sm" @click="confirmDelete(course.id)">Hapus</button>
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

</template>

<style scoped src="../../../styles/pages-shared.css"></style>
