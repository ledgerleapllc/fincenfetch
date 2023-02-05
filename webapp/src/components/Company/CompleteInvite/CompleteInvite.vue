<script>

import App from '../../../App.vue';
import { api } from '../../api.js';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import LoginFooter from '../../Login/LoginFooter.vue';

import '../../Login/login.css';

export default {
	data() {
		return {
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
	},

	methods: {
		gotoReports() {
			window.location.href = '/c/reports';
		}
	},

	watch: {
	}
};

</script>

<template>
	<div class="login-container">
		<div class="signup-box-wrap">
			<div class="login-box">
				<a :href="this.$root.frontend_url" class="nostyle">
					<img src="@/assets/images/logo.png" class="login-img">
				</a>

				<div class="login-form-wrap">
					<p class="fs18 bold">
						Thanks!
					</p>

					<p class="fs14">
						You will now be able to access your reports dashboard to finish this report or view your completed reports.
					</p>

					<div class="form-group mt20">
						<button class="btn btn-yellow mt20 form-control" @click="gotoReports">
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