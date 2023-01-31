import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import cookie from 'vue-cookies';
import VueMobileDetection from "vue-mobile-detection";
import VueApexCharts from 'vue3-apexcharts';

/* common routes */
import Landing from '@/components/Landing/Landing.vue';
import Signup from '@/components/Signup/Signup.vue';
import Login from '@/components/Login/Login.vue';
import ForgotPassword from '@/components/Login/ForgotPassword.vue';
import ResetPassword from '@/components/Login/ResetPassword.vue';
import ConfirmAccount from '@/components/ConfirmAccount/ConfirmAccount.vue';
import Finished from '@/components/Finished/Finished.vue';
import Terms from '@/components/Terms/Terms.vue';
import PrivacyPolicy from '@/components/PrivacyPolicy/PrivacyPolicy.vue';
import NotFound from '@/components/NotFound/NotFound.vue';

/* user routes */
import UserAccount from '@/components/User/Account/Account.vue';

/* admin routes */
import AdminAccount from '@/components/Admin/Account/Account.vue';
import AdminFirms from '@/components/Admin/Firms/Firms.vue';
import AdminFirm from '@/components/Admin/Firm/Firm.vue';

import './assets/css/main.css'

const app_name = 'FincenFetch';

const router = createRouter({
	history: createWebHistory(),
	routes: [
		/* Common endpoints */
		{
			path: '/:pathMatch(.*)*',
			name: 'NotFound',
			component: NotFound,
			meta: {
				title: `${app_name} - Not Found`,
				display_name: 'Not Found'
			}
		},
		{
			path: '/',
			name: 'Landing',
			component: Landing,
			meta: {
				title: `${app_name}`,
				display_name: ''
			}
		},
		{
			path: '/terms-of-service',
			name: 'Terms',
			component: Terms,
			meta: {
				title: `${app_name} - Terms of Service`,
				display_name: 'Terms of Service'
			}
		},
		{
			path: '/privacy-policy',
			name: 'PrivacyPolicy',
			component: PrivacyPolicy,
			meta: {
				title: `${app_name} - Privacy Policy`,
				display_name: 'Privacy Policy'
			}
		},
		{
			path: '/login',
			name: 'Login',
			component: Login,
			meta: {
				title: `${app_name} - Login`,
				display_name: 'Login'
			}
		},
		{
			path: '/u/',
			redirect: to => {
				return {
					name: 'Login'
				}
			},
			meta: {
				protected: false
			}
		},
		{
			path: '/a/',
			redirect: to => {
				return {
					name: 'Login'
				}
			},
			meta: {
				protected: false
			}
		},
		{
			path: '/forgot-password',
			name: 'ForgotPassword',
			component: ForgotPassword,
			meta: {
				title: `${app_name} - Forgot Password`,
				display_name: 'Forgot Password'
			}
		},
		{
			path: '/reset-password/:hash?',
			name: 'ResetPassword',
			component: ResetPassword,
			meta: {
				title: `${app_name} - Reset Password`,
				display_name: 'Reset Password'
			}
		},
		{
			path: '/signup',
			name: 'Signup',
			component: Signup,
			meta: {
				title: `${app_name} - Signup`,
				display_name: 'Signup'
			}
		},
		{
			path: '/finished',
			name: 'Finished',
			component: Finished,
			meta: {
				title: `${app_name} - Finished`,
				display_name: 'Finished'
			}
		},

		/* User endpoints */
		{
			path: '/confirm-account',
			name: 'ConfirmAccount',
			component: ConfirmAccount,
			meta: {
				title: `${app_name} - Confirm Account`,
				display_name: 'Confirm Account',
				protected: true
			}
		},
		{
			path: '/u/account',
			redirect: to => {
				return {
					path: '/u/account/detail'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/u/account/:category?',
			name: 'UserAccount',
			component: UserAccount,
			meta: {
				title: `${app_name} - Account Settings`,
				display_name: 'Account Settings',
				protected: true
			}
		},

		/* Admin endpoints */
		{
			path: '/a/dashboard',
			redirect: to => {
				return {
					path: '/a/firms'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/a/firms',
			name: 'AdminFirms',
			component: AdminFirms,
			meta: {
				title: `${app_name} - Firms`,
				display_name: 'Firms',
				protected: true
			}
		},
		{
			path: '/a/firm',
			redirect: to => {
				return {
					path: '/a/firms'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/a/firm/:firm_guid/:category?',
			name: 'AdminFirm',
			component: AdminFirm,
			meta: {
				title: `${app_name} - Firm`,
				display_name: 'Firm',
				protected: true
			}
		},
		{
			path: '/a/account',
			redirect: to => {
				return {
					path: '/a/account/detail'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/a/account/:category?',
			name: 'AdminAccount',
			component: AdminAccount,
			meta: {
				title: `${app_name} - Account Settings`,
				display_name: 'Account Settings',
				protected: true
			}
		}
	]
})

router.beforeEach(async (to, from) => {
	let fetch_bearer_token = cookie.get('bearer_token');

	if (
		to.meta.protected && (
			!fetch_bearer_token || 
			fetch_bearer_token == 'undefined'
		)
	) {
		console.log('No Bearer token - Route to login - 2');
		return { name: 'Login' }
	}
})

const app = createApp(App)
app.use(router)
app.use(VueMobileDetection)
app.use(VueApexCharts)

let cookie_config = {
	path: '/'
}

if (window.location.protocol == 'https:') {
	cookie_config = {
		path: '/',
		secure: true,
		sameSite: 'Strict'
	}
}

app.use(cookie, cookie_config)
app.mount('#app')
