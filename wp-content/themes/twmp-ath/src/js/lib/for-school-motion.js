import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const SECTION_SELECTOR = '.for-school'

const toArray = value => Array.from(value || [])

const hasReducedMotion = () =>
    window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false

const getTargets = section => {
    const header = section.querySelector('.for-school__header')
    const title = section.querySelector('.for-school__title')
    const description = section.querySelector('.for-school__description')
    const actions = section.querySelectorAll('.for-school__button')
    const media = section.querySelector('.for-school__media-primary')
    const shapes = toArray(section.querySelectorAll('.for-school__shape'))

    return { header, title, description, actions: toArray(actions), media, shapes }
}

const initSection = section => {
    const { header, title, description, actions, media, shapes } = getTargets(section)
    if (!title && !description && !media && !actions.length) return

    // if (hasReducedMotion()) {
    //     section.querySelectorAll('*').forEach(el => el.style.opacity = 1)
    //     return
    // }

    gsap.set(section, { autoAlpha: 0 })
    if (header) gsap.set(header, { autoAlpha: 0, y: 100 })
    if (title) gsap.set(title, { autoAlpha: 0, y: 100 })
    if (description) gsap.set(description, { autoAlpha: 0, y: 100 })
    if (actions.length) gsap.set(actions, { autoAlpha: 0, y: 100 })
    if (media) gsap.set(media, { autoAlpha: 0, x: 100 })
    if (shapes.length) gsap.set(shapes, { autoAlpha: 0, scale: 0 })

    const tl = gsap.timeline({
        defaults: { duration: 1.2, ease: 'power3.out' },
        scrollTrigger: {
            trigger: section,
            start: 'top 80%',
            invalidateOnRefresh: true,
        }
    })

    tl.to(section, { autoAlpha: 1 }, 0)
    if (header) tl.to(header, { autoAlpha: 1, y: 0 }, 0.1)
    if (title) tl.to(title, { autoAlpha: 1, y: 0 }, 0.25)
    if (description) tl.to(description, { autoAlpha: 1, y: 0 }, 0.45)
    if (actions.length) tl.to(actions, { autoAlpha: 1, y: 0, stagger: 0.12 }, 0.6)
    if (media) tl.to(media, { autoAlpha: 1, x: 0, duration: 1.2 }, 0.4)
    if (shapes.length) tl.to(shapes, { autoAlpha: 1, scale: 1, duration: 1.2, stagger: 0.12 }, 0.8)
}

const initForSchoolMotion = () => {
    const sections = toArray(document.querySelectorAll(SECTION_SELECTOR))
    if (!sections.length) return null

    sections.forEach(initSection)

    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true })

    return null
}

export default initForSchoolMotion
