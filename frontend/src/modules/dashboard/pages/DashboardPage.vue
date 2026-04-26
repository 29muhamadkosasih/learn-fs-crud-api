<script setup>
import { ref, onMounted } from 'vue'
import api from '../../../services/api'

const user = ref(null)
const stats = ref({
  books: 0,
  products: 0,
  courses: 0,
  users: 0,
})
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
    const [booksRes, productsRes, coursesRes, usersRes] = await Promise.all([
      api.get('/books'),
      api.get('/products'),
      api.get('/courses'),
      api.get('/users'),
    ])
    
    stats.value = {
      books: Array.isArray(booksRes.data?.data) ? booksRes.data.data.length : 0,
      products: Array.isArray(productsRes.data?.data) ? productsRes.data.data.length : 0,
      courses: Array.isArray(coursesRes.data?.data) ? coursesRes.data.data.length : 0,
      users: Array.isArray(usersRes.data?.data) ? usersRes.data.data.length : 0,
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
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
  </div>
</template>

<style scoped>
.dashboard-container {
  padding: 0;
}

.welcome-section {
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.page-title {
  color: var(--gray-900);
  font-weight: 700;
}

.stats-grid {
  display: grid;
  gap: 1.5rem;
}

.stat-card {
  background: var(--white);
  border: 1px solid var(--gray-200);
  border-radius: 0.75rem;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.stat-card:hover {
  border-color: var(--primary);
  box-shadow: 0 4px 12px rgba(78, 115, 223, 0.1);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 0.6rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: var(--white);
}

.stat-icon.books {
  background: linear-gradient(135deg, var(--primary) 0%, #4e73df 100%);
}

.stat-icon.products {
  background: linear-gradient(135deg, var(--success) 0%, #1cc88a 100%);
}

.stat-icon.courses {
  background: linear-gradient(135deg, var(--info) 0%, #36b9cc 100%);
}

.stat-icon.users {
  background: linear-gradient(135deg, var(--warning) 0%, #f6c23e 100%);
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--gray-600);
  margin: 0 0 0.25rem;
  font-weight: 500;
}

.stat-number {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0;
}

.quick-actions-section {
  margin-top: 0.5rem;
  margin-bottom: 1.5rem;
}

.action-link {
  text-decoration: none;
  display: block;
}

.action-card {
  background: var(--white);
  border: 1px solid var(--gray-200);
  border-radius: 0.75rem;
  padding: 2rem 1rem;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.action-card:hover {
  border-color: var(--primary);
  background: var(--gray-50);
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(78, 115, 223, 0.15);
}

.action-card i {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 0.75rem;
  display: block;
}

.action-card p {
  font-weight: 600;
  color: var(--gray-900);
  margin: 0;
  font-size: 0.95rem;
}

@media (max-width: 768px) {
  .stat-card {
    flex-direction: column;
    text-align: center;
  }

  .stat-number {
    font-size: 1.5rem;
  }
}
</style>
