<template>
  <td v-show="column.isVisible">
    <template v-if="column.searchable && column.searchType == 'equal'">
      <el-input
        @keyup.enter.native="doSearch"
        v-model="search"
        clearable
        @clear="
          search = '';
          doSearch();
        "
        size="mini"
      ></el-input>
    </template>

    <template v-if="column.searchable && column.searchType == 'text'">
      <el-input
        @keyup.enter.native="doSearch"
        v-model="search"
        clearable
        @clear="
          search = '';
          doSearch();
        "
        size="mini"
      ></el-input>
    </template>

    <template v-if="column.searchable && column.searchType == 'date'">
      <el-date-picker
        v-model="search"
        type="daterange"
        unlink-panels
        range-separator="~"
        start-placeholder="Start date"
        end-placeholder="End date"
        :picker-options="pickerOptions"
        @change="doSearch()"
        format="yyyy-MM-dd"
        value-format="yyyy-MM-dd"
        size="mini"
        style="max-width: 200px"
      ></el-date-picker>
    </template>

    <template v-if="column.searchable && column.searchType == 'select'">
      <el-select
        v-model="search"
        clearable
        filterable
        @change="doSearch()"
        size="mini"
      >
        <el-option
          v-for="(o, key) in column.searchOption"
          :key="key"
          :label="o.label"
          :value="o.value"
        ></el-option>
      </el-select>
    </template>

    <template v-if="column.searchable && column.searchType == 'multiselect'">
      <el-select
        v-model="search"
        clearable
        filterable
        multiple
        collapse-tags
        @change="doSearch()"
        size="mini"
      >
        <el-option
          v-for="(o, key) in column.searchOption"
          :key="key"
          :label="o.label"
          :value="o.value"
        ></el-option>
      </el-select>
    </template>
  </td>
</template>

<script>
export default {
  inject:['rTable'],
  props: {
    column: Object,
  },
  computed: {
    name() {
      return this.column.prop;
    },
  },
  data() {
    return {
      search: null,
      pickerOptions: {
        shortcuts: [
          {
            text: "Today",
            onClick(picker) {
              const start = new Date();
              const end = new Date();
              picker.$emit("pick", [start, end]);
            },
          },
          {
            text: "Last week",
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit("pick", [start, end]);
            },
          },
          {
            text: "Last month",
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
              picker.$emit("pick", [start, end]);
            },
          },
          {
            text: "Last 3 months",
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
              picker.$emit("pick", [start, end]);
            },
          },
        ],
      },
    };
  },
  created(){
    this.search=this.rTable.searchValue[this.column.prop];
  },
  methods: {
    doSearch() {
      let method = "like";
      switch (this.column.searchType) {
        case "equal":
          method = "eq";
          break;
        case "select":
          method = "eq";
          break;
        case "multiselect":
          method = "in";
          break;
        case "date":
          method = "between";
          break;
      }

      this.$emit("search", [this.column.prop, this.search, method]);
    },
  },
};
</script>