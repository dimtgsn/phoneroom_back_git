@extends('layouts.admin')
@section('content')

    <h1 class="pt-2 mb-3">Добавить тег:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-12 col-lg-10">
                    <div class="">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Новый тег</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.tags.store') }}" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-4">
                                        <label for="name" class="form-label">Название</label>
                                        <input value="{{ old('name') }}" name="name" type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               id="name" required
                                               autocomplete="name" autofocus>

                                        @error("name")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="image" class="form-label">Изображение</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <label for="image" class="custom-file-label">Выберите файл</label>
                                                <input value="{{ old('image') }}" type="file" name="image" class="custom-file-input" id="image">

                                                @error("image")
                                                <span class="invalid-feedback" role="alert">
                                                     <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="input-group-append d-none d-sm-block">
                                                <span class="input-group-text">Загрузка</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" data-select2-id="80">
                                        <label for="products" class="form-label">Связать с товарами:</label>
                                        <div class="select2-purple" data-select2-id="79">
                                            <select name="products_id[]" class="select2 select2-hidden-accessible" multiple="" data-placeholder="Выберите товары" data-dropdown-css-class="select2-purple" style="width: 100%;" data-select2-id="15" tabindex="-1" aria-hidden="true">
                                                @foreach($products as $i => $product )
                                                    <option value="{{ $product->id }}" data-select2-id="{{$i + 82}}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                        <div class="form-group mt-5">
                                            <button type="submit" class="btn w-50 btn-danger btn-block">
                                                Создать
                                            </button>
                                        </div>
                                </form>
                                <div class="pt-5">
                                    <a class="btn mt-5 btn-outline-danger" href="{{ route('admin.tags.index') }}">Назад</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
