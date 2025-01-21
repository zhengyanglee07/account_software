base<template>
  <BaseEmptyData
    v-if="tableDatas.length === 0 && !(isServerSideSearch && search)"
    :title="title"
    :custom-description="customDescription"
  >
    <div class="action-button-wrapper">
      <slot name="action-button" />
    </div>
  </BaseEmptyData>
  <BaseCard
    v-else
    has-toolbar
    no-body-margin
    is-datatable
    :has-header="!noHeader"
  >
    <template #header>
      <div
        v-if="!noSearch"
        class="d-flex align-items-center position-relative my-1"
      >
        <span class="svg-icon svg-icon-1 position-absolute ms-6">
          <i class="fa-solid fa-magnifying-glass" />
        </span>
        <input
          v-model="search"
          type="search"
          class="form-control form-control-solid w-250px ps-15"
          :placeholder="`Search ${title}s`"
          @search="clearSearchQuery($event.target.value)"
          @keyup.enter="isServerSideSearch && triggerBackendFilterAndSorting()"
        >
        <small
          v-if="search && isServerSideSearch"
          class="mb-0 ms-2 text-muted fst-italic"
        >
          Press
          <code>Enter</code>
          to search
        </small>
      </div>
    </template>

    <template #toolbar>
      <!-- @slot To put the buttons associated with the datatable (usually Create button) here -->
      <div class="action-button-wrapper">
        <slot name="action-button" />
      </div>
    </template>

    <div
      class="table-responsive px-0"
      :style="{
        maxHeight: maxHeight,
        'overflow-y': maxHeight ? 'scroll' : 'auto',
        overflow: noResponsive ? 'unset' : '',
      }"
    >
      <table
        v-if="tableDatas.length > 0"
        class="align-middle dataTable fs-6 gy-5 no-footer table table-row-dashed"
        :class="{
          'table-hover': !noAction && !noHover,
        }"
      >
        <thead v-if="!!headers">
          <tr
            class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0"
            role="row"
          >
            <th
              v-for="(header, index) in headers"
              :key="header.key"
              tabindex="0"
              rowspan="1"
              colspan="1"
              :class="{
                'text-end': headers.length - 1 === index,
                sorting: header.sortable,
              }"
              :style="{
                width: header.width ?? 'auto',
                'min-width': header.width ?? 'auto',
              }"
              @click="sortColumn(header, index)"
            >
              <template v-if="header.key === 'checkbox'">
                <div
                  class="form-check form-check-sm form-check-custom form-check-solid mb-0"
                >
                  <input
                    :id="checboxId"
                    class="form-check-input"
                    type="checkbox"
                    @click="$emit('selectAll', $event.target.checked)"
                  >
                </div>
              </template>
              <template v-else>
                {{ header.name }}
                <i
                  v-if="!isServerSideSearch && header.sortable && header.sortOrder !== 0"
                  :class="`ms-2 fa-solid fa-angle-${
                    header.sortOrder === 2 ? 'down' : 'up'
                  }`"
                />
              </template>
            </th>
          </tr>
        </thead>
        <tbody
          v-if="!!datas"
          class="fw-bold text-gray-600"
        >
          <template v-if="datas?.length">
            <tr
              v-for="(data, dataIndex) in filteredTableDatas"
              :key="data?.id"
            >
              <template
                v-for="(header, index) in headers"
                :key="header.key"
              >
                <td
                  :class="{
                    'text-end': headers.length - 1 === index,
                  }"
                  :style="{
                    cursor:
                      (data?.editLink || data?.viewLink) &&
                      header.key !== 'actions' &&
                      !noHover
                        ? 'pointer'
                        : 'default',
                  }"
                  @click.self="
                    redirectToEditPage(data, header.key === 'actions')
                  "
                >
                  <template v-if="header.custom || header.key === 'checkbox'">
                    <!--
                      @slot To customize the cell value.
                      Remember to pass `custom: true` to the corresponding header's configuration
                    -->
                    <slot
                      :name="`cell-${header.key}`"
                      :row="data"
                      :index="dataIndex"
                    />
                  </template>
                  <template v-else-if="header.key === 'actions'">
                    <BaseButton
                      type="light"
                      size="sm"
                      class="btn-active-light-primary"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                    >
                      Actions
                      <span class="svg-icon svg-icon-5 m-0 ms-1">
                        <i class="fa-solid fa-angle-down" />
                      </span>
                    </BaseButton>
                    <BaseDropdown id="datatable-actions">
                      <BaseDropdownOption
                        v-if="!noEditAction"
                        text="Edit"
                        @click="redirectToEditPage(data)"
                      />
                      <slot
                        name="action-options"
                        :row="data"
                        :index="dataIndex"
                      />
                      <BaseDropdownOption
                        v-if="!noDeleteAction"
                        text="Delete"
                        @click="triggerDelete(data?.id)"
                      />
                    </BaseDropdown>
                  </template>
                  <template v-else>
                    {{ data[header.key] }}
                  </template>
                </td>
              </template>
            </tr>
          </template>
        </tbody>
      </table>
      <p
        v-else
        class="text-center mb-0"
      >
        No results found. Try to change the search keywords or
        <button
          type="button"
          class="btn btn-link font-normal"
          @click="search = ''; triggerBackendFilterAndSorting()"
        >
          clear them
        </button>
      </p>
    </div>
  </BaseCard>
  <div
    v-if="
      paginatedData && paginatedData?.last_page > 1
    "
    class="d-flex justify-content-between align-items-center pagination-footer"
  >
    <p v-if="!isFetchingData">
      Showing {{ paginatedData.from }} to {{ paginatedData.to }} of
      {{ paginatedData.total }} records
    </p>
    <p v-else>
      Fetching...
    </p>
    <div
      v-if="paginatedData?.links?.length > 3"
      class="pagination"
    >
      <li
        v-for="(link, index) in paginatedData.links"
        :key="index"
        class="page-item"
      >
        <div
          v-if="link.url === null"
          class="page-link disabled"
          v-html="link.label"
        />
        <div
          v-else-if="!isPaginationRedirect"
          class="page-link cursor-pointer"
          :class="{ active: link.active, disabled: isFetchingData }"
          @click="fetchPage(link.url)"
          v-html="link.label"
        />
        <Link
          v-else
          class="page-link"
          :class="{ active: link.active }"
          :href="`${link.url}${paginationLinkQueryString}`"
          v-html="link.label"
        />
      </li>
    </div>
  </div>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch, watchEffect } from 'vue';
import cloneDeep from 'lodash/cloneDeep';
import arraySort from 'array-sort';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone'; // dependent on utc plugn
import customParseFormat from 'dayjs/plugin/customParseFormat';
import BaseEmptyData from '@shared/components/BaseEmptyData.vue';
import axios from 'axios';

dayjs.extend(customParseFormat);
dayjs.extend(utc);
dayjs.extend(timezone);

const props = defineProps({
  /**
   * Determine whether the datatable is server side search and filtering
   */
  isServerSideSearch: {
    type: Boolean,
    default: false,
  },
  /**
   * The category of the table data
   */
  title: {
    type: String,
    required: true,
  },
  /**
   * An array of table header configuration
   */
  tableHeaders: {
    type: Array,
    required: true,
  },
  /**
   * An array of table datas (usually from db)
   */
  tableDatas: {
    type: Array,
    required: true,
  },
  /**
   * Determine whether the datatable has header section.
   * Usually for search bar and buttons
   */
  noHeader: {
    type: Boolean,
    default: false,
  },
  /**
   * Determine whether the datatable has search bar
   */
  noSearch: {
    type: Boolean,
    default: false,
  },
  /**
   * Determine whether the datatable has sorting functionality for column value. User
   * can sort the column's values by clicking on the header of column
   */
  noSorting: {
    type: Boolean,
    default: false,
  },
  /**
   * Determine whether the datatable has a column for Action
   */
  noAction: {
    type: Boolean,
    default: false,
  },
  /**
   * Determine whether the datatable has Edit action. Edit action will
   * redirect the user to the URL specified by editLink in tableDatas
   */
  noEditAction: {
    type: Boolean,
    default: false,
  },
  /**
   * Set this to true if you want to disable hover effect on datatable.
   */
  noHover: {
    type: Boolean,
    default: false,
  },
  /**
   * Could use it to uncheck the Checkbox when there is not all selected
   */
  checboxId: {
    type: String,
    default: () => 'select-all-checkbox',
  },
  /**
   * Determine whether the datatable has Delete action. Delete action will
   * trigger a delete confirmation modal, and emit a delete event with record's id
   * after user's confirmation
   */
  noDeleteAction: {
    type: Boolean,
    default: false,
  },
  /**
   * Custom description
   */
  customDescription: {
    type: String,
    default: () => '',
  },
  /**
   * Detailed information about paginated records
   */
  paginationInfo: {
    type: Object,
    default: null,
  },
  /**
   * Determine the max-height of datatable, to make it scrollable
   */
  maxHeight: {
    type: String,
    default: null,
  },
  /**
   * Force the usage of window.location.replace for redirection
   */
  isWindowRedirection: {
    type: Boolean,
    default: false,
  },
  isPaginationRedirect: {
    type: Boolean,
    default: true,
  },
  noResponsive: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  /**
   * When the user selects checkbox on table header
   */
  'selectAll',
  /**
   * When the user confirm the deletion of selected record
   */
  'delete',
  /**
   * When the user click the row of table
   */
  'select-row',
  'update-paginate',
]);

const actionHeader = {
  name: 'Actions',
  key: 'actions',
};

const headers = ref([]);
const datas = ref([]);
const search = ref('');
const queryString = ref(null);
const sortColumnIndex = ref(null);
const paginatedData = ref(null);
const isFetchingData = ref(false);
const accountTimezone = computed(
  () => usePage().props.value?.timezone ?? 'Asia/Kuala_Lumpur'
);

const paginationLinkQueryString = computed(() => {
  const queries = (queryString.value ?? '').replace('?', '').split('&');
  return queries.reduce((acc, curr) => {
    const isContainValidQueryStringKey = ['search=', 'column=', 'order='].some(
      (e) => curr.includes(e)
    );
    if (isContainValidQueryStringKey) {
      acc += `&${curr}`;
    }
    return acc;
  }, '');
});

const setupHeaders = () => {
  const params = new URLSearchParams(queryString.value);
  const sortColumnKey = params.get('column');
  const sortColumnOrder = params.get('order');
  search.value = params.get('search') ?? "";
  headers.value = props.tableHeaders.map((e, index) => {
    const isSortedColumn = [e.key, e.sortKey].includes(sortColumnKey);
    if (isSortedColumn) sortColumnIndex.value = index;
    return {
      sortable: !props.noSorting,
      sortOrder:
        isSortedColumn && ['asc', 'desc'].includes(sortColumnOrder)
          ? sortColumnOrder
          : 0,
      ...e,
    };
  });
  if (!props.noAction) headers.value.push(actionHeader);
};

const filteredTableDatas = computed(() => {
  if (search.value === '' || props.isServerSideSearch) {
    return datas.value;
  }
  return datas.value.filter((item) => {
    return Object.keys(item).some((key) => {
      return (item[key] || '')
        .toString()
        .toLowerCase()
        .includes((search.value ?? "").toLowerCase());
    });
  });
});

watchEffect(() => {
  paginatedData.value = props.paginationInfo;
})

watchEffect(() => {
  setupHeaders();
  const dateTimeColumnKey = headers.value.find(
    (header) => header.isDateTime === true
  )?.key;
  datas.value = cloneDeep(props.tableDatas)
    .map((value) => {
      if (dateTimeColumnKey) {
        value[dateTimeColumnKey] = dayjs(value[dateTimeColumnKey])
          .tz(accountTimezone.value)
          .format('MMMM D, YYYY [at] h:mm a');
      }
      return value;
    })
    .sort((a, b) => b.id - a.id);
});

onMounted(() => {
  queryString.value = window.location.search;
  setupHeaders();
});

/**
 * * Query String for search and sorting
 * @search => search keywords
 * @column => column name to search
 * @order => sorting order, either asc or desc
 */
const triggerBackendFilterAndSorting = () => {
  const { origin, pathname, search: searchParams } = window.location;
  const url = new URL(origin + pathname);
  const params = new URLSearchParams(searchParams);
  if (search.value) {
    url.searchParams.set('search', search.value);
  }
  if (params.get('status')) {
    url.searchParams.set('status', params.get('status'));
  }
  // const { key, sortKey, sortOrder } =
  //   headers.value[sortColumnIndex.value] ?? {};
  // if (key && ['asc', 'desc'].includes(sortOrder)) {
  //   const sortingQueryString = `column=${sortKey ?? key}&order=${sortOrder}`;
  //   queryString.value = search.value
  //     ? `${queryString.value}&${sortingQueryString}`
  //     : `?${sortingQueryString}`;
  // } else {
  //   queryString.value = search.value ? queryString.value : '';
  // }
  router.visit(url);
};

const clearSearchQuery = (searchKeyword) => {
  if (searchKeyword.trim() === '' && props.isServerSideSearch) {
    triggerBackendFilterAndSorting();
  }
};

const dateTimeInUnix = (datetime) => {
  return dayjs(datetime, 'MMMM D, YYYY [at] h:mm a').unix();
};

const sortColumn = (
  { key, sortable, sortOrder, isDateTime = false },
  index
) => {
  if (!sortable || props.isServerSideSearch) return;

  headers.value.forEach((header, i) => {
    if (i !== index) {
      header.sortOrder = 0;
    }
  });

  if (props.isServerSideSearch) {
    //* Sorting Order sequence: null > asc > desc > null > ...
    switch (sortOrder) {
      case 'asc':
        headers.value[index].sortOrder = 'desc';
        break;
      case 'desc':
        headers.value[index].sortOrder = null;
        break;
      default:
        headers.value[index].sortOrder = 'asc';
    }

    sortColumnIndex.value = index;
    triggerBackendFilterAndSorting();
    return;
  }
  headers.value[index].sortOrder =
    sortOrder <= 1 ? (sortOrder += 1) : (sortOrder -= 1);
  if (isDateTime) {
    arraySort(datas?.value, (a, b) => {
      if (sortOrder === 2) {
        return dateTimeInUnix(b[key]) - dateTimeInUnix(a[key]);
      }
      return dateTimeInUnix(a[key]) - dateTimeInUnix(b[key]);
    });
  } else {
    const compare = (columnKey) => {
      return (a, b) => {
        const aInLowerCase = a[columnKey].toString().toLowerCase();
        const bInLowerCase = b[columnKey].toString().toLowerCase();
        if (aInLowerCase > bInLowerCase) {
          return 1;
        }
        if (aInLowerCase < bInLowerCase) {
          return -1;
        }
        return 0;
      };
    };
    arraySort(datas?.value, compare(key), {
      reverse: sortOrder === 2,
    });
  }
};

const triggerDelete = (id) => {
  // eslint-disable-next-line no-restricted-globals
  const answer = confirm('Are you sure want to delete this record? This action cannot be undone');
  if (answer) {
    emit('delete', id);
  }
};

const redirectToEditPage = ({ editLink, viewLink, id }, isAction) => {
  emit('select-row', id);
  if (isAction || props.noHover) return;
  if (editLink || viewLink) {
    if (props.isWindowRedirection) {
      window.location.replace(editLink ?? viewLink);
    } else {
      router.visit(editLink ?? viewLink);
    }
  }
};

const fetchPage = (link) => {
  isFetchingData.value = true;
  axios
    .get(link)
    .then(({ data }) => {
      emit('update-paginate',data);
    })
    .finally(() => {
      isFetchingData.value = false;
    });
};
</script>

<style lang="scss" scoped>
.sorting {
  cursor: pointer;
}

th:first-child,
td:first-child {
  padding-left: 1.5rem !important;
}

th:last-child,
td:last-child {
  padding-right: 1.5rem !important;
}

.table-hover tr:hover td :deep(.btn-active-light-primary) {
  background: white;
}

.action-button-wrapper :deep(button.btn) {
  margin-right: 10px !important;
}

.action-button-wrapper :deep(button.btn:last-child) {
  margin-right: 0 !important;
}

@media (max-width: 480px) {
  .action-button-wrapper :deep(button.btn) {
    margin-right: 6px !important;
    margin-bottom: 10px !important;
  }

  .pagination-footer {
    flex-direction: column;
  }
}
</style>
