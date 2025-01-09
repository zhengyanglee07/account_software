<template>
  <div style="background-color: black; min-height: 1000px">
    <div
      style="margin: auto; width: 85%; color: #e0e0e0; padding-bottom: 100px"
    >
      <div style="display: flex; justify-content: space-between">
        <h1 style="margin-top: 10px">
          Master Admin Dashboard
        </h1>
        <div
          class="thin-border"
          style="
            cursor: pointer;
            width: 70px;
            text-align: center;
            background-color: #151515;
            margin-top: auto;
            margin-bottom: auto;
            border-radius: 3px;
          "
          @click="logout()"
        >
          Log out
        </div>
      </div>

      <div class="input-group mt-1 mb-1">
        <div class="input-group-prepand">
          <span class="input-group-text search-icon thin-border">
            <i class="fas fa-search gray_icon" />
          </span>
        </div>
        <input
          v-model="search_input"
          type="text"
          placeholder="Search"
          class="thin-border form-control"
          style="color: white"
        >
      </div>

      <div class="icon-list-content">
        <div
          v-for="(tableName, index) in fliteredTable"
          :key="index"
          class="icon-list-content-item thin-border"
          @click="
            selectedTableName = reverseLodashStartCase(fliteredTable[index]);
            redirectPage();
          "
        >
          <div class="icon-list-content-item-content">
            {{ tableName }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import startCase from 'lodash/startCase';
import cloneDeep from 'lodash/cloneDeep';

export default {
  layout: '',
  props: ['tableNames'],

  data() {
    return {
      tableNameArray: this.getModifiedStringArray(this.tableNames),
      selectedTableName: '',
      search_input: '',
    };
  },

  computed: {
    fliteredTable() {
      // if (this.currentStatus != 'Preview' && this.currentStatus != 'Publish'){
      return this.tableNameArray.filter((table) => {
        return table.toLowerCase().includes(this.search_input.toLowerCase());
      });
      // }
      // return;
    },
  },

  methods: {
    reverseLodashStartCase(name) {
      return name.toLowerCase().replace(/ /g, '_');
    },

    getModifiedStringArray(stringArray) {
      const modifiedArray = [];

      for (let index = 0; index < stringArray.length; index++) {
        modifiedArray[index] = startCase(stringArray[index]);
      }
      return modifiedArray;
    },

    redirectURL() {
      if (this.buttonSettings.style.buttonLinkType[0] === 'Redirect URL') {
        if (
          this.buttonSettings.style.buttonLink[0] !== '' &&
          this.buttonSettings.style.buttonLink[0] != null
        ) {
          if (this.buttonSettings.style.openInNewWindow[0] === true) {
            window.open(this.buttonSettings.style.buttonLink[0]);
          } else if (this.buttonSettings.style.openInNewWindow[0] === false) {
            window.location.assign(this.buttonSettings.style.buttonLink[0]);
          }
        } else {
          window.location.reload();
        }
      }
    },

    logout() {
      axios
        .post('/master-admin/logout', {})
        .then((response) => {
          const urlString = '/master-admin/login';
          window.location.assign(urlString);
        })
        .catch((error) => {});
    },

    redirectPage() {
      const urlString = `/master-admin/tables/${this.selectedTableName}`;
      window.location.assign(urlString);
    },
  },
};
</script>

<style lang="scss" scoped>
.icon-list-content {
  display: grid;
  grid-gap: 20px;
  margin: 20px 0;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
}

@media (max-width: 1439px) {
  .icon-list-content {
    grid-template-columns: repeat(6, 1fr);
  }
}

@media (max-width: 1024px) {
  .icon-list-content {
    grid-template-columns: repeat(5, 1fr);
  }
}
.icon-list-content-item {
  /* // width: 100px;  */
  border-radius: 3px;
  cursor: pointer;
  position: relative;
  height: 0;
  padding-bottom: 55%;
  /* box-shadow: 0 1px 12px rgba(0, 0, 0, 0.28); */
  background-color: #151515;
  box-shadow: 0 1px 12px #e0e0e094;
}

.icon-list-content-item-content {
  display: flex;
  flex-direction: column;
  -webkit-box-align: center;
  align-items: center;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  width: 100%;
  position: absolute;
  padding: 10px;
  top: 25%;
}

.icon-list-content-item-icon {
  font-size: 25px;
}

.icon-list-content-item-text {
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 11px;
  padding: 13px 20px 0;
}

.search-input-wrapper {
  float: right;
  margin-left: auto;
  width: fit-content;
}

.search-icon {
  margin-left: -15px;
  color: lightgray;
  font-size: 11px;
}

span.search_icon {
  height: 100%;
  color: #637381;
  border-right: 0;
  padding-right: 8px;
  background: #fff;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  background-color: black;
}

.thin-border {
  border: thin solid #e0e0e0;
}
</style>
