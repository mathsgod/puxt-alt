<template>
  <th
    v-show="isVisible"
    @click="click"
    class="unselectable"
    v-bind:class="{
      sortable: sortable,
      sorting_desc: localOrder == 'desc',
      sorting_asc: localOrder == 'asc',
    }"
    :style="style"
  >
    {{ label }}
  </th>
</template>
<style scoped>
.unselectable {
  user-select: none;
  -moz-user-select: none;
  -khtml-user-select: none;
  -webkit-user-select: none;
  -o-user-select: none;
}
.sortable {
  cursor: pointer;
  background: url(./assets/images/sort_both.png) no-repeat center right;
}

.sorting_desc {
  background: url(./assets/images/sort_desc.png) no-repeat center right;
}

.sorting_asc {
  background: url(./assets/images/sort_asc.png) no-repeat center right;
}
</style>
<script>
export default {
  props: {
    label: String,
    prop: String,
    type: String,
    sortable: Boolean,
    searchable: Boolean,
    searchType: {
      type: String,
      default: "text",
    },
    searchOption: {
      type: Array,
      default() {
        return [];
      },
    },
    searchMethod: {
      type: String,
      default: "like",
    },
    order: String,
    visible: {
      type: Boolean,
      default: true,
    },
    editable: Boolean,
    editType: String,
    nowrap: Boolean,
    width: String,
  },
  data() {
    return {
      localOrder: this.order,
      isVisible: this.visible,
    };
  },
  inject: ["rTable"],
  computed: {
    style() {
      let style = {};
      style["white-space"] = "nowrap";
      style["overflow"]="hidden";

      if (this.width) {
        style.width = this.width;
        style["min-width"] = this.width;
        style["max-width"] = this.width;
      }

      return style;
    },
  },
  watch: {
    localOrder() {
      this.$emit("order-changed", this.localOrder);
    },
  },
  methods: {
    click() {
      if (!this.sortable) return;
      if (this.localOrder == "desc") {
        this.localOrder = "asc";
      } else {
        this.localOrder = "desc";
      }
    },
  },
};
</script>