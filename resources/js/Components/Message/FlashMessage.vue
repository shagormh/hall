<template>
</template>

<script setup lang="ts">
import toastr from 'toastr';
import 'toastr/toastr.scss';
import { watch } from 'vue';

const props = defineProps({
    flash: Object,
})

// Function to show toast
const showToast = (flash: any) => {
    if(flash && flash.success) {
        toastr.success(flash.success)
        flash.success = null;
    } else if (flash && flash.error){
        toastr.error(flash.error)
        flash.error = null;
    }
}

// Check on mount
showToast(props.flash);

// Watch for changes (for subsequent requests)
watch(() => props.flash, (newFlash) => {
    showToast(newFlash);
}, { deep: true });

toastr.options = {
  "closeButton": false,
  "debug": false,
  "newestOnTop": false,
  "progressBar": true,
  "positionClass": "toast-top-right",
  "preventDuplicates": false,
  "showDuration": 300,
  "hideDuration": 1000,
  "timeOut": 5000,
  "extendedTimeOut": 1000,
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut"
}
</script>
