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
import Logout from '@/components/Login/Logout.vue';
import ForgotPassword from '@/components/Login/ForgotPassword.vue';
import ResetPassword from '@/components/Login/ResetPassword.vue';
import ConfirmAccount from '@/components/ConfirmAccount/ConfirmAccount.vue';
import Finished from '@/components/Finished/Finished.vue';
import Terms from '@/components/Terms/Terms.vue';
import PrivacyPolicy from '@/components/PrivacyPolicy/PrivacyPolicy.vue';
import NotFound from '@/components/NotFound/NotFound.vue';

/* admin routes */
import AdminAccount from '@/components/Admin/Account/Account.vue';
import AdminFirms from '@/components/Admin/Firms/Firms.vue';
import AdminFirm from '@/components/Admin/Firm/Firm.vue';

/* firm routes */
import FirmAcceptInvitation from '@/components/Firm/AcceptInvitation/AcceptInvitation.vue';
import FirmReports from '@/components/Firm/Reports/Reports.vue';
import FirmCompanies from '@/components/Firm/Companies/Companies.vue';
import FirmCompany from '@/components/Firm/Company/Company.vue';
import FirmAccount from '@/components/Firm/Account/Account.vue';

/* company routes */
import CompanyAcceptInvitation from '@/components/Company/AcceptInvitation/AcceptInvitation.vue';
import CompanyCompleteInvite from '@/components/Company/CompleteInvite/CompleteInvite.vue';
import CompanyReports from '@/components/Company/Reports/Reports.vue';
import CompanyAccount from '@/components/Company/Account/Account.vue';

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
		// {
		// 	path: '/',
		// 	redirect: to => {
		// 		return {
		// 			name: 'Login'
		// 		}
		// 	},
		// },
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
			path: '/logout',
			name: 'Logout',
			component: Logout,
			meta: {
				title: `${app_name} - Logout`,
				display_name: 'Logout'
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
			path: '/finished',
			name: 'Finished',
			component: Finished,
			meta: {
				title: `${app_name} - Finished`,
				display_name: 'Finished'
			}
		},
		{
			path: '/f/',
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
			path: '/c/',
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
		},

		/* Firm endpoints */
		{
			path: '/f/accept-invitation/:hash?',
			name: 'FirmAcceptInvitation',
			component: FirmAcceptInvitation,
			meta: {
				title: `${app_name} - Accept Invitation`,
				display_name: 'Accept Invitation'
			}
		},
		{
			path: '/f/dashboard',
			redirect: to => {
				return {
					path: '/f/reports'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/f/reports',
			name: 'FirmReports',
			component: FirmReports,
			meta: {
				title: `${app_name} - Reports`,
				display_name: 'Reports',
				protected: true
			}
		},
		{
			path: '/f/companies',
			name: 'FirmCompanies',
			component: FirmCompanies,
			meta: {
				title: `${app_name} - Companies`,
				display_name: 'Companies',
				protected: true
			}
		},
		{
			path: '/f/company',
			redirect: to => {
				return {
					path: '/f/companies'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/f/company/:company_guid/:category?',
			name: 'FirmCompany',
			component: FirmCompany,
			meta: {
				title: `${app_name} - Firm`,
				display_name: 'Firm',
				protected: true
			}
		},
		{
			path: '/f/account',
			redirect: to => {
				return {
					path: '/f/account/detail'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/f/account/:category?',
			name: 'FirmAccount',
			component: FirmAccount,
			meta: {
				title: `${app_name} - Account Settings`,
				display_name: 'Account Settings',
				protected: true
			}
		},

		/* Company endpoints */
		{
			path: '/c/accept-invitation/:hash?',
			name: 'CompanyAcceptInvitation',
			component: CompanyAcceptInvitation,
			meta: {
				title: `${app_name} - Accept Invitation`,
				display_name: 'Accept Invitation'
			}
		},
		{
			path: '/c/complete-invite',
			name: 'CompanyCompleteInvite',
			component: CompanyCompleteInvite,
			meta: {
				title: `${app_name} - Invitation Complete`,
				display_name: 'Invitation Complete'
			}
		},
		{
			path: '/c/dashboard',
			redirect: to => {
				return {
					path: '/c/reports'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/c/reports',
			name: 'CompanyReports',
			component: CompanyReports,
			meta: {
				title: `${app_name} - Reports`,
				display_name: 'Reports',
				protected: true
			}
		},
		{
			path: '/c/account',
			redirect: to => {
				return {
					path: '/c/account/detail'
				}
			},
			meta: {
				protected: true
			}
		},
		{
			path: '/c/account/:category?',
			name: 'CompanyAccount',
			component: CompanyAccount,
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
