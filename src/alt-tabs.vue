<template>
  <div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
      <ul class="nav nav-tabs">
        <slot></slot>
      </ul>
    </div>
    <div class="card-body">
      <div class="tab-content">
        <div
          class="tab-pane fade active show"
          role="tabpanel"
          ref="content"
        ></div>
      </div>
    </div>
    <!-- /.card -->
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  async mounted() {
    let tabs = [];
    this.$slots.default.forEach((vnode) => {
      if (vnode.tag == undefined) return;
      tabs.push(vnode.componentInstance);
    });

    let link;
    for (let tab of tabs) {
      tab.$on("selected", (e) => {
        this.loadContent(e);
      });

      console.log(tab.active);

      if (tab.active) {
        link = tab.link;
      }
    }
    if (link) {
      await this.loadContent(link);
    }
  },
  methods: {
    async loadContent(url) {
      console.log("load content", url);
      let resp = await this.$http.get(url);
      //this.content = resp.body;
      window.$(this.$refs.content).html(resp.body);
    },
  },
};
</script>