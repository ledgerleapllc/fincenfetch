<script>

import App from '../../App.vue';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import LoginFooter from './LoginFooter.vue';
import './login.css';

export default {
	data() {
		return {
			step: 1,
			loading: false,
			show_reset: true
		}
	},

	mounted() {
		//
	},

	components: {
		ClipLoader,
		LoginFooter
	},

	methods: {
		async gotoStep2() {
			if (
				!this.$root.inputEmail ||
				this.$root.inputEmail == ""
			) {
				this.$root.toast('Email', 'Please enter your email address to receive a reset link', 'info');
				this.$refs["email_ref"].focus();
				return;
			}

			this.loading = true;
			const reset = await this.sendLink();

			if (reset) {
				this.loading = false;
				this.step = 2;
			}
		},

		goback() {
			this.$root.routeTo('/login');
		},

		async sendLink() {
			const result = await this.$root.forgotPassword(this.$root.inputEmail);
			return result;
		},

		async resendLink() {
			const result = await this.sendLink();
			this.show_reset = false;
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
					<label>Reset Password</label>
					<div class="form-group mt20 login-form">
						<form @submit.prevent>
							<input class="form-control fincen-input" v-model="this.$root.inputEmail" type="email" placeholder="Email Address" ref="email_ref" autofocus autosave="" autocomplete="">
							<button @click="this.gotoStep2" class="btn btn-success mt20 full-width bold">Get Reset Link</button>
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

				<div v-show="step == 2" class="login-form-wrap text-center">
					<p>
						<img src="@/assets/images/forgot-password.svg" class="forgot-password-img">
					</p>
					<p class="bold pt15">Check your inbox!</p>
					<p>
						<span @click="this.$root.routeTo('login')" class="text-blue fs14 pointer">Back to Log In</span>
					</p>
					<p v-if="show_reset == true" class="pt30">
						Didn't get the email?&ensp;
						<span @click="this.resendLink()" class="text-blue fs14 pointer">Click here to resend it</span>
					</p>
				</div>

			</div>
		</div>

		<LoginFooter></LoginFooter>
	</div>
</template>
