import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const HERO_SELECTOR = '.hero-banner'
const PRODUCT_SELECTOR = '.product-cat-grid, .product-cat-grid-section'

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
	window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const initHeroProductStackMotion = () => {
	const hero = document.querySelector(HERO_SELECTOR)
	const product = document.querySelector(PRODUCT_SELECTOR)

	if (!hero || !product) {
		return null
	}

	if (hasReducedMotion()) {
		gsap.set([hero, product], { clearProps: 'all' })
		return null
	}

	gsap.set(hero, {
		position: 'relative',
		zIndex: 1,
	})

	gsap.set(product, {
		position: 'relative',
		zIndex: 2,
		autoAlpha: 1,
		y: 40,
	})

	ScrollTrigger.create({
		trigger: hero,
		start: 'top top',
		endTrigger: product,
		end: 'top top',
		pin: true,
		pinSpacing: false,
		anticipatePin: 1,
		invalidateOnRefresh: true,
	})

	const items = toArray(product.querySelectorAll('.product-cat-grid__item'))
	const firstItems = items.slice(0, 2)
	const laterItems = items.slice(2)
	const header = product.querySelector('.product-cat-grid__header, .heading')
	const light = product.querySelector('.product-cat-grid__light, .product-cat-grid__logo, .glow')

	if (header) {
		gsap.set(header, {
			autoAlpha: 0,
			y: 20,
		})
	}

	if (light) {
		gsap.set(light, {
			autoAlpha: 0,
			y: 20,
		})
	}

	if (firstItems.length) {
		gsap.set(firstItems, {
			autoAlpha: 0,
			y: 32,
		})
	}

	if (laterItems.length) {
		gsap.set(laterItems, {
			autoAlpha: 0,
			y: 32,
		})
	}

	const tl = gsap.timeline({
		paused: true,
		defaults: {
			ease: 'power2.out',
			overwrite: 'auto',
		},
	})

	tl.to(product, {
		autoAlpha: 1,
		y: 0,
		duration: 0.9,
	}, 0)

	if (header) {
		tl.to(header, {
			autoAlpha: 1,
			y: 0,
			duration: 0.7,
		}, 0.35)
	}

	if (light) {
		tl.to(light, {
			autoAlpha: 1,
			y: 0,
			duration: 0.8,
		}, 0.4)
	}

	if (firstItems.length) {
		tl.to(firstItems, {
			autoAlpha: 1,
			y: 0,
			duration: 0.7,
			stagger: 0.12,
		}, 0.55)
	}

	if (laterItems.length) {
		tl.to(laterItems, {
			autoAlpha: 1,
			y: 0,
			duration: 0.7,
			stagger: 0.12,
		}, 0.95)
	}

	ScrollTrigger.create({
		trigger: product,
		start: 'top 72%',
		end: 'bottom 20%',
		invalidateOnRefresh: true,
		once: true,
		onEnter: () => {
			// Set time show main section
			// gsap.delayedCall(0.5, () => {
			// 	tl.play(0)
			// })
			tl.play(0)
		},
		onEnterBack: () => {
			if (!tl.isActive()) {
				tl.play(0)
			}
		},
	})

	window.addEventListener('load', () => {
		ScrollTrigger.refresh()
	}, { once: true })

	return null
}

export default initHeroProductStackMotion
