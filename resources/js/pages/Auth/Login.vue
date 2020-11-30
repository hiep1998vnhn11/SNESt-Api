<template>
  <v-container>
    <v-col md="6" offset-md="3">
      <div class="text-center">
        <span class="text-h6 orange--text lighten-3">
          {{ $t('Admin.WelcomeNekoringoAdmin') }}
        </span>
        <v-img
          class="mx-auto"
          width="160"
          height="!60"
          src="/assets/logo.png"
        />
      </div>
      <v-card class="rounded-lg mt-3" :loading="loading">
        <v-container>
          <v-alert
            :value="registerSuccess"
            transition="scale-transition"
            type="success"
            height="50"
          >
            {{ $t('Register Successfully! Please login') }}
          </v-alert>
          <v-alert
            v-if="error"
            :value="loginError"
            transition="scale-transition"
            type="error"
            height="50"
          >
            {{ error.data.message }}
          </v-alert>
          <v-form ref="form" v-model="valid" lazy-validation>
            <v-text-field
              v-model="email"
              :rules="emailRules"
              :label="$t('Email')"
            ></v-text-field>
            <v-text-field
              v-model="password"
              type="password"
              :rules="passwordRules"
              :label="$t('Password')"
              required
            ></v-text-field>
            <v-btn
              color="primary"
              block
              class="text-h6 text-capitalize"
              @click="onLogin"
            >
              {{ $t('common.login') }}
            </v-btn>
          </v-form>
        </v-container>
      </v-card>
    </v-col>
  </v-container>
</template>
<script>
import { mapGetters } from 'vuex'
export default {
  data(){
    const _this = this
    return {
      valid: true,
      email: null,
      password: null,
      emailRules: [
        v => !!v || _this.$t('E-mail is required!'),
        v => /.+@.+/.test(v) || _this.$t('E-mail must be valid')
      ],
      passwordRules: [v => !!v || _this.$t('Password is required!')],
      registerSuccess: false,
      loading: false,
      error: null,
      loginError: false
    }
  },
  computed: mapGetters('user', ['isLoggedIn']),

  methods: {
    async onLogin() {
      if (!this.password || !this.email) {
        this.$refs.form.validate()
        return
      }
      if (!this.valid) return
      this.loading = true
      this.error = null
      try {
        await this.$store.dispatch(
          'user/login',
          {
            email: this.email,
            password: this.password
          },
          { root: true }
        )
        this.$router.push({ name: 'Dashboard' })
      } catch (err) {
        this.error = err.response
        this.loginError = true
      }
      this.loading = false
    }
  },
  watch: {
    registerSuccess: function() {
      if (this.registerSuccess === true) {
        const vm = this
        setTimeout(function() {
          vm.registerSuccess = false
        }, 2000)
      }
    },
    loginError: function() {
      if (this.loginError === true) {
        const vm = this
        setTimeout(function() {
          vm.registerSuccess = false
        }, 2000)
      }
    }
  }
}
</script>
