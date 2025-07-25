<section>
    @if($this->enabled && $this->showingQrCode)
        <div class="mt-4 p-2 inline-block bg-white">
            {!! $this->getUser()->twoFactorQrCodeSvg() !!}
        </div>

        <div class="mt-4 max-w-xl text-sm text-gray-600">
            <p class="font-semibold">
                {{ __('Setup Key') }}: {{ decrypt($this->getUser()->two_factor_secret) }}
            </p>
        </div>
    @elseif ($this->showingRecoveryCodes)
        <div class="mt-4 space-y-4">
            <div class="max-w-xl text-sm">
                <p class="font-semibold">
                    Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.
                </p>
            </div>

            <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm border border-gray-500 rounded-lg">
                @foreach (json_decode(decrypt($this->getUser()->two_factor_recovery_codes), true) as $code)
                    <div>{{ $code }}</div>
                @endforeach
            </div>
        </div>
    @endif
</section>
