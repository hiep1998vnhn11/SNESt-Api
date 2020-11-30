<template>
  <v-dialog v-model="dialog" max-width="500">
    <template v-slot:activator="{ on, attrs }">
      <v-btn
        class="text-capitalize text-h6 mt-n10 mb-n5"
        large
        color="success"
        dark
        v-bind="attrs"
        v-on="on"
      >
        {{ $t('common.register') }}
      </v-btn>
    </template>
    <v-card :loading="loading">
      <v-card-title>
        {{ $t('common.register') }}
        <v-spacer></v-spacer>
        <v-btn icon @click="dialog = false">
          <v-icon color="dark">mdi-close</v-icon>
        </v-btn>
      </v-card-title>
      <v-divider class="mx-4"></v-divider>
      <v-container>
        <v-form ref="form" v-model="valid" lazy-validation>
          <v-text-field
            v-model="email"
            :rules="emailRules"
            :label="$t('Email')"
            required
          ></v-text-field>
          <v-text-field
            v-model="name"
            :rules="nameRules"
            :label="$t('Name')"
            required
          ></v-text-field>
          <v-text-field
            v-model="password"
            type="password"
            :rules="passwordRules"
            :label="$t('Password')"
            required
          ></v-text-field>
          <v-text-field
            v-model="password_comfirmation"
            type="password"
            :rules="passwordRules"
            :label="$t('Password Confirm')"
            required
          ></v-text-field>
        </v-form>
      </v-container>
      <v-divider />
      <v-card-actions>
        <v-spacer />
        <v-btn
          color="success"
          class="text-h6 text-capitalize"
          :disabled="!valid"
          @click="onRegister"
        >
          {{ $t('common.register') }}
        </v-btn>
        <v-btn
          color="error"
          class="text-h6 text-capitalize"
          @click="dialog = false"
        >
          {{ $t('Cancel') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from 'axios'
import { mapActions } from 'vuex'
export default {
  data() {
    return {
      loading: false,
      error: null,
      dialog: false,
      birthDayDialog: false,
      notifications: false,
      sound: true,
      widgets: false,
      gender: '',
      valid: true,
      name: '',
      email: '',
      password: '',
      password_comfirmation: '',
      select: false,
      birthDay: new Date().toISOString().substr(0, 10),
      nameRules: [
        v => !!v || this.$t('Name is required!'),
        v =>
          (!!v && v.length >= 4) ||
          this.$t('Name too short! Name must contain more than 4 charactor!'),
        v =>
          (!!v && v.length <= 50) ||
          this.$t('Name too long! Name must contain less than 50 charactor!')
      ],
      emailRules: [
        v => !!v || this.$t('Email is required!'),
        v => (!!v && v.length >= 5) || this.$t('E-mail must be valid!'),
        v => (!!v && v.length <= 255) || 'Email too long!',
        v => /.+@.+/.test(v) || this.$t('E-mail must be valid!')
      ],
      passwordRules: [
        v => !!v || this.$t('Password is required!'),
        v =>
          (!!v && v.length >= 4) ||
          this.$t('Password too short! Please choose other password!'),
        v => (!!v && v.length <= 255) || 'Password too long!'
      ],
      genderRules: [v => !!v || 'Gender is required!']
    }
  },
  methods: {
    ...mapActions('user', ['login']),
    async onRegister() {
      if (
        !this.name ||
        !this.email ||
        !this.password ||
        this.password !== this.password_comfirmation
      ) {
        this.$refs.form.validate()
        return
      }
      this.loading = true
      this.error = null
      const user = {
        name: this.name,
        email: this.email,
        password: this.password,
        password_confirmation: this.password_comfirmation
      }
      try {
        const url = '/auth/register'
        await axios.post(url, user)
        this.dialog = false
        await this.$store.dispatch(
          'user/login',
          {
            email: this.email,
            password: this.password
          },
          { root: true }
        )
        this.$router.push({ name: 'Home' })
      } catch (err) {
        this.error = err.response
        this.$swal({
          icon: 'error',
          title: this.$t('Error'),
          text: this.error
        })
      }
      this.loading = false
    }
  }
}
</script>

<style></style>
