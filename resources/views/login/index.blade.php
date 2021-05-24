  <div class="login-body">
      <loading-component v-if="loading" />

      <div class="login-card">
          <loading-component v-if="facebook.loading && facebook.user" :text="`${$t('Welcome')} ${facebook.user}`" />
          <loading-component v-if="google.loading && google.user" :text="`${$t('Welcome')} ${google.user.name}`" />
          <h1>
              {{ $t('Login') }}
          </h1>
          <v-container>
              <v-form ref="form" v-model="valid" lazy-validation>
                  <v-text-field class="login-input" v-model="email" :rules="emailRules" autocomplete="off"
                      :label="$t('Email')" required color="rgba(255,255,255,0.5)" @keyup.enter="onLogin"></v-text-field>
                  <v-text-field v-model="password" autocomplete="off" type="password" :rules="passwordRules"
                      :label="$t('Password')" required @keyup.enter="onLogin"></v-text-field>
              </v-form>
              {{ $t('common.forgotPassword') }}
          </v-container>
          <div>
              <v-btn color="primary" class="text-capitalize mb-3" block outlined rounded @click="onLogin">
                  {{ $t('common.login') }}
              </v-btn>
              <auth-register class="mx-auto" />
          </div>
          <div class="mt-3">
              <v-btn :loading="facebook.loggingIn" :disabled="facebook.loggingIn || !!facebook.user" icon text outlined
                  x-large class="mr-1" @click="onLoginFacebook">
                  <v-avatar size="50">
                      <img src="~/assets/icons/facebook.png" />
                  </v-avatar>
              </v-btn>
              <v-btn :loading="google.loggingIn" icon x-large class="mr-1" @click="onSignInGoogle">
                  <v-avatar size="50">
                      <img src="~/assets/icons/google-icon.webp" />
                  </v-avatar>
              </v-btn>
          </div>
          <v-btn v-if="facebook.user && facebook.accessToken" color="primary" class="text-capitalize mt-3" block
              outlined rounded @click="onContinueFacebook">
              <v-spacer />
              {{ $t('ContinueWith') }} {{ facebook . user }}
              <v-spacer />
              <v-avatar size="35" class="mr-n4">
                  <img :src="facebook.picture" />
              </v-avatar>
          </v-btn>
          <v-btn v-if="google.user && google.id_token" color="primary" class="text-capitalize mt-3" block outlined
              rounded @click="onContinueGoogle">
              <v-spacer />
              {{ $t('ContinueWith') }} {{ google . user . name }}
              <v-spacer />
              <v-avatar size="35" class="mr-n4">
                  <img :src="google.user.profile_photo_path" />
              </v-avatar>
          </v-btn>
      </div>
  </div>
