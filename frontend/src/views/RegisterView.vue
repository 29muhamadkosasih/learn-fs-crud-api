<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { saveToken, saveUserData } from '../services/auth'

const router = useRouter()
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const isLoading = ref(false)
const errorMessage = ref('')
const validationMessage = ref('')
const currentYear = new Date().getFullYear()

const canSubmit = computed(() => {
  return (
    form.name.trim() !== ''
    && form.email.trim() !== ''
    && form.password.trim() !== ''
    && form.password_confirmation.trim() !== ''
    && !isLoading.value
  )
})

function getErrorMessage(error) {
  if (typeof error?.response?.data?.message === 'string') {
    return error.response.data.message
  }

  if (error?.response?.data && typeof error.response.data === 'object') {
    return Object.values(error.response.data).flat().join(', ')
  }

  return 'Register gagal. Coba lagi.'
}

async function register() {
  validationMessage.value = ''

  if (!form.name.trim() || !form.email.trim() || !form.password.trim() || !form.password_confirmation.trim()) {
    validationMessage.value = 'Semua field wajib diisi.'
    return
  }

  if (form.password !== form.password_confirmation) {
    validationMessage.value = 'Konfirmasi password tidak sama.'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await api.post('/register', {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })
    const token = response.data?.access_token
    const user = response.data?.user

    saveToken(token)
    if (user) {
      saveUserData(user)
    }

    await router.push({ name: 'dashboard' })
  } catch (error) {
    errorMessage.value = getErrorMessage(error)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <main class="login-page">
    <div class="container py-0">
      <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-12 col-xl-12">
          <div class="card login-card border-0 shadow-lg overflow-hidden">
            <div class="row no-gutters">
              <div class="col-lg-6 d-none d-lg-flex login-showcase p-5">
                <div class="w-100 d-flex flex-column">
                  <div class="login-brand mb-4">CRUD API</div>
                  <div class="showcase-content mt-auto mb-auto text-center">
                    <p class="showcase-kicker mb-2">Create your account</p>
                    <h1 class="showcase-title mb-3">JOIN WITH US</h1>
                    <p class="showcase-subtitle mb-0">
                      Daftarkan akun baru untuk mulai mengelola data aplikasi Anda.
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-6 bg-white">
                <div class="p-4 p-md-5 login-form-wrap">
                  <div class="mb-4 text-center text-lg-left">
                    <h2 class="h3 text-primary font-weight-bold mb-2">Register Account</h2>
                    <p class="text-muted mb-0">Isi data akun Anda untuk melanjutkan.</p>
                    <div v-if="validationMessage" class="alert alert-warning mt-3 mb-0" role="alert">
                      {{ validationMessage }}
                    </div>
                    <div v-if="errorMessage" class="alert alert-danger mt-3 mb-0" role="alert">
                      {{ errorMessage }}
                    </div>
                  </div>

                  <form @submit.prevent="register" novalidate>
                    <div class="form-group mb-3">
                      <label for="name">Name</label>
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="form-control login-input"
                        placeholder="Full Name"
                        autocomplete="name"
                        required
                      />
                    </div>

                    <div class="form-group mb-3">
                      <label for="email">Email</label>
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-control login-input"
                        placeholder="Email"
                        autocomplete="email"
                        required
                      />
                    </div>

                    <div class="form-group mb-3">
                      <label for="password">Password</label>
                      <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="form-control login-input"
                        placeholder="Password"
                        autocomplete="new-password"
                        required
                      />
                    </div>

                    <div class="form-group mb-3">
                      <label for="password-confirmation">Confirm Password</label>
                      <input
                        id="password-confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="form-control login-input"
                        placeholder="Confirm Password"
                        autocomplete="new-password"
                        required
                      />
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 login-meta">
                      <span class="small text-muted">Sudah punya akun?</span>
                      <RouterLink :to="{ name: 'login' }" class="small text-primary font-weight-bold">Login di sini</RouterLink>
                    </div>

                    <button class="btn btn-primary btn-block login-btn" :disabled="!canSubmit" type="submit">
                      <span v-if="isLoading" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                      {{ isLoading ? 'Memproses...' : 'DAFTAR' }}
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <p class="text-center text-muted small mb-0 mt-3">© {{ currentYear }} CRUD API</p>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.login-page {
  min-height: 90vh;
  background-color: var(--gray-100);
}

.login-card {
  border-radius: 0.85rem;
}

.login-showcase {
  background: linear-gradient(140deg, var(--info) 0%, var(--primary) 65%);
  position: relative;
  color: var(--white);
  min-height: 620px;
}

.login-showcase::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: linear-gradient(var(--white) 1px, transparent 1px), linear-gradient(90deg, var(--white) 1px, transparent 1px);
  background-size: 48px 48px;
  opacity: 0.08;
}

.login-showcase > * {
  position: relative;
  z-index: 1;
}

.login-brand {
  font-size: 0.95rem;
  font-weight: 700;
  letter-spacing: 0.06em;
}

.showcase-kicker {
  font-size: 1.05rem;
  font-weight: 600;
}

.showcase-title {
  font-size: clamp(2rem, 4vw, 3rem);
  line-height: 1.05;
  letter-spacing: 0.06em;
  font-weight: 700;
}

.showcase-subtitle {
  max-width: 320px;
  margin-left: auto;
  margin-right: auto;
  color: var(--gray-100);
}

.login-form-wrap {
  min-height: 620px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.login-input {
  min-height: 50px;
  border-left: 0.2rem solid var(--primary);
  border-radius: 0.45rem;
}

.login-meta {
  gap: 1rem;
}

.login-btn {
  border-radius: 999px;
  min-height: 48px;
  font-weight: 700;
  letter-spacing: 0.08em;
}

@media (max-width: 991.98px) {
  .login-form-wrap {
    min-height: auto;
  }
}
</style>