<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.users.login.title')
        </x-slot>

        <div class="flex h-screen w-full overflow-hidden font-poppins">
            <!-- Left Side: Branding & Visuals -->
            <div class="relative hidden w-3/5 flex-col justify-between bg-cover bg-center p-12 lg:flex"
                style="background-image: url('{{ url('images/crm_login_background.png') }}');">
                <!-- Overlay for better text legibility -->
                <div class="absolute inset-0 bg-black/20 backdrop-blur-[2px]"></div>

                <div class="relative z-10">
                    @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                        <img class="h-12 w-auto" src="{{ Storage::url($logo) }}" alt="{{ config('app.name') }}" />
                    @else
                        <img class="h-12 w-auto brightness-0 invert" src="{{ url('images/logo_3lackd.png') }}"
                            alt="{{ config('app.name') }}" />
                    @endif
                </div>

                <div class="relative z-10">
                    <h1 class="text-5xl font-extrabold text-white leading-tight drop-shadow-lg">
                        Impulsione suas Vendas<br />
                        <span class="text-brandColor">com Inteligência.</span>
                    </h1>
                    <p class="mt-6 max-w-md text-lg text-gray-100/90 leading-relaxed drop-shadow-md">
                        Gerencie seus leads, cotações e relacionamentos em uma plataforma unificada e desenhada para o
                        alto desempenho.
                    </p>
                </div>

                <div class="relative z-10 text-sm text-white/70">
                    © {{ date('Y') }} 3lackd Serviços de Tecnologia.
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="flex w-full flex-col items-center justify-center bg-gray-50 p-8 dark:bg-gray-950 lg:w-2/5">
                <div class="w-full max-w-[420px]">

                    <!-- Mobile Logo (shown only on small screens) -->
                    <div class="mb-10 flex flex-col items-center lg:hidden">
                        @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                            <img class="h-10 w-auto" src="{{ Storage::url($logo) }}" alt="{{ config('app.name') }}" />
                        @else
                            <img class="h-10 w-auto" src="{{ url('images/logo_3lackd.png') }}"
                                alt="{{ config('app.name') }}" />
                        @endif
                    </div>

                    <div class="mb-8">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                            @lang('admin::app.users.login.title')
                        </h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Bem-vindo de volta! Por favor, insira suas credenciais.
                        </p>
                    </div>

                    <div
                        class="glass-card rounded-2xl border border-white/20 bg-white/70 p-8 shadow-2xl backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/50">
                        {!! view_render_event('admin.sessions.login.form_controls.before') !!}

                        <x-admin::form :action="route('admin.session.store')">
                            <div class="space-y-6">
                                <!-- Email -->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label
                                        class="required font-medium text-gray-700 dark:text-gray-300">
                                        @lang('admin::app.users.login.email')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control type="email"
                                        class="w-full transition-all duration-200 focus:ring-2 focus:ring-brandColor/20"
                                        id="email" name="email" rules="required|email"
                                        :label="trans('admin::app.users.login.email')"
                                        :placeholder="trans('admin::app.users.login.email')" />

                                    <x-admin::form.control-group.error control-name="email" />
                                </x-admin::form.control-group>

                                <!-- Password -->
                                <x-admin::form.control-group class="relative w-full">
                                    <div class="flex items-center justify-between mb-1">
                                        <x-admin::form.control-group.label
                                            class="required !mb-0 font-medium text-gray-700 dark:text-gray-300">
                                            @lang('admin::app.users.login.password')
                                        </x-admin::form.control-group.label>

                                        <a class="text-xs font-semibold text-brandColor hover:underline transition-colors"
                                            href="{{ route('admin.forgot_password.create') }}">
                                            @lang('admin::app.users.login.forget-password-link')
                                        </a>
                                    </div>

                                    <div class="relative">
                                        <x-admin::form.control-group.control type="password"
                                            class="w-full ltr:pr-12 rtl:pl-12 transition-all duration-200 focus:ring-2 focus:ring-brandColor/20"
                                            id="password" name="password" rules="required|min:6"
                                            :label="trans('admin::app.users.login.password')"
                                            :placeholder="trans('admin::app.users.login.password')" />

                                        <span
                                            class="icon-eye-hide absolute top-1/2 -translate-y-1/2 cursor-pointer text-2xl text-gray-400 hover:text-gray-600 transition-colors ltr:right-4 rtl:left-4"
                                            onclick="switchVisibility()" id="visibilityIcon" role="presentation"
                                            tabindex="0">
                                        </span>
                                    </div>

                                    <x-admin::form.control-group.error control-name="password" />
                                </x-admin::form.control-group>

                                <!-- Submit Button -->
                                <button
                                    class="w-full primary-button py-3 text-base font-semibold shadow-lg shadow-brandColor/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                                    aria-label="{{ trans('admin::app.users.login.submit-btn')}}">
                                    @lang('admin::app.users.login.submit-btn')
                                </button>
                            </div>
                        </x-admin::form>

                        {!! view_render_event('admin.sessions.login.form_controls.after') !!}
                    </div>

                    <div class="mt-8 text-center lg:hidden">
                        <p class="text-xs text-gray-500">
                            © {{ date('Y') }} 3lackd Serviços de Tecnologia.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .font-poppins {
                font-family: 'Poppins', sans-serif;
            }

            .glass-card {
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            @media (prefers-color-scheme: dark) {
                .glass-card {
                    background: rgba(17, 24, 39, 0.7) !important;
                    border-color: rgba(31, 41, 55, 0.5) !important;
                }
            }

            /* Customize input appearance for premium feel */
            input:focus {
                border-color: var(--brand-color) !important;
                box-shadow: 0 0 0 4px rgba(var(--brand-rgb, 14, 144, 217), 0.1) !important;
            }

            .primary-button {
                background-color: var(--brand-color) !important;
                border-color: var(--brand-color) !important;
            }

            .primary-button:hover {
                opacity: 0.9;
                box-shadow: 0 10px 15px -3px rgba(var(--brand-rgb, 14, 144, 217), 0.3) !important;
            }
        </style>

        @push('scripts')
            <script>
                function switchVisibility() {
                    let passwordField = document.getElementById("password");
                    let visibilityIcon = document.getElementById("visibilityIcon");

                    passwordField.type = passwordField.type === "password" ? "text" : "password";
                    visibilityIcon.classList.toggle("icon-eye");
                    visibilityIcon.classList.toggle("icon-eye-hide");
                }
            </script>
        @endpush
</x-admin::layouts.anonymous>