<script>

import { api } from '../api.js';
import $ from 'jquery';
import iziToast from 'izitoast';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';

export default {
	data() {
		return {
			loading: false,
			section: this.$route.query.section
		}
	},

	components: {
		ClipLoader
	},

	created() {
	},

	mounted() {
		let that = this;

		if (that.section && that.section != '') {
			setTimeout(function() {
				document.getElementById(that.section).scrollIntoView();
			},750);
		}
	},

	methods: {
		async contactUs() {
			let contact_name    = this.$refs['contact_name'].value    ?? '';
			let contact_email   = this.$refs['contact_email'].value   ?? '';
			let contact_message = this.$refs['contact_message'].value ?? '';

			if (
				contact_name    != '' &&
				contact_email   != '' &&
				contact_message != ''
			) {
				this.loading = true;

				const response = await api(
					'POST',
					'public/contact-us',
					{
						name:    contact_name,
						email:   contact_email,
						message: contact_message
					}
				);

				if (response.status == 200) {
					this.loading = false;
					// console.log(response);
					this.$refs['contact_name'].value = '';
					this.$refs['contact_email'].value = '';
					this.$refs['contact_message'].value = '';

					this.$root.toast(
						'',
						response.message,
						'success'
					);
				} else {
					this.loading = false;
					this.$root.toast(
						'',
						response.message,
						'error'
					);
				}
			}
		}
	}
};

</script>

<template>
	<div class="landing-container">

	</div>
</template>

<style scoped>

.landing-container {
	background-image: url('@/assets/images/bg2.jpg');
	background-repeat: no-repeat;
	background-size: cover;
	width: 100%;
	height: auto;
	min-height: 100vh;
	background-attachment: fixed;
}

</style>