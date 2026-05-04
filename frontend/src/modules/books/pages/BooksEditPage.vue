<script setup>
    import {
        onMounted,
        ref
    } from 'vue'
    import {
        useRoute,
        useRouter
    } from 'vue-router'
    import PageBanner from '../../../components/PageBanner.vue'
    import BookForm from '../components/BookForm.vue'
    import {
        getBookById,
        updateBook
    } from '../services/booksApi'
    import {
        showToast
    } from '../../../services/toast'
    import {
        getErrorList,
        mapBookDetail
    } from '../utils/booksHelpers'

    const route = useRoute()
    const router = useRouter()

    const isLoading = ref(false)
    const isSaving = ref(false)
    const message = ref('')
    const formInitialValues = ref({
        name: '',
        harga: '',
        stock: '',
    })
    const errorMessages = ref([])

    async function loadDetail() {
        isLoading.value = true
        message.value = ''
        errorMessages.value = []

        try {
            const payload = await getBookById(route.params.id)
            const book = mapBookDetail(payload)

            formInitialValues.value = {
                name: book?.name || '',
                harga: book?.harga || '',
                stock: book?.stock || '',
            }
        } catch (error) {
            errorMessages.value = getErrorList(error)
        } finally {
            isLoading.value = false
        }
    }

    async function submit(form) {
        isSaving.value = true
        message.value = ''
        errorMessages.value = []

        try {
            const payload = new FormData()
            payload.append('name', form.name)
            payload.append('harga', form.harga)
            payload.append('stock', form.stock)
            if (form.image) {
                payload.append('image', form.image)
            }

            await updateBook(route.params.id, payload)
            showToast({
                variant: 'success',
                message: 'Book berhasil diperbarui.',
            })
            router.push({
                name: 'books.index',
            })
        } catch (error) {
            errorMessages.value = getErrorList(error)
        } finally {
            isSaving.value = false
        }
    }

    function cancel() {
        router.push({
            name: 'books.index'
        })
    }

    onMounted(loadDetail)
</script>

<template>
    <div>
        <div class="page-header">
            <h5 class="page-title mb-0">Edit Buku</h5>
            <button class="btn btn-secondary" @click="cancel">Kembali</button>
        </div>

        <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />
        <BookForm v-else :initial-values="formInitialValues" :loading="isSaving" :require-image="false"
            submit-label="Update" @submit="submit" @cancel="cancel" />
    </div>
</template>
