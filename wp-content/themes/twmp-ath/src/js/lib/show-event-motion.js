import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const SECTION_SELECTOR = '.show-event'
const PREVIOUS_SECTION_SELECTOR = '.about-us'
const HORIZONTAL_SCROLL_DISTANCE = 800

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
	window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const createProgressLogger = label => {
	let previousProgress = -1

	return self => {
		const progress = Math.round(self.progress * 100)

		if (progress !== previousProgress && progress % 10 === 0) {
			previousProgress = progress
			console.log(`[${label}] progress`, {
				progress,
				start: self.start,
				end: self.end,
				scroll: self.scroll(),
			})
		}
	}
}

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
	const heading = section.querySelector('.event-section__heading')
	const triangle = section.querySelector('.section-shape--triangle')
	const circle = section.querySelector('.section-shape--circle')
	const slides = toArray(section.querySelectorAll('.event-section__swiper .swiper-slide'))

	return {
		viewport,
		track,
		intro,
		heading,
		triangle,
		circle,
		slides,
	}
}

const initSectionMotion = section => {
	const previousSection = getPreviousSection(section)
	const { viewport, track, intro, heading, triangle, circle, slides } = getTargets(section)

	if (!viewport || !track || (!intro && !heading && !triangle && !circle && !slides.length)) {
		return
	}

	gsap.set(section, {
		position: 'relative',
		zIndex: 4,
		autoAlpha: 1,
	})

	gsap.set(viewport, {
		position: 'relative',
		overflow: 'hidden',
	})

	gsap.set(track, {
		xPercent: 100,
		x: 0,
	})

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
			x: 96,
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

	if (previousSection) {
		gsap.set(previousSection, {
			position: 'relative',
			zIndex: 3,
		})

		ScrollTrigger.create({
			trigger: previousSection,
			start: 'top 80px',
			end: "+=2200",
			pin: true,
			pinSpacing: false,
			anticipatePin: 1,
			invalidateOnRefresh: true,
			onEnter: self => {
				console.log('[about-us pin] enter', {
					start: self.start,
					end: self.end,
					scroll: self.scroll(),
				})
			},
			onLeave: self => {
				console.log('[about-us pin] leave', {
					start: self.start,
					end: self.end,
					scroll: self.scroll(),
				})
			},
			onEnterBack: self => {
				console.log('[about-us pin] enter back', {
					start: self.start,
					end: self.end,
					scroll: self.scroll(),
				})
			},
			onLeaveBack: self => {
				console.log('[about-us pin] leave back', {
					start: self.start,
					end: self.end,
					scroll: self.scroll(),
				})
			},
			onUpdate: createProgressLogger('about-us pin'),
		})
	}

	const masterTimeline = gsap.timeline({
		defaults: {
			ease: 'none',
			overwrite: 'auto',
		},
		scrollTrigger: {
			trigger: viewport,
			start: 'top -100px',
			end: "+=2000",
			scrub: 1.4,
			pin: true,
			pinSpacing: true,
			anticipatePin: 1,
			invalidateOnRefresh: true,
			onEnter: self => {
				console.log('[show-event viewport] enter', {
					start: self.start,
					end: self.end,
					scroll: self.scroll(),
				})
			},
			onLeave: self => {
				console.log('[show-event viewport] leave', {
					start: self.start,
					end: self.end,
					scroll: self.scroll(),
				})
			},
			onEnterBack: self => {
				console.log('[show-event viewport] enter back', {
					start: self.start,
					end: self.end,
					scroll: self.scroll(),
				})
			},
			onLeaveBack: self => {
				console.log('[show-event viewport] leave back', {
					start: self.start,
					end: self.end,
					scroll: self.scroll(),
				})
			},
			onUpdate: createProgressLogger('show-event viewport'),
		},
	})

	masterTimeline.to(track, {
		xPercent: 0,
		duration: 1,
	}, 0)

	if (triangle) {
		masterTimeline.to(triangle, {
			autoAlpha: 1,
			scale: 1,
			rotation: 180,
			duration: 0.5,
		}, 0.2)
	}

	if (circle) {
		masterTimeline.to(circle, {
			autoAlpha: 1,
			scale: 1,
			duration: 0.5,
		}, 0.2)
	}

	if (intro) {
		masterTimeline.to(intro, {
			autoAlpha: 1,
			x: 0,
			duration: 0.5,
		}, 0.4)
	}

	if (heading) {
		masterTimeline.to(heading, {
			autoAlpha: 1,
			x: 0,
			duration: 0.5,
		}, 0.6)
	}

	if (slides.length) {
		masterTimeline.to(slides, {
			autoAlpha: 1,
			x: 0,
			duration: 0.5,
			stagger: 0.1,
		}, 0.8)
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
