<template>
  <td @click="onClick" :style="style">
    <template v-if="editMode">
      <template v-if="column.editType == 'text'">
        <el-input
          ref="edit_element"
          v-model="localValue"
          size="mini"
          @blur="updateData()"
        ></el-input>
      </template>

      <template v-if="column.editType == 'date'">
        <el-date-picker
          ref="edit_element"
          v-model="localValue"
          size="mini"
          value-format="yyyy-MM-dd"
          @change="updateData()"
        ></el-date-picker>
      </template>
    </template>
    <template v-else>
      <runtime-template-compiler
        v-if="type == 'vue'"
        :template="value"
      ></runtime-template-compiler>
      <template v-else-if="type == 'subrow'">
        <el-button
          size="mini"
          :icon="showSubRow ? 'el-icon-arrow-down' : 'el-icon-arrow-up'"
          @click="toggleSubRow()"
        ></el-button>
      </template>
      <template v-else-if="type == 'html'">
        <div v-html="value"></div>
      </template>
      <template v-else-if="type == 'delete'">
        <button class="btn btn-xs btn-danger" @click="deleteRow()">
          <i class="fa fa-fw fa-times"></i>
        </button>
      </template>
      <template v-else>
        {{ value }}
      </template>
    </template>
  </td>
</template>
<style scoped>
td {
  padding: 0.15rem;
}
</style>
<script>
export default {
  props: {
    column: Object,
    data: Object,
  },
  data() {
    return {
      editMode: false,
      localValue: null,
      showSubRow: false,
    };
  },
  computed: {
    style() {
      let style = {};
      if (this.column.nowrap) {
        style["white-space"] = "nowrap";
      }

      if (this.column.prop == "__view__") {
        style["text-align"] = "center";
      }
      if (this.column.prop == "__edit__") {
        style["text-align"] = "center";
      }
      if (this.column.prop == "__del__") {
        style["text-align"] = "center";
      }
      return style;
    },
    type() {
      var o = this.data[this.column.prop];
      if (o === null) return "text";

      if (typeof this.data[this.column.prop] == "object") {
        return this.data[this.column.prop].type;
      }
      return "text";
    },
    value() {
      if (this.type == "html" || this.type == "vue") {
        return this.data[this.column.prop].content;
      } else {
        return this.data[this.column.prop];
      }
    },
  },
  mounted() {
    this.localValue = this.value;
  },
  methods: {
    async deleteRow() {
      try {
        await this.$confirm("Are you sure?");
        let content = this.data[this.column.prop].content;
        await this.$http.delete(content);
        this.$emit("data-deleted");
      } catch (e) {
        console.log(e);
      }
    },
    onClick() {
      if (!this.column.editable) return;
      if (this.editMode) return;
      this.$emit("edit-started");
      this.editMode = true;
      this.$nextTick(() => {
        this.$refs.edit_element.focus();
      });
    },
    updateData() {
      this.editMode = false;

      if (this.value != this.localValue) {
        this.$emit("update-data", this.localValue);
      }
    },
    toggleSubRow() {
      this.showSubRow = !this.showSubRow;
      let d = this.data[this.column.prop];
      this.$emit("toggle-sub-row", { url: d.url, params: d.params });
    },
  },
};
</script>