@extends('layouts.admin')

@section('content')
    <div>
        <h2 class="pt-2 mb-3">Мой склад</h2>
{{--        <div class="row w-100 pt-2 justify-content-between">--}}
{{--            <div class="col">--}}
{{--                <a class="btn d-inline-block btn-primary w-25" href="{{ route('admin.main_images.create') }}">Добавить изображение</a>--}}
{{--            </div>--}}
{{--        </div>--}}
        <hr>
        <div class="col-12 col-lg-8">
            <div class="card card-primary">
                <div class="card-header text-lg">Подключение к аккаунту</div>
                <div class="card-body">
                    @if(isset($error_msg))
                        <h5 class="text-danger">{{$error_msg}}</h5>
                    @endif
                    <form action="{{ route('admin.my-warehouse.connect') }}" method="post" class="pt-3 my-login-validation" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-4">
                            <label for="login" class="form-label">Логин</label>
                            <input value="{{ old('login') }}" placeholder="Введите логин" name="login" type="text"
                                   class="form-control @error('login') is-invalid @enderror"
                                   id="login" required
                                   autocomplete="login" autofocus>

                            @error("login")
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group mb-4">
                            <label for="password" class="form-label">Пароль</label>
                            <div class="password-warehouse">
                                <input value="{{ old('password') }}" placeholder="Введите пароль" name="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password-input" required
                                       autocomplete="password" autofocus>
                                <a href="#" class="password-control" onclick="return show_hide_password(this);"></a>
                            </div>

                            @error("password")
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group mt-5">
                            <button type="submit" class="btn w-100 btn-danger btn-block">
                                Подключиться
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function show_hide_password(target){
            let input = document.getElementById('password-input');
            if (input.getAttribute('type') == 'password') {
                target.classList.add('view');
                input.setAttribute('type', 'text');
            } else {
                target.classList.remove('view');
                input.setAttribute('type', 'password');
            }
            return false;
        }
    </script>
@endsection