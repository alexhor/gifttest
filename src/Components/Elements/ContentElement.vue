<template>
	<div class="element content-element">
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div class="content-wrapper" v-html="element.data.text" />

		<button v-if="prevElementExists" class="button button-secondary" @click="prevElement">
			{{ text.back }}
		</button>

		<button class="button button-primary" @click="nextElement">
			{{ text.continue }}
		</button>
	</div>
</template>

<script>
export default {
	name: 'ContentElement',
	props: {
		value: {
			type: Object,
			required: true,
			default() { return {} },
		},
		prevElementExists: {
			type: Boolean,
			required: false,
			default() { return true },
		},
		text: {
			type: Object,
			required: true,
			default() { return {} },
		},
	},
	data() {
		return {
			element: this.value,
		}
	},
	watch: {
		value(newValue, oldValue) {
			this.element = newValue
		},
	},
	methods: {
		prevElement() {
			this.$emit('prev')
		},
		nextElement() {
			this.$emit('next')
		},
	},
}
</script>
