<template>
  <v-container>
    <v-card elevation="0" outlined tile>
      <v-card-title>
        <v-spacer />
        {{ $t('Form create pub') }}
        <v-spacer />
      </v-card-title>
      <v-card-text>
        <v-form ref="form" v-model="valid" lazy-validation>
          <v-row>
            <v-col cols="7">
              <v-text-field
                v-model="name"
                :counter="60"
                :label="`${$t('Name')} *`"
                :rules="[
                  (v) => !!v || $t('Required'),
                  (v) => (!!v && v.length > 4) || 'Name too sort!',
                ]"
              ></v-text-field>
              <v-text-field
                v-model="address"
                :counter="120"
                :label="`${$t('Address')} *`"
                :rules="[(v) => !!v || $t('Required')]"
              ></v-text-field>
              <v-text-field
                v-model="phone"
                :counter="12"
                :label="`${$t('Phone number')} *`"
                :rules="[(v) => !!v || $t('Required')]"
              ></v-text-field>
              <v-text-field
                v-model="email"
                :counter="55"
                :label="`${$t('Main email')} *`"
                :rules="[(v) => !!v || $t('Required')]"
              ></v-text-field>
            </v-col>
            <v-col cols="2">
              <v-file-input
                accept="image/png, image/jpeg, image/bmp"
                :placeholder="$t('Pick an photo')"
                prepend-icon="mdi-camera"
                :label="`${$t('Home photo')} *`"
                @change="onFileChange"
                v-model="image"
                :rules="[(v) => !!v || $t('Required')]"
              ></v-file-input>
            </v-col>
            <v-col cols="3">
              <v-img height="200" width="200" :src="src" />
            </v-col>
          </v-row>
          <v-text-field
            v-model="mapPath"
            :label="$t('Google map path')"
            :rules="mapRules"
          ></v-text-field>
          <v-text-field
            v-model="videoPath"
            :label="$t('Youtube video path')"
            :rules="videoRules"
          ></v-text-field>
          <v-textarea
            auto-grow
            :label="$t('Description')"
            rows="3"
            row-height="20"
            :rules="[(v) => !!v || $t('Required')]"
            v-model="description"
          ></v-textarea>
          <v-checkbox
            v-model="checkbox"
            :rules="[(v) => !!v || 'You must agree to continue!']"
            :label="$t('Accept our Terms')"
            required
          ></v-checkbox>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          :disabled="!valid"
          :loading="loading"
          color="primary"
          class="text-capitalize"
          @click="onSubmit"
        >
          {{ $t('Submit') }}
        </v-btn>
        <v-btn
          :disabled="!valid"
          color="success"
          class="text-capitalize"
          @click="onPreview"
        >
          {{ $t('Preview') }}
        </v-btn>
        <v-btn color="error" class="text-capitalize" @click="onCancel">
          {{ $t('Cancel') }}
        </v-btn>
        <v-spacer />
      </v-card-actions>
    </v-card>
    {{ preview }} {{ valid }}
    <v-dialog width="1200" v-model="preview">
      <v-card>
        <v-card-title>
          <v-spacer />
          <v-btn icon class="grey lighten-3" @click="preview = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-card-text class="mt-3">
          <preview
            :name="name"
            :main_email="email"
            :address="address"
            :phone_number="phone"
            :home_photo_path="imageUrl"
            :video_path="videoPath"
            :map_path="mapPath"
            :description="description"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn class="primary text-capitalize" large @click="onSubmit">
            Submit
          </v-btn>
          <v-btn
            color="error"
            class="text-capitalize"
            large
            @click="preview = false"
          >
            Cancel
          </v-btn>
          <v-spacer />
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="loading" hide-overlay persistent width="300">
      <v-card color="primary" dark>
        <v-card-text>
          {{ $t('Submitting new pub... Please stand by a minute') }}
          <v-progress-linear
            indeterminate
            color="white"
            class="mb-0"
          ></v-progress-linear>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import { mapActions } from 'vuex'
import Preview from './PreviewPub'
export default {
  components: { Preview },
  data() {
    const _this = this
    return {
      valid: true,
      name: '',
      address: '',
      phone: '',
      image: null,
      imageUrl: null,
      videoPath: '',
      mapPath: '',
      description: '',
      checkbox: false,
      preview: false,
      loading: false,
      email: '',
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
    src() {
      return this.imageUrl
        ? this.imageUrl
        : 'https://pdsohio.com/wp-content/uploads/2017/04/default-image.jpg'
    }
  },

  methods: {
    ...mapActions('pub', ['createPub']),
    onFileChange: function() {
      // Reference to the DOM input element
      // Ensure that you have a file before attempting to read it
      if (this.image) {
        // create a new FileReader to read this image and convert to base64 format
        var reader = new FileReader()
        // Define a callback function to run, when FileReader finishes its job
        reader.onload = e => {
          // Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
          // Read image as base64 and set to imageData
          this.imageUrl = e.target.result
        }
        // Start the reader job - read file as a data url (base64 format)
        reader.readAsDataURL(this.image)
      }
    },
    onCancel() {
      this.name = this.address = this.phone = ''
      this.image = this.imageUrl = null
      this.checkbox = false
      this.$router.push({ name: 'Home' })
    },
    async onPreview() {
      await this.$refs.form.validate()
      if (this.valid) this.preview = true
    },
    async onSubmit() {
      await this.$refs.form.validate()
      if (!this.valid) return
      this.loading = true
      try {
        let formData = new FormData()
        formData.append('name', this.name)
        formData.append('phone_number', this.phone)
        formData.append('image', this.image)
        formData.append('address', this.address)
        formData.append('description', this.description)
        formData.append('main_email', this.email)
        formData.append('video_path', this.videoPath)
        formData.append('map_path', this.mapPath)
        await this.createPub(formData)
        this.$swal({
          icon: 'success',
          title: 'Success',
          text: `Create ${this.name} successfully!`
        })
      } catch (err) {
        this.$swal({
          icon: 'error',
          title: 'Error',
          text: err.toString()
        })
      }
      this.name = this.phone = this.address = this.description = ''
      this.email = this.videoPath = this.mapPath = ''
      this.image = this.imageUrl = null
      this.$refs.form.resetValidation()
      this.loading = this.preview = false
    }
  }
}
</script>

<style></style>
