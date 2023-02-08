<script>

import App from '../../App.vue';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import LoginFooter from './LoginFooter.vue';

import './login.css';

export default {
	data() {
		return {
			start_action:  this.$route.query.start,
			inputPassword: "",
			inputMfa:      "",
			step:          1,
			mfa_message:   "We sent a 2FA verification code to your inbox.",
			loading:       false
		}
	},

	mounted() {
		if (this.start_action) {
			this.$cookies.set('start_action', this.start_action);
		}
	},

	components: {
		ClipLoader,
		LoginFooter
	},

	methods: {
		gotoStep2() {
			let self = this;

			if (
				!this.$root.inputEmail ||
				this.$root.inputEmail == ""
			) {
				this.$root.toast('Email', 'Please enter your email address to login', 'info');
				this.$refs["email_ref"].focus();
				return;
			}

			this.step = 2;
			setTimeout(function() {
				self.$refs["password_ref"].focus();
			},100)
		},
		async do_login() {
			let self = this;

			if (
				!self.inputPassword ||
				self.inputPassword == ""
			) {
				self.$root.toast('Password', 'Please enter your password to login', 'info');
				self.$refs["password_ref"].focus();
				return;
			}

			this.loading = true;
			const login_result = await this.$root.login(this.$root.inputEmail, this.inputPassword);

			if (login_result) {
				this.loading = false;
			}
		},
		goback() {
			let self = this;

			if (self.step == 1) {
				return;
			}

			self.step = self.step - 1;

			setTimeout(function() {
				self.$refs["email_ref"].focus();
			},100)
		}
	},
};

</script>

<template>
	<div class="login-container">
		<div class="login-box-wrap">
			<div v-if="loading" class="ajax-box">
				<ClipLoader size="45px" :color="this.$root.color_primary"></ClipLoader>
			</div>

			<div class="login-box">
				<div class="text-center">
					<a :href="this.$root.frontend_url" class="nostyle">
						<img src="@/assets/images/logo.png" class="login-img">
					</a>
				</div>

				<div v-show="step == 1" class="login-form-wrap">
					<label>Log in</label>
					<div class="form-group login-form mt20">
						<form @submit.prevent>
							<input class="form-control fincen-input" v-model="this.$root.inputEmail" type="email" placeholder="Email Address" ref="email_ref" autofocus autosave="" autocomplete="">
							<button @click="this.gotoStep2" class="btn btn-success mt20 full-width bold">Next</button>
						</form>
					</div>

					<div class="mt20 fs14">
						<span class="float-right">
							Don't have an account yet?
							<span class="text-blue pointer" @click="this.$root.routeTo('/signup')">
								Register
							</span>
						</span>
					</div>
				</div>

				<div v-show="step == 2" class="login-form-wrap">
					<label>Hello there</label>
					<div class="form-group login-form mt20">
						<form @submit.prevent>
							<input class="form-control fincen-input" v-model="inputPassword" type="password" placeholder="Password" ref="password_ref">
							<button class="btn btn-success mt20 full-width bold" @click="do_login">Login</button>
						</form>
					</div>

					<div class="mt20 fs14">
						<span @click="goback()" class="pointer text-blue">
							Back
						</span>

						<p class="float-right">
							<span @click="this.$root.routeTo('forgot-password')" class="pointer text-blue">
								Forgot Password?
							</span>
						</p>
					</div>
				</div>
			</div>
		</div>

		<LoginFooter></LoginFooter>
	</div>
</template>
