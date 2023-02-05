<script>

import App from '../../App.vue';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import LoginFooter from './LoginFooter.vue';

import './login.css';

export default {
	data() {
		return {
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
	},
};

</script>

<template>
	<div class="login-container">
		<div class="login-box-wrap" style="box-shadow: none;">
			<div class="login-box">
				<div class="text-center">
					<a :href="this.$root.frontend_url" class="nostyle">
						<img src="@/assets/images/dog.png" class="login-img">
					</a>

					<p class="mt20 bold fs18">
						You are now logged out!
					</p>

					<p class="mt10">
						See you next time.
					</p>

					<button class="btn btn-success width-200 mt30" @click="this.$root.routeTo('/')">
						Return Home
					</button>
				</div>
			</div>
		</div>
	</div>
</template>
