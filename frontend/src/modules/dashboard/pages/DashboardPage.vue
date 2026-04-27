<script setup>
import { ref, onMounted } from 'vue'
import api from '../../../services/api'
import { showToast } from '../../../services/toast'

const user = ref(null)
const stats = ref({
  books: 0,
  products: 0,
  courses: 0,
  users: 0,
})
const weeklyTrend = ref([])
const loading = ref(true)

onMounted(() => {
  fetchUserData()
  fetchStats()
})

async function fetchUserData() {
  try {
    const response = await api.get('/user')
    user.value = response.data
  } catch (error) {
    console.error('Error fetching user data:', error)
  }
}

async function fetchStats() {
  try {
    const response = await api.get('/dashboard/analytics')
    const payload = response.data?.data || {}
    const totals = payload.totals || {}

    stats.value = {
      books: Number(totals.books) || 0,
      products: Number(totals.products) || 0,
      courses: Number(totals.courses) || 0,
      users: Number(totals.users) || 0,
    }

    weeklyTrend.value = Array.isArray(payload.weekly_trend) ? payload.weekly_trend : []
  } catch (error) {
    showToast({
      variant: 'danger',
      message: 'Gagal memuat analytics dashboard.',
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="dashboard-container">
    <div class="welcome-section mb-2">
      <div class="row align-items-center">
        <div class="col">
          <h5 class="page-title mb-2">
            Selamat Datang, {{ user?.name || 'User' }}! 👋
          </h5>
          <p class="text-muted mb-0">
            Kelola data aplikasi Anda dengan mudah dan efisien.
          </p>
        </div>
      </div>
    </div>

    <div class="stats-grid">
      <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-3 mb-3">
          <div class="stat-card">
            <div class="stat-icon books">
              <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
              <p class="stat-label">Books</p>
              <h3 class="stat-number">{{ stats.books }}</h3>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3 mb-3">
          <div class="stat-card">
            <div class="stat-icon products">
              <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
              <p class="stat-label">Products</p>
              <h3 class="stat-number">{{ stats.products }}</h3>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3 mb-3">
          <div class="stat-card">
            <div class="stat-icon courses">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-content">
              <p class="stat-label">Courses</p>
              <h3 class="stat-number">{{ stats.courses }}</h3>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3 mb-3">
          <div class="stat-card">
            <div class="stat-icon users">
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
              <p class="stat-label">Users</p>
              <h3 class="stat-number">{{ stats.users }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="quick-actions-section">
      <div class="row">
        <div class="col">
          <h5 class="mb-4 font-weight-bold">Akses Cepat</h5>
        </div>
      </div>
      <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-3 mb-3">
          <RouterLink :to="{ name: 'books.index' }" class="action-link">
            <div class="action-card">
              <i class="fas fa-book"></i>
              <p>Kelola Books</p>
            </div>
          </RouterLink>
        </div>

        <div class="col-12 col-md-6 col-lg-3 mb-3">
          <RouterLink :to="{ name: 'products.index' }" class="action-link">
            <div class="action-card">
              <i class="fas fa-box"></i>
              <p>Kelola Products</p>
            </div>
          </RouterLink>
        </div>

        <div class="col-12 col-md-6 col-lg-3 mb-3">
          <RouterLink :to="{ name: 'courses.index' }" class="action-link">
            <div class="action-card">
              <i class="fas fa-graduation-cap"></i>
              <p>Kelola Courses</p>
            </div>
          </RouterLink>
        </div>

        <div class="col-12 col-md-6 col-lg-3 mb-3">
          <RouterLink :to="{ name: 'users.index' }" class="action-link">
            <div class="action-card">
              <i class="fas fa-users"></i>
              <p>Kelola Users</p>
            </div>
          </RouterLink>
        </div>
      </div>
    </div>

    <div class="weekly-section">
      <div class="row">
        <div class="col">
          <h5 class="mb-3 font-weight-bold">Tren 7 Hari Terakhir</h5>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body p-3">
          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <thead class="thead-primary">
                <tr>
                  <th>Tanggal</th>
                  <th class="text-center">Books</th>
                  <th class="text-center">Products</th>
                  <th class="text-center">Courses</th>
                  <th class="text-center">Users</th>
                  <th class="text-center">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in weeklyTrend" :key="item.date">
                  <td>{{ item.label }}</td>
                  <td class="text-center">{{ item.books }}</td>
                  <td class="text-center">{{ item.products }}</td>
                  <td class="text-center">{{ item.courses }}</td>
                  <td class="text-center">{{ item.users }}</td>
                  <td class="text-center font-weight-bold">{{ item.total }}</td>
                </tr>
                <tr v-if="!weeklyTrend.length && !loading">
                  <td colspan="6" class="text-center text-muted py-4">Belum ada data tren mingguan.</td>
                </tr>
                <tr v-if="loading">
                  <td colspan="6" class="text-center text-muted py-4">Memuat analytics...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped src="../../../styles/pages-shared.css"></style>
