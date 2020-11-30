<template>
  <v-fade-transition mode="out-in">
    <v-app id="inspire">
      <router-view />
    </v-app>
  </v-fade-transition>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
export default {
  name: "App",
  computed: mapGetters("user", ["currentUser", "isLoggedIn"]),
  created() {
    if (!this.currentUser && this.isLoggedIn) this.getUser();
  },
  methods: {
    ...mapActions("user", ["logout", "getUser"]),
    async signOut() {
      await this.logout();
      this.$router.push({ name: "Login" });
    },
    onClickOutsideWithConditional() {
      this.expand = false;
    },
    closeConditional(e) {
      return this.expand;
    }
  }
};
</script>

<style>
#app {
  font-family: "inherit", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
