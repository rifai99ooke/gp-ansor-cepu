@php
    $recordKey = (string) $getRecord()->getKey();
@endphp
<div
    data-sortable-key="{{ $recordKey }}"
    x-sortable-handle
    x-data
    x-init="
        const key = $el.dataset.sortableKey;
        const ensureSortable = () => {
            const tr = $el.closest('tr');
            if (tr && tr.getAttribute('x-sortable-item') !== key) {
                tr.setAttribute('x-sortable-item', key);
            }
        };
        ensureSortable();
        const tr = $el.closest('tr');
        if (tr && typeof MutationObserver !== 'undefined') {
            const obs = new MutationObserver(ensureSortable);
            obs.observe(tr, { attributes: true, attributeFilter: ['x-sortable-item'] });
        }
    "
    x-on:click.stop.prevent
    title="Geser untuk mengubah urutan"
    class="gp-drag-cell"
>
    <button
        type="button"
        tabindex="-1"
        draggable="false"
        class="fi-ta-reorder-handle fi-icon-btn gp-drag-handle-btn"
    >
        <svg class="gp-drag-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="4" x2="20" y1="9" y2="9"/>
            <line x1="4" x2="20" y1="15" y2="15"/>
        </svg>
    </button>
</div>
