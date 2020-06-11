export default {
	displayMessage(messageText, messageType) {
		/*
		 * Display the given message
		 * @param string message: The message content
		 * @param string messageType: Type of message [success, warning, error]
		 */
		let messageContainer = document.getElementById('gifttest-message-container')
		if (typeof messageContainer === 'undefined' || messageContainer === null) {
			messageContainer = document.createElement('div')
			messageContainer.id = 'gifttest-message-container'
			document.getElementById('wpbody-content').appendChild(messageContainer)
		}
		const message = document.createElement('div')
		message.classList.add('message')
		message.classList.add('message-' + messageType)
		message.innerHTML = messageText

		const closeButton = document.createElement('button')
		closeButton.classList.add('message-close-button')
		closeButton.innerHTML = '×'
		closeButton.onclick = function() {
			messageContainer.removeChild(message)
		}
		message.appendChild(closeButton)

		messageContainer.appendChild(message)

		window.setTimeout(function() { closeButton.click() }, 5000)
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
