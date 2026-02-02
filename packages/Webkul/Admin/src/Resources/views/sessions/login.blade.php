<x-admin::layouts.anonymous>
    <x-slot:title>
        @lang('admin::app.users.login.title')
    </x-slot>

    <div class="flex h-screen w-full overflow-hidden bg-gray-50 dark:bg-gray-950" style="font-family: 'Poppins', sans-serif;">
        <!-- Left Side: Branding & Visuals -->
        <div class="relative hidden w-3/5 flex-col justify-between p-12 lg:flex" 
             style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ asset('images/crm_login_background.png') }}'); background-size: cover; background-position: center;">
            
            <div class="relative z-10 transition-all duration-700 delay-100">
                @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                    <img class="h-12 w-auto" src="{{ Storage::url($logo) }}" alt="{{ config('app.name') }}" />
                @else
                    <img class="h-12 w-auto brightness-0 invert" src="{{ url('images/logo_3lackd.png') }}" alt="{{ config('app.name') }}" />
                @endif
            </div>

            <div class="relative z-10">
                <h1 class="text-5xl font-extrabold text-white leading-tight drop-shadow-xl">
                    Impulsione suas Vendas<br/>
                    <span style="color: var(--brand-color);">com Inteligência.</span>
                </h1>
                <p class="mt-6 max-w-md text-lg text-white opacity-90 leading-relaxed shadow-black">
                    Gerencie seus leads, cotações e relacionamentos em uma plataforma única desenhada para o alto desempenho.
                </p>
            </div>

            <div class="relative z-10 text-sm text-white opacity-70">
                © {{ date('Y') }} 3lackd Serviços de Tecnologia.
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="flex w-full flex-col items-center justify-center p-8 lg:w-2/5 shadow-2xl z-20 bg-white dark:bg-gray-900">
            <div class="w-full max-w-[420px]">
                
                <!-- Mobile Logo -->
                <div class="mb-10 flex flex-col items-center lg:hidden">
                    @if ($logo = core()->getConfigData('general.design.admin_logo.logo_image'))
                        <img class="h-10 w-auto" src="{{ Storage::url($logo) }}" alt="{{ config('app.name') }}" />
                    @else
                        <img class="h-10 w-auto" src="{{ url('images/logo_3lackd.png') }}" alt="{{ config('app.name') }}" />
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

                <div class="p-8 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
                    {!! view_render_event('admin.sessions.login.form_controls.before') !!}

                    <x-admin::form :action="route('admin.session.store')">
                        <div class="space-y-6">
                            <!-- Email -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required font-semibold text-gray-700 dark:text-gray-300">
                                    @lang('admin::app.users.login.email')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control 
                                    type="email" 
                                    class="w-full h-12 rounded-xl transition-all duration-200 border-gray-200 dark:border-gray-700"
                                    id="email" 
                                    name="email" 
                                    rules="required|email"
                                    :label="trans('admin::app.users.login.email')"
                                    :placeholder="trans('admin::app.users.login.email')" 
                                />

                                <x-admin::form.control-group.error control-name="email" />
                            </x-admin::form.control-group>

                            <!-- Password -->
                            <x-admin::form.control-group class="relative w-full">
                                <div class="flex items-center justify-between mb-1">
                                    <x-admin::form.control-group.label class="required !mb-0 font-semibold text-gray-700 dark:text-gray-300">
                                        @lang('admin::app.users.login.password')
                                    </x-admin::form.control-group.label>

                                    <a class="text-xs font-bold text-brandColor hover:opacity-80 transition-opacity"
                                        href="{{ route('admin.forgot_password.create') }}">
                                        @lang('admin::app.users.login.forget-password-link')
                                    </a>
                                </div>

                                <div class="relative">
                                    <x-admin::form.control-group.control 
                                        type="password"
                                        class="w-full h-12 rounded-xl ltr:pr-12 rtl:pl-12 transition-all duration-200 border-gray-200 dark:border-gray-700" 
                                        id="password" 
                                        name="password"
                                        rules="required|min:6" 
                                        :label="trans('admin::app.users.login.password')"
                                        :placeholder="trans('admin::app.users.login.password')" 
                                    />

                                    <span
                                        class="icon-eye-hide absolute top-1/2 -translate-y-1/2 cursor-pointer text-2xl text-gray-400 hover:text-gray-600 transition-colors ltr:right-4 rtl:left-4"
                                        onclick="switchVisibility()" 
                                        id="visibilityIcon" 
                                        role="presentation" 
                                        tabindex="0">
                                    </span>
                                </div>

                                <x-admin::form.control-group.error control-name="password" />
                            </x-admin::form.control-group>

                            <!-- Submit Button -->
                            <button class="w-full primary-button h-12 rounded-xl text-base font-bold shadow-lg shadow-brandColor/30 hover:shadow-brandColor/40 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200" aria-label="{{ trans('admin::app.users.login.submit-btn')}}">
                                @lang('admin::app.users.login.submit-btn')
                            </button>
                        </div>
                    </x-admin::form>

                    {!! view_render_event('admin.sessions.login.form_controls.after') !!}
                </div>
                
                <div class="mt-8 text-center text-xs text-gray-400 dark:text-gray-500">
                    © {{ date('Y') }} 3lackd Serviços de Tecnologia. Todos os direitos reservados.
                </div>
            </div>
        </div>
    </div>

    <style>
        .primary-button {
            background-color: var(--brand-color) !important;
            border: none !important;
            color: white !important;
        }
        
        input:focus {
            border-color: var(--brand-color) !important;
            outline: none !important;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.05) !important;
        }

        .dark input:focus {
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.05) !important;
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