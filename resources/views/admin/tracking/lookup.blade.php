@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4 lookup-page">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="lookup-title mb-0">{{ __('tracking.lookup.title') }}</h3>
        @if(request()->filled('q'))
            <a href="{{ route('admin.tracking.lookup') }}" class="btn btn-sm btn-light border" title="Clear search">
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

    <form method="GET" action="{{ route('admin.tracking.lookup') }}" class="lookup-search mb-4">
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
    </form>

    @if(request()->filled('q'))
        @if(($cases ?? collect())->isNotEmpty())
            <section class="lookup-results" aria-labelledby="lookup-results-title">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 id="lookup-results-title" class="mb-0">Results</h5>
                    <span class="result-count">{{ $cases->count() }}</span>
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
                                        <div>{!! $highlight($item->current_section) !!}</div>
                                        @if($item->currentHolder?->name)
                                            <div class="table-secondary-text">{!! $highlight($item->currentHolder->name) !!}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.tracking.timeline', $item) }}" class="btn btn-sm btn-outline-brand" title="{{ __('tracking.lookup.timeline') }}">
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
            <div class="lookup-empty text-center py-5">
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
    .lookup-title { font-size: 1.35rem; font-weight: 650; color: #1f2937; }
    .lookup-search { max-width: 760px; }
    .lookup-search .form-control,
    .lookup-search .input-group-text,
    .lookup-search .btn { min-height: 46px; }
    .lookup-search .form-control:focus { box-shadow: none; border-color: #ced4da; }
    .lookup-search:focus-within .input-group { box-shadow: 0 0 0 .2rem rgba(0, 40, 77, .12); border-radius: .375rem; }
    .lookup-results { border-top: 1px solid #e5e7eb; padding-top: 1rem; }
    .lookup-results h5 { font-size: 1rem; font-weight: 650; color: #374151; }
    .result-count { min-width: 28px; padding: .15rem .45rem; border-radius: 4px; background: #eef2f6; color: #4b5563; font-size: .8rem; text-align: center; }
    .lookup-table { font-size: .9rem; }
    .lookup-table thead th { padding: .65rem .75rem; border-bottom-width: 1px; color: #6b7280; font-size: .75rem; font-weight: 600; text-transform: uppercase; }
    .lookup-table tbody td { padding: .75rem; border-color: #edf0f2; }
    .file-reference { font-weight: 650; color: #111827; }
    .file-barcode, .table-secondary-text { margin-top: .15rem; color: #6b7280; font-size: .8rem; }
    .lookup-empty { color: #6b7280; border-top: 1px solid #e5e7eb; }
    .lookup-empty .bi { font-size: 1.75rem; color: #9ca3af; }
    .btn-brand { background: #00284d; color: #fff; border-color: #00284d; }
    .btn-brand:hover { background: #001e3a; color: #fff; border-color: #001e3a; }
    .btn-outline-brand { color: #00284d; border-color: #00284d; }
    .btn-outline-brand:hover { color: #fff; background: #00284d; border-color: #00284d; }
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
