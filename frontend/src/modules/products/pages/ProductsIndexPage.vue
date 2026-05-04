<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import PageBanner from '../../../components/PageBanner.vue'
import { showToast } from '../../../services/toast'
import { deleteProduct, getProducts } from '../services/productsApi'
import { getErrorList, mapPaginatedProducts } from '../utils/productsHelpers'
import { getUserRole } from '../../../services/auth'
import { canPerformAction } from '../../../services/permissions'

const router = useRouter()

const products = ref([])
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

const filteredProducts = computed(() => {
    const keyword = searchQuery.value.trim().toLowerCase()

    let filtered = products.value

    if (keyword) {
        filtered = products.value.filter((product) => {
            return [product.name, product.sku, product.stock, product.price, product.description]
                .filter((value) => value !== null && value !== undefined)
                .some((value) => String(value).toLowerCase().includes(keyword))
        })
    }

    return filtered
})

const canCreate = computed(() => canPerformAction(userRole.value, 'products', 'create'))
const canEdit = computed(() => canPerformAction(userRole.value, 'products', 'edit'))
const canDelete = computed(() => canPerformAction(userRole.value, 'products', 'delete'))

async function loadProducts(page = 1, options = {}) {
    const { preserveNotice = false } = options

    isLoading.value = true

    if (!preserveNotice) {
        errorMessages.value = []
    }

    try {
        const payload = await getProducts(page, perPage.value)
        const mapped = mapPaginatedProducts(payload)

        products.value = mapped.items
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
        text: 'Anda yakin ingin menghapus data product ini? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: async () => {
            try {
                return await deleteProduct(id)
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
        message: result.value?.message || 'Product berhasil dihapus.',
    })
    errorMessages.value = []
    await loadProducts(pagination.currentPage, { preserveNotice: true })

    if (!products.value.length && pagination.currentPage > 1) {
        await loadProducts(pagination.currentPage - 1, { preserveNotice: true })
    }
}

async function initializePage() {
    userRole.value = getUserRole()
    await loadProducts()
}

function goToCreate() {
    router.push({ name: 'products.create' })
}

function goToEdit(id) {
    router.push({ name: 'products.edit', params: { id } })
}

function searchProducts() {
    loadProducts(1, { preserveNotice: true })
}

function onPerPageChange() {
    loadProducts(1, { preserveNotice: true })
}

onMounted(initializePage)
</script>

<template>
    <div>
        <div class="page-header flex-wrap mb-3">
            <h5 class="page-title mb-0">Data Product</h5>
            <button v-if="canCreate" class="btn btn-primary" @click="goToCreate">Tambah</button>
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

                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <label class="mb-0 me-2">Show entries &nbsp;</label>
                        <select v-model="perPage" class="form-select form-control-sm ms-5 w-auto" @change="onPerPageChange">
                            <option v-for="option in perPageOptions" :key="option" :value="option">
                                {{ option === 'all' ? 'All' : option }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <div class="d-flex justify-content-end">
                            <div class="input-group" style="max-width: 350px; width:100%;">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="form-control"
                                    placeholder="masukkan kata kunci dan enter..."
                                    @keyup.enter="searchProducts"
                                />
                                <button class="btn btn-outline-secondary" type="button" @click="searchProducts">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-primary">
                            <tr>
                                <th width="1px" class="text-center">NO.</th>
                                <th>NAMA</th>
                                <th>SKU</th>
                                <th>STOCK</th>
                                <th>PRICE</th>
                                <th width="150px" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(product, index) in filteredProducts" :key="product.id">
                                <td class="text-center">{{ index + 1 }}</td>
                                <td>{{ product.name }}</td>
                                <td>{{ product.sku }}</td>
                                <td>{{ product.stock }}</td>
                                <td>{{ product.price }}</td>
                                <td>
                                    <div class="d-flex action-group">
                                        <button v-if="canEdit" class="btn btn-outline-primary btn-sm mr-2" @click="goToEdit(product.id)">Edit</button>
                                        <button v-if="canDelete" class="btn btn-danger btn-sm" @click="confirmDelete(product.id)">Hapus</button>
                                        <span v-if="!canEdit && !canDelete" class="text-muted small">No Actions</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredProducts.length">
                                <td colspan="6" class="text-center text-muted py-4">
                                    {{ searchQuery ? 'Data tidak ditemukan.' : 'Belum ada data products.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-end mb-0">
                        <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 || isLoading }">
                            <a class="page-link" href="#" @click.prevent="loadProducts(pagination.currentPage - 1)">Prev</a>
                        </li>
                        <li class="page-item">
                            <span class="page-link">{{ pagination.currentPage }} dari {{ pagination.lastPage }} (Total: {{ pagination.total }})</span>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.currentPage >= pagination.lastPage || isLoading }">
                            <a class="page-link" href="#" @click.prevent="loadProducts(pagination.currentPage + 1)">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>
