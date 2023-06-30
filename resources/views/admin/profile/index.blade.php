@extends('layouts.admin')

@section('content')
    <div>
        <h1 class="pt-2 mb-3">Мой профиль</h1>
        <hr>
        <div class="row">
            <div class="col-xs-12 col-sm-6 col-md-6">
                <div class="well well-sm">
                    <div class="row">
                        <div class="col-sm-5">
                            <img src="http://www.freeiconspng.com/uploads/profile-icon-9.png" alt=""
                                 class="img-rounded w-100 img-responsive"/>
                        </div>
                        <div class="col-sm-7 col-md-7 pt-5">
                            <h4>
                                {{ $user->first_name }} {{ $user->profile->middle_name }} {{ $user->profile->last_name }}
                            </h4>
                            <small class="pb-2">
                                <cite>
                                    @if($user->position->name === 'admin')
                                        Администратор

                                    @else()
                                        Модератор
                                    @endif
                                </cite>
                            </small>
                            <p>
                                <i class="fa-regular fa glyphicon fa-envelope"></i>{{ $user->email }}
                                <br/>
                                <i class="fa-solid fa glyphicon fa-phone"></i>{{ $user->phone}}
                                <br/>
                                <i class="fa-solid fa glyphicon fa-address-book"></i>{{ ($profile->address->fullAddress ?? "") }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-6 mb-3" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                <form class="pl-3" action="{{ route('admin.profiles.update', [$profile->slug, $user->id]) }}" method="post">
                    @csrf
                    @method('patch')
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Имя</label>
                        <input value="{{ $user->first_name }}" placeholder="{{ $user->first_name }}" name="first_name" type="text"
                               class="form-control @error('first_name') is-invalid @enderror" id="first_name"
                               autocomplete="first_name" autofocus>

                        @error("first_name")
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Отчество</label>
                        <input value="{{ old('middle_name') }}" placeholder="{{ $profile->middle_name }}" name="middle_name" type="text"
                               class="form-control @error('middle_name') is-invalid @enderror" id="middle_name"
                               autocomplete="middle_name" autofocus>

                        @error("middle_name")
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Фамилия</label>
                        <input value="{{ old('last_name') }}" placeholder="{{ $profile->last_name }}" name="last_name" type="text"
                               class="form-control @error('last_name') is-invalid @enderror" id="last_name"
                               autocomplete="last_name" autofocus>

                        @error("last_name")
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input value="{{ old('email') }}" placeholder="{{ $user->email }}" name="email" type="email"
                               class="form-control @error('email') is-invalid @enderror" id="email"
                               autocomplete="email" autofocus>

                        @error("email")
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Номер телефона</label>
{{--                        <div class="control">--}}
{{--                            <input value="{{ $user->phone }}" placeholder="{{ $user->phone }}" name="phone" maxlength="10" type="tel"--}}
{{--                                   class="form-control @error('phone') is-invalid @enderror" id="phone" required--}}
{{--                                   autocomplete="phone" autofocus>--}}
{{--                            <span class="prefix">+7</span>--}}
{{--                            @error("phone")--}}
{{--                                <span class="invalid-feedback" role="alert">--}}
{{--                                    <strong>{{ $message }}</strong>--}}
{{--                                </span>--}}
{{--                            @enderror--}}
{{--                        </div>--}}
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">7</span>
                            </div>
                            <input value="{{ mb_substr($user->phone, 1) }}" placeholder="{{ $user->phone }}" name="phone" type="tel"
                                   class="form-control @error('phone') is-invalid @enderror" id="phone" required
                                   autocomplete="phone" autofocus
                                   data-inputmask="&quot;mask&quot;: &quot;(999) 999-9999&quot;" data-mask="" inputmode="text">
{{--                            <span class="prefix">+7</span>--}}
                            @error("phone")
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
{{--                            <input type="text" name="phone" id="phone" class="form-control" data-inputmask="&quot;mask&quot;: &quot;(999) 999-9999&quot;" data-mask="" inputmode="text">--}}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="fullAddress" class="form-label">Адрес</label>
                        <input value="{{ old('fullAddress') }}" placeholder="{{ ($profile->address->fullAddress ?? "") }}" name="fullAddress" type="text"
                               class="form-control @error('fullAddress') is-invalid @enderror" id="fullAddress"
                               autocomplete="fullAddress" autofocus>

                        @error("fullAddress")
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
{{--                    <div class="mb-3">--}}
{{--                        <label for="address" class="form-label">Адресс</label>--}}
{{--                        <textarea placeholder="{{ $profile->address->city . $profile->address->street . $profile->address->house }}" name="address" class="form-control w-100" id="address">{{ old('address') }}</textarea>--}}

{{--                        @error("address")--}}
{{--                        <span class="invalid-feedback" role="alert">--}}
{{--                            <strong>{{ $message }}</strong>--}}
{{--                        </span>--}}
{{--                        @enderror--}}
{{--                    </div>--}}
                    <button type="submit" class="mb-3 mt-3 btn btn-info">Изменить</button>
                </form>
            </div>
        </div>
    </div>
@endsection