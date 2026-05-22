import gsap from 'gsap'

const HERO_SELECTOR = '.hero-banner'
const LOAD_DELAY = 1000

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
	window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const prepareHero = hero => {
	const left = hero.querySelector('.hero-banner__left')
	const right = hero.querySelector('.hero-banner__right')

	if (left) {
		gsap.set(left, { autoAlpha: 0, x: -80 })
	}

	if (right) {
		gsap.set(right, { autoAlpha: 0, y: 80 })
	}

	return { left, right }
}

const animateHero = hero => {
	const { left, right } = prepareHero(hero)

	if (!left && !right) {
		return
	}

	gsap.timeline({
		defaults: {
			duration: 1,
			ease: 'power3.out',
			overwrite: 'auto',
		},
	})
		.to(left, {
			autoAlpha: 1,
			x: 0,
			immediateRender: false,
		}, 0)
		.to(right, {
			autoAlpha: 1,
			y: 0,
			immediateRender: false,
		}, 0)
}

const initHeroBannerMotion = () => {
	const heroes = toArray(document.querySelectorAll(HERO_SELECTOR))

	if (!heroes.length) {
		return null
	}

	// if (hasReducedMotion()) {
	// 	heroes.forEach(hero => {
	// 		gsap.set(hero.querySelectorAll('.hero-banner__left, .hero-banner__right'), {
	// 			clearProps: 'all',
	// 		})
	// 	})
	// 	return null
	// }

	heroes.forEach(hero => {
		const left = hero.querySelector('.hero-banner__left')
		const right = hero.querySelector('.hero-banner__right')

		if (left) {
			gsap.set(left, { autoAlpha: 0, x: -80 })
		}

		if (right) {
			gsap.set(right, { autoAlpha: 0, y: 80 })
		}
	})

	const start = () => {
		heroes.forEach(animateHero)
	}

	if (document.readyState === 'complete') {
		window.setTimeout(start, LOAD_DELAY)
		return null
	}

	window.addEventListener('load', () => {
		window.setTimeout(start, LOAD_DELAY)
	}, { once: true })

	return null
}

export default initHeroBannerMotion
