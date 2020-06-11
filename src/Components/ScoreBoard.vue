<template>
	<div class="element content-element">
		<h2>{{ text.scoreboard }}</h2>

		<button class="printTalentListButton" @click="printTalentList">
			{{ text.printTalentList }}
		</button>

		<div ref="scoreboard" class="scoreboard">
			<div v-for="talent in shownOrderedTalentList" :key="talent.id" class="score">
				<h3>{{ talent.data.title }} ({{ scoreText(talentScores[talent.id]) }})</h3>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<div v-html="talent.data.text" />
			</div>

			<div v-if="showMoreTalents && moreOrderedTalentList.length > 0">
				<button class="show-more-talents-toggle" @click="showMoreTalentsToggle">
					{{ text.showMoreTalents }}
				</button>

				<div ref="moreTalents" style="display: none;">
					<div v-for="talent in moreOrderedTalentList" :key="talent.id" class="score">
						<h3>{{ talent.data.title }} ({{ scoreText(talentScores[talent.id]) }})</h3>
						<!-- eslint-disable-next-line vue/no-v-html -->
						<div v-html="talent.data.text" />
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import $ from 'jquery'

export default {
	name: 'ScoreBoard',
	props: {
		resultList: {
			type: Object,
			required: true,
			default: function() { return [] },
		},
		elementList: {
			type: Array,
			required: true,
			default: function() { return [] },
		},
		answerList: {
			type: Array,
			required: true,
			default: function() { return [] },
		},
		talentList: {
			type: Array,
			required: true,
			default: function() { return [] },
		},
		showMoreTalents: {
			type: String,
			required: false,
			default: function() { return 'true' },
		},
		shownTalentCount: {
			type: Number,
			required: false,
			default: function() { return 5 },
		},
		text: {
			type: Object,
			required: true,
			default: function() { return {} },
		},
	},
	data: function() {
		return {
			moreTalentsShown: this.showMoreTalents === 'false',
			orderedTalentList: [],
			talentScores: {},
			elementByIdList: {},
		}
	},
	computed: {
		shownOrderedTalentList: function() {
			const self = this
			let count = 0
			const outputList = []

			$.each(self.orderedTalentList, function(i, talentList) {
				if (talentList === 'undefined' || talentList == null) return

				$.each(talentList, function(j, talent) {
					outputList.push(talent)
				})

				count += talentList.length
				if (count >= self.shownTalentCount) return false
			})

			return outputList
		},
		moreOrderedTalentList: function() {
			const self = this
			let count = 0
			const outputList = []

			$.each(self.orderedTalentList, function(i, talentList) {
				if (talentList === 'undefined' || talentList == null) return

				if (count < self.shownTalentCount) {
					count += talentList.length
					return
				}

				$.each(talentList, function(j, talent) {
					outputList.push(talent)
				})
			})

			return outputList
		},
	},
	beforeMount: function() {
		this.calcScores()
	},
	methods: {
		printTalentList() {
			// collect elements
			const scoreboard = $(this.$refs.scoreboard)
			let displayElements = scoreboard.parents().toArray()
			displayElements = displayElements.concat(scoreboard.find('*:not(button)').toArray())
			displayElements.push(scoreboard[0])

			// apply print displaying
			$.each(displayElements, function(i, element) {
				element.classList.add('gifttest-print')
			})
			const style = $($.parseHTML('<style type="text/css" media="print">body *:not(.gifttest-print) { display: none !important; } @page { margin: 0; }</style>'))
			$('body').append(style)

			// the actual printing
			window.print()

			// reset
			$.each(displayElements, function(i, element) {
				element.classList.add('gifttest-print')
			})
			style.remove()
		},
		calcScores() {
			const self = this

			$.each(self.talentList, function(i, talent) {
				self.talentScores[talent.id] = 0
				$.each(talent.data.questionList, function(j, questionId) {
					self.talentScores[talent.id] += self.resultList[questionId]
				})
			})

			self.orderTalents()
		},
		orderTalents() {
			const self = this
			self.orderedTalentList = []

			// order talents
			$.each(self.talentList, function(i, talent) {
				if (!(self.talentScores[talent.id] in self.orderedTalentList)) {
					self.orderedTalentList[self.talentScores[talent.id]] = []
				}
				self.orderedTalentList[self.talentScores[talent.id]].push(talent)
			})

			self.orderedTalentList.reverse()
		},
		showMoreTalentsToggle() {
			$(this.$refs.moreTalents).toggle()
			this.moreTalentsShown = !this.moreTalentsShown
		},
		scoreText(score) {
			if (score === 1) return score + ' ' + this.text.pointSingular
			else return score + ' ' + this.text.pointsPlural
		},
	},
}
</script>
