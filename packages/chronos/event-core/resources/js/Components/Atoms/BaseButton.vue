<template>
  <button
    :class="['chronos-btn', `chronos-btn--${variant}`]"
    :type="type"
    @click="$emit('click', $event)"
  >
    <slot />
  </button>
</template>
  
  <script setup>
  defineProps({
    variant: {
      type: String,
      default: 'primary' // primary, ghost, icon, etc.
    },
    type: {
      type: String,
      default: 'button'
    }
  });
  
  defineEmits(['click']);
  </script>
  
  <style lang="scss" scoped>
  .chronos-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    
    &--ghost {
      background: transparent;
      &:hover { background: rgb(0 0 0 / 5%); }
    }
  }
  </style>