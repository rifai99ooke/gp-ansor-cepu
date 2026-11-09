<style>
    /* ==========================================================================
       GP Ansor Unified Public & Admin Design System
       ========================================================================== */

    :root {
        --gp-bg-gradient: linear-gradient(135deg, #effaf8 0%, #eef5ff 100%);
        --gp-accent-gradient: linear-gradient(90deg, #16a34a 0%, #10b981 55%, #06b6d4 100%);
        --gp-icon-bg: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        --gp-primary: #16a34a;
        --gp-primary-hover: #15803d;
        --gp-emerald-dark: #047857;
        --gp-emerald-mid: #059669;
        --gp-emerald-soft: #ecfdf5;
        --gp-emerald-border: #a7f3d0;
        --gp-text-dark: #181e2b;
        --gp-text-muted: #64748b;
        --gp-border: #e5e7eb;
        --gp-card-shadow: 0 4px 20px -2px rgba(24, 30, 43, 0.06), 0 2px 6px -1px rgba(24, 30, 43, 0.04);
        --gp-card-shadow-hover: 0 10px 25px -4px rgba(22, 163, 74, 0.12), 0 4px 10px -2px rgba(24, 30, 43, 0.05);
    }

    /* 1. Page Background & Typography */
    body.fi-body {
        background: var(--gp-bg-gradient) !important;
        background-attachment: fixed !important;
        color: var(--gp-text-dark) !important;
        font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, sans-serif !important;
    }

    /* 2. Brand Logo & Topbar Actions — Identical to Public <x-navbar /> */
    .gp-brand {
        display: inline-flex;
        align-items: center;
        gap: 0.625rem;
        white-space: nowrap;
    }

    .gp-brand-logo {
        width: 2rem !important;
        height: 2rem !important;
        object-fit: contain;
        flex-shrink: 0;
        border-radius: 0.375rem;
    }

    .gp-brand-fallback {
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        background-color: var(--gp-primary);
        color: #ffffff;
        font-weight: 700;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .gp-brand-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--gp-text-dark);
        letter-spacing: -0.015em;
        line-height: 1;
    }

    .gp-brand-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        background-color: var(--gp-emerald-soft);
        color: var(--gp-emerald-dark);
        border: 1px solid var(--gp-emerald-border);
        line-height: 1.2;
    }

    .gp-topbar-action-wrap {
        display: inline-flex;
        align-items: center;
        margin-right: 0.5rem;
    }

    .gp-topbar-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.42rem 0.85rem;
        border-radius: 0.5rem;
        background-color: var(--gp-emerald-soft);
        color: var(--gp-emerald-dark);
        border: 1px solid var(--gp-emerald-border);
        font-size: 13px;
        font-weight: 600;
        line-height: 1.25;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .gp-topbar-link:hover {
        background-color: #d1fae5;
        color: #065f46;
    }

    .gp-topbar-icon {
        width: 14px !important;
        height: 14px !important;
        flex-shrink: 0;
    }

    /* 3. Topbar & Sidebar Shell */
    .fi-topbar nav,
    .fi-sidebar-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid var(--gp-border) !important;
        box-shadow: none !important;
        height: 4rem !important;
        min-height: 4rem !important;
    }

    .fi-sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid var(--gp-border) !important;
        box-shadow: 2px 0 16px rgba(24, 30, 43, 0.03) !important;
    }

    .fi-sidebar-nav {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }

    .fi-sidebar-item-btn {
        border-radius: 0.625rem !important;
        padding: 0.55rem 0.85rem !important;
        transition: all 0.15s ease !important;
    }

    .fi-sidebar-item-label {
        font-size: 14px !important;
        font-weight: 500 !important;
        color: #1f2937 !important;
    }

    .fi-sidebar-item-icon {
        color: var(--gp-emerald-mid) !important;
        width: 1.15rem !important;
        height: 1.15rem !important;
    }

    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-btn:hover {
        background-color: rgba(236, 253, 245, 0.75) !important;
    }

    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-btn:hover .fi-sidebar-item-label,
    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-btn:hover .fi-sidebar-item-icon {
        color: var(--gp-emerald-dark) !important;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background-color: var(--gp-emerald-soft) !important;
        box-shadow: inset 0 0 0 1px rgba(167, 243, 208, 0.7) !important;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-label {
        color: var(--gp-emerald-dark) !important;
        font-weight: 600 !important;
    }

    .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
        color: var(--gp-emerald-dark) !important;
    }

    /* 4. Page Headings — Signature GP Ansor Green-to-Cyan Underline */
    .fi-header-heading {
        color: var(--gp-text-dark) !important;
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        position: relative;
        display: inline-block;
        padding-bottom: 0.45rem;
    }

    .fi-header-heading::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 52px;
        height: 3px;
        border-radius: 9999px;
        background: linear-gradient(90deg, #22c55e, #06b6d4);
    }

    /* 5. Sections, Tables & Cards — Match Public Rounded-2xl White Cards */
    .fi-section,
    .fi-ta-ctn,
    .fi-wi-stats-overview-stat {
        background-color: #ffffff !important;
        border-radius: 1rem !important;
        border: 1px solid rgba(229, 231, 235, 0.9) !important;
        box-shadow: var(--gp-card-shadow) !important;
        overflow: hidden;
    }

    .fi-section-header,
    .fi-ta-header-ctn {
        background-color: rgba(249, 250, 251, 0.7) !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .fi-section-header-heading,
    .fi-ta-header-heading {
        color: var(--gp-text-dark) !important;
        font-weight: 700 !important;
        font-size: 16px !important;
    }

    .fi-ta-header-cell {
        background-color: #f8fafc !important;
        color: #334155 !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.03em !important;
    }

    .fi-ta-row:hover {
        background-color: rgba(236, 253, 245, 0.4) !important;
    }

    /* 6. Buttons & Form Inputs — Identical to Public Buttons & Form Fields */
    .fi-btn-color-primary,
    .fi-ac-btn-action[data-color="primary"] {
        background-color: var(--gp-primary) !important;
        border-radius: 0.625rem !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 6px rgba(22, 163, 74, 0.2) !important;
        transition: all 0.15s ease !important;
    }

    .fi-btn-color-primary:hover {
        background-color: var(--gp-primary-hover) !important;
    }

    .fi-input-wrp {
        border-radius: 0.75rem !important;
        background-color: #f9fafb !important;
        transition: all 0.15s ease !important;
    }

    .fi-input-wrp:focus-within {
        background-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15) !important;
    }

    .fi-resource-posts .fi-fo-rich-editor .tiptap > p + p {
        margin-top: 0;
    }

    /* 7. Login Page (.fi-simple-layout) — Identical to Public Registration Card */
    .fi-simple-layout {
        background: var(--gp-bg-gradient) !important;
    }

    .fi-simple-main {
        background-color: #ffffff !important;
        border-radius: 1.25rem !important;
        border: 1px solid var(--gp-border) !important;
        box-shadow: 0 12px 32px -4px rgba(24, 30, 43, 0.08) !important;
        position: relative;
        overflow: hidden;
    }

    .fi-simple-main::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gp-accent-gradient);
    }

    /* 8. Custom Dashboard Hero & Public-Identical Stat Cards */
    .gp-dashboard-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .gp-hero-card {
        background: #ffffff;
        border-radius: 1rem;
        border: 1px solid rgba(229, 231, 235, 0.9);
        box-shadow: var(--gp-card-shadow);
        overflow: hidden;
    }

    .gp-hero-accent {
        height: 5px;
        width: 100%;
        background: var(--gp-accent-gradient);
    }

    .gp-hero-body {
        padding: 1.5rem 1.75rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        justify-content: space-between;
    }

    @media (min-width: 1024px) {
        .gp-hero-body {
            flex-direction: row;
            align-items: center;
        }
    }

    .gp-hero-info {
        display: flex;
        align-items: center;
        gap: 1.125rem;
    }

    .gp-hero-logo-box {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 0.875rem;
        background: var(--gp-icon-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid #a7f3d0;
    }

    .gp-hero-logo {
        width: 2.25rem !important;
        height: 2.25rem !important;
        object-fit: contain;
    }

    .gp-hero-logo-fallback {
        font-weight: 800;
        color: var(--gp-emerald-dark);
        font-size: 1rem;
    }

    .gp-hero-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.65rem;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        background-color: var(--gp-emerald-soft);
        color: var(--gp-emerald-dark);
        border: 1px solid var(--gp-emerald-border);
        margin-bottom: 0.35rem;
    }

    .gp-hero-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--gp-text-dark);
        line-height: 1.25;
        letter-spacing: -0.015em;
    }

    .gp-hero-subtitle {
        margin-top: 0.25rem;
        font-size: 13.5px;
        color: #4b5563;
        line-height: 1.5;
        max-width: 40rem;
    }

    .gp-hero-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.625rem;
        flex-shrink: 0;
    }

    .gp-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.58rem 1.05rem;
        border-radius: 0.65rem;
        background-color: var(--gp-primary);
        color: #ffffff;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(22, 163, 74, 0.22);
        transition: all 0.15s ease;
    }

    .gp-btn-primary:hover {
        background-color: var(--gp-primary-hover);
        transform: translateY(-1px);
    }

    .gp-btn-soft {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.58rem 1rem;
        border-radius: 0.65rem;
        background-color: var(--gp-emerald-soft);
        color: var(--gp-emerald-dark);
        border: 1px solid var(--gp-emerald-border);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .gp-btn-soft:hover {
        background-color: #d1fae5;
    }

    .gp-btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.58rem 1rem;
        border-radius: 0.65rem;
        background-color: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .gp-btn-outline:hover {
        background-color: #f9fafb;
        border-color: #9ca3af;
    }

    .gp-stats-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .gp-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1280px) {
        .gp-stats-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    .gp-stat-card {
        background: #ffffff;
        border-radius: 0.875rem;
        border: 1px solid rgba(229, 231, 235, 0.9);
        box-shadow: var(--gp-card-shadow);
        padding: 1.2rem 1.3rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 0.85rem;
        text-decoration: none;
        position: relative;
        transition: all 0.2s ease;
    }

    .gp-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--gp-card-shadow-hover);
        border-color: #a7f3d0;
    }

    .gp-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .gp-stat-icon-box {
        width: 2.65rem;
        height: 2.65rem;
        border-radius: 0.75rem;
        background: var(--gp-icon-bg);
        color: var(--gp-emerald-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .gp-stat-content {
        width: 100%;
        min-width: 0;
    }

    .gp-stat-label {
        font-size: 13px;
        font-weight: 600;
        color: #4b5563;
        line-height: 1.3;
    }

    .gp-stat-value {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--gp-text-dark);
        line-height: 1.2;
        margin-top: 0.2rem;
    }

    .gp-stat-slash {
        font-weight: 500;
        color: #9ca3af;
        font-size: 1.2rem;
    }

    .gp-stat-desc {
        font-size: 12.5px;
        color: #6b7280;
        margin-top: 0.25rem;
        line-height: 1.35;
    }

    .gp-stat-action {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.55rem;
        border-radius: 0.5rem;
        background-color: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        color: var(--gp-emerald-mid);
        flex-shrink: 0;
        transition: all 0.15s ease;
    }

    .gp-stat-card:hover .gp-stat-action {
        background-color: var(--gp-emerald-soft);
        color: var(--gp-emerald-dark);
    }

    .gp-status-open {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .gp-status-closed {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        background-color: #f3f4f6;
        color: #4b5563;
        border: 1px solid #e5e7eb;
    }

    .gp-icon-sm {
        width: 16px !important;
        height: 16px !important;
        flex-shrink: 0;
    }

    .gp-icon-md {
        width: 22px !important;
        height: 22px !important;
        flex-shrink: 0;
    }

    /* 9. Always-visible Drag Handle Column (=) */
    .fi-ta-cell-drag-handle {
        padding: 0 !important;
        width: 48px !important;
        user-select: none !important;
        -webkit-user-select: none !important;
    }

    .fi-ta-cell-drag-handle .fi-ta-col {
        padding: 0 !important;
        height: 100%;
    }

    .gp-drag-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 3.25rem;
        padding: 0.5rem;
        cursor: grab;
        user-select: none;
        -webkit-user-select: none;
        touch-action: none;
    }

    .gp-drag-cell:active {
        cursor: grabbing;
    }

    .gp-drag-handle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.15rem;
        height: 2.15rem;
        border-radius: 0.5rem;
        color: #64748b;
        cursor: inherit;
        pointer-events: none;
        transition: all 0.15s ease;
    }

    .gp-drag-cell:hover .gp-drag-handle-btn {
        background-color: var(--gp-emerald-soft);
        color: var(--gp-emerald-dark);
    }

    .gp-drag-icon {
        width: 20px !important;
        height: 20px !important;
        pointer-events: none;
    }

    /* Hide the default ↑↓ reorder toggle button and collapse empty table header toolbars */
    [wire\:click="toggleTableReordering"] {
        display: none !important;
    }

    .fi-ta-header-ctn:not(:has(.fi-ta-search-field, .fi-ta-header-heading, .fi-ta-actions, .fi-ta-filters-trigger-action-ctn)) {
        display: none !important;
    }
</style>

<script>
    (function () {
        const syncSortableRows = (root = document) => {
            root.querySelectorAll('[data-sortable-key]').forEach((el) => {
                const tr = el.closest('tr');
                if (tr && tr.getAttribute('x-sortable-item') !== el.dataset.sortableKey) {
                    tr.setAttribute('x-sortable-item', el.dataset.sortableKey);
                }
            });
        };

        document.addEventListener('DOMContentLoaded', () => syncSortableRows());
        document.addEventListener('livewire:navigated', () => syncSortableRows());

        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updating', ({ el, toEl }) => {
                if (!el || !toEl) {
                    return;
                }

                if (el.tagName === 'TBODY' && el.classList.contains('fi-ta-records')) {
                    const liveRows = new Map();
                    Array.from(el.children).forEach((child) => {
                        if (child.tagName === 'TR' && child.hasAttribute('wire:key')) {
                            liveRows.set(child.getAttribute('wire:key'), child);
                        }
                    });

                    if (liveRows.size > 0) {
                        const fragment = document.createDocumentFragment();
                        Array.from(toEl.childNodes).forEach((node) => {
                            if (node.nodeType === Node.ELEMENT_NODE && node.tagName === 'TR') {
                                const handle = node.querySelector('[data-sortable-key]');
                                if (handle) {
                                    node.setAttribute('x-sortable-item', handle.dataset.sortableKey);
                                }

                                const key = node.getAttribute('wire:key');
                                const liveTr = key ? liveRows.get(key) : null;
                                if (liveTr) {
                                    if (handle) {
                                        liveTr.setAttribute('x-sortable-item', handle.dataset.sortableKey);
                                    }
                                    liveRows.delete(key);
                                    fragment.appendChild(liveTr);
                                    return;
                                }
                            }
                            fragment.appendChild(node.cloneNode(true));
                        });
                        el.replaceChildren(fragment);
                    }
                }

                if (toEl.tagName === 'TR') {
                    const handle = toEl.querySelector('[data-sortable-key]');
                    if (handle) {
                        toEl.setAttribute('x-sortable-item', handle.dataset.sortableKey);
                    }
                }
            });

            Livewire.hook('morph.updated', ({ el }) => {
                if (el && el.tagName === 'TR') {
                    const handle = el.querySelector('[data-sortable-key]');
                    if (handle) {
                        el.setAttribute('x-sortable-item', handle.dataset.sortableKey);
                    }
                }
            });

            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    queueMicrotask(() => syncSortableRows());
                    requestAnimationFrame(() => syncSortableRows());
                });
            });
        });
    })();
</script>
