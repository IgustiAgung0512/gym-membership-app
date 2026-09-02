<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Masuk · GymPulse</title>

    @vite(['resources/css/app.css', 'resources/css/login.css'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet">
</head>

<body class="login-page">
    <div class="login-background" aria-hidden="true">
        <div class="glow glow-one"></div>
        <div class="glow glow-two"></div>
        <div class="background-grid"></div>
        <div class="floating-dot dot-one"></div>
        <div class="floating-dot dot-two"></div>
        <div class="floating-dot dot-three"></div>
    </div>

    <main class="login-container">

        <div class="login-brand">
            <div class="login-logo-wrapper">
                <div class="login-logo">
                    G
                </div>
                <span class="logo-pulse"></span>
            </div>

            <div class="login-brand-text">
                <span class="login-brand-name">
                    GymPulse
                </span>

                <span class="login-brand-subtitle">
                    SMART GYM MANAGEMENT
                </span>
            </div>
        </div>

        {{-- LOGIN CARD --}}
        <section class="login-card">
            <div class="card-accent"></div>

            {{-- HEADER --}}
            <div class="login-header">

                <span class="login-eyebrow">
                    <span class="eyebrow-dot"></span>
                    GYM MANAGEMENT SYSTEM
                </span>

                <h1>
                    Selamat datang kembali
                </h1>

                <p>
                    Masuk untuk mengelola membership, absensi,
                    dan aktivitas gym kamu.
                </p>

            </div>


            {{-- ERROR --}}
            @if ($errors->any())

                <div class="login-alert">

                    <div class="login-alert-icon">
                        !
                    </div>

                    <div class="login-alert-content">
                        <strong>Login gagal</strong>
                        <span>{{ $errors->first() }}</span>
                    </div>

                </div>

            @endif


            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('login') }}"
                class="login-form"
                id="loginForm"
            >

                @csrf


                {{-- EMAIL --}}
                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <div class="input-wrapper">

                        <div class="input-icon">
                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                <path d="m3 7 9 6 9-6"></path>
                            </svg>
                        </div>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            required
                            autofocus
                        >

                        <div class="input-check" id="emailCheck">
                            ✓
                        </div>

                    </div>

                </div>


                {{-- PASSWORD --}}
                <div class="form-group">

                    <div class="form-label-row">

                        <label for="password">
                            Password
                        </label>

                    </div>

                    <div class="input-wrapper">

                        <div class="input-icon">

                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect x="4" y="10" width="16" height="11" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            </svg>

                        </div>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="passwordToggle"
                            aria-label="Tampilkan password"
                        >

                            <svg
                                class="eye-open"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>

                            <svg
                                class="eye-closed"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="m3 3 18 18"></path>
                                <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a18 18 0 0 1-3 3.8"></path>
                                <path d="M6.2 6.2C3.4 8.3 2 12 2 12s3.5 7 10 7c1.3 0 2.5-.3 3.6-.8"></path>
                            </svg>

                        </button>

                    </div>

                </div>


                {{-- OPTIONS --}}
                <div class="login-options">

                    <label class="remember-me">

                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                        >

                        <span class="custom-checkbox">
                            <span>✓</span>
                        </span>

                        <span class="remember-text">
                            Ingat saya
                        </span>

                    </label>

                </div>


                {{-- BUTTON --}}
                <button
                    type="submit"
                    class="login-button"
                    id="loginButton"
                >

                    <span class="button-content">

                        <span class="button-text">
                            Masuk ke Dashboard
                        </span>

                        <span class="button-loading">
                            Memproses...
                        </span>

                        <span class="login-button-arrow">
                            →
                        </span>

                    </span>

                </button>

            </form>


            {{-- SECURITY INFO --}}
            <div class="login-security">

                <span class="security-icon">
                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 3 5 6v5c0 4.5 2.9 8.3 7 10 4.1-1.7 7-5.5 7-10V6l-7-3Z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </span>

                <span>
                    Data kamu dilindungi dengan aman
                </span>

            </div>

        </section>


        {{-- FOOTER --}}
        <footer class="login-footer">

            <span>
                © {{ date('Y') }} GymPulse
            </span>

            <span class="footer-separator">
                •
            </span>

            <span>
                Smart Gym Management System
            </span>

        </footer>

    </main>


    {{-- LOGIN INTERACTION --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const passwordInput =
                document.getElementById('password');

            const passwordToggle =
                document.getElementById('passwordToggle');

            const emailInput =
                document.getElementById('email');

            const emailCheck =
                document.getElementById('emailCheck');

            const loginForm =
                document.getElementById('loginForm');

            const loginButton =
                document.getElementById('loginButton');


            /* =========================================
               PASSWORD TOGGLE
            ========================================= */

            passwordToggle.addEventListener('click', function () {

                const isPassword =
                    passwordInput.type === 'password';

                passwordInput.type =
                    isPassword ? 'text' : 'password';

                passwordToggle.classList.toggle(
                    'is-visible',
                    isPassword
                );

                passwordToggle.setAttribute(
                    'aria-label',
                    isPassword
                        ? 'Sembunyikan password'
                        : 'Tampilkan password'
                );

            });


            /* =========================================
               EMAIL VALIDATION VISUAL
            ========================================= */

            emailInput.addEventListener('input', function () {

                const value =
                    emailInput.value.trim();

                const valid =
                    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

                emailCheck.classList.toggle(
                    'show',
                    valid
                );

            });


            /* =========================================
               BUTTON LOADING
            ========================================= */

            loginForm.addEventListener('submit', function () {

                if (!loginForm.checkValidity()) {
                    return;
                }

                loginButton.classList.add('loading');

                loginButton.disabled = true;

            });

        });

    </script>

</body>

</html>
