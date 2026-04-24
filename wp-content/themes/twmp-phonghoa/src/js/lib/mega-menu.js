const $ = jQuery

const initMegaMenu = () => {
	try {
		$('li.has-mega-menu', '#primary-menu').hoverIntent({
			over: openSubMenu,
			timeout: 500,
			out: closeSubMenu
		})
	} catch (e) {
		$('li.has-mega-menu', '#primary-menu')
			.on('mouseover', e => {
				openSubMenu(e.target)
			})
			.on('mouseout', e => {
				closeSubMenu(e.target)
			})
	}

	const openSubMenu = element => {
		$('li.has-mega-menu.is-active', '#primary-menu').removeClass('is-active')
		$(element)
			.closest('.has-mega-menu')
			.addClass('is-active')
	}

	const closeSubMenu = element => {
		$(element)
			.closest('.has-mega-menu')
			.removeClass('is-active')
	}
}

export default initMegaMenu
