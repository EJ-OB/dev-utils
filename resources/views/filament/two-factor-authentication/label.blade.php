<div class="space-y-2">
    @if ($this->enabled)
        @if ($this->showingConfirmation)
            {{ 'Finish enabling two factor authentication.' }}
        @else
            {{ 'You have enabled two factor authentication.' }}
        @endif
    @else
        {{ 'You have not enabled two factor authentication.' }}
    @endif

    <p class="text-sm text-gray-500">
        When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
    </p>

    @if($this->enabled && $this->showingQrCode)
        <p class="text-sm font-medium">
            To finish enabling two factor authentication, scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code.
        </p>
    @endif
</div>
