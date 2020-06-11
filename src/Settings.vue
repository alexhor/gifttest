<template>
	<div class="section">
		<div id="content">
			<div v-if="loading">
				<img :src="pluginDirUrl + 'image/loading.svg'" class="loading">
			</div>

			<div v-else>
				<h2>{{ text.loadQuestionaire }}</h2>
				<div class="questionaire-selector-wrapper">
					<button
						v-for="(questionaire, i) in availabeQuestionaireList"
						:key="i"
						:class="{ loading: loadingQuestionaireDetails }"
						:disabled="loadingQuestionaireDetails"
						class="button button-primary"
						@click="loadQuestionaireDetails(questionaire.id)">
						{{ questionaire.name }}
					</button>
				</div>

				<p v-if="availabeQuestionaireList.length < 1">
					{{ text.noQuestionaireExistsYes }}
				</p>

				<div>
					<input v-model="newQuestionaireName" :placeholder="text.questionaireName" type="text">
					<button
						:class="{ loading: creatingNewQuestionaire }"
						:disabled="creatingNewQuestionaire"
						class="button button-primary"
						@click="createQuestionaire">
						{{ text.addQuestionaire }}
					</button>
				</div>

				<div>
					<button
						:class="{ loading: importingQuestionaire }"
						:disabled="importingQuestionaire"
						class="button button-secondary"
						@click="importQuestionaire">
						{{ text.importQuestionaire }}
					</button>
				</div>
			</div>

			<div v-if="loadedQuestionaire">
				<br><hr>
				<h2>{{ text.editQuestionaire }}</h2>

				<div class="controls">
					<button
						:class="{ loading: savingQuestionaire }"
						:disabled="savingQuestionaire"
						class="button button-success"
						@click="saveQuestionaire">
						{{ text.save }}
					</button>
					<button
						v-if="gifttest.user_can_delete"
						:class="{ loading: deletingQuestionaire }"
						:disabled="deletingQuestionaire"
						class="button button-danger"
						@click="deleteQuestionaire">
						{{ text.delete }}
					</button>
					<button
						:class="{ loading: exportingQuestionaire }"
						:disabled="exportingQuestionaire"
						class="button button-info"
						@click="exportQuestionaire">
						{{ text.export }}
					</button>
				</div>

				<div class="shortcode">
					<h3>{{ text.shortcode }}</h3>
					<code>[gifttest test-id="{{ loadedQuestionaire.settings.id }}"]</code>
					<button
						class="icon icon-copy button-copy has-tooltip"
						@click="copyToClipboard('[gifttest test-id=\'' + loadedQuestionaire.settings.id + '\']')">
						<p class="tooltip hover">
							{{ text.copyToClipboard }}
						</p>
						<p class="tooltip focus">
							{{ text.copied }}
						</p>
					</button>
				</div>

				<table class="settings">
					<tbody>
						<tr>
							<td>
								<label for="questionaireName">
									{{ text.questionaireName }}
								</label>
							</td>
							<td>
								<input
									id="questionaireName"
									v-model="loadedQuestionaire.settings.name"
									:placeholder="text.name"
									type="text">
							</td>
						</tr>
						<tr>
							<td>
								<label for="shownTalentCount">
									{{ text.numberOfTalentsShown }}
								</label>
							</td>
							<td>
								<input
									id="shownTalentCount"
									v-model="loadedQuestionaire.settings.shownTalentCount"
									type="number">
							</td>
						</tr>
						<tr>
							<td>
								<label for="showMoreTalents">
									{{ text.enableShowMoreTalentsButton }}
								</label>
							</td>
							<td>
								<select
									id="showMoreTalents"
									v-model="loadedQuestionaire.settings.showMoreTalents"
									:placeholder="text.name"
									type="text">
									<option value="true">
										{{ text.yes }}
									</option>
									<option value="false">
										{{ text.no }}
									</option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>

				<ElementSettings
					v-model="loadedQuestionaire.elementList"
					:text="text"
					:questionaire-id="loadedQuestionaire.settings.id" />

				<AnswerSettings
					v-model="loadedQuestionaire.answerList"
					:text="text"
					:questionaire-id="loadedQuestionaire.settings.id" />

				<TalentSettings
					v-model="loadedQuestionaire.talentList"
					:available-question-list="questionList"
					:text="text"
					:questionaire-id="loadedQuestionaire.settings.id" />
			</div>
		</div>
	</div>
</template>

<script>
import $ from 'jquery'

import ElementSettings from './Components/Settings/ElementSettings.vue'
import AnswerSettings from './Components/Settings/AnswerSettings.vue'
import TalentSettings from './Components/Settings/TalentSettings.vue'

import utilities from './Components/Utilities.js'
const displayMessage = utilities.displayMessage
const copyToClipboard = utilities.copyToClipboard
/* global ajaxurl */
/* global gifttest */

export default {
	name: 'Settings',
	components: {
		ElementSettings,
		AnswerSettings,
		TalentSettings,
	},
	data: function() {
		return {
			loading: true,
			creatingNewQuestionaire: false,
			loadingQuestionaireDetails: false,
			showAddElementSelector: false,
			creatingElement: false,
			savingQuestionaire: false,
			deletingQuestionaire: false,
			exportingQuestionaire: false,
			importingQuestionaire: false,
			pluginDirUrl: '',
			loadedQuestionaire: false,
			availabeQuestionaireList: [],
			newQuestionaireName: '',
			gifttest: gifttest,
			text: gifttest.text,
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
	computed: {
		questionList: function() {
			const self = this
			const questionList = []
			if (self.loadedQuestionaire === 'undefined' || self.loadedQuestionaire === false || self.loadedQuestionaire.elementList === 'undefined') return questionList

			$.each(self.loadedQuestionaire.elementList, function(i, element) {
				if (element.type === self.elementTypes.question.id || element.type === self.elementTypes.customQuestion.id) {
					questionList.push(element)
				}
			})

			return questionList
		},
	},
	beforeMount: function() {
		this.pluginDirUrl = gifttest.plugin_dir_url
		this.loadQuestionaireList()
	},
	methods: {
		displayMessage,
		copyToClipboard,
		highestQuestionaireId() {
			let highestId = 0
			if (this.availabeQuestionaireList === 'undefined' || !$.isArray(this.availabeQuestionaireList)) return highestId
			// check each questionaire
			$.each(this.availabeQuestionaireList, function(i, questionaire) {
				if (questionaire.id > highestId) highestId = questionaire.id
			})
			return highestId
		},
		importQuestionaire() {
			const self = this

			// create file upload input
			const uploadDialog = document.createElement('input')
			uploadDialog.setAttribute('type', 'file')
			uploadDialog.style.display = 'none'
			document.body.appendChild(uploadDialog)

			// upload handler
			uploadDialog.addEventListener('change', function(e) {
				// check if a file was selected
				if (e.target.files.length < 1) return

				// only allow one request at a time
				if (self.importingQuestionaire) return
				else self.importingQuestionaire = true

				// read file
				const file = e.target.files[0]
				const reader = new FileReader()
				reader.onload = function(event) {
					const fileContent = event.target.result
					try {
						const json = JSON.parse(fileContent)
						json.settings.id = self.highestQuestionaireId() + 1
						self.loadedQuestionaire = json
						self.displayMessage(self.text.importSuccessful_saveTheQuestionaireIfYouWantToKeepIt, 'success')
					} catch (e) {
						console.debug(e)
						self.displayMessage(self.text.importFailed_invalidFileContent, 'error')
					}

					// cleanup
					document.body.removeChild(uploadDialog)
					self.importingQuestionaire = false
				}
				reader.readAsText(file)
			}, false)

			// open dialog
			uploadDialog.click()
		},
		saveQuestionaire() {
			const self = this

			// only allow one request at a time
			if (self.savingQuestionaire) return
			else self.savingQuestionaire = true

			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.update,
				action: 'gifttest_update_questionaire',
				questionaireAsJson: this.getQuestionaireAsJson(),
			}

			// do request
			$.post(ajaxurl, requestData, function(response) {
				self.displayMessage(response.message, response.status)
				// loading done
				self.savingQuestionaire = false
				// make sure the questionaire list is up to date
				self.loadQuestionaireList()
			}, 'json')
		},
		deleteQuestionaire() {
			if (confirm(this.text.reallyDeleteQuestionaire)) this.reallyDeleteQuestionaire()
		},
		reallyDeleteQuestionaire() {
			const self = this

			// only allow one request at a time
			if (self.deletingQuestionaire) return
			else self.deletingQuestionaire = true

			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.delete,
				action: 'gifttest_delete_questionaire',
				questionaireId: this.loadedQuestionaire.settings.id,
			}

			// do request
			$.post(ajaxurl, requestData, function(response) {
				self.displayMessage(response.message, response.status)
				// loading done
				self.deletingQuestionaire = false
				self.loadedQuestionaire = false
				// reload questionaire list
				self.loadQuestionaireList()
			}, 'json')
		},
		exportQuestionaire() {
			const downloadContent = JSON.stringify(this.getQuestionaireAsJson(), null, 4)
			// create virtual download link
			const element = document.createElement('a')
			element.setAttribute('href', 'data:application/json;charset=utf-8,' + encodeURIComponent(downloadContent))
			element.setAttribute('download', this.loadedQuestionaire.settings.name + '.json')
			element.style.display = 'none'
			document.body.appendChild(element)
			// download file
			element.click()
			// remove virtual download link
			document.body.removeChild(element)
		},
		getQuestionaireAsJson() {
			return this.loadedQuestionaire
		},
		loadQuestionaireList() {
			const self = this
			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.get_list,
				action: 'gifttest_get_questionaire_list',
			}

			// do request
			$.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.availabeQuestionaireList = response.data
				} else {
					self.displayMessage(response.message, response.status)
				}
				// loading done
				self.loading = false
			}, 'json')
		},
		loadQuestionaireDetails(id) {
			const self = this

			// only allow one loading request at a time
			if (self.loadingQuestionaireDetails) return
			else self.loadingQuestionaireDetails = true

			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.get_details,
				action: 'gifttest_get_questionaire_details',
				id: id,
			}

			// do request
			$.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.loadedQuestionaire = response.data
				} else {
					self.displayMessage(response.message, response.status)
				}
				// reactivate button
				self.loadingQuestionaireDetails = false
			}, 'json')
		},
		createQuestionaire() {
			const self = this

			// only allow one loading request at a time
			if (self.creatingNewQuestionaire) return
			else self.creatingNewQuestionaire = true

			// collect data
			const requestData = {
				_ajax_nonce: gifttest._ajax_nonce.create,
				action: 'gifttest_create_questionaire',
				name: self.newQuestionaireName,
			}

			// do request
			$.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					// reset questionaire name
					self.newQuestionaireName = ''
					// add the new questionaire to the list
					self.availabeQuestionaireList.push({
						id: response.questionaireId,
						name: response.questionaireName,
					})
				} else {
					self.displayMessage(response.message, response.status)
				}
				// reactivate button
				self.creatingNewQuestionaire = false
			}, 'json')
		},
	},
}
</script>
