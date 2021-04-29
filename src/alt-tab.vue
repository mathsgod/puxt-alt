<template>
  <li class="nav-item">
    <a
      class="nav-link"
      data-toggle="pill"
      href="javascript:void(0)"
      role="tab"
      @click="onClick()"
      >{{ label }}</a
    >
  </li>
</template>
<script>
export default {
  props: {
    label: String,
    src: String,
  },
  methods: {
    onClick() {
      this.$emit("selected", this.src);
    },
  },
};
</script>