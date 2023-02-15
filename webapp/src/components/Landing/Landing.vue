<script>

import { api } from '../api.js';
import $ from 'jquery';
import iziToast from 'izitoast';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';

export default {
	data() {
		return {
			loading: false
		}
	},

	components: {
		ClipLoader
	},

	created() {
		window.location.href = '/login';
	},

	mounted() {
		let that = this;
	},

	methods: {
	}
};

</script>

<template>
	<div class="landing-container">

	</div>
</template>

<style scoped>

.landing-container {
	width: 100%;
	height: auto;
	min-height: 100vh;
}

</style>