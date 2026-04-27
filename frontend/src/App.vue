<script setup>
import Toast from './components/Toast.vue'
import { hideToast, toastState } from './services/toast'
</script>

<template>
  <RouterView />
  <Toast
    :message="toastState.message"
    :variant="toastState.variant"
    :duration="toastState.duration"
    @close="hideToast"
  />
</template>
