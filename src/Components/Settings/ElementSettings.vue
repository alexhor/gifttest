<template>
	<div class="elements">
		<h3 class="toggle-element-contents" @click="toggleElementContents">
			{{ text.elements }}
		</h3>

		<div
			ref="elementContentsCounterpart"
			class="element-contents-counterpart"
			@click="toggleElementContents">
			<i class="icon icon-edit" />
			{{ text.edit_elements }}
		</div>

		<div ref="elementContents" class="element-contents">
			<draggable
				v-model="elementList"
				handle=".drag-handle"
				draggable=".element"
				@input="emitElementListUpdate">
				<div
					v-for="(element, i) in elementList"
					:key="i"
					class="element">
					<!-- Question Element -->
					<table v-if="element.type === elementTypes.question.id">
						<thead @click="toggleBody(element.id)">
							<tr>
								<th colspan="2">
									<i class="icon icon-align-justify drag-handle" />
									<p class="open-panel">
										{{ text.question }} {{ excerpt(element.data.text) }}
									</p>
								</th>
							</tr>
						</thead>
						<tbody :ref="'body_' + element.id" class="table-body-hidden">
							<tr>
								<td>
									<label :for="'questionText_' + element.id">
										{{ text.text }}
									</label>
								</td>
								<td>
									<textarea
										:id="'questionText_' + element.id"
										v-model="element.data.text"
										:placeholder="text.question"
										@input="emitElementListUpdate" />
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<button class="button button-danger" @click="deleteElement(element.id)">
										{{ text.delete }}
									</button>
								</td>
							</tr>
						</tbody>
					</table>
					<!-- End of Question Element -->

					<!-- Custom Question Element -->
					<table v-else-if="element.type === elementTypes.customQuestion.id">
						<thead @click="toggleBody(element.id)">
							<tr>
								<th colspan="2">
									<i class="icon icon-align-justify drag-handle" />
									<p class="open-panel">
										{{ text.custom_question }} {{ excerpt(element.data.question_text) }}
									</p>
								</th>
							</tr>
						</thead>
						<tbody :ref="'body_' + element.id" class="table-body-hidden">
							<tr>
								<td>
									<label :for="'questionText_' + element.id">
										{{ text.text }}
									</label>
								</td>
								<td>
									<textarea
										:id="'questionText_' + element.id"
										v-model="element.data.question_text"
										:placeholder="text.question"
										@input="emitElementListUpdate" />
								</td>
							</tr>
							<tr>
								<td>
									<label>
										{{ text.answers }}
									</label>
								</td>
								<td>
									<draggable v-model="element.data.answers_list" draggable=".answer" handle=".answer-drag-handle">
										<div v-for="answer in element.data.answers_list" :key="answer.id" class="answer">
											<i class="icon icon-align-justify answer-drag-handle" />
											<label :for="'answerText_' + answer.id">
												{{ text.answer_text }}
											</label>
											<input
												:id="'answerText_' + answer.id"
												v-model="answer.text"
												type="text"
												:placeholder="text.answer_text"
												@input="emitElementListUpdate">

											<label :for="'answerValue_' + answer.id">
												{{ text.answer_value }}
											</label>
											<input
												:id="'answerValue_' + answer.id"
												v-model="answer.value"
												type="number"
												:placeholder="text.answer_value"
												@input="emitElementListUpdate">
											<button class="button button-danger" @click="deleteAnswer(element.id, answer.id)">
												{{ text.delete }}
											</button>
										</div>

										<div slot="footer">
											<button
												:class="{ loading: addingAnswerInProgress }"
												:disabled="addingAnswerInProgress"
												class="button button-primary"
												@click="customQuestionAddAnswer(element.id)">
												{{ text.add_answer }}
											</button>
										</div>
									</draggable>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<button class="button button-danger" @click="deleteElement(element.id)">
										{{ text.delete }}
									</button>
								</td>
							</tr>
						</tbody>
					</table>
					<!-- End of Custom Question Element -->

					<!-- Content Element -->
					<table v-else-if="element.type === elementTypes.content.id">
						<thead @click="toggleBody(element.id)">
							<tr>
								<th colspan="2">
									<i class="icon icon-align-justify drag-handle" />
									<p class="open-panel">
										{{ text.content }} {{ excerpt(element.data.text) }}
									</p>
								</th>
							</tr>
						</thead>
						<tbody :ref="'body_' + element.id" class="table-body-hidden">
							<tr>
								<td>
									<label for="content">
										{{ text.text }}
									</label>
								</td>
								<td>
									<editor
										:id="'content_' + element.id"
										v-model="element.data.text"
										:placeholder="text.content"
										:init="{
											height: 200,
											menu: {},
											toolbar: [
												'undo redo | fontselect fontsizeselect formatselect | forecolor backcolor | removeformat | fullscreen',
												'casechange | bold italic underline strikethrough | bullist numlist outdent indent | blockquote hr link unlink',
											],
										}"
										@input="emitElementListUpdate" />
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<button class="button button-danger" @click="deleteElement(element.id)">
										{{ text.delete }}
									</button>
								</td>
							</tr>
						</tbody>
					</table>
					<!-- End of Content Element -->

					<!-- Invalid Element -->
					<div v-else>
						<h3 class="alert alert-warning">
							{{ text.invalid_element }}
						</h3>

						<button class="button button-danger" @click="deleteElement(element.id)">
							{{ text.delete }}
						</button>
					</div>
					<!-- End of Invalid Element -->
				</div>

				<div slot="footer" class="quick-menu-wrapper">
					<button
						:class="{ loading: creatingInProgress }"
						:disabled="creatingInProgress"
						class="button button-primary"
						@click="showAddElementSelector = !showAddElementSelector">
						{{ text.add_element }}
					</button>
					<div v-if="showAddElementSelector" class="quick-menu">
						<button
							v-for="type in elementTypes"
							:key="type.id"
							class="button button-secondary"
							@click="addElement(type.id)">
							{{ type.name }}
						</button>
					</div>
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
	name: 'ElementSettings',
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
			elementList: this.value,
			creatingInProgress: false,
			addingAnswerInProgress: false,
			showAddElementSelector: false,
			elementTypes: {
				question: {
					name: 'Question',
					id: 0,
				},
				customQuestion: {
					name: 'Custom Question',
					id: 2,
				},
				content: {
					name: 'Content',
					id: 3,
				},
			},
		}
	},
	watch: {
		value: function(newValue, oldValue) {
			this.elementList = newValue
		},
	},
	methods: {
		displayMessage,
		deleteElement(elementId) {
			const self = this

			$.each(self.elementList, function(i, element) {
				if (element.id === elementId) {
					Vue.delete(self.elementList, i)
					return false
				}
			})
		},
		deleteAnswer(elementId, answerId) {
			const element = this.getElementById(elementId)
			if (element.type !== this.elementTypes.customQuestion.id) return
			if (typeof element.data === 'undefined' || typeof element.data.answers_list === 'undefined') return

			$.each(element.data.answers_list, function(i, answer) {
				if (answer.id === answerId) {
					Vue.delete(element.data.answers_list, i)
					return false
				}
			})
		},
		excerpt(text, length = 20) {
			text = $('<div/>').html(text).text()
			if (text.length < 1) return ''
			let excerpt = '[' + text.substring(0, length)
			if (text.length > length) excerpt += ' ...'
			excerpt += ']'
			return excerpt
		},
		getElementById(elementId) {
			const self = this
			let requestedElement = false
			$.each(self.elementList, function(i, element) {
				if (element.id === elementId) {
					requestedElement = element
					return false
				}
			})
			return requestedElement
		},
		highestCustomQuestionAnswerId(elementId) {
			let highestId = 0
			const element = this.getElementById(elementId)
			$.each(element.data.answers_list, function(i, answer) {
				if (answer.id > highestId) highestId = answer.id
			})
			return highestId
		},
		customQuestionAddAnswer(elementId) {
			const self = this

			// only one request allowed at a time
			if (self.addingAnswerInProgress) return
			else self.addingAnswerInProgress = true

			/* create new answer */
			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.create_custom_question_answer,
				action: 'gifttest_create_questionaire_custom_question_answer',
				answer_id: self.highestCustomQuestionAnswerId(elementId) + 1,
				question_id: self.highestElementId() + 1,
				questionaire_id: self.questionaireId,
			}

			// do request
			$.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					const element = self.getElementById(elementId)
					if (element !== false) element.data.answers_list.push(response.data)
				} else {
					self.displayMessage(response.message, response.status)
				}
				// loading done
				self.addingAnswerInProgress = false
			}, 'json')
		},
		toggleBody(elementId) {
			if (typeof elementId === 'undefined' || elementId === null) return
			const body = $(this.$refs['body_' + elementId])
			if (body.hasClass('table-body-hidden')) body.removeClass('table-body-hidden')
			else body.addClass('table-body-hidden')
		},
		toggleElementContents() {
			$(this.$refs.elementContents).toggle()
			$(this.$refs.elementContentsCounterpart).toggle()
		},
		emitElementListUpdate() {
			this.$emit('input', this.elementList)
		},
		highestElementId() {
			let highestId = 0
			if (this.elementList === 'undefined' || !$.isArray(this.elementList)) return highestId
			// check each element
			$.each(this.elementList, function(i, element) {
				if (element.id > highestId) highestId = element.id
			})
			return highestId
		},
		addElement(type = null) {
			const self = this

			// open up element menu if no element was specified
			if (typeof type === 'undefined' || type === null) {
				self.showAddElementSelector = true
			}
			// only one request allowed at a time
			if (self.creatingInProgress) return
			else self.creatingInProgress = true
			// close menu
			self.showAddElementSelector = false

			/* create new element */
			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.create_element,
				action: 'gifttest_create_questionaire_element',
				type: type,
				id: self.highestElementId() + 1,
				questionaire_id: self.questionaireId,
			}

			// do request
			$.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.elementList.push(response.data)
				} else {
					self.displayMessage(response.message, response.status)
				}
				// loading done
				self.creatingInProgress = false
			}, 'json')
		},
	},
}
</script>
