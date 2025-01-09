<template>
  <div
    style="background-color: white"
    :style="{ 'min-height': dataLength < 15 ? '1000px' : '' }"
  >
    <div
      style="margin: auto; width: 85%; color: #e0e0e0; padding-bottom: 100px"
    >
      <div>
        <h1 style="padding-top: 10px">
          Table Name: {{ tableModifiedName }}
        </h1>
      </div>

      <!-- <div v-for="(columnName, index) in tableColumn" :key="index">
			{{ columnName }}
		</div> -->

      <div style="width: 95%">
        <div
          class="thin-border"
          style="cursor:pointer; width:150px; text-align:center; margin-bottom:5px; border-radius:3px; p"
          @click="redirectDashboard()"
        >
          Dashboard
        </div>
        <div
          class="thin-border"
          style="
            cursor: pointer;
            width: 150px;
            text-align: center;
            margin-bottom: 5px;
            border-radius: 3px;
            background-color: #151515;
          "
          @click="redirectAddRecordPage()"
        >
          Add new record
        </div>
      </div>

      <div class="view">
        <div class="wrapper">
          <table class="table">
            <tr>
              <th
                v-for="(columnName, index) in tableColumn"
                :key="index"
              >
                {{ columnName }}
              </th>
              <th class="sticky-col last-col">
                <div>Actions</div>
              </th>
            </tr>

            <tr
              v-for="(obj, index) in tableData"
              :key="index"
            >
              <td
                v-for="(data, i) in obj"
                :key="i"
              >
                {{ data }}
              </td>
              <td class="sticky-col last-col">
                <div
                  style="cursor: pointer; background-color: #faebd7"
                  @click="redirectEditRecordPage(obj.id)"
                >
                  Edit
                  <!-- {{obj.id}} -->
                </div>
              </td>
            </tr>

            <tr
              v-if="tableData.length == 0"
              style="text-align: center"
            >
              <td :colspan="tableColumn.length + 1">
                No data in this table.
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import startCase from 'lodash/startCase';

export default {
  components: {},
  layout: '',

  props: ['tableData', 'tableName', 'tableColumn'],
  data() {
    return {
      dataLength: this.tableData.length,
    };
  },

  computed: {
    tableModifiedName() {
      return startCase(this.tableName);
    },
  },

  mounted() {
    console.log(this.tableData);
  },

  methods: {
    // objectData(index){
    // 	console.log(index);
    // 	return this.tableData[index];
    // },

    redirectDashboard() {
      const urlString = '/master-admin/dashboard';
      window.location.assign(urlString);
    },

    redirectEditRecordPage(id) {
      const urlString = `/master-admin/tables/${this.tableName}/record/${id}/edit-record`;
      window.location.assign(urlString);
    },

    redirectAddRecordPage() {
      const urlString = `/master-admin/tables/${this.tableName}/add-record`;
      window.location.assign(urlString);
    },
  },
};
</script>

<style scoped>
.view {
  /* margin: auto; */
  width: 95%;
  background-color: white;
}

.wrapper {
  position: relative;
  overflow: auto;
  border: thin solid #e0e0e0;
  white-space: nowrap;
}

.sticky-col {
  position: -webkit-sticky;
  position: sticky;
  background-color: #faebd7;
  color: #151515;
}

.first-col {
  width: 100px;
  min-width: 100px;
  max-width: 100px;
  left: 0px;
}

.last-col {
  width: 150px;
  min-width: 150px;
  max-width: 150px;
  right: 0px;
  text-align: center;
}

.table th,
.table td {
  border: thin solid #e0e0e0;
}

.thin-border {
  border: thin solid #e0e0e0;
}

.table {
  color: #e0e0e0;
  margin-bottom: 0em;
}
</style>
