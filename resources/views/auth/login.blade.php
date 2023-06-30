@extends('layouts.app')

@section('content')
    <section class="h-100">
        <div class="container h-100">
            <div class="row justify-content-md-center h-100">
                <div class="card-wrapper">
                    <div class="brand">
                        <a href="{{ env('APP_URL', '/') }}">
                            <img class="" src="{{ asset("dist/img/Group 771.svg") }}">
                        </a>
                    </div>

                    <div class="card fat">
                        <div class="card-body">
                            <h4 class="card-title">Вход</h4>
                            <form method="POST" action="{{ route('login') }}" class="my-login-validation">
                                @csrf

                                <div class="form-group mb-4">
                                    <label for="phone" class="pb-1">{{ __('Номер телефона') }}</label>

                                    <input id="phone" type="tel"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           name="phone"
                                           value="7"
                                           required autocomplete="phone"
                                           placeholder="7___ ___ __ __" pattern="[7]{1}[0-9]{3}[0-9]{3}[0-9]{2}[0-9]{2}">

                                    @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="password" class="pb-1">{{ __('Пароль') }}
                                        @if (Route::has('password.request'))
                                            <a class="float-end" href="{{ route('password.request') }}">
                                                {{ __('Забыли пароль?') }}
                                            </a>
                                        @endif
                                    </label>

                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('Запомнить меня') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group m-0">
                                    <button type="submit" class="btn w-100 btn-danger btn-block">
                                        {{ __('Войти') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
