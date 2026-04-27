// import {
//     on,
//     select,
//     selectAll,
//     closest,
//     hasClass,
//     addClass,
//     removeClass,
//     toggleClass,
// } from '../lib/dom';

// export default el => {
//     const input = select('#search-input', el),
//         clearBtn = select('#clear-btn', el),
//         searchBtn = select('#search-btn', el),
//         suggestions = select('#suggestions', el),
//         resultBox = select('#search-results', el),
//         body = document.body;

//     const keywordList = ["Tivi", "Tủ lạnh", "Máy giặt", "Điều hòa", "Máy lọc nước", "Quạt điện", "Nồi cơm", "Loa bluetooth"];

//     const renderSuggestions = (filtered = keywordList) => {
//         suggestions.innerHTML = '';
//         filtered.forEach(keyword => {
//             const div = document.createElement("div");
//             div.textContent = keyword;
//             suggestions.appendChild(div);
//         });
//         toggleClass('active', suggestions, filtered.length > 0);
//     };

//     on('focus', () => renderSuggestions(), input);

//     on('input', () => {
//         const keyword = input.value.toLowerCase().trim();
//         const filtered = keywordList.filter(k => k.toLowerCase().includes(keyword));
//         renderSuggestions(filtered);
//     }, input);

//     on('click', e => {
//         if (e.target.tagName === 'DIV') {
//             input.value = e.target.textContent;
//             removeClass('active', suggestions);
//             fetchResults(input.value);
//         }
//     }, suggestions);

//     on('click', e => {
//         if (!closest('.search-container', e.target)) {
//             removeClass('active', suggestions);
//         }
//     }, body);

//     on('click', () => {
//         input.value = '';
//         input.focus();
//         removeClass('active', suggestions);
//         resultBox.innerHTML = '';
//     }, clearBtn);

//     on('click', () => {
//         const query = input.value.trim();
//         if (query) fetchResults(query);
//     }, searchBtn);

//     const fetchResults = (query) => {
//         const url = `https://demo.taiwebmienphi.com/phonghoa/wp-json/ywcas/v1/search?query=${encodeURIComponent(query)}&lang=en_US&category=0&showCategories=false&maxResults=5&page=0&_locale=user`;
//         resultBox.innerHTML = "<p>Đang tìm kiếm...</p>";

//         fetch(url)
//             .then(res => res.json())
//             .then(data => displayResults(data))
//             .catch(err => {
//                 resultBox.innerHTML = `<p style="color: red;">Lỗi: ${err.message}</p>`;
//             });
//     };

//     const displayResults = (data) => {
//         const products = data.results || [];
//         const related = (data.related_content && data.related_content.results) || [];

//         let html = '';

//         if (products.length) {
//             html += `<div class="result-section"><h3>Sản phẩm</h3>`;
//             products.forEach(item => {
//                 html += `
// 					<div class="result-item">
// 						<img src="${item.thumbnail?.small}" alt="${item.name}" />
// 						<div>
// 							<a href="${item.url}" target="_blank"><strong>${item.name}</strong></a>
// 							<div style="color:#d92121;">${formatPrice(item.min_price, item.max_price)}</div>
// 						</div>
// 					</div>`;
//             });
//             html += `</div>`;
//         }

//         if (related.length) {
//             html += `<div class="result-section"><h3>Bài viết liên quan</h3>`;
//             related.forEach(item => {
//                 html += `
// 					<div class="result-item">
// 						<img src="${extractImgSrc(item.thumbnail?.small)}" alt="${item.name}" />
// 						<div>
// 							<a href="${item.url}" target="_blank"><strong>${item.name}</strong></a>
// 						</div>
// 					</div>`;
//             });
//             html += `</div>`;
//         }

//         resultBox.innerHTML = html || `<p>Không tìm thấy kết quả.</p>`;
//     };

//     const formatPrice = (min, max) => {
//         min = parseFloat(min);
//         max = parseFloat(max);
//         if (!min && !max) return '';
//         if (min === max) return min.toLocaleString('vi-VN') + 'đ';
//         return `${min.toLocaleString('vi-VN')} - ${max.toLocaleString('vi-VN')}đ`;
//     };

//     const extractImgSrc = (html) => {
//         const match = html?.match(/src="([^"]+)"/);
//         return match ? match[1] : '';
//     };
// };

