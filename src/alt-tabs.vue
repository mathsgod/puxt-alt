<template>
  <div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
      <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
        <slot></slot>
      </ul>
    </div>
    <div class="card-body">
      <div class="tab-content" id="custom-tabs-four-tabContent">
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
  mounted() {
    let tabs = [];
    this.$slots.default.forEach((vnode) => {
      if (vnode.tag == undefined) return;
      tabs.push(vnode.componentInstance);
    });

    for (let tab of tabs) {
      tab.$on("selected", (e) => {
        console.log(e);
      });
    }
  },
};
</script>