@extends('layouts.admin')
@section('content')

    <h1 class="pt-2 mb-3">Добавить модератора:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    {{--                    <div class="brand">--}}
                    {{--                        <img class="" src="{{ asset("dist/img/Group 771.svg") }}">--}}
                    {{--                    </div>--}}

                    <div class="card fat">
                        <div class="card-body">
                            <h4 class="card-title">Регистрация</h4>

                            <form method="POST" action="{{ route('admin.users.store') }}"
                                  class="pt-5 my-login-validation">
                                @csrf
                                <div class="form-group mb-4">
                                    <label for="first_name" class="form-label">Имя</label>
                                    <input value="{{ old('first_name') }}" name="first_name" type="text"
                                           class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" required
                                           autocomplete="first_name" autofocus>

                                    @error("first_name")
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-4">
                                    <label for="last_name" class="form-label">Фамилия</label>
                                    <input value="{{ old('last_name') }}" name="last_name" type="text"
                                           class="form-control @error('last_name') is-invalid @enderror" id="last_name"
                                           autocomplete="last_name" autofocus>

                                    @error("last_name")
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-4">
                                    <label for="middle_name" class="form-label">Отчество</label>
                                    <input value="{{ old('middle_name') }}" name="middle_name" type="text"
                                           class="form-control @error('middle_name') is-invalid @enderror"
                                           id="middle_name"
                                           autocomplete="middle_name" autofocus>

                                    @error("middle_name")
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-4">
                                    <label for="email" class="">Email</label>

                                    <input id="email" type="email"
                                           class="form-control @error('email') is-invalid @enderror" name="email"
                                           value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                {{--                                    <div class="form-group mb-4">--}}
                                {{--                                    <label for="position_id" class="form-label mr-2">Роль</label>--}}
                                {{--                                    <select name="position_id" class="form-select" aria-label="position_id">--}}
                                {{--                                        {{ $positions }}--}}
                                {{--                                        @foreach($positions as $position)--}}

                                {{--                                            <option--}}
                                {{--                                                    {{ 3 === $position->id ? 'selected':'' }}--}}
                                {{--                                                    value="{{ $position->id }}">{{ $position->name }}--}}
                                {{--                                            </option>--}}
                                {{--                                        @endforeach--}}
                                {{--                                    </select>--}}

                        <div class="form-group mb-4">
                            <label for="phone" class="form-label">Номер телефона</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">+7</span>
                                </div>
                                <input name="phone" type="tel"
                                    class="form-control @error('phone') is-invalid @enderror" id="phone" required
                                    autocomplete="phone" autofocus
                                    data-inputmask="&quot;mask&quot;: &quot;(999) 999-9999&quot;" data-mask=""
                                    inputmode="text">
                            {{--                            <span class="prefix">+7</span>--}}
                                @error("phone")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                         </div>
                        <div class="form-group mb-4">
                            <label for="fullAddress" class="form-label">Адрес</label>
                            <input value="{{ old('city') }}" name="fullAddress" type="text"
                                   class="form-control @error('city') is-invalid @enderror" id="fullAddress"
                                   autocomplete="fullAddress" autofocus>

                            @error("fullAddress")
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
{{--                        <div class="form-group mb-4">--}}
{{--                            <label for="street" class="form-label">Улица</label>--}}
{{--                            <input value="{{ old('street') }}" name="street" type="text"--}}
{{--                                   class="form-control @error('street') is-invalid @enderror" id="street"--}}
{{--                                   autocomplete="street" autofocus>--}}

{{--                            @error("street")--}}
{{--                            <span class="invalid-feedback" role="alert">--}}
{{--                                    <strong>{{ $message }}</strong>--}}
{{--                                </span>--}}
{{--                            @enderror--}}
{{--                        </div>--}}
{{--                        <div class="form-group mb-4">--}}
{{--                            <label for="house" class="form-label">Дом/Квартира</label>--}}
{{--                            <input value="{{ old('house') }}" name="house" type="text"--}}
{{--                                   class="form-control @error('house') is-invalid @enderror" id="house"--}}
{{--                                   autocomplete="house" autofocus>--}}

{{--                            @error("house")--}}
{{--                            <span class="invalid-feedback" role="alert">--}}
{{--                                    <strong>{{ $message }}</strong>--}}
{{--                                </span>--}}
{{--                            @enderror--}}
{{--                        </div>--}}

                        <div class="form-group mb-4">
                            <label for="password" class="">Пароль</label>

                            <input id="password" type="password"
                                   class="form-control @error('password') is-invalid @enderror" name="password"
                                   required autocomplete="current-password">

                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                            @enderror
                        </div>
                        <div class="form-group mb-4">
                            <label for="password-confirm" class="form-label">Подтверждение пароля</label>

                            <input id="password-confirm" type="password" class="form-control"
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="form-group m-0">
                            <button type="submit" class="btn w-100 btn-danger btn-block">
                                Зарегистрировать
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
