<template>
	<div class="element content-element">
		<div class="content-wrapper">
			<h4 class="question">
				{{ element.data.questionText }}
			</h4>

			<div class="answerWrapper">
				<span v-for="answer in answersList" :key="answer.id" class="answer">
					<label :for="'answer_' + answer.id" class="answerInner">
						<input
							:id="'answer_' + answer.id"
							v-model="resultAnswerId"
							type="radio"
							:value="answer.id"
							@click="answerSelected(answer)">
						<span class="customCheckbox" />
						<p>{{ answer.text }}</p>
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
	name: 'CustomQuestionElement',
	props: {
		value: {
			type: Object,
			required: true,
			default: function() { return {} },
		},
		result: {
			type: Number,
			required: false,
			default: function() { return -1 },
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
			answersList: this.value.data.answersList,
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
