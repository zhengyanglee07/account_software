<template>
  <div style="background-color: black; min-height: 1000px">
    <div
      style="width: 80%; margin: auto; color: #e0e0e0; padding-bottom: 100px"
    >
      <h1>Add records on {{ tableName }}</h1>

      <div
        v-for="(columnName, index) in tableColumn"
        :key="index"
      >
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
            background-color: #151515;
            border-radius: 3px;
          "
          @click="addRecord()"
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
  props: ['tableName', 'tableColumn'],
  data() {
    const dataObject = {};

    for (let index = 0; index < this.tableColumn.length; index++) {
      const element = this.tableColumn[index];

      if (element !== 'id') {
        dataObject[element] = '';
      }
    }

    return {
      dataObject,
      addStatus: null,
    };
  },

  methods: {
    update(value, columnName) {
      this.dataObject[columnName] = value;
    },

    redirectTable() {
      const urlString = `/master-admin/tables/${this.tableName}`;
      window.location.assign(urlString);
    },

    addRecord() {
      axios
        .post(`/master-admin/tables/${this.tableName}/add-record/add`, {
          dataObject: JSON.stringify(this.dataObject),
        })
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
          this.$toast.error('Error', 'Opps. Something went wrong.');
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
  background-color: black;
}
</style>
