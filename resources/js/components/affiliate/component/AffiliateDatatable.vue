<template>
  <div>

      <h1 class="header">
        <slot name="name"></slot>
      </h1>

    <slot name="headerButton"></slot>

    <div class="datatable-container mb-5">
      <template v-if="!isDataEmpty">
        <div class="searchbar-wrapper">
          <div class="input-group search_wrapper">
            <div class="input-group-prepand">
              <span class="input-group-text search-icon thin-border">
                <i class="fas fa-search gray-icon"></i>
              </span>
            </div>
            <input
              v-model="searchKeyword"
              type="text"
              :placeholder="tableType"
              class="thin-border form-control search-bar"
            />
          </div>
        </div>

        <div class="pagination-text">
          Showing {{ filterRows.length }} of {{ rowsCount.total }} results.
          <a
            class="expand-rows-link"
            v-show="rowsCount.available !== 0"
            @click="addShowedRows"
          >
            Load next {{ availableRows }}
          </a>
        </div>
        <div class="table-wrapper table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <slot name="Image"></slot>
                <th
                  v-for="(header, index) in headers"
                  :key="index"
                  @click="dynamicSorting(header.value, index)"
                  @mouseover="triggerSortIcon(true, header.value)"
                  @mouseleave="triggerSortIcon(false, header.value)"
                  :style="{ width: header.width }"
                  class="text-uppercase"
                >
                  <div class="w-100" :class="{ textRight: header.textalign == 'flex-end' }">
                    <span :class="{ textRight: header.textalign == 'flex-end' }"> {{ header.text }} </span>
                    <div class="sortingIcon ps-1">
                      <span
                        :class="{ 'text-secondary': header.order == 0 }"
                        v-show="
                          showSortIcon.status &&
                          header.order !== 2 &&
                          showSortIcon.targetHeader == header.value
                        "
                      >
                        <i class="fas fa-arrow-up"></i>
                      </span>
                      <span
                        v-show="
                          showSortIcon.status &&
                          header.order == 2 &&
                          showSortIcon.targetHeader == header.value
                        "
                      >
                        <i class="fas fa-arrow-down"></i>
                      </span>
                    </div>
                  </div>
                </th>
                <slot name="buttonHeader"></slot>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(item, rowIndex) in filterRows" :key="rowIndex">
                <slot name="ImageData" :item="item"></slot>
                <td
                  class="p-auto"
                  v-for="(header, index) in headers"
                  :key="index"
                  :class="{ textRight: header.textalign == 'flex-end' }"
                >
                  {{ item[header.value] }}
                </td>
                <slot name="buttonAction" :item="item"></slot>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
      <template v-else>
		<empty-data-container />
      </template>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Datatable',

  props: {
    headers: Array,
    datas: Array,
  },

  data() {
    return {
      // isDataEmpty : true,
      searchKeyword: '',
      isDefault: true,
      showSortIcon: { status: false, targetHeader: '' },
      rowsCount: { total: null, digit: null, showed: null, available: null },
    };
  },

  computed: {
    tableType() {
      if (this.headers[0].text.toLowerCase().includes('product')) {
        return 'Search Product';
      }

      return 'Filter Table';
    },

    isDataEmpty() {
      return this.datas.length === 0;
    },

    paginatedDatas() {
      return this.datas.slice(0, this.rowsCount.showed);
    },

    availableRows() {
      // 2 digits : 20, >=3 digits : 100
      const added = this.rowsCount.digit == 2 ? 20 : 100;
      return this.rowsCount.available > added
        ? added
        : this.rowsCount.available;
    },

    filterRows() {
      // filter rows by search keywords
      const showedRows = this.isDefault
        ? this.datas.slice(0, this.rowsCount.showed)
        : this.paginatedDatas;
      return showedRows.filter((item) => {
        return Object.keys(item).some((key) => {
          return (item[key] || '')
            .toString()
            .toLowerCase()
            .includes(this.searchKeyword.toLowerCase());
        });
      });
    },
  },

  watch: {
    datas() {
      // also update rowsCount, especially rowsCount.showed when datas props updated
      // fix datas.slice problem in filterRows
      this.dataShowed();
    },
  },

  methods: {
    sortingOrder(index) {
      var currentOrder = this.headers[index].order;
      this.headers[index].order = currentOrder + 1 > 2 ? 0 : currentOrder + 1;
      // this.$set(this.headers[_index], 'order', currentOrder + 1 > 2 ? 0 : currentOrder + 1)
      this.isDefault = this.headers[index].order == 0 ? true : false;
      return this.headers[index].order;
    },

    dynamicSorting(key, index) {
      const order = this.sortingOrder(index);
      if (order == 0) {
        return;
      }
      this.filterRows.sort((a, b) => {
        if (!a.hasOwnProperty(key) || !b.hasOwnProperty(key)) {
          return 0;
        }
        const itemA =
          typeof a[key] === 'string' ? a[key].toLowerCase() : a[key];
        const itemB =
          typeof b[key] === 'string' ? b[key].toLowerCase() : b[key];
        if (order == 1) {
          return itemA > itemB ? 1 : itemB > itemA ? -1 : 0;
        }
        return itemA < itemB ? 1 : itemB < itemA ? -1 : 0;
      });
    },

    dataShowed() {
      const totalLength = this.datas.length;
      const lengthDigit = totalLength.toString().length;
      const initialRowCount = totalLength > 20 ? 20 : totalLength;
      this.rowsCount = {
        total: totalLength,
        digit: lengthDigit,
        showed: initialRowCount,
        available: totalLength - initialRowCount,
      };
    },

    addShowedRows() {
      this.rowsCount.showed += this.availableRows;
      this.rowsCount.available -= this.availableRows;
    },

    triggerSortIcon(_boolean, _header) {
      this.showSortIcon.status = _boolean;
      this.showSortIcon.targetHeader = _header;
    },
  },

  mounted() {
    this.dataShowed();
    // this.isDataEmpty = this.datas.length == 0 ? true : false;
    // console.log(JSON.stringify(this.headers))
    // console.log(JSON.stringify(this.datas))
  },
};
</script>

<style scoped lang="scss">

.form-control:focus {
  border-color: lightgrey;
}
// th:nth-child(3),
// tr td:nth-child(3){
//   text-align: right;
//   padding-right: 32px;
// }

// tr td:nth-child(2){
//   text-transform: capitalize;
// }
</style>
