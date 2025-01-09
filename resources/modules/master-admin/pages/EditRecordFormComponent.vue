<template>
  <div style="background-color: black; min-height: 1000px">
    <div
      style="width: 80%; margin: auto; color: #e0e0e0; padding-bottom: 100px"
    >
      <h1 style="padding-top: 10px">
        Edit records for ID: {{ recordData[0].id }} on {{ tableName }}
      </h1>

      <div
        v-for="(columnName, index) in tableColumn"
        :key="index"
      >
        <!-- && columnName != 'password' && columnName != 'email' -->
        <div
          v-if="
            columnName != 'id' &&
              columnName != 'created_at' &&
              columnName != 'updated_at'
          "
        >
          {{ columnName }}

          <div>
            <textarea
              style="width: 25%"
              rows="3"
              :value="inputValue(columnName)"
              @input="update($event.target.value, columnName)"
            />
          </div>
        </div>
      </div>

      <div style="display: flex">
        <div
          class="thin-border"
          style="
            cursor: pointer;
            width: 100px;
            text-align: center;
            margin: 5px;
            border-radius: 3px;
            background-color: #151515;
          "
          @click="editRecord()"
        >
          Save
        </div>

        <div
          class="cancel-button"
          @click="redirectTable()"
        >
          Cancel
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  layout: '',
  props: ['tableName', 'tableColumn', 'recordData'],
  data() {
    const dataObject = {};

    for (let index = 0; index < this.tableColumn.length; index++) {
      const element = this.tableColumn[index];

      // && element != 'password' && element != 'email'
      if (element !== 'id' && element !== 'created_at') {
        dataObject[element] = this.recordData[0][element];
      }
    }

    return {
      dataObject,
      addStatus: null,
    };
  },

  computed: {},

  methods: {
    inputValue(columnName) {
      return this.recordData[0][columnName];
    },

    update(value, columnName) {
      this.dataObject[columnName] = value;
    },

    redirectTable() {
      const urlString = `/master-admin/tables/${this.tableName}`;
      window.location.assign(urlString);
    },

    editRecord() {
      axios
        .post(
          `/master-admin/tables/${this.tableName}/record/${this.recordData[0].id}/edit-record/edit`,
          {
            dataObject: JSON.stringify(this.dataObject),
          }
        )
        .then((response) => {
          if (response.data.status === 'success') {
            this.addStatus = true;
            this.$toast.success('Success', 'Redirecting back to the table.');
          } else {
            this.addStatus = false;
            this.$toast.error('Error', 'Opps. Something went wrong.');
          }
        })
        .catch((error) => {
          this.addStatus = false;
          this.$toast.error('Error', error);
        })
        .finally(() => {
          if (this.addStatus === true) {
            setTimeout(() => {
              this.redirectTable();
            }, 1500);
          }
        });
    },
  },
};
</script>

<style scoped>
.thin-border {
  border: thin solid #e0e0e0;
}

textarea {
  color: #e0e0e0;
  background-color: #151515;
}
</style>
