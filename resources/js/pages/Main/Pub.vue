<template>
  <v-container>
    <pub-table
      :pubs="pubs"
      :loading="loading"
      name="Nekoringo"
      @fetch="fetchData"
    />
  </v-container>
</template>
<script>
import PubTable from '../../components/PubTable'
import axios from 'axios'

export default{
  components: {
    PubTable
  },
  data(){
    return{
      pubs: [],
      loading: false,
      error: null
    }
  },
  methods:{
    async fetchData(){
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('/admin/pub/index')
        this.pubs = response.data.data
      } catch(err) {
        this.error = err.toString()
      }
      this.loading = false
    }
  },
  created(){
    
  },
  mounted(){
    this.fetchData()
  },
  computed:{
    
  }
}
</script>
<style scoped>
</style>