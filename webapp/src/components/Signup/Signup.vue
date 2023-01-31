<script>

import App from '../../App.vue';
import { api } from '../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import LoginFooter from '../Login/LoginFooter.vue';

import '../Login/login.css';

export default {
	data() {
		return {
			dev_mode:     false,
			loading:      false,
			email:        ''
		}
	},

	mounted() {
		this.getDevMode();
	},

	components: {
		ClipLoader,
		LoginFooter
	},

	methods: {
		async getDevMode() {
			const response = await api(
				'GET',
				'public/get-dev-mode',
				{}
			);

			if (response.status == 200) {
				this.dev_mode = response.detail.dev_mode;
			}
		},

		async getISOCountries() {
			this.loading = true;

			const response = await api(
				'GET',
				'public/get-countries',
				{}
			);

			if (response.status == 200) {
				this.loading   = false;
				this.countries = response.detail;
			}
		},

		quickFill() {
			let rhash = this.$root.randomHash(8);
		},

		async submitForm(event) {
			// console.log(event);
			// console.log(this.form_data);

			this.loading = true;

			const response = await api(
				'POST',
				'user/register',
				{
					email: this.email
				}
			);

			if (response.status == 200) {
				// console.log(response.message);
				this.loading              = false;
				this.$root.bearer_token   = response.detail.bearer;
				this.$root.session_cookie = response.detail.cookie;

				this.$cookies.set('bearer_token', this.$root.bearer_token);
				this.$cookies.set('session_cookie', this.$root.session_cookie);

				window.location.reload()
			} else {
				this.$root.toast(
					'',
					response.message,
					'error'
				);
				this.loading = false;
			}
		}
	}
};

</script>

<template>
	<div class="login-container">
		<div class="signup-box-wrap">
			<div v-if="loading" class="ajax-box">
				<ClipLoader size="45px" :color="this.$root.color_primary"></ClipLoader>
			</div>

			<div class="login-box">
				<div class="text-center">
					<a :href="this.$root.frontend_url" class="nostyle">
						<img src="@/assets/images/logo.png" class="login-img">
					</a>
				</div>

				<div class="login-form-wrap">
					<p class="bold">
						Register a new account
					</p>

					<div class="form-group mt20">
						<input type="email" class="form-control fincen-input" placeholder="Enter an email for your firm" autofocus v-model="email">

						<button class="btn btn-success mt20 form-control" @click="submitForm">
							Next
						</button>
					</div>
				</div>

				<div class="mt20 fs14">
					<span class="float-right">
						Already have an account?
						<span class="text-blue pointer" @click="this.$root.routeTo('/login')">
							Log in
						</span>
					</span>
				</div>
			</div>
		</div>

		<LoginFooter></LoginFooter>
	</div>
</template>

<style scoped>



</style>