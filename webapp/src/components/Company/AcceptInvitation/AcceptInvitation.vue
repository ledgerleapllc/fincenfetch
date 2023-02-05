<script>

import App from '../../../App.vue';
import { api } from '../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import LoginFooter from '../../Login/LoginFooter.vue';

import '../../Login/login.css';

export default {
	data() {
		return {
			email:            '',
			law_firm_name:    '',
			complete:         false,
			hash:             this.$route.params.hash,
			loading:          true,
			password:         '',
			password2:        '',
			password_hidden:  true,
			password2_hidden: true,
			min8chars:        false,
			onenumber:        false,
			specialchar:      false
		}
	},

	components: {
		ClipLoader,
		LoginFooter
	},

	created() {
		if (!this.hash || this.hash == '') {
			this.$root.routeTo('/login');
		} else {
			this.verifyInvitation();
		}
	},

	mounted() {
		let that = this;
	},

	methods: {
		async verifyInvitation() {
			const response = await api(
				'GET',
				'user/verify-invitation',
				{
					hash: this.hash
				}
			);
			console.log(response);
			if (response.status == 200) {
				this.email         = response.detail.email;
				this.law_firm_name = response.detail.law_firm_name;
				this.loading       = false;
			} else {
				this.$root.routeTo('/c/reports');
			}
		},

		async setPassword() {
			let pw  = this.password;
			let pw2 = this.password2;

			if (pw != pw2) {
				this.$root.toast(
					'',
					'Passwords do not match',
					'warning'
				);
				return;
			}

			if (
				!this.min8chars ||
				!this.onenumber ||
				!this.specialchar
			) {
				this.$root.toast(
					'',
					'Password do not meet minimum complexity requirements',
					'warning'
				);
				return;
			}

			this.password_hidden  = true;
			this.password2_hidden = true;
			this.loading          = true;

			const response = await api(
				'POST',
				'user/accept-invitation',
				{
					hash:     this.hash,
					password: this.password
				},
				this.$root.bearer_token
			);

			if (response.status == 200) {
				// console.log(response);
				this.$root.bearer_token   = response.detail.bearer;
				this.$root.session_cookie = response.detail.cookie;

				this.$cookies.set('bearer_token', this.$root.bearer_token);
				this.$cookies.set('session_cookie', this.$root.session_cookie);

				window.location.href = '/c/complete-invite';
			} else {
				this.$root.toast(
					'Oops',
					response.message,
					'error'
				);
				this.loading = false;
			}
		},

		checkPassword() {
			if (this.password.length >= 8) {
				this.min8chars = true;
			} else {
				this.min8chars = false;
			}

			let number_pattern = /([0-9])+/g;

			if (number_pattern.test(this.password)) {
				this.onenumber = true;
			} else {
				this.onenumber = false;
			}

			let special_pattern = /\W|_/g;

			if (special_pattern.test(this.password)) {
				this.specialchar = true;
			} else {
				this.specialchar = false;
			}
		}
	},

	watch: {
		password: 'checkPassword'
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
				<a :href="this.$root.frontend_url" class="nostyle">
					<img src="@/assets/images/logo.png" class="login-img">
				</a>

				<div 
					v-if="!complete"
					class="login-form-wrap"
				>
					<p class="bold fs18">
						{{ this.email }}
					</p>

					<p class="fs14">
						You have been invited to submit the beneficial ownership information for your business to {{ law_firm_name }}. Please set a password to continue.
					</p>

					<p class="pt20 check-circle" :class="{ 'check-circle-active': min8chars }">
						<img v-if="min8chars == true" src="@/assets/images/check-circle-green.svg">
						<img v-else src="@/assets/images/check-circle.svg">
						Min 8 characters
					</p>
					<p class="check-circle" :class="{ 'check-circle-active': onenumber }">
						<img v-if="onenumber == true" src="@/assets/images/check-circle-green.svg">
						<img v-else src="@/assets/images/check-circle.svg">
						1 Number
					</p>
					<p class="check-circle" :class="{ 'check-circle-active': specialchar }">
						<img v-if="specialchar == true" src="@/assets/images/check-circle-green.svg">
						<img v-else src="@/assets/images/check-circle.svg">
						1 Special character
					</p>
					<div class="form-group pt10 login-form">
						<form @submit.prevent>
							<input class="form-control fincen-input" v-model="password" type="password" placeholder="New Password" autofocus>
							<input class="form-control fincen-input mt20" v-model="password2" type="password" placeholder="Confirm Password">
							<button @click="setPassword" class="btn btn-yellow form-control mt20 bold">Save Password</button>
						</form>
					</div>
				</div>

				<div 
					v-else
					class="login-form-wrap"
				>
					<p class="fs18 bold">
						Thanks!
					</p>

					<p class="fs14">
						You will now be able to access your reports dashboard to finish this report or view your completed reports.
					</p>

					<div class="form-group mt20">
						<button class="btn btn-yellow mt20 form-control" @click="this.$root.routeTo('/c/reports')">
							Continue to My Reports
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>

</style>