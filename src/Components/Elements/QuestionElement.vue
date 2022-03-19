<template>
	<div class="element content-element">
		<div class="content-wrapper">
			<h4 class="question">
				{{ element.data.text }}
			</h4>

			<div class="answer-wrapper">
				<span v-for="answer in answerList" :key="answer.id" class="answer">
					<label :for="'answer_' + answer.id" class="answer-inner">
						<input :id="'answer_' + answer.id"
							v-model="resultAnswerId"
							type="radio"
							:value="answer.id"
							@click="answerSelected(answer)">
						<span class="custom-checkbox" />
						<p>{{ answer.data.content }}</p>
					</label>
				</span>
			</div>
		</div>

		<button v-if="prevElementExists" class="button button-secondary" @click="prevElement">
			{{ text.back }}
		</button>
	</div>
</template>

<script>
export default {
	name: 'QuestionElement',
	props: {
		value: {
			type: Object,
			required: true,
			default() { return {} },
		},
		answerList: {
			type: Array,
			required: true,
			default() { return [] },
		},
		result: {
			type: Number,
			required: false,
			default() { return -1 },
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
			resultAnswerId: this.result,
			latestRequestId: '',
		}
	},
	methods: {
		prevElement() {
			this.$emit('prev')
		},
		answerSelected(answer) {
			const self = this
			const requestId = (Math.random() + 1).toString(36).substring(2)
			self.latestRequestId = requestId

			setTimeout(function() {
				// only omit if the user didn't change his mind
				if (self.latestRequestId === requestId) self.$emit('next', answer.id)
			}, 500)
		},
	},
}
</script>
