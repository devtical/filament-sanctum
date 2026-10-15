<x-filament::page>
    @if (filled($plainTextToken))
        <x-filament::section
            icon="heroicon-o-exclamation-triangle"
            icon-color="warning"
            class="mb-6"
            wire:key="sanctum-plain-text-token"
        >
            <x-slot name="heading">
                {{ __('Attention needed') }}
            </x-slot>

            <x-slot name="description">
                {{ __("Make sure to copy your new personal access token now. You won't be able to see it again!") }}
            </x-slot>

            <div
                class="mt-4 space-y-3"
                x-data="{
                    token: @js($plainTextToken),
                    copied: false,
                    async copy() {
                        try {
                            await navigator.clipboard.writeText(this.token)
                            this.copied = true
                            return
                        } catch (error) {}

                        const field = this.$refs.token
                        field.focus()
                        field.select()
                        field.setSelectionRange(0, this.token.length)
                        this.copied = document.execCommand('copy')
                    },
                }"
            >
                <p class="font-mono text-sm break-all text-gray-950 dark:text-white">{{ $plainTextToken }}</p>

                <div class="flex items-center gap-2">
                    <x-filament::input.wrapper class="min-w-0 flex-1">
                        <x-filament::input
                            x-ref="token"
                            type="text"
                            readonly
                            autocomplete="off"
                            spellcheck="false"
                            x-bind:value="token"
                            class="font-mono"
                        />
                    </x-filament::input.wrapper>

                    <x-filament::button type="button" color="gray" x-on:click="copy()">
                        <span x-show="! copied">{{ __('Copy') }}</span>
                        <span x-cloak x-show="copied">{{ __('Copied') }}</span>
                    </x-filament::button>

                    <x-filament::button type="button" color="gray" wire:click="forgetPlainTextToken">
                        {{ __('Hide token') }}
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    @endif

    {{ $this->table }}
</x-filament::page>
