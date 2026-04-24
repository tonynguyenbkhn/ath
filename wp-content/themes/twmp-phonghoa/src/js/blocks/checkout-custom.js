import { select, getData } from 'lib/dom'

export default async el => {
    console.log(el);
    
    const provinceField = select('#billing_delivery_address_field', el)
    const districtField = select('#billing_district_district_field', el)
    const wardField = select('#billing_wards_and_communes_field', el)

    const provinceWrapper = select('.woocommerce-input-wrapper', provinceField)
    const districtWrapper = select('.woocommerce-input-wrapper', districtField)
    const wardWrapper = select('.woocommerce-input-wrapper', wardField)

    const defaultRadio = el.querySelector('input[name="billing_sexy"][value="male"]')
    if (defaultRadio && !el.querySelector('input[name="billing_sexy"]:checked')) {
        defaultRadio.checked = true
    }

    const defaultDelivery = el.querySelector('input[name="billing_delivery_form"][value="nhận-hàng-tại-nhà"]')
    if (defaultDelivery && !el.querySelector('input[name="billing_delivery_form"]:checked')) {
        defaultDelivery.checked = true
    }

    const settings = getData('settings', el)
        ? JSON.parse(getData('settings', el))
        : {}

    const getRestUrl = (index) => {
        return `${twmpConfig.ajax.restUrl}/${settings.endpoint[index]}`
    }

    // Tạo <select>
    function createSelect(name, id, options, placeholder = '') {
        const selectEl = document.createElement('select')
        selectEl.name = name
        selectEl.id = id
        selectEl.classList.add('select')

        if (placeholder) {
            const option = document.createElement('option')
            option.value = ''
            option.textContent = placeholder
            selectEl.appendChild(option)
        }

        options.forEach(opt => {
            const option = document.createElement('option')
            option.value = opt.name
            option.textContent = opt.name
            if (opt.key) option.dataset.key = opt.key
            if (opt.maqh) option.dataset.maqh = opt.maqh
            if (opt.xaid) option.dataset.xaid = opt.xaid
            selectEl.appendChild(option)
        })

        return selectEl
    }

    // Load tỉnh/thành
    try {
        const res = await fetch(getRestUrl(0))
        const data = await res.json()
        if (data && typeof data === 'object') {
            const options = Object.entries(data).map(([key, name]) => ({ key, name }))

            // Lấy giá trị hiện tại (nếu user đã đăng nhập)
            const currentProvinceInput = provinceWrapper.querySelector('input')
            const currentProvinceValue = currentProvinceInput?.value || ''

            const select = createSelect('billing_delivery_address', 'billing_delivery_address', options)

            provinceWrapper.innerHTML = ''
            provinceWrapper.appendChild(select)

            // Nếu có giá trị hiện tại, tìm và selected nó
            if (currentProvinceValue) {
                const matchedOption = Array.from(select.options).find(opt => opt.value === currentProvinceValue)
                if (matchedOption) {
                    select.value = currentProvinceValue
                    
                    // Tự động load quận/huyện và xã/phường tương ứng
                    ;(async () => {
                        const matp = matchedOption.dataset.key
                        if (!matp) return

                        // Load quận/huyện
                        const res = await fetch(getRestUrl(1) + `?matp=${matp}`)
                        const districts = await res.json()
                        
                        // Lấy giá trị district hiện tại
                        const currentDistrictInput = districtWrapper.querySelector('input')
                        const currentDistrictValue = currentDistrictInput?.value || ''
                        
                        const districtSelect = createSelect('billing_district_district', 'billing_district_district', districts, 'Chọn Quận/Huyện')
                        
                        // Selected district nếu có
                        if (currentDistrictValue) {
                            const matchedDistrictOption = Array.from(districtSelect.options).find(opt => opt.value === currentDistrictValue)
                            if (matchedDistrictOption) {
                                districtSelect.value = currentDistrictValue

                                // Load xã/phường tương ứng
                                ;(async () => {
                                    const maqh = matchedDistrictOption.dataset.maqh
                                    if (!maqh) return

                                    const res = await fetch(getRestUrl(2) + `?maqh=${maqh}`)
                                    const wards = await res.json()
                                    
                                    // Lấy giá trị ward hiện tại
                                    const currentWardInput = wardWrapper.querySelector('input')
                                    const currentWardValue = currentWardInput?.value || ''
                                    
                                    const wardSelect = createSelect('billing_wards_and_communes', 'billing_wards_and_communes', wards, 'Chọn Phường/Xã')
                                    
                                    // Selected ward nếu có
                                    if (currentWardValue) {
                                        wardSelect.value = currentWardValue
                                    }
                                    
                                    wardWrapper.innerHTML = ''
                                    wardWrapper.appendChild(wardSelect)
                                })()
                            }
                        } else {
                            wardWrapper.innerHTML = '<input type="text" name="billing_wards_and_communes" id="billing_wards_and_communes" placeholder="Phường/Xã" class="input-text">'
                        }
                        
                        districtWrapper.innerHTML = ''
                        districtWrapper.appendChild(districtSelect)
                    })()
                }
            }

            select.addEventListener('change', async e => {
                const matp = e.target.selectedOptions[0].dataset.key
                if (!matp) return

                // Load quận/huyện
                const res = await fetch(getRestUrl(1) + `?matp=${matp}`)
                const districts = await res.json()
                const districtSelect = createSelect('billing_district_district', 'billing_district_district', districts, 'Chọn Quận/Huyện')
                districtWrapper.innerHTML = ''
                districtWrapper.appendChild(districtSelect)

                // Reset phường/xã về input
                wardWrapper.innerHTML = '<input type="text" name="billing_wards_and_communes" id="billing_wards_and_communes" placeholder="Phường/Xã" class="input-text">'

                districtSelect.addEventListener('change', async e => {
                    const maqh = e.target.selectedOptions[0].dataset.maqh
                    if (!maqh) return

                    // Load xã/phường
                    const res = await fetch(getRestUrl(2) + `?maqh=${maqh}`)
                    const wards = await res.json()
                    const wardSelect = createSelect('billing_wards_and_communes', 'billing_wards_and_communes', wards, 'Chọn Phường/Xã')
                    wardWrapper.innerHTML = ''
                    wardWrapper.appendChild(wardSelect)
                })
            })
        } else {
            alert(twmpConfig.message.error)
        }
    } catch (error) {
        alert(twmpConfig.message.error)
    }
}
