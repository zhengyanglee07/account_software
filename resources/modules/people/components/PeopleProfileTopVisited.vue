<template>
  <BaseDatatable
    no-header
    no-action
    custom-description="This contact doesn't visit any page yet"
    :table-headers="tableHeaders"
    :table-datas="pageVisitRecords"
  />
</template>

<script>
export default {
  name: 'PeopleProfileTopVisited',
  props: {
    trackingActivities: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      tableHeaders: [
        { name: 'Page Name', key: 'name' },
        { name: 'Visit', key: 'count' },
      ],
    };
  },
  computed: {
    pageVisitRecords() {
      const pageViewPerPage = this.trackingActivities.reduce(
        (acc, { activity_logs: activityLogs }) => {
          activityLogs.forEach((log) => {
            const { type, name, url } = log;
            if (
              ['builder-page', 'store-page', 'product', 'page'].includes(type)
            ) {
              if (!acc[name]) {
                acc[name] = {
                  count: 0,
                  type,
                  url,
                };
              }
              acc[log.name].count += 1;
            }
          });
          return acc;
        },
        {}
      );

      return Object.entries(pageViewPerPage)
        .map(([name, log]) => {
          return {
            name: log.type === 'product' ? `product ${name}` : `${name} page`,
            ...log,
          };
        })
        .sort((a, b) => b.count - a.count);
    },
  },
};
</script>
