<script setup>
import { onMounted, ref } from 'vue'
import PageBanner from '../../../components/PageBanner.vue'
import { getReports } from '../services/reportsApi'
import { getErrorList } from '../../books/utils/booksHelpers'

const reportData = ref(null)
const errorMessages = ref([])
const isLoading = ref(false)

async function loadReports() {
  isLoading.value = true
  errorMessages.value = []

  try {
    const payload = await getReports()
    reportData.value = payload?.data || null
  } catch (error) {
    errorMessages.value = getErrorList(error)
  } finally {
    isLoading.value = false
  }
}

onMounted(loadReports)
</script>

<template>
  <div>
    <div class="page-header flex-wrap mb-3">
      <h5 class="page-title mb-0">Reports</h5>
    </div>

    <PageBanner
      v-if="errorMessages.length"
      variant="danger"
      :items="errorMessages"
      class="mb-3"
      @close="errorMessages = []"
    />

    <div class="row" v-if="reportData && !isLoading">
      <div class="col-12 col-md-3 mb-3" v-for="item in [
        { label: 'Books', value: reportData.totals.books },
        { label: 'Products', value: reportData.totals.products },
        { label: 'Courses', value: reportData.totals.courses },
        { label: 'Users', value: reportData.totals.users },
      ]" :key="item.label">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small text-uppercase">{{ item.label }}</div>
            <div class="h2 mb-0 font-weight-bold">{{ item.value }}</div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="card shadow-sm">
      <div class="card-body text-center text-muted py-5">
        {{ isLoading ? 'Memuat reports...' : 'Belum ada data reports.' }}
      </div>
    </div>
  </div>
</template>