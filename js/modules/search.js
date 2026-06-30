/**
 * DFC Search — WP Engine Smart Search AJAX
 * Adapted from PLGC theme for DFC.
 */
(function () {
    'use strict';

    var searchToggle  = document.querySelector('.js-search-toggle');
    var searchPanel   = document.getElementById('dfc-search-panel');
    var searchInput   = searchPanel ? searchPanel.querySelector('.dfc-search-form__input') : null;
    var resultsBox    = document.getElementById('dfc-search-results');
    var iconSearch    = searchToggle ? searchToggle.querySelector('.nav__search-icon--search') : null;
    var iconClose     = searchToggle ? searchToggle.querySelector('.nav__search-icon--close')  : null;

    var REST_BASE = (window.dfcSearch && window.dfcSearch.restUrl) ? window.dfcSearch.restUrl : '/wp-json/wp/v2/';
    var searchTimeout = null, activeRequest = null, currentQuery = '';

    function openSearch() {
        if (!searchPanel) return;
        searchPanel.removeAttribute('hidden');
        if (searchToggle) { searchToggle.setAttribute('aria-expanded', 'true'); searchToggle.setAttribute('aria-label', 'Close search'); }
        if (iconSearch) iconSearch.style.display = 'none';
        if (iconClose)  iconClose.style.display  = 'block';
        if (searchInput) { searchInput.focus(); searchInput.select(); }
    }

    function closeSearch() {
        if (!searchPanel) return;
        searchPanel.setAttribute('hidden', '');
        if (searchToggle) { searchToggle.setAttribute('aria-expanded', 'false'); searchToggle.setAttribute('aria-label', 'Open search'); }
        if (iconSearch) iconSearch.style.display = '';
        if (iconClose)  iconClose.style.display  = 'none';
        clearResults();
        searchToggle && searchToggle.focus();
    }

    function clearResults() {
        if (!resultsBox) return;
        resultsBox.innerHTML = '';
        resultsBox.setAttribute('hidden', '');
    }

    searchToggle && searchToggle.addEventListener('click', function () {
        searchPanel && !searchPanel.hasAttribute('hidden') ? closeSearch() : openSearch();
    });

    searchPanel && searchPanel.addEventListener('keydown', function (e) { if (e.key === 'Escape') { e.preventDefault(); closeSearch(); } });

    document.addEventListener('click', function (e) {
        if (searchPanel && !searchPanel.hasAttribute('hidden') && !searchPanel.contains(e.target) && e.target !== searchToggle && !searchToggle.contains(e.target)) closeSearch();
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = searchInput.value.trim();
            if (q === currentQuery) return;
            currentQuery = q;
            clearTimeout(searchTimeout);
            if (q.length < 2) { clearResults(); return; }
            showStatus('Searching\u2026');
            searchTimeout = setTimeout(function () { runSearch(q); }, 300);
        });
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' && resultsBox && !resultsBox.hasAttribute('hidden')) {
                e.preventDefault();
                var first = resultsBox.querySelector('.dfc-search-results__item a');
                if (first) first.focus();
            }
        });
    }

    if (searchPanel) {
        var form = searchPanel.querySelector('.dfc-search-form');
        form && form.addEventListener('submit', function (e) {
            e.preventDefault();
            var q = searchInput ? searchInput.value.trim() : '';
            if (q.length >= 2) { clearTimeout(searchTimeout); runSearch(q); }
            else if (searchInput) searchInput.focus();
        });
    }

    function runSearch(q) {
        if (activeRequest) activeRequest.abort && activeRequest.abort();
        var controller = new AbortController();
        activeRequest = controller;

        var contentUrl = REST_BASE + 'search?' + new URLSearchParams({ search: q, per_page: 10, _fields: 'id,title,url,subtype,type' }).toString() + '&type=post&subtype=any';
        var mediaUrl   = REST_BASE + 'media?'  + new URLSearchParams({ search: q, per_page: 4, media_type: 'application', _fields: 'id,title,source_url,link,mime_type' }).toString();

        Promise.all([
            fetch(contentUrl, { signal: controller.signal }).then(function (r) { return r.ok ? r.json() : []; }),
            fetch(mediaUrl,   { signal: controller.signal }).then(function (r) { return r.ok ? r.json() : []; }),
        ]).then(function (results) {
            var pages = results[0], rawDocs = results[1];
            var fileExt = /\.(pdf|doc|docx|xls|xlsx|csv|ppt|pptx|zip|txt)(\?|$)/i;
            var validDocs = (rawDocs || []).filter(function (d) { return d.source_url && fileExt.test(d.source_url) && d.mime_type && d.mime_type.startsWith('application/'); });
            var titles = new Set((pages || []).map(function (p) { var t = p.title && p.title.rendered ? p.title.rendered : (p.title || ''); return stripHtml(t).toLowerCase().trim(); }));
            var docs = validDocs.filter(function (d) { var t = d.title && d.title.rendered ? d.title.rendered : (d.title || ''); return !titles.has(stripHtml(t).toLowerCase().trim()); });
            renderResults(q, pages || [], docs);
        }).catch(function (err) { if (err.name !== 'AbortError') showStatus('Search unavailable. Press Enter to search.'); });
    }

    var CATEGORY_MAP = { page: 'Pages', post: 'News' };
    var ICONS = {
        page: '<svg class="dfc-search-results__item-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>',
        document: '<svg class="dfc-search-results__item-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/></svg>',
    };
    var FUEL_KEYWORDS = ['fuel', 'jet', 'avgas', 'av gas', 'price', 'gallon', 'retail'];
    var fuelData = (window.dfcSearch && window.dfcSearch.fuelData) || {};

    function isFuelQuery(q) {
        var lower = q.toLowerCase();
        return FUEL_KEYWORDS.some(function (kw) { return lower.indexOf(kw) !== -1; });
    }

    function fuelCard() {
        if (!fuelData.jet_retail && !fuelData.avgas_retail) return '';
        var date = fuelData.effective_date || '';
        var url = fuelData.fuel_url || '/fuel-prices/';
        var html = '<div class="dfc-search-fuel-card">';
        html += '<span class="dfc-search-results__group-label">Current Fuel Prices</span>';
        html += '<div class="dfc-search-fuel-card__inner">';
        if (fuelData.jet_retail) html += '<div class="dfc-search-fuel-card__item"><span class="dfc-search-fuel-card__label">Jet A Retail</span><span class="dfc-search-fuel-card__price">' + esc(fuelData.jet_retail) + '</span></div>';
        if (fuelData.avgas_retail) html += '<div class="dfc-search-fuel-card__item"><span class="dfc-search-fuel-card__label">AvGas Retail</span><span class="dfc-search-fuel-card__price">' + esc(fuelData.avgas_retail) + '</span></div>';
        html += '</div>';
        if (date) html += '<p class="dfc-search-fuel-card__date">Effective ' + esc(date) + '</p>';
        html += '<a class="dfc-search-fuel-card__link" href="' + esc(url) + '">View all fuel prices &amp; discounts \u2192</a>';
        html += '</div>';
        return html;
    }

    function renderResults(q, pages, docs) {
        if (!resultsBox) return;

        var fuelHtml = isFuelQuery(q) ? fuelCard() : '';

        if (pages.length + docs.length === 0 && !fuelHtml) { showStatus('No results found for \u201c' + esc(q) + '\u201d.'); return; }

        var groups = {}, order = Object.keys(CATEGORY_MAP), html = fuelHtml;
        pages.forEach(function (item) { var s = item.subtype || 'page'; if (!groups[s]) groups[s] = []; groups[s].push(item); });

        order.forEach(function (k) { if (!groups[k] || !groups[k].length) return; html += '<span class="dfc-search-results__group-label">' + esc(CATEGORY_MAP[k] || k) + '</span><ul class="dfc-search-results__list" role="list">'; groups[k].forEach(function (i) { html += resultItem(i, 'content'); }); html += '</ul>'; });
        Object.keys(groups).forEach(function (k) { if (order.includes(k) || !groups[k].length) return; html += '<span class="dfc-search-results__group-label">' + esc(k.charAt(0).toUpperCase() + k.slice(1).replace(/_/g,' ')) + '</span><ul class="dfc-search-results__list" role="list">'; groups[k].forEach(function (i) { html += resultItem(i, 'content'); }); html += '</ul>'; });

        if (docs.length) { html += '<span class="dfc-search-results__group-label">Documents</span><ul class="dfc-search-results__list" role="list">'; docs.slice(0,4).forEach(function (i) { html += resultItem(i, 'document'); }); html += '</ul>'; }

        html += '<div class="dfc-search-results__footer"><a href="/?s=' + encodeURIComponent(q) + '">See all results for \u201c' + esc(q) + '\u201d \u2192</a></div>';
        resultsBox.innerHTML = html;
        resultsBox.removeAttribute('hidden');
        wireKeys();
    }

    function resultItem(item, type) {
        var title = item.title && item.title.rendered ? item.title.rendered : (item.title || 'Untitled');
        var url = item.source_url || item.url || item.link || '#';
        var label = '', mime = item.mime_type || '';
        if (type === 'document') { if (mime.includes('pdf')) label = 'PDF'; else if (mime.includes('word')) label = 'Word'; else if (mime.includes('excel') || mime.includes('sheet')) label = 'Spreadsheet'; else label = 'Document'; }
        var tgt = type === 'document' ? ' target="_blank" rel="noopener noreferrer"' : '';
        var icon = type === 'document' ? ICONS.document : ICONS.page;
        var lbl = label ? '<span class="dfc-search-results__item-type">' + esc(label) + '</span>' : '';
        return '<li class="dfc-search-results__item"><a href="' + esc(url) + '"' + tgt + '>' + icon + '<span class="dfc-search-results__item-text"><span class="dfc-search-results__item-title">' + esc(stripHtml(title)) + '</span>' + lbl + '</span></a></li>';
    }

    function showStatus(msg) { if (!resultsBox) return; resultsBox.innerHTML = '<p class="dfc-search-results__status">' + esc(msg) + '</p>'; resultsBox.removeAttribute('hidden'); }

    function wireKeys() {
        if (!resultsBox) return;
        var links = Array.from(resultsBox.querySelectorAll('a'));
        links.forEach(function (link, idx) {
            link.addEventListener('click', function (e) { e.stopPropagation(); });
            link.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); if (links[idx+1]) links[idx+1].focus(); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); if (idx === 0) searchInput && searchInput.focus(); else links[idx-1].focus(); }
                else if (e.key === 'Escape') closeSearch();
            });
        });
    }

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function stripHtml(s) { var t = document.createElement('div'); t.innerHTML = s; return t.textContent || t.innerText || s; }
})();
