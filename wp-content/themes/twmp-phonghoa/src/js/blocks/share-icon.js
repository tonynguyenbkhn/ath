import { selectAll, on } from 'lib/dom'

function openCenteredPopup(url, title, w, h) {
	const dualScreenLeft = window.screenLeft ?? screen.left
	const dualScreenTop = window.screenTop ?? screen.top

	const width = window.innerWidth || document.documentElement.clientWidth || screen.width
	const height = window.innerHeight || document.documentElement.clientHeight || screen.height

	const left = width / 2 - w / 2 + dualScreenLeft
	const top = height / 2 - h / 2 + dualScreenTop

	const popup = window.open(url, title, `scrollbars=yes,width=${w},height=${h},top=${top},left=${left}`)
	if (window.focus) popup.focus()
}

export default el => {
	const links = selectAll('a', el)

	links.forEach(link => {
		const href = link.getAttribute('href')
		let name = 'share'

		if (link.classList.contains('share-icon__facebook')) name = 'fbshare'
		else if (link.classList.contains('share-icon__twitter')) name = 'twshare'
		else if (link.classList.contains('share-icon__linkedin')) name = 'lnshare'

		on('click', e => {
			e.preventDefault()
			openCenteredPopup(href, name, 600, 400)
		}, link)
	})
}
