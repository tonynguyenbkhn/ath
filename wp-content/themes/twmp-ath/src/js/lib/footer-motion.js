import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const SECTION_SELECTOR = '.footer__primary-wrapper'

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
	window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const getTargets = footer => {
	const logo = footer.querySelector('.footer--logo')
	const infoItems = toArray(footer.querySelectorAll('.footer__info-item'))
	const columns = toArray(footer.querySelectorAll('.footer-column-list'))
	const columnHeadings = toArray(footer.querySelectorAll('.footer-column-list h3'))
	const separators = toArray(footer.querySelectorAll('.footer-column-list .wp-block-separator'))
	const menuItems = toArray(footer.querySelectorAll('.footer-menu-list li'))

	return {
		logo,
		infoItems,
		columns,
		columnHeadings,
		separators,
		menuItems,
	}
}

const initFooterSection = footer => {
	const { logo, infoItems, columns, columnHeadings, separators, menuItems } = getTargets(footer)
	const targets = [logo, ...infoItems, ...columns, ...columnHeadings, ...separators, ...menuItems].filter(Boolean)

	if (!targets.length) {
		return
	}

	// if (hasReducedMotion()) {
	// 	gsap.set(targets, { clearProps: 'all' })
	// 	return
	// }

	gsap.set(footer, {
		autoAlpha: 0,
	})

	if (logo) {
		gsap.set(logo, {
			autoAlpha: 0,
			y: 28,
			scale: 0.96,
			transformOrigin: '50% 50%',
		})
	}

	if (infoItems.length) {
		gsap.set(infoItems, {
			autoAlpha: 0,
			x: 32,
		})
	}

	if (columns.length) {
		gsap.set(columns, {
			autoAlpha: 0,
			y: 44,
		})
	}

	if (columnHeadings.length) {
		gsap.set(columnHeadings, {
			autoAlpha: 0,
			y: 18,
		})
	}

	if (separators.length) {
		gsap.set(separators, {
			autoAlpha: 0,
			scaleX: 0,
			transformOrigin: '0% 50%',
		})
	}

	if (menuItems.length) {
		gsap.set(menuItems, {
			autoAlpha: 0,
			y: 18,
		})
	}

	const timeline = gsap.timeline({
		defaults: {
			duration: 0.9,
			ease: 'power3.out',
			overwrite: 'auto',
		},
		scrollTrigger: {
			trigger: footer,
			start: 'top 82%',
			once: true,
			invalidateOnRefresh: true,
		},
	})

	timeline.to(footer, { autoAlpha: 1, duration: 0.3 }, 0)

	if (logo) {
		timeline.to(logo, {
			autoAlpha: 1,
			y: 0,
			scale: 1,
		}, 0.1)
	}

	if (infoItems.length) {
		timeline.to(infoItems, {
			autoAlpha: 1,
			x: 0,
			stagger: 0.12,
		}, 0.2)
	}

	if (columns.length) {
		timeline.to(columns, {
			autoAlpha: 1,
			y: 0,
			stagger: 0.08,
		}, 0.35)
	}

	if (columnHeadings.length) {
		timeline.to(columnHeadings, {
			autoAlpha: 1,
			y: 0,
			stagger: 0.06,
			duration: 0.7,
		}, 0.48)
	}

	if (separators.length) {
		timeline.to(separators, {
			autoAlpha: 1,
			scaleX: 1,
			stagger: 0.06,
			duration: 0.75,
		}, 0.58)
	}

	if (menuItems.length) {
		timeline.to(menuItems, {
			autoAlpha: 1,
			y: 0,
			stagger: 0.035,
			duration: 0.65,
		}, 0.66)
	}
}

const initFooterMotion = () => {
	const footers = toArray(document.querySelectorAll(SECTION_SELECTOR))

	if (!footers.length) {
		return null
	}

	footers.forEach(initFooterSection)

	window.addEventListener('load', () => {
		ScrollTrigger.refresh()
	}, { once: true })

	return null
}

export default initFooterMotion
