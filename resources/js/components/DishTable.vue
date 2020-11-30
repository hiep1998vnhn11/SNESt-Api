<template>
  <v-card>
    <v-card-title>
      <v-spacer></v-spacer>
      <v-col cols="3">
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          rounded
          outlined
          dense
          hide-details
        ></v-text-field>
      </v-col>
    </v-card-title>
    <v-data-table
      sort-by="id"
      :loading="loading"
      loading-text="Loading... Please wait"
      :headers="headers"
      :items="dishes"
      :search="search"
      :group-by="showGroupBy ? 'category.name' : null"
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title> {{ name }} dishes management </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-btn
            text
            small
            class="text-capitalize"
            @click="showGroupBy = !showGroupBy"
          >
            Group by: Category
          </v-btn>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="600px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                color="primary"
                dark
                class="mb-2 text-capitalize"
                v-bind="attrs"
                v-on="on"
              >
                New Dish
              </v-btn>
            </template>
            <v-card :loading="loadingSave">
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <v-card-text>
                <v-container>
                  <v-form ref="form" v-model="valid" lazy-validation>
                    <v-row>
                      <v-col cols="12" md="6">
                        <v-select
                          :items="categories"
                          item-text="name"
                          return-object
                          :label="$t('Category')"
                          v-model="editedItem.category"
                          :rules="[(v) => !!v || $t('Required')]"
                        ></v-select>
                        <v-text-field
                          v-model="editedItem.name"
                          label="Dish name"
                          :rules="[
                            (v) => !!v || $t('Required'),
                            (v) => (!!v && v.length <= 60) || 'Name too long!',
                          ]"
                        ></v-text-field>
                        <v-text-field
                          v-model="editedItem.description"
                          label="Description"
                          :rules="[
                            (v) => !!v || $t('Required'),
                            (v) => (!!v && v.length <= 255) || 'Too long!',
                          ]"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" md="6">
                        <v-file-input
                          accept="image/png, image/jpeg, image/bmp"
                          :placeholder="`${$t('Pick an photo')} *`"
                          prepend-icon="mdi-camera"
                          :label="`${$t('Home photo')} *`"
                          @change="onFileChange"
                          v-model="editedItem.image"
                          :rules="
                            editedIndex !== -1
                              ? []
                              : [(v) => !!v || $t('Required')]
                          "
                        ></v-file-input>
                        <v-img :src="editedItem.photo_path" />
                      </v-col>
                    </v-row>
                  </v-form>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="close">
                  Cancel
                </v-btn>
                <v-btn
                  color="blue darken-1"
                  text
                  @click="save"
                  :disabled="!valid"
                >
                  Save
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card>
              <v-card-title class="headline">
                Are you sure you want to delete this item?
              </v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                  color="blue darken-1 text-capitalize"
                  text
                  @click="closeDelete"
                >
                  Cancel
                </v-btn>
                <v-btn
                  color="blue darken-1 text-capitalize"
                  text
                  @click="deleteItemConfirm"
                >
                  OK
                </v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>

      <template v-slot:item.actions="{ item }">
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-icon
              color="primary"
              class="mr-2"
              v-bind="attrs"
              v-on="on"
              @click="editItem(item)"
            >
              mdi-pencil
            </v-icon>
          </template>
          <span>Edit</span>
        </v-tooltip>
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-icon
              color="red"
              v-bind="attrs"
              v-on="on"
              @click="deleteItem(item)"
            >
              mdi-delete
            </v-icon>
          </template>
          <span>Delete user</span>
        </v-tooltip>
      </template>

      <template v-slot:item.image="{ item }">
        <v-avatar width="200" height="150" tile>
          <v-img :src="item.photo_path" />
        </v-avatar>
      </template>

      <template v-slot:group.header="{ group, headers, toggle, isOpen }">
        <td style="cursor: pointer" :colspan="headers.length" @click="toggle">
          <v-icon v-if="isOpen">mdi-plus</v-icon>
          <v-icon v-else>mdi-minus</v-icon>
          <span class="mx-5 font-weight-bold"> Category: {{ group }} </span>
        </td>
      </template>

      <template v-slot:no-data>
        <v-btn color="primary" @click="$emit('fetch')"> Reset </v-btn>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
import axios from 'axios'

export default {
  data(){
    return{
      search: '',
      valid: true,
      loadingSave: false,
      error: null,
      dialog: false,
      dialogDelete: false,
      headers: [
        {
          text: 'id',
          align: 'start',
          value: 'id',
          groupable: false,
        },
        { text: 'Name', value: 'name', groupable: false },
        { text: 'Category', value: 'category.name' },
        { text: 'Image', align: 'center', sortable: false,groupable: false,  value: 'image' },
        { text: 'Description', sortable: false, value: 'description', groupable: false },
        { text: 'Actions', value: 'actions', sortable: false, groupable: false },
      ],
      editedIndex: -1,
      editedItem: {
        id: null,
        name: '',
        photo_path: null,
        image: null,
        description: '',
        category: null
      },
      defaultItem: {
        id: null,
        name: '',
        photo_path: null,
        image: null,
        description: '',
        category: null
      },
      showGroupBy: false,
    }
  },
  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
    },
  },
  props: ['dishes', 'name', 'loading', 'categories'],
  watch: {
    dialog (val) {
      val || this.close()
    },
    dialogDelete (val) {
      val || this.closeDelete()
    },
  },
  methods: {
    showItem(item) {
      this.$router.push({name: 'ParamUser', params: {user_id: item.id}})
    },
    editItem (item) {
      this.editedIndex = this.dishes.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
    },

    deleteItem (item) {
      this.editedIndex = this.dishes.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },

    async deleteItemConfirm () {
      try {
        const url = `/admin/dish/${this.dishes[this.editedIndex].id}/delete`
        const response = await axios.post(url)
        this.$swal({
          icon: 'success',
          title: 'Success',
          text: response.data.message
        })
        this.dishes.splice(this.editedIndex, 1)
      } catch (err) {
        this.error = err.toString()
        this.$swal({
          icon: 'error',
          title: 'Error',
          text: this.error
        })
      }
      this.closeDelete()
    },

    close () {
      this.dialog = false
      this.$nextTick(() => {
        this.$refs.form.reset()
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    closeDelete () {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    async save () {
      await this.$refs.form.validate()
      if (!this.valid) return
      this.loadingSave = true
      this.error = null
      try {
        let response = null
        if (this.editedIndex > -1) {
          // edit
          const formData = new FormData()
          if(this.dishes[this.editedIndex].category !== this.editedItem.category){
            formData.append('category', this.editedItem.category.id)
          }
          if(this.dishes[this.editedIndex].name !== this.editedItem.name){
            formData.append('name', this.editedItem.name)
          }
          if(this.dishes[this.editedIndex].description !== this.editedItem.description){
            formData.append('description', this.editedItem.description)
          }
          if(this.dishes[this.editedIndex].photo_path !== this.editedItem.photo_path && this.editedItem.image){
            formData.append('image', this.editedItem.image)
          }
          if(formData.has('category' || formData.has('name') || formData.has('description') || formData.has('image')))
          {
            const url = `/admin/dish/${this.editedItem.id}/update`
            response = await axios.post(url, formData, {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            })
            Object.assign(this.dishes[this.editedIndex], this.editedItem)
          }
        } else {
          //create
          let formData = new FormData()
          formData.append('category', this.editedItem.category.id)
          formData.append('name', this.editedItem.name)
          formData.append('description', this.editedItem.description)
          formData.append('image', this.editedItem.image)
          const url = `/admin/dish/create`
          response = await axios.post(url, formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
          this.dishes.push(response.data.data)
        }
        this.$swal({
          icon: 'success',
          title: 'Success',
          text: response.data.message
        })
      } catch (err) {
        this.error = err.toString()
        this.$swal({
          icon: 'error',
          title: 'Error',
          text: this.error
        })
      }
      this.loadingSave = false
      this.close()
    },
    onFileChange: function() {
      // Reference to the DOM input element
      // Ensure that you have a file before attempting to read it
      if (this.editedItem.image) {
        // create a new FileReader to read this image and convert to base64 format
        var reader = new FileReader()
        // Define a callback function to run, when FileReader finishes its job
        reader.onload = e => {
          // Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
          // Read image as base64 and set to imageData
          this.editedItem.photo_path = e.target.result
        }
        // Start the reader job - read file as a data url (base64 format)
        reader.readAsDataURL(this.editedItem.image)
      }
    },

  },
}
</script>

<style>
</style>