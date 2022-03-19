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
/* global jQuery */

export default {
	name: 'ScoreBoard',
	props: {
		resultList: {
			type: Object,
			required: true,
			default() { return {} },
		},
		elementList: {
			type: Array,
			required: true,
			default() { return [] },
		},
		answerList: {
			type: Array,
			required: true,
			default() { return [] },
		},
		giftList: {
			type: Array,
			required: true,
			default() { return [] },
		},
		showMoreGifts: {
			type: String,
			required: false,
			default() { return 'true' },
		},
		shownGiftCount: {
			type: Number,
			required: false,
			default() { return 5 },
		},
		text: {
			type: Object,
			required: true,
			default() { return {} },
		},
	},
	data() {
		return {
			orderedGiftList: [],
			giftScores: {},
			elementByIdList: {},
		}
	},
	computed: {
		shownOrderedGiftList() {
			const self = this
			let count = 0
			const outputList = []

			jQuery.each(self.orderedGiftList, function(i, giftList) {
				if (giftList === 'undefined' || giftList == null) return

				jQuery.each(giftList, function(j, gift) {
					outputList.push(gift)
				})

				count += giftList.length
				if (count >= self.shownGiftCount) return false
			})

			return outputList
		},
		moreOrderedGiftList() {
			const self = this
			let count = 0
			const outputList = []

			jQuery.each(self.orderedGiftList, function(i, giftList) {
				if (giftList === 'undefined' || giftList == null) return

				if (count < self.shownGiftCount) {
					count += giftList.length
					return
				}

				jQuery.each(giftList, function(j, gift) {
					outputList.push(gift)
				})
			})

			return outputList
		},
	},
	beforeMount() {
		this.calcScores()
	},
	methods: {
		printGiftList() {
			// collect elements
			const scoreboard = jQuery(this.$refs.scoreboard)
			let displayElements = scoreboard.parents().toArray()
			displayElements = displayElements.concat(scoreboard.find('*:not(button)').toArray())
			displayElements.push(scoreboard[0])

			// apply print displaying
			jQuery.each(displayElements, function(i, element) {
				element.classList.add('gifttest-print')
			})
			const styleElement = document.createElement('style')
			styleElement.media = 'print'
			styleElement.textContent = 'body *:not(.gifttest-print) { display: none !important; } @page { margin: 0; }'
			const style = jQuery(styleElement)
			jQuery('body').append(style)

			// the actual printing
			window.print()

			// reset
			jQuery.each(displayElements, function(i, element) {
				element.classList.add('gifttest-print')
			})
			style.remove()
		},
		calcScores() {
			const self = this

			jQuery.each(self.giftList, function(i, gift) {
				self.giftScores[gift.id] = 0
				jQuery.each(gift.data.question_list, function(j, questionId) {
					self.giftScores[gift.id] += self.resultList[questionId]
				})
			})

			self.orderGifts()
		},
		orderGifts() {
			const self = this
			self.orderedGiftList = []

			// order gifts
			jQuery.each(self.giftList, function(i, gift) {
				if (!(self.giftScores[gift.id] in self.orderedGiftList)) {
					self.orderedGiftList[self.giftScores[gift.id]] = []
				}
				self.orderedGiftList[self.giftScores[gift.id]].push(gift)
			})

			self.orderedGiftList.reverse()
		},
		showMoreGiftsToggle() {
			jQuery(this.$refs.moreGifts).toggle()
		},
		scoreText(score) {
			if (score === 1) return score + ' ' + this.text.point_singular
			else return score + ' ' + this.text.points_plural
		},
	},
}
</script>
