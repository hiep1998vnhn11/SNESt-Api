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
      :loading="loadingData"
      loading-text="Loading... Please wait"
      :headers="headers"
      :items="users"
      :search="search"
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title> {{ name }} users management </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card :loading="loading">
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

          <v-dialog v-model="dialogBlock" max-width="500px">
            <v-card :loading="loading">
              <v-card-title class="headline">
                Are you sure you want to block this user?
              </v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="closeBlock">
                  Cancel
                </v-btn>
                <v-btn color="blue darken-1" text @click="blockItemConfirm">
                  OK
                </v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>

          <v-dialog v-model="dialogUnblock" max-width="600px">
            <v-card :loading="loading">
              <v-card-title class="headline">
                Are you sure you want to unblock this user?
              </v-card-title>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="closeBlock">
                  Cancel
                </v-btn>
                <v-btn color="blue darken-1" text @click="unblockItemConfirm">
                  OK
                </v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>

      <template v-slot:item.actions="{ item }">
        <v-tooltip bottom v-if="item.roles[0].name.toLowerCase() !== 'blocked'">
          <template v-slot:activator="{ on, attrs }">
            <v-icon
              color="error"
              class="mr-2"
              v-bind="attrs"
              v-on="on"
              @click="blockUser(item)"
            >
              mdi-account-alert
            </v-icon>
          </template>
          <span>Blocked {{ item.name }}</span>
        </v-tooltip>
        <v-tooltip bottom v-else>
          <template v-slot:activator="{ on, attrs }">
            <v-icon
              color="primary"
              class="mr-2"
              v-bind="attrs"
              v-on="on"
              @click="unblockUser(item)"
            >
              mdi-account-key
            </v-icon>
          </template>
          <span>Unblock {{ item.name }}</span>
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
          <span>Show user</span>
        </v-tooltip>
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
      dialogBlock: false,
      dialogUnblock: false,
      loading: false,
      headers: [
        {
          text: 'id',
          align: 'start',
          value: 'id'
        },
        { text: 'Email', value: 'email' },
        { text: 'Name', sortable: false, value: 'name' },
        { text: 'Role', value: 'roles[0].name' },
        { text: 'Phone Number', value: 'phone_number' },
        { text: 'Created at', value: 'created_at' },
        { text: 'Actions', value: 'actions', sortable: false }
      ],
      editedIndex: -1
    }
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
    }
  },
  props: ['users', 'name', 'loadingData'],
  watch: {
    dialog(val) {
      val || this.close()
    },
    dialogDelete(val) {
      val || this.closeDelete()
    },
    dialogBlock(val) {
      val || this.closeBlock()
    },
    dialogUnblock(val) {
      val || this.closeUnblock()
    }
  },
  methods: {
    showItem(item) {
      this.$router.push({ name: 'ParamUser', params: { user_id: item.id } })
    },
    editItem(item) {
      this.editedIndex = this.users.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
    },

    unblockUser(item) {
      this.editedIndex = this.users.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogUnblock = true
    },

    deleteItem(item) {
      this.editedIndex = this.users.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },

    blockUser(item) {
      this.editedIndex = this.users.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogBlock = true
    },

    async deleteItemConfirm() {
      this.loading = true
      try {
        const response = await axios.post(
          `/admin/user/${this.users[this.editedIndex].id}/delete`
        )
        Object.assign(this.users[this.editedIndex], response.data.data)
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
      this.loading = false
      this.users.splice(this.editedIndex, 1)
      this.closeDelete()
    },

    async blockItemConfirm() {
      this.loading = true
      try {
        const response = await axios.post(
          `/admin/user/${this.users[this.editedIndex].id}/block`
        )
        Object.assign(this.users[this.editedIndex], response.data.data)
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
      this.loading = false
      this.closeBlock()
    },

    async unblockItemConfirm() {
      this.loading = true
      try {
        const response = await axios.post(
          `/admin/user/${this.users[this.editedIndex].id}/unblock`
        )
        Object.assign(this.users[this.editedIndex], response.data.data)
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
      this.loading = false
      this.closeUnblock()
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

    closeBlock() {
      this.dialogBlock = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    closeUnblock() {
      this.dialogUnblock = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },

    save() {
      if (this.editedIndex > -1) {
        Object.assign(this.users[this.editedIndex], this.editedItem)
      } else {
        this.users.push(this.editedItem)
      }
      this.close()
    }
  }
}
</script>

<style>
</style>