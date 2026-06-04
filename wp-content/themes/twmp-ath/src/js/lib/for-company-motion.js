import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const SECTION_SELECTOR = '.for-company'

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
    window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const getTargets = section => {
    const media = section.querySelector('.for-company__media')
    const title = section.querySelector('.for-company__title')
    const description = section.querySelector('.for-company__description')
    const actions = section.querySelectorAll('.for-company__button')
    const shapes = toArray(section.querySelectorAll('.for-company__shape, .section-shape'))
    // const lights = toArray(section.querySelectorAll('.image__light'))

    return { media, title, description, actions: toArray(actions), shapes }
}

const initSection = section => {
    const { media, title, description, actions, shapes } = getTargets(section)
    if (!title && !description && !media && !actions.length) return

    // if (hasReducedMotion()) {
    //     section.querySelectorAll('*').forEach(el => el.style.opacity = 1)
    //     return
    // }

    // doubled distances/durations consistent with for-school
    gsap.set(section, { autoAlpha: 0 })
    if (title) gsap.set(title, { autoAlpha: 0, y: 80 })
    if (description) gsap.set(description, { autoAlpha: 0, y: 80 })
    if (actions.length) gsap.set(actions, { autoAlpha: 0, y: 48 })
    if (media) gsap.set(media, { autoAlpha: 0, x: 80 })
    if (shapes.length) gsap.set(shapes, { autoAlpha: 0, scale: 0.9 })
    // if (lights && lights.length) gsap.set(lights, { autoAlpha: 0, y: -100 })

    const tl = gsap.timeline({
        defaults: { duration: 1.8, ease: 'power3.out' },
        scrollTrigger: {
            trigger: section,
            start: 'top 80%',
            invalidateOnRefresh: true,
        }
    })

    tl.to(section, { autoAlpha: 1 }, 0)
    if (title) tl.to(title, { autoAlpha: 1, y: 0 }, 0.4)
    if (description) tl.to(description, { autoAlpha: 1, y: 0 }, 0.8)
    if (actions.length) tl.to(actions, { autoAlpha: 1, y: 0, stagger: 0.24 }, 1.2)
    if (media) tl.to(media, { autoAlpha: 1, x: 0, duration: 2.2 }, 0.6)
    if (shapes.length) tl.to(shapes, { autoAlpha: 1, scale: 1, duration: 2.4, stagger: 0.24 }, 0.4)
    // if (lights && lights.length) tl.to(lights, { autoAlpha: 1, y: 0, duration: 1.8, stagger: 0.24 }, 0.2)

    // if (lights && lights.length) {
    //     lights.forEach(l => gsap.to(l, { y: '+=12', repeat: -1, yoyo: true, ease: 'sine.inOut', duration: 12 }))
    // }
}

const initForCompanyMotion = () => {
    const sections = toArray(document.querySelectorAll(SECTION_SELECTOR))
    if (!sections.length) return null

    sections.forEach(initSection)

    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true })

    return null
}

export default initForCompanyMotion
