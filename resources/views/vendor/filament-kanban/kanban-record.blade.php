<div
    id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
    class="record bg-white dark:bg-gray-700 rounded-lg px-4 py-2 cursor-grab font-medium text-gray-600 dark:text-gray-200"
    @if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}) < 3)
        x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        "
    @endif
>
    {{-- <div class="">
        {{ $record->{static::$recordTitleAttribute} }}
    </div> --}}

    <div class="mb-1 flex justify-between">
        <span class="text-xs text-gray-400">
            # {{ $record->id }}
        </span>

        @if ($record->price)
            <span class="text-sm text-gray-400">
                R$ {{ $record->display_price }}
            </span>
        @endif
    </div>

    <div class="text-md">
        {{ $record->contact->name }}
    </div>

    @if ($record->substage)
        <div class="mt-2 text-sm text-gray-400">
            {{ $record->substage->name }}
        </div>
    @endif

    <div class="mt-3 text-sm">
        {{ $record->display_current_user }}
    </div>

    <div class="mt-3 flex">
        <span class="text-xs me-4">
            <span class="text-gray-400">Cadastro</span> <br/>
            {{ $record->updated_at->format('d/m/Y H:i') }}
        </span>

        <span class="text-xs">
            <span class="text-gray-400">Últ. atualização</span> <br/>
            {{ $record->updated_at->format('d/m/Y H:i') }}
        </span>
    </div>
</div>
