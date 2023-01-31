<script>

import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import LoginFooter from '../Login/LoginFooter.vue';

import '../Login/login.css';

export default {
	data() {
		return {
			loading: false
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
		gotoDashboard() {
			window.location.href = '/u/dashboard';
		}
	}
};

</script>

<template>
	<div class="login-container">
		<div class="progress-wrap">
			<div 
				class="progress" 
				:style="{ '--progress_width': '100%' }"
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

				<div class="login-form-wrap text-center">
					<img src="@/assets/images/green-check.png" class="check-circle">

					<p class="bold mt25 fs17">
						Nide work! Your account is active in demo mode.
					</p>

					<p class="mt5 fs14">
						You can log in and explore your dashboard.
					</p>

					<div class="form-group mt10">
						<button class="btn btn-success mt20 form-control" @click="gotoDashboard">
							Explore my dashboard
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>

.check-circle {
	width: 70px;
	filter: drop-shadow(1px 2px 5px rgba(0,0,0,0.2));
}

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