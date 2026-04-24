<script setup>
import { onMounted, ref } from 'vue'
import PageBanner from '../../../components/PageBanner.vue'
import { getCurrentUser } from '../services/usersApi'
import { getErrorList } from '../utils/usersHelpers'

const user = ref(null)
const isLoading = ref(false)
const errorMessages = ref([])

async function loadUser() {
  isLoading.value = true
  errorMessages.value = []

  try {
    const payload = await getCurrentUser()
    user.value = payload
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isLoading.value = false
  }
}

onMounted(loadUser)
</script>

<template>
  <div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0 page-title h5">User Profile</h5>
      <button class="btn btn-outline-primary btn-sm" :disabled="isLoading" @click="loadUser">
        {{ isLoading ? 'Loading...' : 'Refresh' }}
      </button>
    </div>

    <div class="card-body">
      <PageBanner v-if="errorMessages.length" variant="danger" :items="errorMessages" class="mb-3" />

      <PageBanner v-if="isLoading" variant="secondary" message="Loading data user..." class="mb-3" :timeout="0" />

      <div v-else-if="user" class="table-responsive">
        <table class="table table-bordered mb-0">
          <tbody>
            <tr>
              <th class="w-25">ID</th>
              <td>{{ user.id }}</td>
            </tr>
            <tr>
              <th>Name</th>
              <td>{{ user.name }}</td>
            </tr>
            <tr>
              <th>Email</th>
              <td>{{ user.email }}</td>
            </tr>
            <tr>
              <th>Role</th>
              <td>
                <span class="badge" :class="user.role === 'admin' ? 'badge-primary' : 'badge-success'">
                  {{ user.role || 'user' }}
                </span>
              </td>
            </tr>
            <tr>
              <th>Created At</th>
              <td>{{ user.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-else class="mb-0 text-muted">Data user tidak ditemukan.</p>
    </div>
  </div>
</template>
