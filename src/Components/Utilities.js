export default {
	displayMessage(message, messageType) {
		/*
		 * Display the given message
		 * @param string message: The message content
		 * @param string messageType: Type of message [success, warning, error]
		 */
		console.debug(messageType + ' - ' + message)
	},
	copyToClipboard(valueToCopy) {
		/*
		 * Copy a value to the clients clipboard
		 * @param string valueToCopy: The value to copy
		 */
		const input = document.createElement('input')
		input.value = valueToCopy

		input.style.position = 'absolute'
		input.style.top = -100
		input.style.left = -100
		input.style.display = 'block'
		input.style.width = 0
		input.style.height = 0
		input.style.opacity = 0
		input.style['z-index'] = -1000
		input.style.padding = 0
		input.style.margin = 0
		input.style.border = 0

		document.body.appendChild(input)
		input.select()
		document.execCommand('copy')
		input.remove()
	},
}
