<template>
	<div class="element content-element">
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
			default: function() { return {} },
		},
		prevElementExists: {
			type: Boolean,
			required: false,
			default: function() { return true },
		},
		text: {
			type: Object,
			required: true,
			default: function() { return {} },
		},
	},
	data: function() {
		return {
			element: this.value,
		}
	},
	watch: {
		value: function(newValue, oldValue) {
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
