<script>

import App from '../../../App.vue';
import { api } from '../../api.js';
import $ from 'jquery';
import iziToast from 'izitoast';
import UserDetail from './UserDetail/UserDetail.vue';
import UserIpLog from './UserIpLog/UserIpLog.vue';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';

export default {
	data() {
		return {
			uri_category: this.$route.params.category,
			loading:  false
		}
	},

	components: {
		UserDetail,
		UserIpLog,
		ClipLoader
	},

	created() {
		//
	},

	mounted() {
		let that = this;

		if (
			this.uri_category != 'detail' &&
			this.uri_category != 'iplog'
		) {
			this.uri_category = 'detail';
			this.$root.routeTo(`/c/settings/detail`);
		}
	},

	watch: {
		'$route' (to, from) {
			this.uri_category = this.$route.params.category;
		}
	},

	methods: {}
};

</script>

<template>
	<div class="sub-view">
		<div class="sub-view-left">
			<p class=" text-blue fs18 bold">
				Navigation
			</p>

			<div 
				class="sub-view-menu-item mt30"
				:class="uri_category == 'detail' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/settings/detail')"
			>
				User Details
			</div>

			<div 
				class="sub-view-menu-item"
				:class="uri_category == 'iplog' ? 'sub-view-menu-item-active' : ''"
				@click="this.$root.routeTo('/c/settings/iplog')"
			>
				IP Log
			</div>
		</div>

		<div class="sub-view-right">
			<div v-if="loading" class="ajax-box">
				<ClipLoader size="45px" :color="this.$root.color_primary"></ClipLoader>
			</div>

			<UserDetail v-if="this.uri_category == 'detail'"></UserDetail>
			<UserIpLog v-else-if="this.uri_category == 'iplog'"></UserIpLog>
		</div>
	</div>

	<div id="updateEmailModal">
		<div class="izi-padding">
			<p class="op7">
				<i class="fa fa-envelope"></i>
				<span> Update Email</span>
				<span class="float-right x-close">&times;</span>
			</p>
			<hr>
			<div class="form-group pt10">
				<p>You will need to verify your new email address before updating it.</p>
				<label>New Email</label>
				<input id="input-updateEmailModal" type="text" class="form-control form-control-sm">
				<button id="button-updateEmailModal" class="btn btn-success btn-sm mt10 text-light">Send Email Confirmation</button>
			</div>
		</div>
	</div>

	<div id="updateEmailMfaModal">
		<div class="izi-padding">
			<p class="op6">
				<i class="fa fa-user"></i>
				<span> Multi-factor Authentication Requested</span>
			</p>
			<hr>
			<p>Please verify your identity to change your email address.</p>
			<div class="form-group pt10">
				<label for="usr">Code</label>
				<input id="input-updateEmailMfaModal" type="text" class="form-control form-control-sm transform-uppercase">
				<button id="button-updateEmailMfaModal" class="btn btn-success btn-sm mt10 text-light">Submit</button>
			</div>
		</div>
	</div>

	<div id="updateEmailConfirmModal">
		<div class="izi-padding">
			<p class="op6">
				<i class="fa fa-user"></i>
				<span> Confirm Email Change</span>
			</p>
			<hr>
			<p>We sent a confirmation code to your new email address. To finish your email change, please enter it below.</p>
			<div class="form-group pt10">
				<label for="usr">Code</label>
				<input id="input-updateEmailConfirmModal" type="text" class="form-control form-control-sm transform-uppercase">
				<button id="button-updateEmailConfirmModal" class="btn btn-success btn-sm mt10 text-light">Submit</button>
			</div>
		</div>
	</div>

	<div id="updatePasswordMfaModal">
		<div class="izi-padding">
			<p class="op6">
				<i class="fa fa-user"></i>
				<span> Multi-factor Authentication Requested</span>
			</p>
			<hr>
			<p>Please verify your identity to change your password.</p>
			<div class="form-group pt10">
				<label for="usr">Code</label>
				<input id="input-updatePasswordMfaModal" type="text" class="form-control form-control-sm transform-uppercase">
				<button id="button-updatePasswordMfaModal" class="btn btn-success btn-sm mt10 text-light">Submit</button>
			</div>
		</div>
	</div>

</template>

<style scoped>

#updatePasswordMfaModal,
#updateEmailConfirmModal,
#updateEmailModal,
#updateEmailMfaModal {
	display: none;
}

</style>