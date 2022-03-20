<template>
	<div class="elements">
		<h3 class="toggle-element-contents" @click="toggleElementContents">
			{{ text.elements }}
		</h3>

		<div ref="elementContentsCounterpart"
			class="element-contents-counterpart"
			@click="toggleElementContents">
			<i class="icon icon-edit" />
			{{ text.edit_elements }}
		</div>

		<div ref="elementContents" class="element-contents">
			<draggable v-model="elementList"
				item-key="id"
				handle=".drag-handle"
				draggable=".element"
				@input="emitElementListUpdate">
				<template #item="{element}">
					<div class="element">
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
										<textarea :id="'questionText_' + element.id"
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
										<textarea :id="'questionText_' + element.id"
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
										<draggable v-model="element.data.answers_list"
											item-key="id"
											draggable=".answer"
											handle=".answer-drag-handle">
											<template #item="answer">
												<div class="answer">
													<i class="icon icon-align-justify answer-drag-handle" />
													<label :for="'answerText_' + answer.element.id">
														{{ text.answer_text }}
													</label>
													<input :id="'answerText_' + answer.element.id"
														v-model="answer.element.text"
														type="text"
														:placeholder="text.answer_text"
														@input="emitElementListUpdate">

													<label :for="'answerValue_' + answer.element.id">
														{{ text.answer_value }}
													</label>
													<input :id="'answerValue_' + answer.element.id"
														v-model="answer.element.value"
														type="number"
														:placeholder="text.answer_value"
														@input="emitElementListUpdate">
													<button class="button button-danger" @click="deleteAnswer(element.id, answer.element.id)">
														{{ text.delete }}
													</button>
												</div>
											</template>

											<template #footer>
												<button :class="{ loading: addingAnswerInProgress }"
													:disabled="addingAnswerInProgress"
													class="button button-primary"
													@click="customQuestionAddAnswer(element.id)">
													{{ text.add_answer }}
												</button>
											</template>
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
										<editor :id="'content_' + element.id"
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
				</template>

				<template #footer>
					<div class="quick-menu-wrapper">
						<button :class="{ loading: creatingInProgress }"
							:disabled="creatingInProgress"
							class="button button-primary"
							@click="showAddElementSelector = !showAddElementSelector">
							{{ text.add_element }}
						</button>
						<div v-if="showAddElementSelector" class="quick-menu">
							<button v-for="type in elementTypes"
								:key="type.id"
								class="button button-secondary"
								@click="addElement(type.id)">
								{{ type.name }}
							</button>
						</div>
					</div>
				</template>
			</draggable>
		</div>
	</div>
</template>

<script>
/* global ajaxurl */
/* global jQuery */
/* global gifttest */
import Editor from '@tinymce/tinymce-vue'
import draggable from 'vuedraggable'
__webpack_public_path__ = gifttest.vue_components_path // eslint-disable-line

const Utilities = () => import(/* webpackChunkName: "Utilities" */'../Utilities.js')

export default {
	name: 'ElementSettings',
	components: {
		draggable,
		editor: Editor,
	},
	props: {
		modelValue: {
			type: Array,
			required: true,
			default() { return [] },
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
	data() {
		return {
			elementList: this.modelValue,
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
		modelValue(newValue, oldValue) {
			this.elementList = newValue
		},
	},
	beforeMount() {
		const self = this
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
		deleteElement(elementId) {
			const self = this

			jQuery.each(self.elementList, function(i, element) {
				if (element.id === elementId) {
					self.elementList.splice(i, 1)
					return false
				}
			})
		},
		deleteAnswer(elementId, answerId) {
			const element = this.getElementById(elementId)
			if (element.type !== this.elementTypes.customQuestion.id) return
			if (typeof element.data === 'undefined' || typeof element.data.answers_list === 'undefined') return

			jQuery.each(element.data.answers_list, function(i, answer) {
				if (answer.id === answerId) {
					element.data.answers_list.splice(i, 1)
					return false
				}
			})
		},
		excerpt(text, length = 20) {
			text = jQuery('<div/>').html(text).text()
			if (text.length < 1) return ''
			let excerpt = '[' + text.substring(0, length)
			if (text.length > length) excerpt += ' ...'
			excerpt += ']'
			return excerpt
		},
		getElementById(elementId) {
			const self = this
			let requestedElement = false
			jQuery.each(self.elementList, function(i, element) {
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
			jQuery.each(element.data.answers_list, function(i, answer) {
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
			jQuery.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					const element = self.getElementById(elementId)
					if (element !== false) element.data.answers_list.push(response.data)
				} else {
					self.displayMessage(response.message, response.status)
				}
			}, 'json').fail(function(e) {
				console.debug(e)
				console.debug('Adding answer failed')
			}).always(function() {
				// loading done
				self.addingAnswerInProgress = false
			})
		},
		toggleBody(elementId) {
			if (typeof elementId === 'undefined' || elementId === null) return
			const body = jQuery(this.$refs['body_' + elementId])
			if (body.hasClass('table-body-hidden')) body.removeClass('table-body-hidden')
			else body.addClass('table-body-hidden')
		},
		toggleElementContents() {
			jQuery(this.$refs.elementContents).toggle()
			jQuery(this.$refs.elementContentsCounterpart).toggle()
		},
		emitElementListUpdate() {
			this.$emit('input', this.elementList)
		},
		highestElementId() {
			let highestId = 0
			if (this.elementList === 'undefined' || !jQuery.isArray(this.elementList)) return highestId
			// check each element
			jQuery.each(this.elementList, function(i, element) {
				if (element.id > highestId) highestId = element.id
			})
			return highestId
		},
		addElement(type = null) {
			const self = this

			// open up element menu if no element was specified
			if (typeof type === 'undefined' || type === null) {
				self.showAddElementSelector = true
				return
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
				type,
				id: self.highestElementId() + 1,
				questionaire_id: self.questionaireId,
			}

			// do request
			jQuery.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.elementList.push(response.data)
				} else {
					self.displayMessage(response.message, response.status)
				}
			}, 'json').fail(function(e) {
				console.debug('Adding element failed')
			}).always(function() {
				// loading done
				self.creatingInProgress = false
			})
		},
	},
}
</script>
