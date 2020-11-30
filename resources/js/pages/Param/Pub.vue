<template>
  <div>
    {{ pub }}
  </div>
</template>
<script>
import Axios from 'axios'
export default{
  data(){
    return{
      loading: false,
      error: null,
      pub: null
    }
  },
  methods:{
    async fetchData(){
      this.loading = true
      this.error = null
      try {
        const response = await Axios.get(`/admin/pub/${this.$route.params.pub_id}/show`)
        this.pub = response.data.data
      } catch(err) {
        this.error = err.toString()
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