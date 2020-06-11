<template>
	<div class="element content-element">
		<h2>{{ text.scoreboard }}</h2>

		<button class="print-gift-list-button" @click="printGiftList">
			{{ text.print_gift_list }}
		</button>

		<div ref="scoreboard" class="scoreboard">
			<div v-for="gift in shownOrderedGiftList" :key="gift.id" class="score">
				<h3>{{ gift.data.title }} ({{ scoreText(giftScores[gift.id]) }})</h3>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<div v-html="gift.data.text" />
			</div>

			<div v-if="showMoreGifts && moreOrderedGiftList.length > 0">
				<button class="show-more-gifts-toggle" @click="showMoreGiftsToggle">
					{{ text.show_more_gifts }}
				</button>

				<div ref="moreGifts" style="display: none;">
					<div v-for="gift in moreOrderedGiftList" :key="gift.id" class="score">
						<h3>{{ gift.data.title }} ({{ scoreText(giftScores[gift.id]) }})</h3>
						<!-- eslint-disable-next-line vue/no-v-html -->
						<div v-html="gift.data.text" />
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
		giftList: {
			type: Array,
			required: true,
			default: function() { return [] },
		},
		showMoreGifts: {
			type: String,
			required: false,
			default: function() { return 'true' },
		},
		shownGiftCount: {
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
			moreGiftsShown: this.showMoreGifts === 'false',
			orderedGiftList: [],
			giftScores: {},
			elementByIdList: {},
		}
	},
	computed: {
		shownOrderedGiftList: function() {
			const self = this
			let count = 0
			const outputList = []

			$.each(self.orderedGiftList, function(i, giftList) {
				if (giftList === 'undefined' || giftList == null) return

				$.each(giftList, function(j, gift) {
					outputList.push(gift)
				})

				count += giftList.length
				if (count >= self.shownGiftCount) return false
			})

			return outputList
		},
		moreOrderedGiftList: function() {
			const self = this
			let count = 0
			const outputList = []

			$.each(self.orderedGiftList, function(i, giftList) {
				if (giftList === 'undefined' || giftList == null) return

				if (count < self.shownGiftCount) {
					count += giftList.length
					return
				}

				$.each(giftList, function(j, gift) {
					outputList.push(gift)
				})
			})

			return outputList
		},
	},
	beforeMount: function() {
		this.calcScores()
	},
	methods: {
		printGiftList() {
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

			$.each(self.giftList, function(i, gift) {
				self.giftScores[gift.id] = 0
				$.each(gift.data.question_list, function(j, questionId) {
					self.giftScores[gift.id] += self.resultList[questionId]
				})
			})

			self.orderGifts()
		},
		orderGifts() {
			const self = this
			self.orderedGiftList = []

			// order gifts
			$.each(self.giftList, function(i, gift) {
				if (!(self.giftScores[gift.id] in self.orderedGiftList)) {
					self.orderedGiftList[self.giftScores[gift.id]] = []
				}
				self.orderedGiftList[self.giftScores[gift.id]].push(gift)
			})

			self.orderedGiftList.reverse()
		},
		showMoreGiftsToggle() {
			$(this.$refs.moreGifts).toggle()
			this.moreGiftsShown = !this.moreGiftsShown
		},
		scoreText(score) {
			if (score === 1) return score + ' ' + this.text.point_singular
			else return score + ' ' + this.text.points_plural
		},
	},
}
</script>
