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
      :items="pubs"
      :search="search"
      :single-expand="singleExpand"
      :expanded.sync="expanded"
      item-key="id"
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title> {{ name }} pubs management </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="1000px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                color="primary"
                dark
                class="mb-2 text-capitalize"
                v-bind="attrs"
                v-on="on"
              >
                New Item
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-form ref="form" v-model="valid" lazy-validation>
                    <v-row>
                      <v-col cols="7">
                        <v-text-field
                          v-model="editedItem.name"
                          :counter="200"
                          :label="`${$t('Name')} *`"
                          :rules="[
                            (v) => !!v || $t('Required'),
                            (v) =>
                              (!!v && v.length >= 4 && v.length < 200) ||
                              'Name contain 4-200 letters',
                          ]"
                        ></v-text-field>
                        <v-text-field
                          v-model="editedItem.address"
                          :counter="255"
                          :label="`${$t('Address')} *`"
                          :rules="[(v) => !!v || $t('Required')]"
                        ></v-text-field>
                        <v-text-field
                          v-model="editedItem.phone_number"
                          :counter="12"
                          :label="`${$t('Phone number')} *`"
                          :rules="[(v) => !!v || $t('Required')]"
                        ></v-text-field>
                        <v-text-field
                          v-model="editedItem.main_email"
                          :counter="50"
                          :label="`${$t('Main email')} *`"
                          :rules="[(v) => !!v || $t('Required')]"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="5" class="mx-auto">
                        <v-file-input
                          accept="image/png, image/jpeg, image/bmp"
                          :placeholder="$t('Pick an photo')"
                          prepend-icon="mdi-camera"
                          :label="`${$t('Home photo')} *`"
                          @change="onFileChange"
                          v-model="editedItem.image"
                          :rules="[(v) => !!v || $t('Required')]"
                        ></v-file-input>
                        <v-img
                          height="200"
                          width="280"
                          :src="editedItem.photo_path"
                        />
                      </v-col>
                    </v-row>
                    <v-text-field
                      v-model="editedItem.map_path"
                      :label="$t('Google map path')"
                      :rules="mapRules"
                    ></v-text-field>
                    <v-text-field
                      v-model="editedItem.video_path"
                      :label="$t('Youtube video path')"
                      :rules="videoRules"
                    ></v-text-field>
                    <v-textarea
                      auto-grow
                      :label="$t('Description')"
                      rows="3"
                      row-height="20"
                      :rules="[(v) => !!v || $t('Required')]"
                      v-model="editedItem.description"
                    ></v-textarea>
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
                <v-btn color="blue darken-1" text @click="closeDelete">
                  Cancel
                </v-btn>
                <v-btn color="blue darken-1" text @click="deleteItemConfirm">
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
              color="orange"
              @click="showItem(item)"
              dark
              v-bind="attrs"
              v-on="on"
            >
              mdi-eye
            </v-icon>
          </template>
          <span>Show pub</span>
        </v-tooltip>
      </template>
      <template v-slot:no-data>
        <v-btn color="primary" @click="$emit('fetch-data')"> Reset </v-btn>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
export default {
  data() {
    const _this = this
    return {
      search: '',
      valid: true,
      dialog: false,
      dialogDelete: false,
      headers: [
        {
          text: 'id',
          align: 'start',
          value: 'id'
        },
        { text: 'Name', sortable: false, value: 'name' },
        { text: 'Email', value: 'main_email' },
        { text: 'Description', value: 'description' },
        { text: 'Address', value: 'address' },
        { text: 'Phone Number', value: 'phone_number' },
        { text: 'Actions', value: 'actions', sortable: false }
      ],
      editedIndex: -1,
      editedItem: {
        name: '',
        main_email: '',
        description: '',
        address: '',
        phone_number: '',
        map_path: '',
        video_path: '',
        photo_path: null,
        image: null
      },
      defaultItem: {
        name: '',
        main_email: '',
        description: '',
        address: '',
        phone_number: '',
        map_path: '',
        video_path: '',
        photo_path: null,
        image: null
      },
      expanded: [],
      singleExpand: false,
      mapRules: [
        v => !!v || _this.$t('Required'),
        v =>
          v.indexOf('https://www.google.com/maps/embed') === 0 ||
          _this.$t('Please input correct link to embedded the map')
      ],
      videoRules: [
        v => !!v || _this.$t('Required'),
        v =>
          v.indexOf('https://www.youtube.com/embed') === 0 ||
          _this.$t('Please input correct link to embedded the video')
      ]
    }
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
    }
  },
  props: ['pubs', 'name', 'loading'],
  watch: {
    dialog(val) {
      val || this.close()
    },
    dialogDelete(val) {
      val || this.closeDelete()
    }
  },
  methods: {
    showItem(item) {
      this.$router.push({ name: 'ParamPub', params: { pub_id: item.id } })
    },
    editItem(item) {
      this.editedIndex = this.users.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
    },

    deleteItem(item) {
      this.editedIndex = this.users.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },

    deleteItemConfirm() {
      this.users.splice(this.editedIndex, 1)
      this.closeDelete()
    },

    close() {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
        this.$refs.form.reset()
      })
    },

    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    async save() {
      await this.$refs.form.validate()
      if (!this.valid) return
      this.loadingSave = true
      this.error = null
      try {
        let response = null
        if (this.editedIndex > -1) {
          // edit
          const formData = new FormData()
          if (
            this.dishes[this.editedIndex].category !== this.editedItem.category
          ) {
            formData.append('category', this.editedItem.category.id)
          }
          if (this.dishes[this.editedIndex].name !== this.editedItem.name) {
            formData.append('name', this.editedItem.name)
          }
          if (
            this.dishes[this.editedIndex].description !==
            this.editedItem.description
          ) {
            formData.append('description', this.editedItem.description)
          }
          if (
            this.dishes[this.editedIndex].photo_path !==
              this.editedItem.photo_path &&
            this.editedItem.image
          ) {
            formData.append('image', this.editedItem.image)
          }
          if (
            formData.has(
              'category' ||
                formData.has('name') ||
                formData.has('description') ||
                formData.has('image')
            )
          ) {
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
          formData.append('address', this.editedItem.address)
          formData.append('name', this.editedItem.name)
          formData.append('phone_number', this.editedItem.phone_number)
          formData.append('main_email', this.editedItem.main_email)
          formData.append('video_path', this.editedItem.video_path)
          formData.append('map_path', this.editedItem.map_path)
          formData.append('description', this.editedItem.description)
          formData.append('image', this.editedItem.image)
          const url = `/admin/pub/create`
          response = await axios.post(url, formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
          console.log(response.data)
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
    }
  }
}
</script>

<style>
</style>