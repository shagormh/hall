<script setup lang="ts">
import { onMounted } from 'vue';
import { injectSpeedInsights } from '@vercel/speed-insights';

onMounted(() => {
  injectSpeedInsights();
});
</script>

<template>
  <!-- Speed Insights tracking component -->
  <div style="display: none;"></div>
</template>
