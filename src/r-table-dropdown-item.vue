<template>
  <el-dropdown-item v-text="label" @click.native="onClick"></el-dropdown-item>
</template>
<script>
export default {
  props: {
    url: String,
    label: String,
  },
  methods: {
    onClick() {
      this.$emit("click");
    },
    clickCallback(table) {
      let filter = Object.values(table.searchData);

      var url = this.url;
      if (url.indexOf("?") === -1) {
        url += "?";
      } else {
        url += "&";
      }

      const params = window.$.param({
        _rt_request: 1,
        filter,
      });
      url += params;

      window.open(url, "_blank");
      //console.log(table, "callback");
    },
  },
};
</script>