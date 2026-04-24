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
    import {
        deleteProduct,
        getProducts
    } from '../services/productsApi'
    import {
        getErrorList,
        mapPaginatedProducts
    } from '../utils/productsHelpers'

    const route = useRoute()
    const router = useRouter()

    const products = ref([])
    const isLoading = ref(false)
    const message = ref('')
    const errorMessages = ref([])
    const searchQuery = ref('')
    const pagination = reactive({
        currentPage: 1,
        lastPage: 1,
        total: 0,
    })

    const filteredProducts = computed(() => {
        const keyword = searchQuery.value.trim().toLowerCase()

        if (!keyword) {
            return products.value
        }

        return products.value.filter((product) => {
            return [product.name, product.sku, product.stock, product.price, product.description]
                .filter((value) => value !== null && value !== undefined)
                .some((value) => String(value).toLowerCase().includes(keyword))
        })
    })

    async function loadProducts(page = 1, options = {}) {
        const {
            preserveNotice = false
        } = options

        isLoading.value = true

        if (!preserveNotice) {
            message.value = ''
            errorMessages.value = []
        }

        try {
            const payload = await getProducts(page)
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

    async function removeProduct(id) {
        if (!confirm('Yakin hapus data product ini?')) {
            return
        }

        message.value = ''
        errorMessages.value = []

        try {
            const payload = await deleteProduct(id)
            message.value = payload?.message || 'Product berhasil dihapus.'
            errorMessages.value = []
            await loadProducts(pagination.currentPage, {
                preserveNotice: true
            })

            if (!products.value.length && pagination.currentPage > 1) {
                await loadProducts(pagination.currentPage - 1, {
                    preserveNotice: true
                })
            }
        } catch (error) {
            errorMessages.value = getErrorList(error)
        }
    }

    async function initializePage() {
        await loadProducts()

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
            name: 'products.create'
        })
    }

    function goToEdit(id) {
        router.push({
            name: 'products.edit',
            params: {
                id
            }
        })
    }

    function searchProducts() {
        loadProducts(1, {
            preserveNotice: true
        })
    }

    onMounted(initializePage)
</script>

<template>
    <div>
        <div class="page-header flex-wrap mb-3">
            <h5 class="page-title mb-0">Kategori Produk</h5>
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
                    <input v-model="searchQuery" type="text" class="form-control"
                        placeholder="masukkan kata kunci dan enter..." @keyup.enter="searchProducts" />
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" @click="searchProducts">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-3">
                <PageBanner v-if="message" variant="success" :message="message" class="mb-3"
                    @close="message = ''" />

                <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3"
                    @close="errorMessages = []" />

                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-primary">
                            <tr>
                                <th class="text-center">NO.</th>
                                <th>NAMA</th>
                                <th>SKU</th>
                                <th>STOCK</th>
                                <th>PRICE</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(product, index) in filteredProducts" :key="product.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ product . name }}</td>
                                <td>{{ product . sku }}</td>
                                <td>{{ product . stock }}</td>
                                <td>{{ product . price }}</td>
                                <td>
                                    <div class="d-flex action-group">
                                        <button class="btn btn-outline-primary btn-sm mr-2"
                                            @click="goToEdit(product.id)">Edit</button>
                                        <button class="btn btn-danger btn-sm"
                                            @click="removeProduct(product.id)">Hapus</button>
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
                            <a class="page-link" href="#"
                                @click.prevent="loadProducts(pagination.currentPage - 1)">Prev</a>
                        </li>
                        <li class="page-item">
                            <span class="page-link">
                                {{ pagination . currentPage }} dari {{ pagination . lastPage }} (Total:
                                {{ pagination . total }})
                            </span>
                        </li>
                        <li class="page-item"
                            :class="{ disabled: pagination.currentPage >= pagination.lastPage || isLoading }">
                            <a class="page-link" href="#"
                                @click.prevent="loadProducts(pagination.currentPage + 1)">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>
