<template>
	<div class="talents elements">
		<h3 class="toggle-contents" @click="toggleContents">
			{{ text.talents }}
		</h3>

		<div
			ref="contentsCounterpart"
			class="contentsCounterpart"
			@click="toggleContents">
			<i class="icon icon-edit" />
			{{ text.editTalents }}
		</div>

		<div ref="contents" class="contents hidden">
			<draggable
				v-model="talentList"
				handle=".drag-handle"
				draggable=".talent"
				@input="emitTalentListUpdate">
				<div
					v-for="(talent, i) in talentList"
					:key="i"
					class="talent element">
					<table>
						<thead @click="toggleBody(talent.id)">
							<tr>
								<th colspan="2">
									<i class="icon icon-align-justify drag-handle" />
									<p class="open-panel">
										<span v-if="!talent.data.title">
											[{{ text.talent }}]
										</span>
										{{ talent.data.title }}
									</p>
								</th>
							</tr>
						</thead>
						<tbody :ref="'body_' + talent.id" class="table-body-hidden">
							<tr>
								<td>
									<label :for="'talentTitle_' + talent.id">
										{{ text.talentTitle }}
									</label>
								</td>
								<td>
									<input
										:id="'talentTitle_' + talent.id"
										v-model="talent.data.title"
										type="text"
										:placeholder="text.talentTitle"
										@input="emitTalentListUpdate">
								</td>
							</tr>
							<tr>
								<td>
									<label :for="'talentText_' + talent.id">
										{{ text.talentText }}
									</label>
								</td>
								<td>
									<editor
										:id="'talentText_' + talent.id"
										v-model="talent.data.text"
										:placeholder="text.talentText"
										:init="{
											height: 200,
											menu: {},
											toolbar: [
												'undo redo | fontselect fontsizeselect formatselect | forecolor backcolor | removeformat | fullscreen',
												'casechange | bold italic underline strikethrough | bullist numlist outdent indent | blockquote hr link unlink',
											],
										}"
										@input="emitTalentListUpdate" />
								</td>
							</tr>
							<tr>
								<td>
									<label>
										{{ text.questionList }}
									</label>
								</td>
								<td>
									<span
										v-for="questionId in talent.data.questionList"
										:key="questionId"
										class="talent-question">
										[{{ questionIndex(questionId) + 1 }}] {{ questionTextById(questionId) }}
										<i class="icon icon-trash action" @click="deleteTalentQuestion(talent, questionId)" />
									</span>

									<br>

									<select :ref="'talent_' + talent.id + '_questionSelection'">
										<option
											v-for="(question, j) in availableQuestionList"
											:key="j"
											:value="question.id">
											[{{ j + 1 }}] {{ questionText(question) }}
										</option>
									</select>
									<button class="button button-primary" @click="addTalentQuestion(talent)">
										{{ text.addQuestion }}
									</button>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<button class="button button-danger" @click="deleteTalent(talent.id)">
										{{ text.delete }}
									</button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div slot="footer">
					<button
						:class="{ loading: addingTalentInProgress }"
						:disabled="addingTalentInProgress"
						class="button button-primary"
						@click="addTalent">
						{{ text.addTalent }}
					</button>
				</div>
			</draggable>
		</div>
	</div>
</template>

<script>
import Vue from 'vue'
import $ from 'jquery'
import Editor from '@tinymce/tinymce-vue'
import draggable from 'vuedraggable'
import utilities from '../Utilities.js'
const displayMessage = utilities.displayMessage
/* global ajaxurl */
/* global gifttest */

export default {
	name: 'TalentSettings',
	components: {
		draggable,
		editor: Editor,
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
			talentList: this.value,
			addingTalentInProgress: false,
		}
	},
	watch: {
		value: function(newValue, oldValue) {
			this.talentList = newValue
		},
	},
	methods: {
		displayMessage,
		questionIndex(questionId) {
			let questionIndex = false
			$.each(this.availableQuestionList, function(i, tmpQuestion) {
				if (tmpQuestion.id.toString() === questionId) {
					questionIndex = i
					return false
				}
			})
			return questionIndex
		},
		getQuestionById(questionId) {
			let question = false
			$.each(this.availableQuestionList, function(i, tmpQuestion) {
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
			else if (typeof question.data.questionText !== 'undefined') return this.excerpt(question.data.questionText)
			else return ''
		},
		excerpt(text, length = 20) {
			text = $('<div/>').html(text).text()
			if (text.length < 1) return ''
			let excerpt = text.substring(0, length)
			if (text.length > length) excerpt += ' ...'
			return excerpt
		},
		deleteTalentQuestion(talent, questionId) {
			const index = talent.data.questionList.indexOf(questionId)
			if (index > -1) talent.data.questionList.splice(index, 1)
		},
		deleteTalent(talentId) {
			const self = this

			$.each(self.talentList, function(i, talent) {
				if (talent.id === talentId) {
					Vue.delete(self.talentList, i)
					return false
				}
			})
		},
		highestTalentId() {
			let highestId = 0
			$.each(this.talentList, function(i, talent) {
				if (talent.id > highestId) highestId = talent.id
			})
			return highestId
		},
		addTalentQuestion(talent, question) {
			const questionId = $(this.$refs['talent_' + talent.id + '_questionSelection']).val()
			if (!talent.data.questionList.includes(questionId)) talent.data.questionList.push(questionId)
		},
		addTalent() {
			const self = this

			// only one request allowed at a time
			if (self.addingTalentInProgress) return
			else self.addingTalentInProgress = true

			/* create new talent */
			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.create_talent,
				action: 'gifttest_create_questionaire_talent',
				id: self.highestTalentId() + 1,
				questionaireId: self.questionaireId,
			}

			// do request
			$.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.talentList.push(response.data)
				} else {
					self.displayMessage(response.message, response.status)
				}
				// loading done
				self.addingTalentInProgress = false
			}, 'json')
		},
		toggleContents() {
			$(this.$refs.contents).toggle()
			$(this.$refs.contentsCounterpart).toggle()
		},
		toggleBody(talentId) {
			if (typeof talentId === 'undefined' || talentId === null) return
			const body = $(this.$refs['body_' + talentId])
			if (body.hasClass('table-body-hidden')) body.removeClass('table-body-hidden')
			else body.addClass('table-body-hidden')
		},
		emitTalentListUpdate() {
			this.$emit('input', this.talentList)
		},
	},
}
</script>
