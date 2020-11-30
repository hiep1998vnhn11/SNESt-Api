<template>
  <v-container>
    <rating-table
      :ratings="ratings"
      :loading="loading"
      name="Nekoringo"
      @fetch="fetchData"
    />
  </v-container>
</template>
<script>
import axios from 'axios'
import RatingTable from '../../components/RatingTable'

export default {
  components: {
    RatingTable
  },
  data() {
    return {
      ratings: [],
      loading: false,
      error: null
    }
  },
  methods: {
    async fetchData() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get(`/admin/rating/index`)
        this.ratings = response.data.data
      } catch (err) {
        this.error = err
      }
      this.loading = false
    }
  },
  mounted() {
    if (!this.ratings.length) this.fetchData()
  }
}
</script>
<style scoped>
</style>