<script setup>
import { alertState, removeAlert } from '../services/alerts'
</script>

<template>
  <div class="global-alerts">
    <div
      v-for="item in alertState.items"
      :key="item.id"
      class="alert alert-dismissible fade show shadow-sm"
      :class="`alert-${item.type}`"
      role="alert"
    >
      {{ item.message }}
      <button type="button" class="btn-close" aria-label="Close" @click="removeAlert(item.id)"></button>
    </div>
  </div>
</template>
