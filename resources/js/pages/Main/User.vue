<template>
  <v-container>
    <user-table
      :users="users"
      :loadingData="loading"
      name="Nekoringo"
      @fetch="fetchData"
    />
  </v-container>
</template>
<script>
import axios from 'axios'
import UserTable from '../../components/UserTable'

export default {
  components: {
    UserTable
  },
  data() {
    return {
      users: [],
      loading: false,
      error: null
    }
  },
  methods: {
    async fetchData() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get(`/admin/user/index`)
        this.users = response.data.data
      } catch (err) {
        this.error = err
      }
      this.loading = false
    }
  },
  mounted() {
    if (!this.users.length) this.fetchData()
  }
}
</script>
<style scoped>
</style>