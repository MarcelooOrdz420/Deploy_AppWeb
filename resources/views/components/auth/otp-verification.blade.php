{{-- Integra este bloque en tu vista actual con: @include('components.auth.otp-verification', ['email' => $user->email]) --}}
<div
    id="otpVerification"
    data-email="{{ $email ?? '' }}"
    data-verify-url="/api/v1/auth/verify-otp"
    data-resend-url="/api/v1/auth/resend-otp"
    style="max-width:420px;padding:24px;border:1px solid #e5e7eb;border-radius:18px;background:#ffffff;"
>
    <h3 style="margin:0 0 8px;font-size:22px;">Verifica tu correo</h3>
    <p style="margin:0 0 18px;color:#6b7280;">Ingresa el codigo de 6 digitos enviado a tu email.</p>

    <form id="otpForm">
        <div style="display:flex;gap:10px;justify-content:space-between;margin-bottom:16px;">
            @for($i = 0; $i < 6; $i++)
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    data-otp-input
                    style="width:54px;height:58px;text-align:center;font-size:24px;border:1px solid #d1d5db;border-radius:12px;"
                >
            @endfor
        </div>

        <button
            type="submit"
            style="width:100%;padding:12px 16px;border:0;border-radius:12px;background:#111827;color:#fff;font-weight:700;cursor:pointer;"
        >
            Verificar codigo
        </button>
    </form>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;gap:12px;">
        <button
            type="button"
            id="otpResendBtn"
            style="border:0;background:none;color:#2563eb;font-weight:700;cursor:pointer;padding:0;"
        >
            Reenviar codigo
        </button>
        <span id="otpCooldown" style="color:#6b7280;font-size:14px;"></span>
    </div>

    <p id="otpMessage" style="margin:14px 0 0;font-size:14px;color:#374151;"></p>
</div>

<script>
(() => {
    const root = document.getElementById('otpVerification');
    if (!root) return;

    const verifyUrl = root.dataset.verifyUrl;
    const resendUrl = root.dataset.resendUrl;
    const email = root.dataset.email;
    const form = root.querySelector('#otpForm');
    const resendBtn = root.querySelector('#otpResendBtn');
    const cooldownLabel = root.querySelector('#otpCooldown');
    const message = root.querySelector('#otpMessage');
    const inputs = Array.from(root.querySelectorAll('[data-otp-input]'));
    let cooldown = 0;
    let cooldownTimer = null;

    const startCooldown = (seconds = 60) => {
        cooldown = seconds;
        resendBtn.disabled = true;
        clearInterval(cooldownTimer);
        cooldownTimer = setInterval(() => {
            cooldownLabel.textContent = cooldown > 0 ? `Reenviar en ${cooldown}s` : '';
            if (cooldown <= 0) {
                clearInterval(cooldownTimer);
                resendBtn.disabled = false;
                return;
            }
            cooldown -= 1;
        }, 1000);
    };

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            if (input.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const code = inputs.map(input => input.value).join('');

        if (code.length !== 6) {
            message.textContent = 'Completa los 6 digitos del codigo.';
            return;
        }

        const res = await fetch(verifyUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, code }),
        });

        const data = await res.json();
        message.textContent = data.message || 'No se pudo verificar el codigo.';
        message.style.color = res.ok ? '#166534' : '#b91c1c';
    });

    resendBtn.addEventListener('click', async () => {
        const res = await fetch(resendUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email }),
        });

        const data = await res.json();
        message.textContent = data.message || 'No se pudo reenviar el codigo.';
        message.style.color = res.ok ? '#166534' : '#b91c1c';

        if (res.ok) {
            startCooldown(60);
        }
    });

    startCooldown(60);
})();
</script>
