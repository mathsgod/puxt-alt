<template>
  <div class="card-header">
    <slot></slot>
    <slot name="title" v-if="!title"></slot>
    <div class="card-title" v-else>
      <i :class="icon" v-if="icon"></i>
      {{title}}
    </div>

    <div class="card-tools">
      <slot name="tools"></slot>

      <button v-if="pinable" type="button" class="btn btn-tool" @click="togglePin()">
        <i class="fa fa-fw" :class="[pinned?'fa-thumbtack':'fa-arrows-alt']"></i>
      </button>

      <button
        type="button"
        class="btn btn-tool"
        data-card-widget="collapse"
        v-if="collapsible"
        @click="toggleCollapse()"
      >
        <i v-if="collapsed" class="fas fa-plus"></i>
        <i v-else class="fas fa-minus"></i>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: "card-header",
  props: ["title", "icon"],
  data() {
    return {
      pinable: false,
      pinned: true,
      collapsible: false,
      collapsed: false,
    };
  },
  methods: {
    togglePin() {
      this.pinned = !this.pinned;
      this.$emit("pinned", this.pinned);
    },
    toggleCollapse() {
      this.collapsed = !this.collapsed;
      this.$emit("collapsed", this.collapsed);
    },
    pin() {
      this.pinned = true;
    },
    unpin() {
      this.pinned = false;
    },
  },
};
</script>