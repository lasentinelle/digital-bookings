@props([
    'name',
    'clients',
    'selected' => null,
    'placeholder' => 'Search clients...',
    'required' => false,
    'dispatchEvent' => null,
])

<div
    x-data="combobox({
        items: @js($clients->map(fn ($c) => ['id' => (int) $c->id, 'label' => $c->company_name])->values()),
        selectedId: @js($selected !== null ? (int) $selected : null),
        eventName: @js($dispatchEvent),
    })"
    x-on:click.outside="open = false"
    class="relative"
>
    <input type="hidden" name="{{ $name }}" :value="selectedId ?? ''" @if ($required) required @endif />
    <input
        type="text"
        x-model="query"
        @focus="open = true"
        @keydown.escape.prevent="open = false"
        @keydown.arrow-down.prevent="highlightNext()"
        @keydown.arrow-up.prevent="highlightPrev()"
        @keydown.enter.prevent="selectHighlighted()"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-400 focus:ring-4 focus:ring-gray-100"
    />
    <ul
        x-show="open && filtered.length"
        x-cloak
        class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
    >
        <template x-for="(item, index) in filtered" :key="item.id">
            <li
                @mousedown.prevent="select(item)"
                @mouseenter="highlightedIndex = index"
                :class="index === highlightedIndex ? 'bg-gray-100' : ''"
                class="cursor-pointer px-4 py-2 text-sm text-gray-900"
                x-text="item.label"
            ></li>
        </template>
    </ul>
    <p x-show="open && !filtered.length && query.length > 0" x-cloak class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-500 shadow-lg">
        No matches for "<span x-text="query"></span>"
    </p>
</div>
