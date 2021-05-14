<template>
  <li class="nav-item">
    <a
      class="nav-link"
      :class="{ active: active }"
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
    link: String,
    active: Boolean,
  },
  methods: {
    onClick() {
      this.$emit("selected", this.link);
    },
  },
};
</script>