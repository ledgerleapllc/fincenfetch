<script>

import { api } from './components/api.js';
import $ from 'jquery';
import iziToast from 'izitoast';
import iziModal from 'izimodal/js/iziModal';

import Popper from 'vue3-popper';
import ClipLoader from 'vue-spinner/src/ClipLoader.vue';
import { Modal } from 'vue-neat-modal';

import MainNav from './components/MainNav.vue';
import MessageArea from './components/MessageArea.vue';

import 'vue-neat-modal/dist/vue-neat-modal.css';

export default {
	// set root state
	data: () => ({
		inner_width:         window.innerWidth,
		guid:                '',
		email:               '',
		password:            null,
		bearer_token:        '',
		session_cookie:      '',
		role:                '',
		created_at:          '',
		verified:            0,
		twofa:               0,
		totp:                0,
		pii: {
			name:  '',
			ip:    '',
			phone: ''
		},
		route:               window.location.pathname,

		sidebar_cookie:      '',
		sidebarWidth:        250 + 'px',
		isMobile:            false,
		notifications:       [],
		alert_notification:  false,
		menu_open:           false,

		api_url:             import.meta.env.VITE_API_URL,
		frontend_url:        import.meta.env.VITE_FRONTEND_URL,

		color_primary:       '#353df9',
		color_secondary:     '#4047f9'
	}),

	components: {
		Popper,
		Modal,
		ClipLoader,
		MainNav,
		MessageArea
	},

	computed: {
		//
	},

	created() {
		if (
			this.$isMobile() ||
			window.innerWidth < 768
		) {
			this.isMobile = true;
		}

		window.addEventListener(
			'resize', 
			() => {
				if (
					this.$isMobile() ||
					window.innerWidth < 768
				) {
					this.isMobile = true;
				} else {
					this.isMobile = false;
				}
			}
		);

		if (!this.in_outer_zone()) {
			let that = this;

			that.getMe();

			setInterval(function() {
				that.getMe();
			},60000);
		}
	},

	mounted() {
		let that = this;
	},

	watch: {
		'$route' (to, from) {
			document.title = to.meta.title || 'FincenFetch';
		}
	},

	methods: {
		in_outer_zone() {
			if (
				window.location.pathname == '/' ||
				window.location.pathname.startsWith('/validator') ||
				window.location.pathname.startsWith('/finished') ||
				window.location.pathname.startsWith('/terms-of-service') ||
				window.location.pathname.startsWith('/privacy-policy') ||
				window.location.pathname.startsWith('/c/complete-invite')
			) {
				return true;
			}

			return false;
		},

		in_common_zone() {
			if (
				window.location.pathname.startsWith('/signup') ||
				window.location.pathname.startsWith('/login') ||
				window.location.pathname.startsWith('/logout') ||
				window.location.pathname.startsWith('/forgot-password') ||
				window.location.pathname.startsWith('/reset-password') ||
				window.location.pathname.startsWith('/confirm-account') ||
				window.location.pathname.startsWith('/f/accept-invitation') ||
				window.location.pathname.startsWith('/c/accept-invitation')
			) {
				return true;
			}

			return false;
		},

		async getMe() {
			let fetch_bearer_token = this.$cookies.get('bearer_token');

			if (
				!fetch_bearer_token || 
				fetch_bearer_token == 'undefined'
			) {
				console.log('No Bearer token - Route to login - 1');

				if (!this.in_common_zone()) {
					this.routeTo('/login');
					this.clearSession();
				}

				return;
			} else {
				this.bearer_token = fetch_bearer_token
			}

			const response = await api(
				'GET',
				'user/me',
				{},
				this.bearer_token
			);

			this.catch401(response);

			if (response.status == 200) {
				console.log(response.detail);
				this.guid           = response.detail.guid;
				this.email          = response.detail.email;
				this.password       = response.detail.password;
				this.role           = response.detail.role;
				this.created_at     = response.detail.created_at;
				this.verified       = parseInt(response.detail.verified);
				this.twofa          = parseInt(response.detail.twofa);
				this.totp           = parseInt(response.detail.totp);

				let pre_route = 'f';

				if (
					this.role == 'admin' ||
					this.role == 'sub-admin'
				) {
					pre_route = 'a';
				}

				if (this.role == 'firm') {
					pre_route = 'f';
				}

				if (this.role == 'company') {
					pre_route = 'c';
				}

				let pii_data = response.detail.pii_data;

				if (pii_data) {
					this.pii.name  = pii_data.name;
					this.pii.ip    = pii_data.ip;
					this.pii.phone = pii_data.phone;
				}

				if (!this.in_outer_zone()) {
					if (
						!this.verified     || 
						this.verified == 0 ||
						!this.password
					) {
						this.routeTo('/confirm-account');
						return;
					}
				}

				let path_split = window.location.pathname.split('/');
				let path0 = path_split[1] ?? '';

				if (
					this.in_common_zone() ||
					pre_route != path0
				) {
					if (
						this.role == 'admin' ||
						this.role == 'sub-admin'
					) {
						this.routeTo(`/${pre_route}/firms`);
					} else {
						this.routeTo(`/${pre_route}/reports`);
					}
				}

				// notifications banner
				this.alert_notification = false;
				this.notifications      = [];

				if (
					response.detail.notifications &&
					response.detail.notifications.length > 0
				) {
					this.notifications = response.detail.notifications;
					let that = this;

					Object.values(this.notifications).forEach(function(item) {
						if (parseInt(item.dismissable) == 1) {
							that.alert_notification = true;
						}
					});
				}

				/*this.notifications[0] = {
					notification_id: 34,
					title: 'title',
					message: 'lajsdkja hskda hksd aksdj alkj dsa akljs dlkja hsdkja hlskdj halksjd hakljsd hlkajs hdlkaj hsdlkaj shdlkaj hsdlkaj hsdlkja hsldkja hslkdjh alskdj halksjd halkjs dhlkajs dhlkajs dhlkajh dasashjhlkajsh dlkajhs dlkja hsdlkja hsdlkja hsdlkja hsldkj ahlksdj halksjd halkjsd hlakjs dhlkaj dhlkas',
					type: 'info',
					dismissable: true,
					priority: 1,
					cta: ''
				}*/

				// refresh bearer_token cookie
				this.$cookies.set('bearer_token', this.bearer_token);
			}
		},

		clearSession() {
			this.guid               = '';
			this.email              = '';
			this.bearer_token       = '';
			this.session_cookie     = '';
			this.role               = '';
			this.pseudonym          = '';
			this.created_at         = '';
			this.verified           = 0;
			this.twofa              = 0;
			this.totp               = 0;
			this.pii                = {};
			this.$cookies.remove('bearer_token');
		},

		routeToLogin() { 
			this.$router.replace('/login')
		},

		routeTo(path) {
			this.route = path;
			this.$router.push(path)
		},

		goHome() {
			window.location.href = '/';
		},

		openLink(link) {
			window.open(link);
		},

		async login(email, password) {
			let cookie = this.$cookies.get('session_cookie');

			const response = await api(
				'POST', 
				'user/login', 
				{
					email:    email,
					password: password,
					cookie:   cookie
				}
			);

			if (
				response.detail.hasOwnProperty('twofa') ||
				response.detail.hasOwnProperty('totp')
			) {
				this.guid = response.detail.guid;

				if (
					response.detail.twofa === false &&
					response.detail.totp === false
				) {
					// No 2fa enabled for this account, but the device is not recognized.
					this.popupModal('newdevice', false);
				} else {
					this.popupModal('mfa', false);
				}

				return true;
			}

			if (response.status >= 400) {
				if (!response.message) {
					response.message = "Could not complete request. Check your internet connection";
				}

				this.toast('Oops', response.message, 'error');
			}

			if (response.status == 200) {
				this.bearer_token   = response.detail.bearer;
				this.session_cookie = response.detail.cookie;

				this.$cookies.set('bearer_token', this.bearer_token);
				this.$cookies.set('session_cookie', this.session_cookie);

				// this.getMe();
				window.location.reload()
			}

			return true;
		},

		async logout() {
			const response = await api(
				'GET',
				'user/logout',
				{},
				this.bearer_token
			);

			if (response.status == 200) {
				this.clearSession();
				this.toast('Logout', response.message, 'success');
				this.routeTo('/logout')
			}
		},

		async forgotPassword(email) {
			const response = await api(
				'POST',
				'user/forgot-password',
				{
					email: email
				}
			);

			return response;
		},

		async resetPassword(email, hash, new_password) {
			const response = await api(
				'POST',
				'user/reset-password',
				{
					email:        email,
					hash:         hash,
					new_password: new_password
				}
			);

			if (response.status == 200) {
				this.toast('Password Reset', response.message, 'success');
				// this.routeToLogin()
			} else {
				this.toast('Password Reset', response.message, 'error');
			}

			return response;
		},

		async dismiss_notification(notification_id) {
			const response = await api(
				'POST',
				'user/dismiss-notification',
				{
					notification_id: notification_id
				},
				this.bearer_token
			);

			this.catch401(response);

			if (response.status == 200) {
				this.getMe();
			}
		},

		popupModal(
			modal_name, 
			easy_close = true
		) {
			let that          = this;
			let modal_id      = `#${modal_name}Modal`;
			let submit_btn_id = `#button-${modal_name}Modal`;
			let submit_func   = `${modal_name}ModalSubmit`;

			$(modal_id).iziModal('destroy')
			$("body").off('click', submit_btn_id)

			let modal = $(modal_id).iziModal({
				appendTo:      false,
				theme:         'light',
				closeOnEscape: easy_close,
				closeButton:   easy_close,
				overlayClose:  easy_close
			})

			this.global_izimodal = modal;

			$(modal_id).iziModal('open')

			$("body").on('click', submit_btn_id, function() {
				that[submit_func](modal, modal_id);
			})
		},

		async mfaModalSubmit(modal, modal_id) {
			let mfa_code = $(modal).find("#input-mfaModal").val();

			if (mfa_code) {
				$(modal_id).iziModal('startLoading')

				const response = await api(
					'POST', 
					'user/login-mfa', 
					{
						guid:     this.guid,
						mfa_code: mfa_code,
						cookie:   ''
					}
				);

				$(modal_id).iziModal('stopLoading');

				if (response.status == 200) {
					this.bearer_token   = response.detail.bearer;
					this.session_cookie = response.detail.cookie;

					this.$cookies.set('bearer_token', this.bearer_token);
					this.$cookies.set('session_cookie', this.session_cookie);

					// this.getMe();
					$(modal_id).iziModal('close')
					window.location.reload()
				}

				if (response.status >= 400) {
					if (!response.message) {
						response.message = "Could not complete request. Check your internet connection";
					}

					this.toast('Oops', response.message, 'warning');
				}
			}
		},

		async newdeviceModalSubmit(modal, modal_id) {
			let mfa_code = $(modal).find("#input-newdeviceModal").val();

			if (mfa_code) {
				$(modal_id).iziModal('startLoading')

				const response = await api(
					'POST', 
					'user/login-mfa', 
					{
						guid:     this.guid,
						mfa_code: mfa_code,
						cookie:   ''
					}
				);

				$(modal_id).iziModal('stopLoading');

				if (response.status == 200) {
					this.bearer_token   = response.detail.bearer;
					this.session_cookie = response.detail.cookie;

					this.$cookies.set('bearer_token', this.bearer_token);
					this.$cookies.set('session_cookie', this.session_cookie);

					// this.getMe();
					$(modal_id).iziModal('close')
					window.location.reload()
				}

				if (response.status >= 400) {
					if (!response.message) {
						response.message = "Could not complete request. Check your internet connection";
					}

					this.toast('Oops', response.message, 'warning');
				}
			}
		},

		catch401(response) {
			if (response) {
				if (response.status == 401) {
					console.log('401 DEAUTH');
					this.clearSession();
					this.routeTo('/login');
					window.location.reload();
				}
			}
		},

		toast(
			title, 
			message, 
			style = 'show'
			/*
			info, success, warning, error, question
			*/
		) {
			iziToast[style]({
				title:        title,
				message:      message,
				timeout:      10000,
				resetOnHover: true
			});
		},

		dropzone_error(event) {
			console.log(event);
			let error = event.error ?? '';

			if (error == 'MAX_FILE_SIZE') {
				this.toast(
					'Upload Error',
					`File size too large`,
					'error'
				);
			}

			if (error == 'INVALID_TYPE') {
				this.toast(
					'Upload Error',
					`Invalid file type`,
					'error'
				);
			}
		},

		in_array(needle, haystack) {
			let length = haystack.length;

			for (let i = 0; i < length; i++) {
				if (haystack[i] == needle) return true;
			}

			return false;
		},

		gettype(obj) {
			return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase()
		},

		formatDateWithZone(date) {
			let s = date.toLocaleString('en-GB');
			let a = s.split(/\D/);
			return a[2] + '-' + a[1] + '-' + a[0] + ' ' + a[4] + ':' + a[5] + ':' + a[6];
		},

		formatZDate(date) {
			console.log(date);
			if (typeof date.toISOString === 'function') {
				date = date.toISOString();
			}
			console.log(date);

			date  = date.replace('T', ' ');
			console.log(date);
			let s = date.split('.');
			console.log(s);
			return s[0];
		},

		formatHash(hash, l = 8) {
			if (typeof hash != 'string') {
				return '';
			}

			if (hash.length <= l) {
				return hash;
			}

			let b = 1;
			let dots = '...';

			if (l % 2 == 0) {
				b = 0;
				dots = '..';
			}

			let split = ((l - b) / 2) - 1;
			let first = hash.slice(0, split);
			let last  = hash.slice(hash.length - split);

			return `${first}${dots}${last}`;
		},

		formatString(s, l = 50) {
			if (typeof s != 'string') {
				return '';
			}

			if (s.length <= l) {
				return s;
			}

			let split = s.slice(0, l - 3);

			return `${split}...`;
		},

		shortGUID(s) {
			if (typeof s != 'string') {
				return s;
			}

			let split  = s.split('-');
			let letter = split[0] ?? '';
			let hash   = split[1] ?? '';

			if (
				letter == '' ||
				hash   == ''
			) {
				return s;
			}

			return `${letter}-${hash}`;
		},

		dateTimeToDate(dt = '') {
			if (!dt) {
				return dt;
			}

			if (typeof dt != 'string') {
				return dt;
			}

			let s = dt.split(' ');
			let t = s[0] ?? '';

			return t;
		},

		randomHash(l = 10) {
			let chars    = '0123456789abcdefABCDEF';
			let char_len = chars.length;
			let ret      = '';

			for (let i = 0; i < l; i++) {
				let rand = Math.floor(Math.random() * char_len);
				ret = ret + chars[rand];
			}

			return ret;
		},

		ucfirst(s) {
			if (!s) {
				return s;
			}

			return `${s[0].toUpperCase()}${s.substring(1)}`;
		},

		isNumberFormat(value) {
			if (this.gettype(value) == 'string') {
				let int = parseInt(value);
				let str = int.toString();

				if (value === str) {
					return true;
				}
			} else

			if (this.gettype(value) == 'number') {
				let str = value.toString();
				let int = parseInt(str);

				if (value === int) {
					return true;
				}
			}

			return false;
		},

		inputIsZipCodeFormat(element) {
			let key    = element.key ?? '';
			let target = element.target ?? '';
			let value  = target.value ?? '';
			let newval = value + key;

			if (
				key == 'Backspace' ||
				key == 'Delete'    ||
				key == 'Tab'
			) {
				return true;
			}

			if (!/^[0-9]+$/.test(key)) {
				return false;
			}

			if (value.length >= 5) {
				return false;
			}
		},

		inputIsDateFormat(element) {
			let key    = element.key ?? '';
			let target = element.target ?? '';
			let value  = target.value ?? '';
			let newval = value + key;

			if (
				key == 'Backspace' ||
				key == 'Delete'    ||
				key == 'Tab'
			) {
				return true;
			}

			if (!/^[0-9]+$/.test(key)) {
				return false;
			}

			if (newval.length == 4) {
				let year = parseInt(newval.slice(0, 4));

				if (year > 2100) {
					element.target.value = '2100/';
				} else {
					element.target.value = newval + '/';
				}

				return false;
			}

			if (value.length == 4) {
				let year = parseInt(value.slice(0, 4));

				if (year > 2100) {
					element.target.value = '2100/' + key;
				} else {
					element.target.value = value + '/' + key;
				}

				return false;
			}

			if (
				newval.length == 6 &&
				parseInt(key) > 1
			) {
				element.target.value = value + '0' + key + '/';
				return false;
			}

			if (newval.length == 7) {
				let month = parseInt(newval.slice(5, 7));

				if (month > 12) {
					element.target.value = value.slice(0, 5) + '12/';
				} else {
					element.target.value = newval + '/';
				}

				return false;
			}

			if (value.length == 7) {
				element.target.value = value + '/' + key;
				return false;
			}

			if (newval.length == 10) {
				let day = parseInt(newval.slice(8, 10));

				if (day > 31) {
					element.target.value = value.slice(0, 8) + '31';
					return false;
				}
			}

			if (newval.length > 10) {
				return false;
			}
		},

		inputPhoneFormat(element) {
			let key    = element.key ?? '';
			let target = element.target ?? '';
			let value  = target.value ?? '';
			let newval = value + key;

			if (
				key == 'Backspace' ||
				key == 'Delete'    ||
				key == 'Tab'
			) {
				return true;
			}

			if (!/^[0-9()+-]+$/.test(key)) {
				return false;
			}

			if (value.length > 16) {
				return false;
			}
		}
	}
};

</script>

<template>
	<MainNav></MainNav>
	<MessageArea></MessageArea>

	<router-view></router-view>

	<div id="mfaModal">
		<div class="izi-padding">
			<p class="op6">
				<i class="fa fa-user"></i>
				<span> Multi-factor Authentication Requested</span>
			</p>
			<hr>
			<h5>Enter Your MFA Code</h5>
			<div class="form-group pt10">
				<label for="usr">Code</label>
				<input id="input-mfaModal" type="text" class="form-control form-control-sm transform-uppercase">
				<button id="button-mfaModal" class="btn btn-success btn-sm mt10 text-light">Login</button>
			</div>
		</div>
	</div>

	<div id="newdeviceModal">
		<div class="izi-padding">
			<p class="op6">
				<i class="fa fa-user"></i>
				<span> Device not recognized. Multi-factor Authentication Requested</span>
			</p>
			<hr>
			<h6>An authentication code has been sent to your email address. Please enter it here.</h6>
			<div class="form-group pt10">
				<label for="">Code</label>
				<input id="input-newdeviceModal" type="text" class="form-control form-control-sm transform-uppercase">
				<button id="button-newdeviceModal" class="btn btn-success btn-sm mt10 text-light">Login</button>
			</div>
		</div>
	</div>

</template>

<style>

.izi-padding {
	padding: 20px;
}

.clip-loader-inline {
	text-align: left;
	display: inline-block;
}

#mfaModal,
#newdeviceModal {
	display: none;
}

.modal-container {
	position: relative;
	z-index: 1000;
	width: 100%;
	max-width: 400px;
	height: auto;
	border-radius: 5px;
	padding: 25px 30px;
	background-color: #fff;
	box-shadow: 0px 3px 6px rgba(0,0,0,0.1);
}

.vue-neat-modal-backdrop {
	z-index: 3;
}

.vue-neat-modal-wrapper {
	z-index: 4;
}

.modal-container h5 {
	border-bottom: 1px solid #ddd;
	font-size: 18px;
	font-weight: bold;
}

.progress2-wrap {
	background-color: #DAD9E0;
	width: 100%;
	height: 5px;
	border-radius: 2px;
	overflow: hidden;
	box-shadow: 0px 0px 2px #e0e0e0;
	position: relative;
}

.progress2 {
	height: 100%;
	width: 0;
	background-color: var(--color-primary);
}

@media all and (max-width: 991px) {
}

@media all and (max-width: 767px) {
}

</style>