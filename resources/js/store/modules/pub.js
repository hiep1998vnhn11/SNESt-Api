import axios from 'axios'

const state = {
  paramPub: null,
  myPubs: [],
  pubs: {
    data: []
  },
  dishes: {
    data: []
  },
  paramDishes: {
    data: []
  },
  categories: []
}

const getters = {
  paramPub: state => state.paramPub,
  myPubs: state => state.myPubs,
  pubs: state => state.pubs,
  dishes: state => state.dishes,
  categories: state => state.categories,
  paramDishes: state => state.paramDishes
}

const actions = {
  async getParamPub({ commit }, id) {
    const response = await axios.get(`/user/pub/${id}/get`)
    commit('SET_PARAM_PUB', response.data.data)
  },
  async getCategories({ commit }) {
    let url = 'user/category/store'
    const response = await axios.get(url)
    commit('SET_CATEGORIES', response.data.data)
  },
  async createPub({ commit }, formData) {
    let url = 'user/pub/create'
    const response = await axios.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    commit('CREATE_PUB', response.data.data)
  },
  async getAllPub({ commit }, param) {
    let url = `/user/pub/store?page=${param.page}`
    const response = await axios.get(url)
    commit('SET_ALL_PUB', response.data.data)
  },
  async getMyPub({ commit }) {
    let url = '/user/pub/store_my_pub'
    const response = await axios.get(url)
    commit('SET_MY_PUB', response.data.data)
  },
  async getDishes({ commit }, param) {
    let url = param.searchKey
      ? `/user/dish/store?category=${param.category}&search_key=${param.searchKey}&page=${param.page}`
      : `/user/dish/store?category=${param.category}&page=${param.page}`
    const response = await axios.get(url)
    commit('SET_DISH', response.data.data)
  },
  async getParamDishes({ commit }, param) {
    let url = `/user/pub/${param.pubId}/dish/store?page=${param.page}`
    const response = await axios.get(url)
    commit('SET_PARAM_DISH', response.data.data)
  },
  async changeDish({ commit }, param) {
    let url = `/user/pub/${param.pub_id}/dish/change`
    const response = await axios.post(url, param.formData)
    commit('SET_PARAM_DISH', response.data.data)
  },
  async createDish({ commit }, formData) {
    let url = `/user/dish/create`
    const response = await axios.post(url, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    commit('CREATE_DISH', response.data.data)
  },
  async addDish({ commit }, param) {
    let url = `/user/pub/${param.pubId}/dish/${param.dishId}/add`
    const response = await axios.post(url)
    commit('ADD_DISH', response.data.data)
  },
  async deleteDish({ commit }, param) {
    let url = `/user/pub/${param.pubId}/dish/${param.hasDishId}/delete`
    const response = await axios.post(url)
    commit('DELETE_DISH', response.data.data)
  },
  async deletePub({ commit }, pubId) {
    let url = `/user/pub/${pubId}/delete`
    await axios.post(url)
    commit('DELETE_PUB', pubId)
  }
}

const mutations = {
  SET_PARAM_PUB: function(state, pub) {
    state.paramPub = pub
  },
  CREATE_PUB: function(state, pub) {
    state.pubs = [pub, ...state.pubs]
  },
  SET_ALL_PUB: function(state, pubs) {
    state.pubs = pubs
  },
  SET_MY_PUB: function(state, pubs) {
    state.myPubs = pubs
  },
  SET_DISH: function(state, dishes) {
    state.dishes = dishes
  },
  CREATE_DISH: function(state, dish) {
    state.dishes = [dish, ...state.dishes]
  },
  SET_CATEGORIES: function(state, categories) {
    state.categories = categories
  },
  SET_PARAM_DISH: function(state, dishes) {
    state.paramDishes = dishes
  },
  DELETE_DISH: function(state, dishId) {
    state.paramDishes.data = state.paramDishes.data.filter(
      dish => dish.id !== dishId
    )
  },
  ADD_DISH: function(state, dish) {
    state.paramDishes.data = [dish, ...state.paramDishes.data]
  },
  DELETE_PUB: function(state, pubId) {
    state.myPubs = state.myPubs.filter(pub => pub.id !== pubId)
  }
}

export default {
  state,
  getters,
  actions,
  mutations
}
