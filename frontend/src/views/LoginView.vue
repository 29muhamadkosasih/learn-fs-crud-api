<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { saveToken } from '../services/auth'

const router = useRouter()
const form = reactive({
  email: '',
  password: '',
})

const isLoading = ref(false)
const errorMessage = ref('')

function getErrorMessage(error) {
  if (typeof error?.response?.data?.message === 'string') {
    return error.response.data.message
  }

  if (error?.response?.data && typeof error.response.data === 'object') {
    return Object.values(error.response.data).flat().join(', ')
  }

  return 'Login gagal. Coba lagi.'
}

async function login() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await api.post('/login', form)
    const token = response.data?.access_token
    saveToken(token)
    await router.push({ name: 'books.index' })
  } catch (error) {
    errorMessage.value = getErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <main class="bg-gradient-primary" style="min-height: 100vh;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
          <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
              <div class="row">
                <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                <div class="col-lg-6">
                  <div class="p-5">
                    <div class="text-center">
                      <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                    </div>

                    <form class="user" @submit.prevent="login">
                      <div class="form-group">
                        <input v-model="form.email" type="email" class="form-control form-control-user" placeholder="Enter Email Address..." />
                      </div>

                      <div class="form-group">
                        <input v-model="form.password" type="password" class="form-control form-control-user" placeholder="Password" />
                      </div>

                      <button class="btn btn-primary btn-user btn-block" :disabled="isLoading" type="submit">
                        {{ isLoading ? 'Loading...' : 'Login' }}
                      </button>
                    </form>

                    <div v-if="errorMessage" class="alert alert-danger mt-3 mb-0" role="alert">{{ errorMessage }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>