@extends('layouts.admin')
@section('content')

    <h1 class="pt-2 mb-3">Изменить логотип:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    <div class="">
                        <div class="card card-danger">
                            <div class="card-header">
                                <img src="{{asset($image->path)}}" alt="Logo Image">
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.logo.update', $image->id) }}" method="post" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                    @csrf
                                    @method('patch')
                                    <div class="form-group mb-4">
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <label style="cursor: pointer" for="path" class="custom-file-label">Логотип</label>
                                                <input style="cursor: pointer" value="{{ $image->path }}" type="file" name="path" class="custom-file-input" id="path">

                                                @error("path")
                                                <span class="invalid-feedback" role="alert">
                                                     <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="input-group-append">
                                                <span class="input-group-text">Загрузка</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <label style="cursor: pointer" for="favicon" class="custom-file-label">Фавикон</label>
                                                <input style="cursor: pointer" value="{{ $image->favicon }}" type="file" name="favicon" class="custom-file-input" id="favicon">

                                                @error("favicon")
                                                <span class="invalid-feedback" role="alert">
                                                     <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="input-group-append">
                                                <span class="input-group-text">Загрузка</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mt-5">
                                        <button type="submit" class="btn w-25 btn-danger btn-block">
                                            Изменить
                                        </button>
                                    </div>
                                </form>
                                <div class="pt-5">
                                    <a class="btn mt-5 btn-outline-danger" href="{{ route('admin.index') }}">Назад</a>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
