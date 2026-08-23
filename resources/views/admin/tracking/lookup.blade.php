@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 lookup-page">
    <div class="lookup-header mb-3">
        <div>
            <div class="system-mark">RTFTS Lookup</div>
            <h4 class="mb-0">{{ auth()->user()->name }}: {{ __('tracking.lookup.title') }}</h4>
            <small>Search by barcode, case number, party or lawyer</small>
        </div>
        @if(request()->filled('q'))
            <a href="{{ route('admin.tracking.lookup') }}" class="btn btn-sm btn-outline-brand" title="Clear search">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
                <span class="visually-hidden">Clear search</span>
            </a>
        @endif
    </div>
    @php
        $searchTerm = trim((string) request('q', ''));
        $highlight = function (?string $value) use ($searchTerm): string {
            if ($value === null || $value === '') {
                return '-';
            }

            if ($searchTerm === '') {
                return e($value);
            }

            $pattern = '/(' . preg_quote($searchTerm, '/') . ')/iu';
            $parts = preg_split($pattern, $value, -1, PREG_SPLIT_DELIM_CAPTURE);
            if ($parts === false) {
                return e($value);
            }

            $out = '';
            foreach ($parts as $i => $part) {
                $escaped = e($part);
                $out .= ($i % 2 === 1) ? ('<mark>' . $escaped . '</mark>') : $escaped;
            }

            return $out;
        };
    @endphp

    <form method="GET" action="{{ route('admin.tracking.lookup') }}" class="lookup-search admin-panel mb-3">
        <div class="panel-heading">
            <div>
                <h5>{{ __('tracking.lookup.search') }}</h5>
                <span>{{ request()->filled('q') ? request('q') : 'Enter at least 3 characters for suggestions' }}</span>
            </div>
        </div>
        <div class="panel-body">
        <label for="q" class="visually-hidden">{{ __('tracking.lookup.search_label') }}</label>
        <div class="position-relative lookup-search-box">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-upc-scan" aria-hidden="true"></i>
                </span>
                <input
                    type="text"
                    id="q"
                    name="q"
                    class="form-control border-start-0 ps-0"
                    value="{{ request('q') }}"
                    autocomplete="off"
                    placeholder="Barcode, case number, petitioner or lawyer"
                    required
                >
                <button type="submit" class="btn btn-brand px-3" title="{{ __('tracking.lookup.search') }}">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span class="visually-hidden">{{ __('tracking.lookup.search') }}</span>
                </button>
            </div>
            <div id="smartSuggest" class="list-group position-absolute w-100 mt-1 d-none" style="z-index: 1050;"></div>
        </div>
        </div>
    </form>

    @if(request()->filled('q'))
        @if(($cases ?? collect())->isNotEmpty())
            <section class="lookup-results admin-panel" aria-labelledby="lookup-results-title">
                <div class="panel-heading">
                    <div>
                        <h5 id="lookup-results-title">Results</h5>
                        <span>{{ $cases->count() }} match(es)</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table lookup-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Parties</th>
                                <th>Current custody</th>
                                <th class="text-end"><span class="visually-hidden">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cases as $item)
                                <tr>
                                    <td>
                                        <div class="file-reference">{{ $item->case_reference ?? ('CASE-' . $item->id) }}</div>
                                        <div class="file-barcode">{!! $highlight($item->permanent_barcode) !!}</div>
                                    </td>
                                    <td>
                                        <div>{!! $highlight($item->petitioners->first()?->name_or_organization) !!}</div>
                                        @if($item->lawyer?->full_name)
                                            <div class="table-secondary-text">{!! $highlight($item->lawyer->full_name) !!}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $custody = $item->current_section;
                                            if (strtolower((string) $custody) === 'court' && $item->latestMovement?->court) {
                                                $custody = 'Court (' . $item->latestMovement->court->displayName() . ')';
                                            }
                                        @endphp
                                        <div><span class="custody-badge">{!! $highlight($custody) !!}</span></div>
                                        @if($item->currentHolder?->name)
                                            <div class="table-secondary-text">{!! $highlight($item->currentHolder->name) !!}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.tracking.timeline', $item) }}" class="btn btn-action" title="{{ __('tracking.lookup.timeline') }}">
                                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                            <span class="visually-hidden">{{ __('tracking.lookup.timeline') }}</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            <div class="lookup-empty admin-panel text-center py-5">
                <i class="bi bi-file-earmark-x" aria-hidden="true"></i>
                <p class="mb-0 mt-2">{{ __('tracking.lookup.not_found') }}</p>
            </div>
        @endif
    @endif
</div>
@endsection

@push('css')
<style>
    .lookup-page { max-width: 1100px; }
    .lookup-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .85rem 1rem;
        background: #fff;
        border: 1px solid #e3e8ef;
        border-top: 3px solid #00284d;
        border-bottom-color: #d4a017;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 40, 77, .08);
    }
    .lookup-header h4 { color: #00284d; font-size: 1.15rem; font-weight: 800; }
    .lookup-header small { color: #6b7280; font-weight: 600; }
    .system-mark { color: #b87d08; font-size: .82rem; font-weight: 800; }
    .admin-panel { background: #fff; border: 1px solid #e3e8ef; border-radius: 4px; box-shadow: 0 1px 5px rgba(0, 40, 77, .07); overflow: visible; }
    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem 1rem;
        background: #fbfcfe;
        border-top: 3px solid #00284d;
        border-bottom: 1px solid #e5e7eb;
    }
    .panel-heading h5 { margin: 0; color: #1f2937; font-size: 1rem; font-weight: 800; }
    .panel-heading span { color: #6b7280; font-size: .84rem; font-weight: 600; }
    .panel-body { padding: 1rem; }
    .lookup-search { max-width: 860px; }
    .lookup-search .form-control,
    .lookup-search .input-group-text,
    .lookup-search .btn { min-height: 52px; }
    .lookup-search .input-group { border: 2px solid #0f766e; border-radius: 4px; box-shadow: 0 0 0 4px rgba(15, 118, 110, .1); }
    .lookup-search .input-group-text,
    .lookup-search .form-control,
    .lookup-search .btn { border: 0; }
    .lookup-search .form-control:focus { box-shadow: none; }
    .lookup-search:focus-within .input-group { border-color: #0b5f59; box-shadow: 0 0 0 5px rgba(15, 118, 110, .18); }
    .lookup-results { overflow: hidden; }
    .lookup-table { font-size: .9rem; }
    .lookup-table thead th { padding: .65rem .75rem; background: #eef5fb; color: #00284d; border-bottom: 0; font-size: .78rem; font-weight: 800; }
    .lookup-table tbody td { padding: .75rem; border-color: #edf0f2; }
    .file-reference { font-weight: 800; color: #111827; }
    .file-barcode, .table-secondary-text { margin-top: .15rem; color: #6b7280; font-size: .8rem; }
    .custody-badge { display: inline-flex; border-radius: 4px; border: 1px solid #d8e3ef; background: #f7fbff; color: #0b4f8a; font-size: .78rem; font-weight: 800; padding: .18rem .45rem; }
    .lookup-empty { color: #6b7280; }
    .lookup-empty .bi { font-size: 1.75rem; color: #9ca3af; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; border-radius: 4px; font-weight: 800; }
    .btn-outline-brand:hover { color: #fff; background: #00284d; border-color: #00284d; }
    .btn-action { width: 2rem; height: 2rem; display: inline-grid; place-items: center; padding: 0; border-radius: 4px; border: 1px solid #bcd0e2; color: #0b4f8a; background: #f2f7fc; }
    .btn-action:hover { background: #0b4f8a; color: #fff; }
    #smartSuggest .list-group-item {
        cursor: pointer;
        border-color: #e5e7eb;
    }
    #smartSuggest .item-title {
        font-weight: 700;
        color: #111827;
        font-size: .95rem;
    }
    #smartSuggest .item-subtitle {
        color: #6b7280;
        font-size: .84rem;
    }
    #smartSuggest .list-group-item.active {
        background: #00284d;
        border-color: #00284d;
        color: #fff;
    }
    #smartSuggest .list-group-item.active .item-title,
    #smartSuggest .list-group-item.active .item-subtitle {
        color: #fff;
    }
    #smartSuggest mark {
        background: #fde68a;
        padding: 0 2px;
    }
    .lookup-table mark { background: #fff1a8; padding: 0 1px; }
    @media (max-width: 767.98px) {
        .lookup-page { padding-top: 1rem !important; }
        .lookup-header { align-items: stretch; flex-direction: column; }
        .lookup-header .btn { width: 100%; }
        .lookup-search { max-width: none; }
        .lookup-table { min-width: 690px; }
    }
</style>
@endpush

@push('js')
<script>
    (function () {
        const input = document.getElementById('q');
        const box = document.getElementById('smartSuggest');
        const suggestUrl = @json(route('admin.tracking.lookup.suggest'));
        let debounceTimer = null;
        let activeIndex = -1;

        function escapeHtml(str) {
            return String(str)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function closeSuggest() {
            box.classList.add('d-none');
            box.innerHTML = '';
            activeIndex = -1;
        }

        function escapeRegex(str) {
            return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function highlightHtml(text, term) {
            if (!term) return escapeHtml(text ?? '');
            const safe = escapeHtml(text ?? '');
            const pattern = new RegExp(`(${escapeRegex(term)})`, 'ig');
            return safe.replace(pattern, '<mark>$1</mark>');
        }

        function getItems() {
            return Array.from(box.querySelectorAll('.list-group-item'));
        }

        function setActive(index) {
            const items = getItems();
            items.forEach((el, i) => el.classList.toggle('active', i === index));
            activeIndex = index;
        }

        async function fetchSuggest(term) {
            const res = await fetch(`${suggestUrl}?q=${encodeURIComponent(term)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return [];
            const data = await res.json();
            return Array.isArray(data.items) ? data.items : [];
        }

        input.addEventListener('input', function () {
            const term = this.value.trim();
            clearTimeout(debounceTimer);

            if (term.length < 3) {
                closeSuggest();
                return;
            }

            debounceTimer = setTimeout(async () => {
                const items = await fetchSuggest(term);
                if (!items.length) {
                    closeSuggest();
                    return;
                }

                box.innerHTML = items.map(item => `
                    <a href="${escapeHtml(item.url)}" class="list-group-item list-group-item-action">
                        <div class="item-title">${highlightHtml(item.title || '', term)}</div>
                        <div class="item-subtitle">${highlightHtml(item.subtitle || '', term)}</div>
                    </a>
                `).join('');
                box.classList.remove('d-none');
                activeIndex = -1;

                getItems().forEach((itemEl, idx) => {
                    itemEl.addEventListener('mouseenter', () => setActive(idx));
                });
            }, 220);
        });

        document.addEventListener('click', function (e) {
            if (!box.contains(e.target) && e.target !== input) {
                closeSuggest();
            }
        });

        input.addEventListener('keydown', function (e) {
            const items = getItems();
            const hasSuggest = !box.classList.contains('d-none') && items.length > 0;

            if (hasSuggest && e.key === 'ArrowDown') {
                e.preventDefault();
                const next = activeIndex >= items.length - 1 ? 0 : activeIndex + 1;
                setActive(next);
                return;
            }

            if (hasSuggest && e.key === 'ArrowUp') {
                e.preventDefault();
                const next = activeIndex <= 0 ? items.length - 1 : activeIndex - 1;
                setActive(next);
                return;
            }

            if (hasSuggest && e.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    window.location.href = items[activeIndex].getAttribute('href');
                }
                return;
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                closeSuggest();
            }
        });
    })();
</script>
@endpush
