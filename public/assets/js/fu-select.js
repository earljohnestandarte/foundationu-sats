/**
 * FuSelect — Custom dropdown component for FU-SATS
 * ─────────────────────────────────────────────────
 * Wraps every .form-select on the page with a fully custom,
 * keyboard-accessible dropdown that keeps the native <select>
 * in sync for form submission.
 *
 * Features:
 *  • Animated open/close with chevron rotation
 *  • Search filtering (shown when > 5 options)
 *  • Keyboard navigation (↑ ↓ Enter Escape)
 *  • Priority colour dots for priority selects
 *  • Status colour dots for status selects
 *  • Closes on outside-click or Escape
 *  • Respects disabled / required on the original <select>
 */

(function () {
    'use strict';

    /* ── Colour map for semantic option decoration ─────────── */
    const PRIORITY_COLOURS = {
        Urgent: 'var(--fu-error)',
        High:   'var(--fu-warning)',
        Medium: 'var(--fu-info)',
        Low:    'var(--fu-success)',
    };

    const STATUS_COLOURS = {
        'Open':        '#1e40af',
        'In Progress': '#92400e',
        'Pending':     '#9d174d',
        'Resolved':    '#15803d',
        'Closed':      '#4b5563',
    };

    /* ── Detect "semantic" selects for colour decorations ───── */
    function getDotColour(value) {
        return PRIORITY_COLOURS[value] || STATUS_COLOURS[value] || null;
    }

    /* ── Build SVG chevron ──────────────────────────────────── */
    function chevronSVG() {
        return `<svg class="fu-select-chevron" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>`;
    }

    /* ── Render a single option element ─────────────────────── */
    function buildOption(nativeOpt, isPlaceholder) {
        const li = document.createElement('li');
        li.className = 'fu-select-option' +
            (nativeOpt.disabled  ? ' disabled-opt'   : '') +
            (isPlaceholder       ? ' placeholder-opt' : '');
        li.dataset.value = nativeOpt.value;
        li.setAttribute('role', 'option');
        li.setAttribute('aria-selected', 'false');

        const dot = getDotColour(nativeOpt.value);
        if (dot) {
            const dotEl = document.createElement('span');
            dotEl.className = 'fu-option-dot';
            dotEl.style.backgroundColor = dot;
            li.appendChild(dotEl);
        }

        li.appendChild(document.createTextNode(nativeOpt.text));
        return li;
    }

    /* ── Core class ─────────────────────────────────────────── */
    class FuSelect {
        constructor(nativeSelect) {
            this.native   = nativeSelect;
            this.wrapper  = null;
            this.trigger  = null;
            this.panel    = null;
            this.list     = null;
            this.search   = null;
            this._focused = -1;   // keyboard-nav cursor
            this._build();
            this._bind();
        }

        /* ── Build DOM ───────────────────────────────────────── */
        _build() {
            const native = this.native;

            // Outer wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'fu-select-wrapper';
            if (native.id)   wrapper.id = 'fu-select-wrap-' + native.id;

            // Inject wrapper before native select, then move native inside
            native.parentNode.insertBefore(wrapper, native);
            wrapper.appendChild(native);

            // Trigger button
            const trigger = document.createElement('button');
            trigger.type      = 'button';
            trigger.className = 'fu-select-trigger' + (native.disabled ? ' disabled' : '');
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            if (native.id) trigger.setAttribute('aria-controls', 'fu-panel-' + native.id);

            const valueSpan = document.createElement('span');
            valueSpan.className = 'fu-select-value';
            trigger.appendChild(valueSpan);
            trigger.insertAdjacentHTML('beforeend', chevronSVG());
            wrapper.appendChild(trigger);

            // Panel
            const panel = document.createElement('div');
            panel.className  = 'fu-select-panel';
            panel.setAttribute('role', 'listbox');
            if (native.id) panel.id = 'fu-panel-' + native.id;

            // Search (show only when > 5 options)
            const hasSearch = native.options.length > 5;
            if (hasSearch) {
                const searchWrap = document.createElement('div');
                searchWrap.className = 'fu-select-search-wrap';
                const searchInput = document.createElement('input');
                searchInput.type        = 'text';
                searchInput.className   = 'fu-select-search';
                searchInput.placeholder = 'Search…';
                searchInput.setAttribute('autocomplete', 'off');
                searchWrap.appendChild(searchInput);
                panel.appendChild(searchWrap);
                this.search = searchInput;
            }

            // Option list
            const list = document.createElement('ul');
            list.className   = 'fu-select-list';
            list.setAttribute('role', 'presentation');

            this._populateList(list, native);
            panel.appendChild(list);
            wrapper.appendChild(panel);

            this.wrapper = wrapper;
            this.trigger = trigger;
            this.valueSpan = valueSpan;
            this.panel  = panel;
            this.list   = list;

            this._syncValue();
        }

        /* ── Populate option <li> elements ───────────────────── */
        _populateList(list, native) {
            list.innerHTML = '';

            // Handle optgroups
            const children = Array.from(native.children);
            children.forEach(child => {
                if (child.tagName === 'OPTGROUP') {
                    const label = document.createElement('li');
                    label.className = 'fu-select-optgroup-label';
                    label.textContent = child.label;
                    list.appendChild(label);
                    Array.from(child.children).forEach(opt => {
                        list.appendChild(buildOption(opt, false));
                    });
                } else {
                    const isPlaceholder = child.value === '' || child.value === null;
                    list.appendChild(buildOption(child, isPlaceholder));
                }
            });
        }

        /* ── Sync display value from native select ───────────── */
        _syncValue() {
            const native   = this.native;
            const selected = native.options[native.selectedIndex];
            const isEmpty  = !selected || selected.value === '';
            this.valueSpan.textContent  = selected ? selected.text : '—';
            this.valueSpan.classList.toggle('placeholder', isEmpty);

            // Mark selected option in list
            this.list.querySelectorAll('.fu-select-option').forEach(li => {
                const sel = li.dataset.value === (selected ? selected.value : '');
                li.classList.toggle('selected', sel);
                li.setAttribute('aria-selected', sel ? 'true' : 'false');
            });
        }

        /* ── Open / Close ────────────────────────────────────── */
        open() {
            if (this.native.disabled) return;
            this.trigger.classList.add('open');
            this.trigger.setAttribute('aria-expanded', 'true');
            this.panel.classList.add('open');
            this._focused = -1;
            if (this.search) {
                this.search.value = '';
                this._filterOptions('');
                requestAnimationFrame(() => this.search.focus());
            }
            // Close other open selects
            document.querySelectorAll('.fu-select-trigger.open').forEach(t => {
                if (t !== this.trigger) t.closest('.fu-select-wrapper')?._fuSelect?.close();
            });
        }

        close() {
            this.trigger.classList.remove('open');
            this.trigger.setAttribute('aria-expanded', 'false');
            this.panel.classList.remove('open');
            this._focused = -1;
        }

        toggle() {
            this.panel.classList.contains('open') ? this.close() : this.open();
        }

        /* ── Select an option by value ───────────────────────── */
        _select(value) {
            this.native.value = value;
            // Trigger change event so other JS reacts
            this.native.dispatchEvent(new Event('change', { bubbles: true }));
            this._syncValue();
            this.close();
            this.trigger.focus();
        }

        /* ── Filter options from search input ────────────────── */
        _filterOptions(query) {
            const q    = query.toLowerCase().trim();
            let visible = 0;

            this.list.querySelectorAll('.fu-select-option').forEach(li => {
                const match = !q || li.textContent.toLowerCase().includes(q);
                li.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            // No-results message
            let noRes = this.list.querySelector('.fu-select-no-results');
            if (visible === 0) {
                if (!noRes) {
                    noRes = document.createElement('li');
                    noRes.className   = 'fu-select-no-results';
                    noRes.textContent = 'No options found';
                    this.list.appendChild(noRes);
                }
                noRes.style.display = '';
            } else if (noRes) {
                noRes.style.display = 'none';
            }
        }

        /* ── Keyboard navigation ─────────────────────────────── */
        _getVisibleOptions() {
            return Array.from(
                this.list.querySelectorAll('.fu-select-option:not([style*="display: none"])')
            );
        }

        _moveFocus(delta) {
            const opts = this._getVisibleOptions();
            if (!opts.length) return;
            opts.forEach(o => o.classList.remove('focused'));
            this._focused = Math.max(0, Math.min(opts.length - 1, this._focused + delta));
            opts[this._focused].classList.add('focused');
            opts[this._focused].scrollIntoView({ block: 'nearest' });
        }

        /* ── Bind events ─────────────────────────────────────── */
        _bind() {
            // Store reference for outside-click cleanup
            this.wrapper._fuSelect = this;

            // Toggle on trigger click
            this.trigger.addEventListener('click', () => this.toggle());

            // Option selection
            this.list.addEventListener('click', e => {
                const li = e.target.closest('.fu-select-option');
                if (!li || li.classList.contains('disabled-opt') ||
                    li.classList.contains('fu-select-optgroup-label')) return;
                this._select(li.dataset.value);
            });

            // Search filtering
            if (this.search) {
                this.search.addEventListener('input', e => {
                    this._filterOptions(e.target.value);
                    this._focused = -1;
                });
                this.search.addEventListener('keydown', e => this._handleKey(e));
            }

            // Keyboard on trigger
            this.trigger.addEventListener('keydown', e => this._handleKey(e));

            // Native select changes from outside (e.g. JS that sets .value directly)
            this.native.addEventListener('change', () => this._syncValue());
        }

        _handleKey(e) {
            const isOpen = this.panel.classList.contains('open');

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (!isOpen) { this.open(); return; }
                    this._moveFocus(1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (!isOpen) { this.open(); return; }
                    this._moveFocus(-1);
                    break;
                case 'Enter':
                case ' ':
                    if (!isOpen) { e.preventDefault(); this.open(); return; }
                    if (this._focused >= 0) {
                        e.preventDefault();
                        const opts = this._getVisibleOptions();
                        if (opts[this._focused]) {
                            this._select(opts[this._focused].dataset.value);
                        }
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.close();
                    this.trigger.focus();
                    break;
                case 'Tab':
                    this.close();
                    break;
            }
        }
    }

    /* ── Global outside-click handler ───────────────────────── */
    document.addEventListener('click', e => {
        if (!e.target.closest('.fu-select-wrapper')) {
            document.querySelectorAll('.fu-select-panel.open').forEach(panel => {
                panel.closest('.fu-select-wrapper')?._fuSelect?.close();
            });
        }
    });

    /* ── Init: replace all .form-select elements ─────────────── */
    function initAll(root) {
        root = root || document;
        root.querySelectorAll('select.form-select').forEach(sel => {
            // Skip if already wrapped
            if (sel.closest('.fu-select-wrapper')) return;
            new FuSelect(sel);
        });
    }

    /* Run on DOM ready */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initAll());
    } else {
        initAll();
    }

    /* Expose for dynamic content (e.g. after AJAX modals load) */
    window.FuSelect = { initAll };

})();
