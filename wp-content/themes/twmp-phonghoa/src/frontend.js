import './scss/frontend.scss'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

import init from 'lib/init-blocks'
import { matchHeightMobile } from 'lib/utils'

document.addEventListener('DOMContentLoaded', () => {
    Fancybox.bind("[data-fancybox]", {});

    const selectors = [
        ['.mobile-scroll .row', ':scope > div .mobile-same-height'],
        ['.product-scroll-wrapper .products', ':scope > li'],
        ['.yith-similar-products .products', ':scope > li']
    ]

    function applyAllMatchHeight() {
        selectors.forEach(([container, target]) => {
            matchHeightMobile(container, target)
        })
    }

    window.addEventListener('DOMContentLoaded', applyAllMatchHeight)
    window.addEventListener('resize', applyAllMatchHeight)

    init({
        block: 'blocks'
    }).mount()
})
