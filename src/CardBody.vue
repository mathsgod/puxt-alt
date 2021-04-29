<template>
  <div>
    <slot></slot>
  </div>
</template>

<script>
export default {
  name: "card-body",
  mounted() {
    if (!this.$el.classList.contains("card-body")) {
      this.$el.classList.add("card-body");
    }
  },
};
</script>