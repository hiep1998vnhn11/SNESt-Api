<template>
  <v-container>
    <report-table
      :reports="reports"
      :loading="loading"
      name="Nekoringo"
      @fetch="fetchData"
    />
  </v-container>
</template>
<script>
import axios from 'axios'
import ReportTable from '../../components/ReportTable'

export default {
  components: {
    ReportTable
  },
  data() {
    return {
      reports: [],
      loading: false,
      error: null
    }
  },
  methods: {
    async fetchData() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get(`/admin/report/index`)
        this.reports = response.data.data
      } catch (err) {
        this.error = err
      }
      this.loading = false
    }
  },
  mounted() {
    if (!this.reports.length) this.fetchData()
  }
}
</script>
<style scoped>
</style>