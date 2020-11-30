<template>
  <v-app id="inspire" v-if="!!currentUser">
    <v-app-bar app elevation="0" class="orange lighten-3">
      <router-link :to="{ name: 'Dashboard' }">
        <v-avatar size="50" tile>
          <v-img size="50" src="/assets/logo.png" />
        </v-avatar>
      </router-link>
      <v-col cols="3">
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
          outlined
          rounded
          dense
        ></v-text-field>
      </v-col>
      <v-spacer />
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" app>
      <v-sheet color="grey lighten-3" class="pa-4 text-center">
        <v-avatar size="64">
          <img :src="currentUser.profile_photo_path" />
        </v-avatar>
        <div>{{ currentUser.email }}</div>
      </v-sheet>
      <v-divider></v-divider>

      <v-list>
        <v-list-item
          :to="{ name: 'Dashboard' }"
          active-class="orange--text lighten-1"
        >
          <v-list-item-icon>
            <v-icon>mdi-home</v-icon>
          </v-list-item-icon>
          <v-list-item-title>{{ $t('Admin.Dashboard') }}</v-list-item-title>
        </v-list-item>

        <v-list-group
          :value="$route.name === 'User' || $route.name === 'ParamUser'"
          prepend-icon="mdi-account-circle"
        >
          <template v-slot:activator>
            <v-list-item-title>{{ $t('Admin.Users') }}</v-list-item-title>
          </template>
          <v-list-item
            link
            :to="{ name: item.name }"
            active-class="orange--text lighten-1"
            v-for="item in users"
            :key="`user-item-${item.text}`"
            class="mx-3"
          >
            <v-list-item-title v-text="item.text"></v-list-item-title>
            <v-list-item-icon>
              <v-icon v-text="item.icon"></v-icon>
            </v-list-item-icon>
          </v-list-item>
        </v-list-group>

        <v-list-group
          :value="
            $route.name === 'Pub' ||
            $route.name === 'PubRequest' ||
            $route.name === 'ParamPub'
          "
          prepend-icon="mdi-store"
        >
          <template v-slot:activator>
            <v-list-item-title>{{ $t('Admin.Pubs') }}</v-list-item-title>
          </template>
          <v-list-item
            link
            :to="{ name: item.name }"
            active-class="orange--text lighten-1"
            v-for="item in pubs"
            :key="`user-item-${item.text}`"
            class="mx-3"
          >
            <v-list-item-title v-text="item.text"></v-list-item-title>
            <v-list-item-icon>
              <v-icon v-text="item.icon"></v-icon>
            </v-list-item-icon>
          </v-list-item>
        </v-list-group>

        <v-list-group
          :value="$route.name === 'Comment' || $route.name === 'Report'"
          prepend-icon="mdi-comment-multiple-outline"
        >
          <template v-slot:activator>
            <v-list-item-title>{{ $t('Admin.Comments') }}</v-list-item-title>
          </template>
          <v-list-item
            link
            :to="{ name: item.name }"
            active-class="orange--text lighten-1"
            v-for="item in comments"
            :key="`user-item-${item.text}`"
            class="mx-3"
          >
            <v-list-item-title v-text="item.text"></v-list-item-title>
            <v-list-item-icon>
              <v-icon v-text="item.icon"></v-icon>
            </v-list-item-icon>
          </v-list-item>
        </v-list-group>

        <v-list-group
          :value="$route.name === 'Rating'"
          prepend-icon="mdi-star-outline"
        >
          <template v-slot:activator>
            <v-list-item-title>{{ $t('Admin.Ratings') }}</v-list-item-title>
          </template>
          <v-list-item
            link
            :to="{ name: item.name }"
            active-class="orange--text lighten-1"
            v-for="item in ratings"
            :key="`user-item-${item.text}`"
            class="mx-3"
          >
            <v-list-item-title v-text="item.text"></v-list-item-title>
            <v-list-item-icon>
              <v-icon v-text="item.icon"></v-icon>
            </v-list-item-icon>
          </v-list-item>
        </v-list-group>

        <v-list-group
          :value="$route.name === 'Dish' || $route.name === 'DishRequest'"
          prepend-icon="mdi-pot"
        >
          <template v-slot:activator>
            <v-list-item-title>{{ $t('Admin.Dishes') }}</v-list-item-title>
          </template>
          <v-list-item
            link
            :to="{ name: item.name }"
            active-class="orange--text lighten-1"
            v-for="item in dishes"
            :key="`user-item-${item.text}`"
            class="mx-3"
          >
            <v-list-item-title v-text="item.text"></v-list-item-title>
            <v-list-item-icon>
              <v-icon v-text="item.icon"></v-icon>
            </v-list-item-icon>
          </v-list-item>
        </v-list-group>

        <v-list-item link @click="onLogout">
          <v-list-item-icon>
            <v-icon>mdi-logout</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Logout</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container class="py-8 px-6" fluid>
        <router-view />
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
export default {
  data() {
    const _this = this
    return {
      cards: ['Today', 'Yesterday'],
      drawer: null,
      users: [
        {
          text: _this.$t('Admin.Management'),
          icon: 'mdi-account-multiple-outline',
          name: 'User'
        }
      ],
      pubs: [
        {
          text: _this.$t('Admin.Management'),
          icon: 'mdi-account-multiple-outline',
          name: 'Pub'
        },
        {
          text: _this.$t('Admin.Request'),
          icon: 'mdi-comment-question-outline',
          name: 'PubRequest'
        }
      ],
      comments: [
        {
          text: _this.$t('Admin.Management'),
          icon: 'mdi-account-multiple-outline',
          name: 'Comment'
        },
        {
          text: _this.$t('Admin.Report'),
          icon: 'mdi-message-alert',
          name: 'Report'
        }
      ],
      ratings: [
        {
          text: _this.$t('Admin.Management'),
          icon: 'mdi-account-multiple-outline',
          name: 'Rating'
        }
      ],
      dishes: [
        {
          text: _this.$t('Admin.Management'),
          icon: 'mdi-account-multiple-outline',
          name: 'Dish'
        },
        {
          text: _this.$t('Admin.Request'),
          icon: 'mdi-comment-question-outline',
          name: 'DishRequest'
        }
      ],
      search: ''
    }
  },
  computed: {
    ...mapGetters('user', ['currentUser'])
  },
  methods: {
    ...mapActions('user', ['logout']),
    async onLogout() {
      await this.logout()
      this.$router.push({ name: 'Login' })
    }
  }
}
</script>