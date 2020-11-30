<template>
  <v-container v-if="!user && error">
    {{ error }}
  </v-container>
  <v-container v-else>
    {{ user }}
  </v-container>
</template>
<script>
import axios from 'axios'
export default{
  data(){
    return{
      loading: false,
      error: null,
      user: null
    }
  },
  methods:{
    async fetchData(){
      this.loading = true
      this.error = null
      console.log(123)
      try {
        const response = await axios.get(`/admin/user/${this.$route.params.user_id}/show`)
        this.user = response.data.data
      } catch(err) {
        this.error = err.response.data.message
      }
    }
  },
  created(){
    
  },
  mounted(){
    this.fetchData()
  },
  watch: {
    '$route': 'fetchData'
  },
  computed:{
    
  }
}
</script>
<style scoped>
</style>