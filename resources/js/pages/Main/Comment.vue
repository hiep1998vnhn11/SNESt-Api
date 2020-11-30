<template>
  <v-container>
    <comment-table
      :comments="comments"
      :loading="loading"
      name="Nekoringo"
      @fetch="fetchData"
    />
  </v-container>
</template>
<script>
import axios from 'axios'
import CommentTable from '../../components/CommentTable'

export default {
  components: {
    CommentTable
  },
  data() {
    return {
      comments: [],
      loading: false,
      error: null
    }
  },
  methods: {
    async fetchData() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get(`/admin/comment/index`)
        this.comments = response.data.data
      } catch (err) {
        this.error = err
      }
      this.loading = false
    }
  },
  mounted() {
    if (!this.comments.length) this.fetchData()
  }
}
</script>
<style scoped>
</style>