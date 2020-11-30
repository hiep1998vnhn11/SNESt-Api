<template>
  <v-container>
    <dish-table
      :loading="loading"
      :dishes="dishes"
      :categories="categories"
      name="Nekoringo"
      @fetch="fetchData"
    />
  </v-container>
</template>
<script>
import axios from 'axios'
import DishTable from '../../components/DishTable'
export default{
  components: {
    DishTable
  },
  data(){
    return{
      dishes: [],
      categories: [],
      loading: false,
      error: null
    }
  },
  methods:{
    async fetchData(){
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('/admin/dish/index')
        const categories = await axios.get('/user/category/store')
        this.dishes = response.data.data
        this.categories = categories.data.data
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
}
</script>
<style scoped>
</style>