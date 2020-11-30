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
      :items="ratings"
      :search="search"
      :group-by="groupBy"
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title> {{ name }} ratings management </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          Group by:
          <v-btn
            small
            text
            class="text-capitalize ml-2"
            @click="groupBy = 'user.name'"
          >
            user
          </v-btn>
          <v-divider class="mx-4" inset vertical></v-divider>

          <v-btn
            small
            text
            class="text-capitalize"
            @click="groupBy = 'pub.name'"
          >
            pub
          </v-btn>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-btn small text class="text-capitalize" @click="groupBy = null">
            clear
          </v-btn>
          <v-divider class="mx-4" inset vertical></v-divider>

          <v-spacer></v-spacer>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card :loading="loadingD">
              <v-card-title class="headline">
                Are you sure you want to delete?
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
        <v-avatar width="200" height="150" tile v-show="item.image_path">
          <v-img :src="item.image_path" />
        </v-avatar>
      </template>

      <template v-slot:group.header="{ group, headers, toggle, isOpen }">
        <td style="cursor: pointer" :colspan="headers.length" @click="toggle">
          <v-icon v-if="isOpen">mdi-plus</v-icon>
          <v-icon v-else>mdi-minus</v-icon>
          <span class="mx-5 font-weight-bold">
            {{ groupBy === 'user.name' ? 'Username' : 'Pub' }}: {{ group }}
          </span>
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
  data() {
    return {
      search: '',
      dialog: false,
      dialogDelete: false,
      loadingD: false,
      headers: [
        {
          text: 'id',
          align: 'start',
          value: 'id'
        },
        { text: 'Email', value: 'user.email' },
        { text: 'Name', value: 'user.name' },
        { text: 'Pub', value: 'pub.name' },
        { text: 'Content', value: 'content' },
        { text: 'Image', sortable: false, value: 'image' },
        { text: 'Actions', value: 'actions', sortable: false }
      ],
      editedIndex: -1,
      groupBy: null
    }
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
    }
  },
  props: ['ratings', 'name', 'loading'],
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
      this.$router.push({ name: 'ParamUser', params: { user_id: item.id } })
    },
    editItem(item) {
      this.editedIndex = this.ratings.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
    },

    deleteItem(item) {
      this.editedIndex = this.ratings.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },

    async deleteItemConfirm() {
      this.loadingD = true
      try {
        const response = await axios.post(
          `/admin/comment/${this.ratings[this.editedIndex].id}/delete`
        )
        Object.assign(this.ratings[this.editedIndex], response.data.data)
        this.$swal({
          icon: 'success',
          title: 'Success',
          text: response.data.message
        })
      } catch (err) {
        this.$swal({
          icon: 'error',
          title: 'Error',
          text: err.toString()
        })
      }
      this.loadingD = false
      this.ratings.splice(this.editedIndex, 1)
      this.closeDelete()
    },

    close() {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    save() {
      if (this.editedIndex > -1) {
        Object.assign(this.ratings[this.editedIndex], this.editedItem)
      } else {
        this.ratings.push(this.editedItem)
      }
      this.close()
    }
  }
}
</script>

<style>
</style>