<script setup>
    import {
        ref
    } from 'vue'
    import {
        useRouter
    } from 'vue-router'
    import PageBanner from '../../../components/PageBanner.vue'
    import BookForm from '../components/BookForm.vue'
    import {
        createBook
    } from '../services/booksApi'
    import {
        showToast
    } from '../../../services/toast'
    import {
        getErrorList
    } from '../utils/booksHelpers'

    const router = useRouter()
    const isSaving = ref(false)
    const message = ref('')
    const errorMessages = ref([])

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

            const response = await createBook(payload)
            showToast({
                variant: 'success',
                message: response?.message || 'Book berhasil ditambahkan.',
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
</script>

<template>
    <div>
        <div class="page-header">
            <h5 class="page-title mb-0">Tambah Buku</h5>
            <button class="btn btn-secondary" @click="cancel">Kembali</button>
        </div>
        <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />
        <BookForm :loading="isSaving" :require-image="true" submit-label="Simpan" @submit="submit"
            @cancel="cancel" />
    </div>
</template>
