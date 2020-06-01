<template>
	<div class="gifttest">
		<div v-if="loading">
			<img :src="pluginDirUrl + 'image/loading.svg'" class="loading">
		</div>

		<div v-else-if="scoreboardShown">
			<ScoreBoard
				:result-list="resultList"
				:element-list="elementList"
				:answer-list="answerList"
				:talent-list="talentList"
				:show-more-talents="settings.showMoreTalents"
				:shown-talent-count="settings.shownTalentCount"
				:text="text" />
		</div>

		<div v-else class="questionaireWrapper">
			<div class="progressbarWrapper">
				<div class="bar">
					<div class="progress" :style="'width: ' + questionaireProgressAccurate + '%'" />
					<div class="progress-number">
						{{ questionaireProgress }}%
					</div>
				</div>
			</div>

			<CustomQuestionElement
				v-if="currentElement.type === elementTypes.customQuestion"
				:key="currentElement.id"
				v-model="currentElement"
				:result="currentElementResult"
				:text="text"
				:prev-element-exists="prevElementExists"
				@next="nextElement"
				@prev="prevElement" />

			<QuestionElement
				v-else-if="currentElement.type === elementTypes.question"
				:key="currentElement.id"
				v-model="currentElement"
				:answer-list="answerList"
				:result="currentElementResult"
				:text="text"
				:prev-element-exists="prevElementExists"
				@next="nextElement"
				@prev="prevElement" />

			<ContentElement
				v-else-if="currentElement.type === elementTypes.content"
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
import $ from 'jquery'
import ContentElement from './Components/Elements/ContentElement.vue'
import QuestionElement from './Components/Elements/QuestionElement.vue'
import CustomQuestionElement from './Components/Elements/CustomQuestionElement.vue'
import ScoreBoard from './Components/ScoreBoard.vue'
/* global gifttest */

export default {
	name: 'Questionaire',
	components: {
		ContentElement,
		QuestionElement,
		CustomQuestionElement,
		ScoreBoard,
	},
	data: function() {
		return {
			loading: true,
			scoreboardShown: false,
			id: gifttest['test-id'],
			settings: {},
			answerList: [],
			elementList: [],
			talentList: [],
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
		currentElement: function() {
			if (typeof this.elementList[this.currentElementIndex] === 'undefined') return { type: -1 }
			else return this.elementList[this.currentElementIndex]
		},
		currentElementResult: function() {
			return this.resultList[this.currentElement.id]
		},
		questionaireProgress: function() {
			return Math.floor(this.questionaireProgressAccurate)
		},
		questionaireProgressAccurate: function() {
			return (this.currentElementIndex) / this.elementList.length * 100
		},
		prevElementExists: function() {
			if (typeof this.elementList[this.currentElementIndex - 1] === 'undefined') return false
			else return true
		},
	},
	beforeMount: function() {
		this.pluginDirUrl = gifttest.plugin_dir_url
		this.loadQuestionaire()
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
			$.post(gifttest.ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.settings = response.data.settings
					self.answerList = response.data.answerList
					self.elementList = response.data.elementList
					self.talentList = response.data.talentList

					$(document.body).trigger('post-load')
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
