import './scss/frontend.scss'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import 'swiper/css/grid'

import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

import init from 'lib/init-blocks'
import initCommon from 'lib/common'
import initAboutUsMotion from 'lib/about-us-motion'
import initHeroBannerMotion from 'lib/hero-banner-motion'
import initHeroProductStackMotion from 'lib/hero-product-stack-motion'

initAboutUsMotion()
initHeroBannerMotion()
initHeroProductStackMotion()

document.addEventListener('DOMContentLoaded', () => {
    Fancybox.bind("[data-fancybox]", {});
    initCommon()

    init({
        block: 'blocks'
    }).mount()
})
