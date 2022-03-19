<template>
	<div class="gifttest">
		<div v-if="loading">
			<img :src="pluginDirUrl + 'image/loading.svg'" class="loading">
		</div>

		<div v-else-if="scoreboardShown">
			<ScoreBoard :result-list="resultList"
				:element-list="elementList"
				:answer-list="answerList"
				:gift-list="giftList"
				:show-more-gifts="settings.show_more_gifts"
				:shown-gift-count="settings.shown_gift_count"
				:text="text" />
		</div>

		<div v-else class="questionaire-wrapper">
			<div class="progressbar-wrapper">
				<div class="bar">
					<div class="progress" :style="'width: ' + questionaireProgressAccurate + '%'" />
					<div class="progress-number">
						{{ questionaireProgress }}%
					</div>
				</div>
			</div>

			<CustomQuestionElement v-if="currentElement.type === elementTypes.customQuestion"
				:key="currentElement.id"
				v-model="currentElement"
				:result="currentElementResult"
				:text="text"
				:prev-element-exists="prevElementExists"
				@next="nextElement"
				@prev="prevElement" />

			<QuestionElement v-else-if="currentElement.type === elementTypes.question"
				:key="currentElement.id"
				v-model="currentElement"
				:answer-list="answerList"
				:result="currentElementResult"
				:text="text"
				:prev-element-exists="prevElementExists"
				@next="nextElement"
				@prev="prevElement" />

			<ContentElement v-else-if="currentElement.type === elementTypes.content"
				:key="currentElement.id"
				v-model="currentElement"
				:text="text"
				:prev-element-exists="prevElementExists"
				@next="nextElement"
				@prev="prevElement" />
		</div>
	</div>
</template>

<script>
/* global jQuery */
/* global gifttest */
// __webpack_public_path__ = gifttest.vue_components_path // eslint-disable-line

const ScoreBoard = () => import(/* webpackChunkName: "ScoreBoard" */'./Components/ScoreBoard.vue')
const ContentElement = () => import(/* webpackChunkName: "ContentElement" *//* webpackPrefetch: true */'./Components/Elements/ContentElement.vue')
const QuestionElement = () => import(/* webpackChunkName: "QuestionElement" *//* webpackPrefetch: true */'./Components/Elements/QuestionElement.vue')
const CustomQuestionElement = () => import(/* webpackChunkName: "CustomQuestionElement" *//* webpackPrefetch: true */'./Components/Elements/CustomQuestionElement.vue')

export default {
	name: 'Questionaire',
	components: {
		ContentElement,
		QuestionElement,
		CustomQuestionElement,
		ScoreBoard,
	},
	data() {
		return {
			loading: true,
			scoreboardShown: false,
			id: gifttest['test-id'],
			settings: {},
			answerList: [],
			elementList: [],
			giftList: [],
			currentElementIndex: 0,
			resultList: {},
			elementTypes: {
				invalid: -1,
				question: 0,
				customQuestion: 2,
				content: 3,
			},
			text: gifttest.text,
		}
	},
	computed: {
		currentElement() {
			if (typeof this.elementList[this.currentElementIndex] === 'undefined') return { type: -1 }
			else return this.elementList[this.currentElementIndex]
		},
		currentElementResult() {
			return this.resultList[this.currentElement.id]
		},
		questionaireProgress() {
			return Math.floor(this.questionaireProgressAccurate)
		},
		questionaireProgressAccurate() {
			return (this.currentElementIndex) / this.elementList.length * 100
		},
		prevElementExists() {
			if (typeof this.elementList[this.currentElementIndex - 1] === 'undefined') return false
			else return true
		},
	},
	beforeMount() {
		this.pluginDirUrl = gifttest.plugin_dir_url
		this.loadQuestionaire()
		// load components needed right away
		ContentElement()
		QuestionElement()
		CustomQuestionElement()
	},
	methods: {
		nextElement(result) {
			// store result if given
			if (typeof result !== 'undefined') {
				this.resultList[this.currentElement.id] = result
			}

			// proceed to next element
			this.currentElementIndex++
			if (this.currentElement.type === this.elementTypes.invalid) {
				// show the scoreboard at the end
				this.currentElementIndex--
				this.scoreboardShown = true
			}
		},
		prevElement() {
			if (this.prevElementExists) this.currentElementIndex--
		},
		loadQuestionaire() {
			const self = this
			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.get_details,
				action: 'gifttest_get_questionaire_details',
				id: self.id,
			}

			// do request
			jQuery.post(gifttest.ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.settings = response.data.settings
					self.answerList = response.data.answer_list
					self.elementList = response.data.element_list
					self.giftList = response.data.gift_list

					jQuery(document.body).trigger('post-load')

					// load additional components needed later
					ScoreBoard()
				}
			}, 'json').fail(function() {
				console.debug('Loading Questionaire failed')
			}).always(function() {
				self.loading = false
			})
		},
	},
}
</script>
