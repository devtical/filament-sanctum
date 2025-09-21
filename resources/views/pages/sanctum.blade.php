<x-filament::page>
  @if($sanctumToken = session('sanctum-token'))
  <x-filament::section  icon="heroicon-o-exclamation-triangle" icon-color="warning" class="mb-6">
    <x-slot name="heading">{{ __('Attention needed') }}</x-slot>
    <x-slot name="description">
      {{ __("Make sure to copy your new personal access token now. You won't be able to see it again!") }}
    </x-slot>

    <x-filament::input.wrapper class="mt-4">
        <x-filament::input id="sanctum-token" type="text" :value="$sanctumToken" class="w-full rounded-l-md font-mono text-sm" readonly/>
        <x-slot name="suffix">
          <x-filament::icon-button icon="heroicon-m-clipboard" label="{{ __('Copy') }}" onclick="copyToken(event)"/>
        </x-slot>
      </x-filament::input.wrapper>
  </x-filament::section>

  <script>
    function copyToken(event) {
      const tokenInput = document.getElementById('sanctum-token');
      tokenInput.select();
      tokenInput.setSelectionRange(0, 99999);
      document.execCommand('copy');
    }
  </script>
  @endif

  {{ $this->table }}
</x-filament::page>