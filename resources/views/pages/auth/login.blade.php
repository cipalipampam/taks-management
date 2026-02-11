<x-layouts::auth>
    <div class="w-full max-w-md">
        <flux:card class="p-8 shadow-sm">
            <div class="text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100/70 text-zinc-900 dark:bg-zinc-800/70 dark:text-white">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.567 3-3.5S13.657 4 12 4s-3 1.567-3 3.5S10.343 11 12 11zm0 2c-3.314 0-6 2.239-6 5h12c0-2.761-2.686-5-6-5z" />
                    </svg>
                </div>
                <h1 class="mt-4 text-2xl font-bold">Masuk ke akun</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Silakan login untuk melanjutkan</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mt-6 text-center" :status="session('status')" />

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <flux:input
                        id="email"
                        name="email"
                        :label="__('Email')"
                        :value="old('email')"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    @error('email')
                        <flux:text size="sm" class="mt-2 text-red-600">{{ $message }}</flux:text>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="text-sm font-medium">Password</label>
                        @if (Route::has('password.request'))
                            <flux:link class="text-sm" :href="route('password.request')" wire:navigate>
                                Lupa password?
                            </flux:link>
                        @endif
                    </div>
                    <flux:input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="Password"
                        viewable
                    />
                    @error('password')
                        <flux:text size="sm" class="mt-2 text-red-600">{{ $message }}</flux:text>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900/10 dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-white/10" {{ old('remember') ? 'checked' : '' }} />
                    Ingat saya
                </label>

                <flux:button type="submit" variant="primary" class="w-full" data-test="login-button">
                    Masuk
                </flux:button>
            </form>

        </flux:card>
    </div>
</x-layouts::auth>
