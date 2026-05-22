import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const SECTION_SELECTOR = '.show-event'
const PREVIOUS_SECTION_SELECTOR = '.about-us'

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
	const contentLayers = toArray(section.children)
	const intro = section.querySelector('.event-section__intro')
	const heading = section.querySelector('.event-section__heading')
	const sliderWrap = section.querySelector('.event-section__slider-wrap')
	const triangle = section.querySelector('.triangle')
	const circle = section.querySelector('.circle')
	const slides = toArray(section.querySelectorAll('.event-section__swiper .swiper-slide'))

	return {
		contentLayers,
		intro,
		heading,
		sliderWrap,
		triangle,
		circle,
		slides,
	}
}

const initSectionMotion = section => {
	const previousSection = getPreviousSection(section)
	const { contentLayers, intro, heading, triangle, circle, slides } = getTargets(section)

	if (!intro && !heading && !triangle && !circle && !slides.length) {
		return
	}

	gsap.set(section, {
		position: 'relative',
		zIndex: 4,
		autoAlpha: 1,
	})

	if (contentLayers.length) {
		gsap.set(contentLayers, {
			xPercent: 100,
			x: 0,
			y: 0,
		})
	}

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
			scale: 0.5,
			transformOrigin: '50% 50%',
		})
	}

	if (intro) {
		gsap.set(intro, {
			autoAlpha: 0,
			y: 56,
		})
	}

	if (heading) {
		gsap.set(heading, {
			autoAlpha: 0,
			y: 40,
		})
	}

	if (slides.length) {
		gsap.set(slides, {
			autoAlpha: 0,
			x: 104,
		})
	}

	if (previousSection) {
		gsap.set(previousSection, {
			position: 'relative',
			zIndex: 3,
		})

		ScrollTrigger.create({
			trigger: previousSection,
			start: 'top 100px',
			endTrigger: section,
			end: 'top top',
			pin: true,
			pinSpacing: false,
			anticipatePin: 1,
			invalidateOnRefresh: true,
		})
	}

	const masterTimeline = gsap.timeline({
		defaults: {
			ease: 'none',
			overwrite: 'auto',
		},
		scrollTrigger: {
			trigger: section,
			start: 'top -100px',
			end: () => `+=${Math.max(window.innerWidth * 1.4, 1600)}`,
			scrub: 1.4,
			pin: true,
			pinSpacing: true,
			anticipatePin: 1,
			invalidateOnRefresh: true,
		},
	})

	if (contentLayers.length) {
		masterTimeline.to(contentLayers, {
			xPercent: 0,
			duration: 1.5,
			stagger: 0,
		}, 0)
	}

	if (triangle) {
		masterTimeline.to(triangle, {
			autoAlpha: 1,
			scale: 1,
			rotation: 180,
			duration: 0.7,
		}, 0.2)
	}

	if (circle) {
		masterTimeline.to(circle, {
			autoAlpha: 1,
			scale: 1,
			duration: 0.7,
		}, 0.28)
	}

	if (intro) {
		masterTimeline.to(intro, {
			autoAlpha: 1,
			y: 0,
			duration: 0.6,
		}, 0.75)
	}

	if (heading) {
		masterTimeline.to(heading, {
			autoAlpha: 1,
			y: 0,
			duration: 0.6,
		}, 0.95)
	}

	if (slides.length) {
		masterTimeline.to(slides, {
			autoAlpha: 1,
			x: 0,
			duration: 0.75,
			stagger: 0.14,
		}, 1.2)
	}
}

const initShowEventMotion = () => {
	const sections = toArray(document.querySelectorAll(SECTION_SELECTOR))

	if (!sections.length) {
		return null
	}

	if (hasReducedMotion()) {
		sections.forEach(section => {
			const previousSection = getPreviousSection(section)
			const { contentLayers, intro, heading, triangle, circle, slides } = getTargets(section)

			if (previousSection) {
				gsap.set(previousSection, {
					clearProps: 'all',
				})
			}

			gsap.set([section, ...contentLayers, intro, heading, triangle, circle, ...slides].filter(Boolean), {
				clearProps: 'all',
			})
		})

		return null
	}

	sections.forEach(initSectionMotion)

	window.addEventListener('load', () => {
		ScrollTrigger.refresh()
	}, { once: true })

	return null
}

export default initShowEventMotion
