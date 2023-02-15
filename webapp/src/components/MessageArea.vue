<script>

export default {
	data() {
		return {
			inner_width: window.innerWidth,
			notification: {}
		}
	},

	created() {
	},

	watch: {
		'$root.notifications' (data) {
			if (data.length > 0) {
				this.notification = data[0];
			}
		}
	}
};

</script>

<template>
	<div 
		v-if="
			(
				this.$root.verified &&
				this.$root.password
			)
		"
		class="message-area"
		:class="!notification.type ? 'message-area-info' : ''"
	>
		<div 
			class="message-area"
			:class="'message-area-' + this.notification.type"
		>
			<div class="message-area-left">
				<i v-if="notification.type == 'info'" class="fa fa-info-circle text-blue fs20"></i>
				<i v-if="notification.type == 'warning'" class="fa fa-exclamation-triangle text-orange fs20"></i>
				<i v-if="notification.type == 'question'" class="fa fa-question-circle text-green fs20"></i>
				<i v-if="notification.type == 'error'" class="fa fa-times-circle text-red fs20"></i>

				<span class="bold fs17 ml5">
					{{ notification.title }}
				</span>

				<p class="pointer">
					{{ this.$root.formatString(notification.message, parseInt(inner_width / 100 * 15)) }}
				</p>
			</div>
			<div class="message-area-right">
				<button v-if="notification.dismissable" class="btn btn-neutral btn-sm fs12">
					Dismiss
				</button>
			</div>
		</div>
	</div>
</template>

<style scoped>

.message-area {
	width: 100%;
	height: var(--message-area-height);
	display: flex;
	flex-direction: row;
	position: relative;
}

.message-area-info {
	background: var(--color-primary-transparent);
}

.message-area-warning {
	background: var(--color-orange-transparent)
}

.message-area-error {
	background: var(--color-red-transparent);
}

.message-area-question {
	background: var(--color-green-transparent);
}

.message-area-left {
	padding-top: 10px;
	padding-bottom: 10px;
	padding-left: 25px;
	padding-right: 25px;
	width: calc(100% - 120px);
	height: 100%;
	white-space: pre-line;
	overflow: hidden;
}

.message-area-right {
	width: 120px;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
}

</style>