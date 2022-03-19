<template>
	<div class="section">
		<div id="content">
			<div v-if="loading">
				<img :src="pluginDirUrl + 'image/loading.svg'" class="loading">
			</div>

			<div v-else>
				<h2>{{ text.load_questionaire }}</h2>
				<div class="questionaire-selector-wrapper">
					<button v-for="(questionaire, i) in availabeQuestionaireList"
						:key="i"
						:class="{ loading: loadingQuestionaireDetails }"
						:disabled="loadingQuestionaireDetails"
						class="button button-primary"
						@click="loadQuestionaireDetails(questionaire.id)">
						{{ questionaire.name }}
					</button>
				</div>

				<p v-if="availabeQuestionaireList.length < 1">
					{{ text.no_questionaire_exists_yet }}
				</p>

				<div>
					<input v-model="newQuestionaireName" :placeholder="text.questionaire_name" type="text">
					<button :class="{ loading: creatingNewQuestionaire }"
						:disabled="creatingNewQuestionaire"
						class="button button-primary"
						@click="createQuestionaire">
						{{ text.add_questionaire }}
					</button>
				</div>

				<div>
					<button :class="{ loading: importingQuestionaire }"
						:disabled="importingQuestionaire"
						class="button button-secondary"
						@click="importQuestionaire">
						{{ text.import_questionaire }}
					</button>
				</div>
			</div>

			<div v-if="loadedQuestionaire">
				<br><hr>
				<h2>{{ text.edit_questionaire }}</h2>

				<div class="controls">
					<button :class="{ loading: savingQuestionaire }"
						:disabled="savingQuestionaire"
						class="button button-success"
						@click="saveQuestionaire">
						{{ text.save }}
					</button>
					<button v-if="gifttest.user_can_delete"
						:class="{ loading: deletingQuestionaire }"
						:disabled="deletingQuestionaire"
						class="button button-danger"
						@click="deleteQuestionaire">
						{{ text.delete }}
					</button>
					<button :class="{ loading: exportingQuestionaire }"
						:disabled="exportingQuestionaire"
						class="button button-info"
						@click="exportQuestionaire">
						{{ text.export }}
					</button>
				</div>

				<div class="shortcode">
					<h3>{{ text.shortcode }}</h3>
					<code>[gifttest test-id="{{ loadedQuestionaire.settings.id }}"]</code>
					<button class="icon icon-copy button-copy has-tooltip"
						@click="copyToClipboard('[gifttest test-id=\'' + loadedQuestionaire.settings.id + '\']')">
						<p class="tooltip hover">
							{{ text.copy_to_clipboard }}
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
									{{ text.questionaire_name }}
								</label>
							</td>
							<td>
								<input id="questionaireName"
									v-model="loadedQuestionaire.settings.name"
									:placeholder="text.name"
									type="text">
							</td>
						</tr>
						<tr>
							<td>
								<label for="shownGiftCount">
									{{ text.number_of_gifts_shown }}
								</label>
							</td>
							<td>
								<input id="shownGiftCount"
									v-model="loadedQuestionaire.settings.shown_gift_count"
									type="number">
							</td>
						</tr>
						<tr>
							<td>
								<label for="showMoreGifts">
									{{ text.enable_show_more_gifts_button }}
								</label>
							</td>
							<td>
								<select id="showMoreGifts"
									v-model="loadedQuestionaire.settings.show_more_gifts"
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

				<ElementSettings v-model="loadedQuestionaire.element_list"
					:text="text"
					:questionaire-id="loadedQuestionaire.settings.id" />

				<AnswerSettings v-model="loadedQuestionaire.answer_list"
					:text="text"
					:questionaire-id="loadedQuestionaire.settings.id" />

				<GiftSettings v-model="loadedQuestionaire.gift_list"
					:available-question-list="questionList"
					:text="text"
					:questionaire-id="loadedQuestionaire.settings.id" />
			</div>
		</div>
	</div>
</template>

<script>
/* global ajaxurl */
/* global jQuery */
/* global gifttest */
__webpack_public_path__ = gifttest.vue_components_path // eslint-disable-line

const ElementSettings = () => import(/* webpackChunkName: "ElementSettings" *//* webpackPrefetch: true */'./Components/Settings/ElementSettings.vue')
const AnswerSettings = () => import(/* webpackChunkName: "AnswerSettings" *//* webpackPrefetch: true */'./Components/Settings/AnswerSettings.vue')
const GiftSettings = () => import(/* webpackChunkName: "GiftSettings" *//* webpackPrefetch: true */'./Components/Settings/GiftSettings.vue')
const Utilities = () => import(/* webpackChunkName: "Utilities" *//* webpackPrefetch: true */'./Components/Utilities.js')

export default {
	name: 'Settings',
	components: {
		ElementSettings,
		AnswerSettings,
		GiftSettings,
	},
	data() {
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
			gifttest,
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
		questionList() {
			const self = this
			const questionList = []
			if (self.loadedQuestionaire === 'undefined' || self.loadedQuestionaire === false || self.loadedQuestionaire.element_list === 'undefined') return questionList

			jQuery.each(self.loadedQuestionaire.element_list, function(i, element) {
				if (element.type === self.elementTypes.question.id || element.type === self.elementTypes.customQuestion.id) {
					questionList.push(element)
				}
			})

			return questionList
		},
	},
	beforeMount() {
		const self = this

		self.pluginDirUrl = gifttest.plugin_dir_url
		self.loadQuestionaireList()

		// load requried modules
		ElementSettings()
		AnswerSettings()
		GiftSettings()

		// load utility functions
		Utilities().then(utilities => {
			self.displayMessage = utilities.default.displayMessage
			self.copyToClipboard = utilities.default.copyToClipboard
		})
	},
	methods: {
		/**
		 * This is a dummy function that will be replace asynchronous
		 */
		displayMessage() {},
		/**
		 * This is a dummy function that will be replace asynchronous
		 */
		copyToClipboard() {},
		highestQuestionaireId() {
			let highestId = 0
			if (this.availabeQuestionaireList === 'undefined' || !jQuery.isArray(this.availabeQuestionaireList)) return highestId
			// check each questionaire
			jQuery.each(this.availabeQuestionaireList, function(i, questionaire) {
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
						self.displayMessage(self.text.import_successful_save_the_questionaire_if_you_want_to_keep_it, 'success')
					} catch (e) {
						console.debug(e)
						self.displayMessage(self.text.import_failed_invalid_file_content, 'error')
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
				questionaire_as_json: this.getQuestionaireAsJson(),
			}

			// do request
			jQuery.post(ajaxurl, requestData, function(response) {
				if (typeof response.message !== 'undefined' && response.message !== null) {
					self.displayMessage(response.message, response.status)
				}
				// make sure the questionaire list is up to date
				self.loadQuestionaireList()
			}, 'json').fail(function() {
				console.debug('Saving Questionaire failed')
			}).always(function() {
				// loading done
				self.savingQuestionaire = false
			})
		},
		deleteQuestionaire() {
			if (confirm(this.text.really_delete_questionaire)) this.reallyDeleteQuestionaire()
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
				questionaire_id: this.loadedQuestionaire.settings.id,
			}

			// do request
			jQuery.post(ajaxurl, requestData, function(response) {
				if (typeof response.message !== 'undefined' && response.message !== null) {
					self.displayMessage(response.message, response.status)
				}
				// loading done
				self.deletingQuestionaire = false
				self.loadedQuestionaire = false
				// reload questionaire list
				self.loadQuestionaireList()
			}, 'json').fail(function() {
				console.debug('Deleting Questionaire failed')
			}).always(function() {
				// loading done
				self.deletingQuestionaire = false
				self.loadedQuestionaire = false
			})
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
			jQuery.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.availabeQuestionaireList = response.data
				} else if (typeof response.message !== 'undefined' && response.message !== null) {
					self.displayMessage(response.message, response.status)
				}
			}, 'json').fail(function() {
				console.debug('Loading Questionaire list failed')
			}).always(function() {
				// loading done
				self.loading = false
			})
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
				id,
			}

			// do request
			jQuery.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					self.loadedQuestionaire = response.data
				} else if (typeof response.message !== 'undefined' && response.message !== null) {
					self.displayMessage(response.message, response.status)
				}
			}, 'json').fail(function() {
				console.debug('Loading Questionaire details failed')
			}).always(function() {
				// loading done
				self.loadingQuestionaireDetails = false
			})
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
			jQuery.post(ajaxurl, requestData, function(response) {
				if (response.status === 'success') {
					// reset questionaire name
					self.newQuestionaireName = ''
					// add the new questionaire to the list
					self.availabeQuestionaireList.push({
						id: response.questionaire_id,
						name: response.questionaire_name,
					})
				} else if (typeof response.message !== 'undefined' && response.message !== null) {
					self.displayMessage(response.message, response.status)
				}
			}, 'json').fail(function(e) {
				console.debug(e)
				console.debug('Creating Questionaire failed')
			}).always(function() {
			// reactivate button
				self.creatingNewQuestionaire = false
			})
		},
	},
}
</script>
