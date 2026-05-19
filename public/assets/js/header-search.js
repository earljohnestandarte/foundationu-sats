/**
 * FU-SATS Header Live Search
 * ───────────────────────────
 * Listens to the header search capsule input, debounces keystrokes,
 * fires an AJAX call to /search?q=, and renders a flyout results panel
 * beneath the capsule. Pressing Enter or clicking "See all results"
 * navigates to the full results page.
 */

(function () {
    'use strict';

    /* ── Status badge colours (must match app CSS badge-fu classes) ── */
    const STATUS_MAP = {
        'Open':        { cls: 'open',        label: 'Open'        },
        'In Progress': { cls: 'in-progress', label: 'In Progress' },
        'Pending':     { cls: 'pending',      label: 'Pending'    },
        'Resolved':    { cls: 'resolved',     label: 'Resolved'   },
        'Closed':      { cls: 'closed',       label: 'Closed'     },
    };

    const PRIORITY_COLOURS = {
        Urgent: 'var(--fu-error)',
        High:   'var(--fu-warning)',
        Medium: 'var(--fu-info)',
        Low:    'var(--fu-success)',
    };

    /* ── DOM refs (set on init) ─────────────────────────────────── */
    let searchForm   = null;
    let searchInput  = null;
    let resultsPanel = null;
    let debounceTimer = null;
    let currentXhr   = null;
    let isOpen       = false;

    /* ── Build the flyout panel element once ────────────────────── */
    function buildPanel() {
        const panel = document.createElement('div');
        panel.id        = 'header-search-panel';
        panel.className = 'hs-panel';
        panel.setAttribute('role', 'listbox');
        panel.setAttribute('aria-label', 'Search results');
        document.body.appendChild(panel);
        return panel;
    }

    /* ── Position panel under the search capsule ─────────────────── */
    function positionPanel() {
        if (!searchInput || !resultsPanel) return;
        const rect = searchForm.getBoundingClientRect();
        resultsPanel.style.top   = (rect.bottom + window.scrollY + 6) + 'px';
        resultsPanel.style.left  = rect.left + 'px';
        resultsPanel.style.width = Math.max(rect.width, 420) + 'px';
    }

    /* ── Open / close helpers ────────────────────────────────────── */
    function openPanel(html) {
        resultsPanel.innerHTML = html;
        positionPanel();
        if (!isOpen) {
            resultsPanel.classList.add('hs-open');
            isOpen = true;
        }
    }

    function closePanel() {
        if (resultsPanel) {
            resultsPanel.classList.remove('hs-open');
            isOpen = false;
        }
    }

    /* ── Loading skeleton ────────────────────────────────────────── */
    function showLoading() {
        const rows = Array.from({ length: 3 }, () =>
            `<div class="hs-skeleton"><div class="hs-skel-ref"></div><div class="hs-skel-subject"></div></div>`
        ).join('');
        openPanel(`<div class="hs-loading">${rows}</div>`);
    }

    /* ── Format a single result row ─────────────────────────────── */
    function renderResult(r) {
        const badge  = STATUS_MAP[r.status]  || { cls: 'open', label: r.status };
        const dotClr = PRIORITY_COLOURS[r.priority] || 'var(--fu-outline)';
        const date   = r.updated_at
            ? new Date(r.updated_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })
            : '';
        return `
        <a href="${r.url}" class="hs-result" role="option">
            <div class="hs-result-left">
                <span class="hs-ref">${r.ref}</span>
                <span class="hs-subject">${escHtml(r.subject)}</span>
                <span class="hs-meta">${escHtml(r.department)}</span>
            </div>
            <div class="hs-result-right">
                <span class="badge-fu ${badge.cls}" style="font-size:12px;padding:3px 10px;">${badge.label}</span>
                <span class="hs-dot" style="background:${dotClr}" title="${r.priority}"></span>
                <span class="hs-date">${date}</span>
            </div>
        </a>`;
    }

    /* ── Render full response ────────────────────────────────────── */
    function renderResponse(data, query) {
        if (!data.results || data.results.length === 0) {
            openPanel(`
                <div class="hs-empty">
                    <i class="fas fa-search-minus"></i>
                    <p>No tickets found for <strong>${escHtml(query)}</strong></p>
                </div>`);
            return;
        }

        const rows     = data.results.map(renderResult).join('');
        const moreText = data.total > data.results.length
            ? `${data.total - data.results.length} more — `
            : '';

        openPanel(`
            <div class="hs-header">
                <span class="hs-count">${data.total} result${data.total !== 1 ? 's' : ''} for "<strong>${escHtml(query)}</strong>"</span>
            </div>
            <div class="hs-list">${rows}</div>
            <a href="${data.seeAllUrl}" class="hs-footer">
                ${moreText}See all results <i class="fas fa-arrow-right ms-1"></i>
            </a>`);
    }

    /* ── Fire AJAX search ────────────────────────────────────────── */
    function doSearch(query) {
        if (currentXhr) { currentXhr.abort(); }

        showLoading();

        const xhr = new XMLHttpRequest();
        currentXhr = xhr;
        xhr.open('GET', siteUrl('search') + '?q=' + encodeURIComponent(query), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    renderResponse(data, query);
                } catch (e) {
                    closePanel();
                }
            }
            currentXhr = null;
        };
        xhr.onerror = () => { currentXhr = null; closePanel(); };
        xhr.send();
    }

    /* ── Tiny HTML escaper ───────────────────────────────────────── */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ── Input handler (debounced 280 ms) ────────────────────────── */
    function onInput(e) {
        const q = e.target.value.trim();
        clearTimeout(debounceTimer);

        if (q.length < 2) { closePanel(); return; }

        debounceTimer = setTimeout(() => doSearch(q), 280);
    }

    /* ── Keyboard: Escape closes, Enter submits form ─────────────── */
    function onKeydown(e) {
        if (e.key === 'Escape')  { closePanel(); searchInput.blur(); }
        if (e.key === 'Enter')   { closePanel(); }  // form submit handles navigation
    }

    /* ── Outside-click ───────────────────────────────────────────── */
    function onOutsideClick(e) {
        if (isOpen && !searchForm.contains(e.target) && !resultsPanel.contains(e.target)) {
            closePanel();
        }
    }

    /* ── Reposition on scroll / resize ──────────────────────────── */
    function onResize() { if (isOpen) positionPanel(); }

    /* ── Init ────────────────────────────────────────────────────── */
    function init() {
        searchForm  = document.querySelector('#header-search-form');
        searchInput = document.querySelector('#header-search-input');
        if (!searchForm || !searchInput) return;

        resultsPanel = buildPanel();

        searchInput.addEventListener('input',   onInput);
        searchInput.addEventListener('keydown',  onKeydown);
        searchInput.addEventListener('focus', function () {
            // Re-open if there's already a query
            if (this.value.trim().length >= 2 && !isOpen) doSearch(this.value.trim());
        });

        document.addEventListener('click',  onOutsideClick);
        window.addEventListener('resize',   onResize);
        window.addEventListener('scroll',   onResize, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
