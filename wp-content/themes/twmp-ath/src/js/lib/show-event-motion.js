import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const SECTION_SELECTOR = '.show-event'
const PREVIOUS_SECTION_SELECTOR = '.about-us'
const HORIZONTAL_SCROLL_DISTANCE = 800

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
	window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const getPreviousSection = section => {
	const previous = section.previousElementSibling

	if (previous?.matches(PREVIOUS_SECTION_SELECTOR)) {
		return previous
	}

	return null
}

const getTargets = section => {
	const viewport = section.querySelector('.show-event__viewport')
	const track = section.querySelector('.show-event__track')
	const intro = section.querySelector('.event-section__intro')
	const actions = section.querySelector('.event-section__actions')
	const heading = section.querySelector('.event-section__heading')
	const triangle = section.querySelector('.section-shape--triangle')
	const circle = section.querySelector('.section-shape--circle')
	const slides = toArray(section.querySelectorAll('.event-section__swiper .swiper-slide'))

	return {
		viewport,
		track,
		intro,
		actions,
		heading,
		triangle,
		circle,
		slides,
	}
}

const initSectionMotion = section => {
	const previousSection = getPreviousSection(section)
	const { viewport, track, intro, actions, heading, triangle, circle, slides } = getTargets(section)

	if (!viewport || !track || (!intro && !actions && !heading && !triangle && !circle && !slides.length)) {
		return
	}

	// gsap.set(section, {
	// 	position: 'relative',
	// 	zIndex: 4,
	// 	autoAlpha: 1,
	// })

	// gsap.set(viewport, {
	// 	position: 'relative',
	// 	overflow: 'hidden',
	// })

	// gsap.set(track, {
	// 	xPercent: 100,
	// 	x: 0,
	// })

	if (triangle) {
		gsap.set(triangle, {
			autoAlpha: 0,
			scale: 0.5,
			rotation: 90,
			transformOrigin: '50% 50%',
		})
	}

	if (circle) {
		gsap.set(circle, {
			autoAlpha: 0,
			scale: 0,
			transformOrigin: '50% 50%',
		})
	}

	if (intro) {
		gsap.set(intro, {
			autoAlpha: 0,
			x: 96,
		})
	}

	if (actions) {
		gsap.set(actions, {
			autoAlpha: 0,
			x: -96,
		})
	}

	if (heading) {
		gsap.set(heading, {
			autoAlpha: 0,
			x: 72,
		})
	}

	if (slides.length) {
		gsap.set(slides, {
			autoAlpha: 0,
			x: 104,
		})
	}

	// if (previousSection) {
	// 	gsap.set(previousSection, {
	// 		position: 'relative',
	// 		zIndex: 3,
	// 	})

	// 	ScrollTrigger.create({
	// 		trigger: previousSection,
	// 		start: 'top 80px',
	// 		end: "+=2200",
	// 		pin: true,
	// 		pinSpacing: false,
	// 		anticipatePin: 1,
	// 		invalidateOnRefresh: true
	// 	})
	// }

	const masterTimeline = gsap.timeline({
		defaults: {
			ease: 'power2.out',
			overwrite: 'auto',
		},
		scrollTrigger: {
			trigger: section,
			// start when more of the section is visible (closer to center)
			start: 'top 50%',
			toggleActions: 'play none none none',
			invalidateOnRefresh: true,
		},
	})

	// masterTimeline.to(track, {
	// 	xPercent: 0,
	// 	duration: 1,
	// }, 0)

	// animate elements from right -> left when section enters viewport
	if (triangle) {
		masterTimeline.to(triangle, {
			autoAlpha: 1,
			scale: 1,
			rotation: 180,
			duration: 1,
		}, 0.1)
	}

	if (circle) {
		masterTimeline.to(circle, {
			autoAlpha: 1,
			scale: 1,
			duration: 2,
		}, 1)
	}

	if (intro) {
		masterTimeline.fromTo(intro, { x: 96, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: 1 }, 0.2)
	}

	if (actions) {
		masterTimeline.fromTo(actions, { x: -96, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: 1 }, 0.4)
	}

	if (heading) {
		masterTimeline.fromTo(heading, { x: 72, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: 1 }, 0.6)
	}

	if (slides.length) {
		masterTimeline.fromTo(slides, { x: 104, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: 1, stagger: 0 }, 0.8)
	}
}

const initShowEventMotion = () => {
	const sections = toArray(document.querySelectorAll(SECTION_SELECTOR))

	if (!sections.length) {
		return null
	}

	// if (hasReducedMotion()) {
	// 	sections.forEach(section => {
	// 		const previousSection = getPreviousSection(section)
	// 		const { viewport, track, intro, heading, triangle, circle, slides } = getTargets(section)

	// 		if (previousSection) {
	// 			gsap.set(previousSection, {
	// 				clearProps: 'all',
	// 			})
	// 		}

	// 		gsap.set([section, viewport, track, intro, heading, triangle, circle, ...slides].filter(Boolean), {
	// 			clearProps: 'all',
	// 		})
	// 	})

	// 	return null
	// }

	sections.forEach(initSectionMotion)

	window.addEventListener('load', () => {
		ScrollTrigger.refresh()
	}, { once: true })

	return null
}

export default initShowEventMotion
