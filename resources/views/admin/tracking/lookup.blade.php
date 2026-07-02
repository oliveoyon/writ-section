@extends('admin.layouts.adminlayout')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">{{ __('tracking.lookup.title') }}</h3>
    @php
        $searchTerm = trim((string) request('q', ''));
        $highlight = function (?string $value) use ($searchTerm): string {
            if ($value === null || $value === '') {
                return '-';
            }

            if ($searchTerm === '') {
                return e($value);
            }

            $pattern = '/' . preg_quote($searchTerm, '/') . '/iu';
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

    <form method="GET" action="{{ route('admin.tracking.lookup') }}" class="card p-3 mb-3 shadow-sm border-0">
        <label for="q" class="form-label">{{ __('tracking.lookup.search_label') }}</label>
        <div class="position-relative">
            <div class="d-flex gap-2">
                <input
                    type="text"
                    id="q"
                    name="q"
                    class="form-control"
                    value="{{ request('q') }}"
                    autocomplete="off"
                    placeholder="Case no, permanent barcode, temp barcode, petitioner, lawyer..."
                    required
                >
                <button type="submit" class="btn btn-brand">{{ __('tracking.lookup.search') }}</button>
            </div>
            <div id="smartSuggest" class="list-group position-absolute w-100 mt-1 d-none" style="z-index: 1050;"></div>
        </div>
        <small class="text-muted mt-2">Type at least 3 characters for live suggestions.</small>
    </form>

    @if ($case)
        <div class="card p-3 mb-3 border-0 shadow-sm">
            <h5 class="mb-2">{{ __('tracking.lookup.current_location') }}</h5>
            <p class="mb-1"><strong>{{ __('tracking.lookup.case') }}:</strong> {{ $case->case_reference ?? __('tracking.lookup.na') }}</p>
            <p class="mb-1"><strong>{{ __('tracking.lookup.permanent_barcode') }}:</strong> {{ $case->permanent_barcode ?? __('tracking.lookup.na') }}</p>
            <p class="mb-1"><strong>{{ __('tracking.lookup.current_section') }}:</strong> {{ $case->current_section ?? __('tracking.lookup.na') }}</p>
            <p class="mb-3"><strong>{{ __('tracking.lookup.responsible_person') }}:</strong> {{ $case->currentHolder?->name ?? __('tracking.lookup.na') }}</p>
            <a href="{{ route('admin.tracking.timeline', $case) }}" class="btn btn-outline-brand">{{ __('tracking.lookup.timeline') }}</a>
        </div>
    @endif

    @if(request()->filled('q'))
        @if(($cases ?? collect())->isNotEmpty())
            <div class="card p-3 border-0 shadow-sm">
                <h5 class="mb-3">Search Results ({{ $cases->count() }})</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Case</th>
                                <th>Permanent Barcode</th>
                                <th>Temporary Barcode</th>
                                <th>Petitioner</th>
                                <th>Lawyer</th>
                                <th>Section</th>
                                <th>Responsible</th>
                                <th width="90">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cases as $item)
                                <tr>
                                    <td>{{ $item->case_reference ?? ('CASE-' . $item->id) }}</td>
                                    <td>{!! $highlight($item->permanent_barcode) !!}</td>
                                    <td>{!! $highlight($item->temporary_barcode) !!}</td>
                                    <td>{!! $highlight($item->petitioners->first()?->name_or_organization) !!}</td>
                                    <td>{!! $highlight($item->lawyer?->full_name) !!}</td>
                                    <td>{!! $highlight($item->current_section) !!}</td>
                                    <td>{!! $highlight($item->currentHolder?->name) !!}</td>
                                    <td>
                                        <a href="{{ route('admin.tracking.timeline', $item) }}" class="btn btn-sm btn-outline-brand">Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-warning">{{ __('tracking.lookup.not_found') }}</div>
        @endif
    @endif
</div>
@endsection

@push('css')
<style>
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
