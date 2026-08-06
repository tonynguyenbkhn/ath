const sendYoutubeCommand = (iframe, func) => {
	if (!iframe || !iframe.contentWindow) {
		return
	}

	iframe.contentWindow.postMessage(
		JSON.stringify({
			event: 'command',
			func,
			args: []
		}),
		'*'
	)
}

export default el => {
	const iframe = el.querySelector('.hero-video__iframe')
	const playButton = el.querySelector('[data-hero-video-toggle-play]')
	const muteButton = el.querySelector('[data-hero-video-toggle-mute]')
	const playLabel = el.querySelector('[data-hero-video-play-label]')
	const muteLabel = el.querySelector('[data-hero-video-mute-label]')

	if (!iframe) {
		return
	}

	let isPlaying = true
	let isMuted = true

	const setPlayState = nextIsPlaying => {
		isPlaying = nextIsPlaying

		if (playButton) {
			playButton.setAttribute(
				'aria-label',
				isPlaying
					? playButton.getAttribute('data-label-pause') || 'Pause'
					: playButton.getAttribute('data-label-play') || 'Play'
			)
			playButton.classList.toggle('is-active', !isPlaying)
		}

		if (playLabel) {
			playLabel.textContent = isPlaying
				? playButton.getAttribute('data-label-pause') || 'Pause'
				: playButton.getAttribute('data-label-play') || 'Play'
		}
	}

	const setMuteState = nextIsMuted => {
		isMuted = nextIsMuted

		if (muteButton) {
			muteButton.setAttribute(
				'aria-label',
				isMuted
					? muteButton.getAttribute('data-label-unmute') || 'Sound'
					: muteButton.getAttribute('data-label-mute') || 'Mute'
			)
			muteButton.classList.toggle('is-active', !isMuted)
		}

		if (muteLabel) {
			muteLabel.textContent = isMuted
				? muteButton.getAttribute('data-label-unmute') || 'Sound'
				: muteButton.getAttribute('data-label-mute') || 'Mute'
		}
	}

	playButton?.addEventListener('click', () => {
		const nextIsPlaying = !isPlaying

		sendYoutubeCommand(iframe, nextIsPlaying ? 'playVideo' : 'pauseVideo')
		setPlayState(nextIsPlaying)
	})

	muteButton?.addEventListener('click', () => {
		const nextIsMuted = !isMuted

		sendYoutubeCommand(iframe, nextIsMuted ? 'mute' : 'unMute')
		setMuteState(nextIsMuted)
	})

	setPlayState(isPlaying)
	setMuteState(isMuted)
}
