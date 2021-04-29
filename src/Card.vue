<template>
  <div :class="c">
    <div class="overlay" v-if="myLoading">
      <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>
    <slot></slot>
  </div>
</template>

<script>
export default {
  name: "card",
  props: {
    type: String,
    outline: Boolean,
    loading: Boolean,
    collapsible: {
      type: Boolean,
      default: false,
    },
    pinable: {
      type: Boolean,
      default: false,
    },
    dataUri: String,
  },
  data() {
    return {
      myLoading: this.loading,
    };
  },
  watch: {
    loading(value) {
      this.myLoading = value;
    },
  },
  computed: {
    c() {
      let c = ["card"];

      if (this.outline) {
        c.push("card-outline");
      }

      switch (this.type) {
        case "primary":
          c.push("card-primary");
          break;
        case "secondary":
          c.push("card-secondary");
          break;
        case "success":
          c.push("card-success");
          break;
        case "info":
          c.push("card-info");
          break;
        case "warning":
          c.push("card-warning");
          break;
        case "danger":
          c.push("card-danger");
          break;
        case "dark":
          c.push("card-dark");
          break;
      }

      return c;
    },
    header() {
      return this.$children.filter((o) => {
        return o.$vnode.componentOptions.tag == "card-header";
      });
    },
    body() {
      return this.$children.filter((o) => {
        return o.$vnode.componentOptions.tag == "card-body";
      });
    },
    footer() {
      return this.$children.filter((o) => {
        return o.$vnode.componentOptions.tag == "card-footer";
      });
    },
  },
  mounted() {
    this.header.forEach((h) => {
      h.collapsible = this.collapsible;
      h.pinable = this.pinable;

      h.$on("collapsed", (collapsed) => {
        this.$emit("collapsed", collapsed);

        if (this.dataUri) {
          var data = {
            type: "card",
            layout: {
              collapsed: collapsed,
            },
            uri: this.dataUri,
          };
          this.$http.post("UI/save", data);
        }
      });

      h.$on("pinned", (pinned) => {
        this.$emit("pinned", pinned);
      });
    });
  },
  methods: {
    showLoading() {
      this.myLoading = true;
    },
    hideLoading() {
      this.myLoading = false;
    },
    pin() {
      this.header.forEach((h) => h.pin());
    },
    unpin() {
      this.header.forEach((h) => h.unpin());
    },
  },
};
</script>