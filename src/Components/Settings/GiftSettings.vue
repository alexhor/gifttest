<template>
	<div class="gifts elements">
		<h3 class="toggle-contents" @click="toggleContents">
			{{ text.gifts }}
		</h3>

		<div
			ref="contentsCounterpart"
			class="contentsCounterpart"
			@click="toggleContents">
			<i class="icon icon-edit" />
			{{ text.edit_gifts }}
		</div>

		<div ref="contents" class="contents hidden">
			<draggable
				v-model="giftList"
				handle=".drag-handle"
				draggable=".gift"
				@input="emitGiftListUpdate">
				<div
					v-for="(gift, i) in giftList"
					:key="i"
					class="gift element">
					<table>
						<thead @click="toggleBody(gift.id)">
							<tr>
								<th colspan="2">
									<i class="icon icon-align-justify drag-handle" />
									<p class="open-panel">
										<span v-if="!gift.data.title">
											[{{ text.gift }}]
										</span>
										{{ gift.data.title }}
									</p>
								</th>
							</tr>
						</thead>
						<tbody :ref="'body_' + gift.id" class="table-body-hidden">
							<tr>
								<td>
									<label :for="'giftTitle_' + gift.id">
										{{ text.gift_title }}
									</label>
								</td>
								<td>
									<input
										:id="'giftTitle_' + gift.id"
										v-model="gift.data.title"
										type="text"
										:placeholder="text.gift_title"
										@input="emitGiftListUpdate">
								</td>
							</tr>
							<tr>
								<td>
									<label :for="'giftText_' + gift.id">
										{{ text.gift_text }}
									</label>
								</td>
								<td>
									<editor
										:id="'giftText_' + gift.id"
										v-model="gift.data.text"
										:placeholder="text.gift_text"
										:init="{
											height: 200,
											menu: {},
											toolbar: [
												'undo redo | fontselect fontsizeselect formatselect | forecolor backcolor | removeformat | fullscreen',
												'casechange | bold italic underline strikethrough | bullist numlist outdent indent | blockquote hr link unlink',
											],
										}"
										@input="emitGiftListUpdate" />
								</td>
							</tr>
							<tr>
								<td>
									<label>
										{{ text.question_list }}
									</label>
								</td>
								<td>
									<span
										v-for="questionId in gift.data.question_list"
										:key="questionId"
										class="gift-question">
										[{{ questionIndex(questionId) + 1 }}] {{ questionTextById(questionId) }}
										<i class="icon icon-trash action" @click="deleteGiftQuestion(gift, questionId)" />
									</span>

									<br>

									<select :ref="'gift_' + gift.id + '_questionSelection'">
										<option
											v-for="(question, j) in availableQuestionList"
											:key="j"
											:value="question.id">
											[{{ j + 1 }}] {{ questionText(question) }}
										</option>
									</select>
									<button class="button button-primary" @click="addGiftQuestion(gift)">
										{{ text.add_question }}
									</button>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<button class="button button-danger" @click="deleteGift(gift.id)">
										{{ text.delete }}
									</button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div slot="footer">
					<button
						:class="{ loading: addingGiftInProgress }"
						:disabled="addingGiftInProgress"
						class="button button-primary"
						@click="addGift">
						{{ text.add_gift }}
					</button>
				</div>
			</draggable>
		</div>
	</div>
</template>

<script>
/* global ajaxurl */
/* global jQuery */
/* global Vue */
/* global gifttest */
__webpack_public_path__ = gifttest.vue_components_path // eslint-disable-line

const Tinymce = () => import(/* webpackChunkName: "Tinymce" *//* webpackPrefetch: true */'@tinymce/tinymce-vue')
const Vuedraggable = () => import(/* webpackChunkName: "Vuedraggable" *//* webpackPrefetch: true */'vuedraggable')
const Utilities = () => import(/* webpackChunkName: "Utilities" */'../Utilities.js')

export default {
	name: 'GiftSettings',
	components: {
		draggable: Vuedraggable,
		editor: Tinymce,
	},
	props: {
		value: {
			type: Array,
			required: true,
			default: function() { return [] },
		},
		availableQuestionList: {
			type: Array,
			required: true,
			default: function() { return [] },
		},
		questionaireId: {
			type: Number,
			required: true,
		},
		text: {
			type: Object,
			required: true,
		},
	},
	data: function() {
		return {
			giftList: this.value,
			addingGiftInProgress: false,
		}
	},
	watch: {
		value: function(newValue, oldValue) {
			this.giftList = newValue
		},
	},
	beforeMount: function() {
		const self = this
		// load requried modules
		Vuedraggable()
		Tinymce()
		// load displayMessage function
		Utilities().then(utilities => {
			self.displayMessage = utilities.default.displayMessage
		})
	},
	methods: {
		/**
		 * This is a dummy function that will be replace asynchronous
		 */
		displayMessage() {},
		questionIndex(questionId) {
			let questionIndex = false
			jQuery.each(this.availableQuestionList, function(i, tmpQuestion) {
				if (tmpQuestion.id.toString() === questionId) {
					questionIndex = i
					return false
				}
			})
			return questionIndex
		},
		getQuestionById(questionId) {
			let question = false
			jQuery.each(this.availableQuestionList, function(i, tmpQuestion) {
				if (tmpQuestion.id.toString() === questionId) {
					question = tmpQuestion
					return false
				}
			})
			return question
		},
		questionTextById(questionId) {
			const question = this.getQuestionById(questionId)
			return this.questionText(question)
		},
		questionText(question) {
			if (question === false || typeof question.data === 'undefined') return ''
			else if (typeof question.data.text !== 'undefined') return this.excerpt(question.data.text)
			else if (typeof question.data.question_text !== 'undefined') return this.excerpt(question.data.question_text)
			else return ''
		},
		excerpt(text, length = 20) {
			text = jQuery('<div/>').html(text).text()
			if (text.length < 1) return ''
			let excerpt = text.substring(0, length)
			if (text.length > length) excerpt += ' ...'
			return excerpt
		},
		deleteGiftQuestion(gift, questionId) {
			const index = gift.data.question_list.indexOf(questionId)
			if (index > -1) gift.data.question_list.splice(index, 1)
		},
		deleteGift(giftId) {
			const self = this

			jQuery.each(self.giftList, function(i, gift) {
				if (gift.id === giftId) {
					Vue.delete(self.giftList, i)
					return false
				}
			})
		},
		highestGiftId() {
			let highestId = 0
			jQuery.each(this.giftList, function(i, gift) {
				if (gift.id > highestId) highestId = gift.id
			})
			return highestId
		},
		addGiftQuestion(gift, question) {
			const questionId = jQuery(this.$refs['gift_' + gift.id + '_questionSelection']).val()
			if (!gift.data.question_list.includes(questionId)) gift.data.question_list.push(questionId)
		},
		addGift() {
			const self = this

			// only one request allowed at a time
			if (self.addingGiftInProgress) return
			else self.addingGiftInProgress = true

			/* create new gift */
			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.create_gift,
				action: 'gifttest_create_questionaire_gift',
				id: self.highestGiftId() + 1,
				questionaire_id: self.questionaireId,
			}

			// do request
			jQuery.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.giftList.push(response.data)
				} else {
					self.displayMessage(response.message, response.status)
				}
				// loading done
				self.addingGiftInProgress = false
			}, 'json')
		},
		toggleContents() {
			jQuery(this.$refs.contents).toggle()
			jQuery(this.$refs.contentsCounterpart).toggle()
		},
		toggleBody(giftId) {
			if (typeof giftId === 'undefined' || giftId === null) return
			const body = jQuery(this.$refs['body_' + giftId])
			if (body.hasClass('table-body-hidden')) body.removeClass('table-body-hidden')
			else body.addClass('table-body-hidden')
		},
		emitGiftListUpdate() {
			this.$emit('input', this.giftList)
		},
	},
}
</script>
