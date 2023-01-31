<script>

import App from '../../App.vue';
import { api } from '../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import LoginFooter from '../Login/LoginFooter.vue';

import '../Login/login.css';

export default {
	data() {
		return {
			loading:           false,
			confirmation_code: '',
			able_to_resend:    0,
			delay:             30,

			password:         '',
			password2:        '',
			password_hidden:  true,
			password2_hidden: true,
			min8chars:        false,
			onenumber:        false,
			specialchar:      false,

			name:             '',
			phone:            ''
		}
	},

	components: {
		ClipLoader,
		LoginFooter
	},

	created() {
	},

	mounted() {
		let that = this;

		setInterval(function() {
			that.able_to_resend += 1;
		},1000);
	},

	methods: {
		async confirmAccount() {
			this.loading = true;

			const response = await api(
				'POST',
				'user/confirm-registration',
				{
					confirmation_code: this.confirmation_code
				},
				this.$root.bearer_token
			);

			if (response.status == 200) {
				// console.log(response);
				this.$root.getMe();
				this.loading = false;
			} else {
				this.$root.toast(
					'Oops',
					'Incorrect confirmation code',
					'error'
				);
				this.loading = false;
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
				'user/set-password',
				{
					password: this.password
				},
				this.$root.bearer_token
			);

			if (response.status == 200) {
				// console.log(response);
				this.$root.getMe();
				this.loading = false;
			} else {
				this.$root.toast(
					'Oops',
					response.message,
					'error'
				);
				this.loading = false;
			}
		},

		async setName() {
			this.loading = true;

			const response = await api(
				'POST',
				'user/set-name',
				{
					name:  this.name,
					phone: this.phone
				},
				this.$root.bearer_token
			);

			if (response.status == 200) {
				// console.log(response);
				this.$root.routeTo('/finished');
				this.loading = false;
			} else {
				this.$root.toast(
					'',
					response.message,
					'warning'
				);
				this.loading = false;
			}
		},

		async resendCode() {
			this.loading        = true;
			this.able_to_resend = 0;
			this.$refs['confirmation_code'].focus();

			const response = await api(
				'POST',
				'user/resend-code',
				{},
				this.$root.bearer_token
			);

			if (response.status == 200) {
				// console.log(response);
				this.$root.toast(
					'',
					response.message,
					'success'
				);
				this.loading = false;
			} else {
				this.$root.toast(
					'',
					response.message,
					'warning'
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
		<div class="progress-wrap">
			<div 
				class="progress" 
				:style="{
					'--progress_width': 
					!this.$root.verified ? 
					'20%' : (
						!this.$root.password ? 
						'40%' : (
							!this.$root.pii.name ? 
							'70%' : 
							'100%'
						)
					)
				}"
			></div>
		</div>

		<div class="signup-box-wrap">
			<div v-if="loading" class="ajax-box">
				<ClipLoader size="45px" :color="this.$root.color_primary"></ClipLoader>
			</div>

			<div class="login-box">
				<a :href="this.$root.frontend_url" class="nostyle">
					<img src="@/assets/images/logo.png" class="login-img">
				</a>

				<div 
					v-if="!this.$root.verified" 
					class="login-form-wrap"
				>
					<p class="bold">
						{{ this.$root.email }}
					</p>

					<p class="mt5">
						A verification code has been sent to your email. Please enter it below.
					</p>

					<div class="form-group mt20">
						<input type="email" class="form-control fincen-input" placeholder="Enter verification code" v-model="confirmation_code" ref="confirmation_code">

						<button class="btn btn-success mt20 form-control" @click="confirmAccount">
							Next
						</button>
					</div>

					<p class="mt10 fs13 op8">
						* Codes can take up to 1 minute to reach your inbox. Refresh your email and check spam if you don't see it immediately. 
						<span v-if="able_to_resend > delay">
							Click <span class="pointer text-blue underline bold" @click="this.resendCode">here</span> to resend it.
						</span>
					</p>
				</div>

				<div 
					v-if="
						this.$root.verified &&
						!this.$root.password
					" 
					class="login-form-wrap"
				>
					<p class="bold">
						Set new password for {{ this.$root.email }}
					</p>

					<p class="pt15 check-circle" :class="{ 'check-circle-active': min8chars }">
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
							<button @click="setPassword" class="btn btn-success form-control mt20 bold">Set New Password</button>
						</form>
					</div>
				</div>

				<div 
					v-if="
						this.$root.verified &&
						this.$root.password
					" 
					class="login-form-wrap"
				>
					<p class="bold">
						Thanks, {{ this.$root.email }}
					</p>

					<p class="fs14">
						Enter the name and contact phone number of your firm.
					</p>

					<div class="form-group mt20">
						<p class="fs14">
							Name of Firm
						</p>

						<input type="email" class="form-control fincen-input mt5" placeholder="Enter name of firm" v-model="name" ref="name">

						<p class="fs14 mt20">
							Best contact phone number
						</p>

						<input type="tel" class="form-control fincen-input mt5" :onkeydown="this.$root.inputPhoneFormat" placeholder="Enter contact phone" v-model="phone" ref="phone">

						<button class="btn btn-success mt20 form-control" @click="setName">
							Finish Registration
						</button>
					</div>
				</div>
			</div>
		</div>

		<LoginFooter></LoginFooter>
	</div>
</template>

<style scoped>

.progress-wrap {
	width: 100%;
	max-width: 700px;
	height: 5px;
	background-color: #e0dfe5;
	margin-bottom: 40px;
	border-radius: 2px;
	position: relative;
}

.progress {
	background-color: var(--color-primary);
	border-radius: 2px;
	width: var(--progress_width);
	height: 100%;
	transition: .3s ease;
}

</style>