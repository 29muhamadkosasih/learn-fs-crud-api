<script setup>
    import {
        computed,
        onMounted,
        reactive,
        ref
    } from 'vue'
    import {
        useRoute,
        useRouter
    } from 'vue-router'
    import PageBanner from '../../../components/PageBanner.vue'
    import Toast from '../../../components/Toast.vue'
    import {
        deleteBook,
        getBooks
    } from '../services/booksApi'
    import {
        getErrorList,
        mapPaginatedBooks
    } from '../utils/booksHelpers'
    import { getUserRole } from '../../../services/auth'
    import { canPerformAction } from '../../../services/permissions'

    const route = useRoute()
    const router = useRouter()

    const books = ref([])
    const isLoading = ref(false)
    const message = ref('')
    const toastMessage = ref('')
    const toastVariant = ref('success')
    const errorMessages = ref([])
    const searchQuery = ref('')
    const userRole = ref('user')
    
    const showDeleteModal = ref(false)
    const deleteItemId = ref(null)
    const isDeleting = ref(false)
    
    const pagination = reactive({
        currentPage: 1,
        lastPage: 1,
        total: 0,
    })

    const filteredBooks = computed(() => {
        const keyword = searchQuery.value.trim().toLowerCase()
        let result = books.value

        // Filter by search query
        if (keyword) {
            result = result.filter((book) => {
                return [book.name, book.harga, book.stock]
                    .filter((value) => value !== null && value !== undefined)
                    .some((value) => String(value).toLowerCase().includes(keyword))
            })
        }

        return result
    })

    const canCreate = computed(() => canPerformAction(userRole.value, 'books', 'create'))
    const canEdit = computed(() => canPerformAction(userRole.value, 'books', 'edit'))
    const canDelete = computed(() => canPerformAction(userRole.value, 'books', 'delete'))

    async function loadBooks(page = 1, options = {}) {
        const {
            preserveNotice = false
        } = options

        isLoading.value = true

        if (!preserveNotice) {
            message.value = ''
            errorMessages.value = []
        }

        try {
            const payload = await getBooks(page)
            const mapped = mapPaginatedBooks(payload)

            books.value = mapped.items
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
        errorMessages.value = []

        try {
            const payload = await deleteBook(deleteItemId.value)
            toastMessage.value = payload?.message || 'Book berhasil dihapus.'
            toastVariant.value = 'success'
            await loadBooks(pagination.currentPage, {
                preserveNotice: false
            })

            if (!books.value.length && pagination.currentPage > 1) {
                await loadBooks(pagination.currentPage - 1, {
                    preserveNotice: true
                })
            }
        } catch (error) {
            errorMessages.value = getErrorList(error)
        } finally {
            isDeleting.value = false
            closeDeleteModal()
        }
    }

    async function initializePage() {
        // Load user role
        userRole.value = getUserRole()
        
        await loadBooks()

        const successMessage = route.query?.success
        if (typeof successMessage === 'string' && successMessage.trim()) {
            message.value = successMessage
            await router.replace({
                name: route.name,
                query: {}
            })
        }
    }

    function goToCreate() {
        router.push({
            name: 'books.create'
        })
    }

    function goToEdit(id) {
        router.push({
            name: 'books.edit',
            params: { id }
        })
    }

    function searchBooks() {
        loadBooks(1, {
            preserveNotice: true
        })
    }

    onMounted(initializePage)
</script>

<template>
    <div>
        <Toast :message="toastMessage" :variant="toastVariant" @close="toastMessage = ''" />
        
        <div class="page-header flex-wrap mb-3">
            <h5 class="page-title mb-0">Kategori Buku</h5>
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
                    <input v-model="searchQuery" type="text" class="form-control"
                        placeholder="masukkan kata kunci dan enter..." @keyup.enter="searchBooks" />
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" @click="searchBooks">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-3">
                <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3"
                    @close="errorMessages = []" />

                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-primary">
                            <tr>
                                <th class="text-center">NO.</th>
                                <th>NAMA</th>
                                <th>HARGA</th>
                                <th>STOCK</th>
                                <th>IMAGE</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(book, index) in filteredBooks" :key="book.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ book.name }}</td>
                                <td>{{ book.harga }}</td>
                                <td>{{ book.stock }}</td>
                                <td>
                                    <a v-if="book.image" :href="book.image" target="_blank"
                                        class="link-primary">Lihat</a>
                                </td>
                                <td>
                                    <div class="d-flex action-group">
                                        <button v-if="canEdit" class="btn btn-outline-primary btn-sm mr-2"
                                            @click="goToEdit(book.id)">Edit</button>
                                        <button v-if="canDelete" class="btn btn-danger btn-sm"
                                            @click="openDeleteModal(book.id)">Hapus</button>
                                        <span v-if="!canEdit && !canDelete" class="text-muted small">
                                            No Actions
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredBooks.length">
                                <td colspan="6" class="text-center text-muted py-4">
                                    {{ searchQuery ? 'Data tidak ditemukan.' : 'Belum ada data books.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-end mb-0">
                        <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 || isLoading }">
                            <a class="page-link" href="#"
                                @click.prevent="loadBooks(pagination.currentPage - 1)">Prev</a>
                        </li>
                        <li class="page-item">
                            <span class="page-link">
                                {{ pagination.currentPage }} dari {{ pagination.lastPage }} (Total:
                                {{ pagination.total }})
                            </span>
                        </li>
                        <li class="page-item"
                            :class="{ disabled: pagination.currentPage >= pagination.lastPage || isLoading }">
                            <a class="page-link" href="#"
                                @click.prevent="loadBooks(pagination.currentPage + 1)">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div v-if="showDeleteModal" class="modal-backdrop-overlay">
        <div class="modal-dialog-centered ">
            <div class="modal-content ">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="close" @click="closeDeleteModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        Anda yakin ingin menghapus data book ini? Tindakan ini tidak dapat dibatalkan.
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
</style>
