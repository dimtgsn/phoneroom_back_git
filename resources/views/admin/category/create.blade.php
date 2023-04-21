@extends('layouts.admin')
@section('content')

    <h1 class="pt-2 mb-3">Добавить категорию:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    <div class="">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Новая категория</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.categories.store') }}" class="pt-3 my-login-validation" enctype="multipart/form-data">
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
                                            <div class="input-group-append">
                                                <span class="input-group-text">Загрузка</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" data-select2-id="80">
                                        <label for="brands" class="form-label">Бренды этой категории</label>
                                        <div class="select2-purple" data-select2-id="79">
                                            <select name="brands_id[]" class="select2 select2-hidden-accessible" multiple="" data-placeholder="Выберите бренды" data-dropdown-css-class="select2-purple" style="width: 100%;" data-select2-id="15" tabindex="-1" aria-hidden="true">
                                                @foreach($brands as $i => $brand )
                                                    <option value="{{ $brand->id }}" data-select2-id="{{$i + 82}}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <div class="custom-control custom-checkbox">
                                            <input onclick="onOrOffCheckbox()" checked class="custom-control-input custom-control-input-danger" type="checkbox" id="mainCategory" >
                                            <label for="mainCategory" class="custom-control-label">Главная категория</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label disabled="" for="parent_id" class="form-label mr-2">Относится к</label>

                                        <select name="parent_id" class="custom-select form-control-border"
                                                aria-label="parent_id" id="parentSelect" disabled={{old('disabled')}} >
                                            @foreach($categories as $i => $category)
                                                @if($i === 0)
                                                    <option
                                                            value="null">
                                                    </option>
                                                    @continue(true)
                                                @endif
                                                <option
                                                        value="{{ $category->id }}">{{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="form-group mt-5">
                                            <button type="submit" class="btn w-25 btn-danger btn-block">
                                                Создать
                                            </button>
                                        </div>
                                </form>
                                <div class="pt-5">
                                    <a class="btn mt-5 btn-outline-danger" href="{{ route('admin.categories.index') }}">Назад</a>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        function onOrOffCheckbox(){
            const mainCategory = document.querySelector('#mainCategory');
            const parentSelect = document.querySelector('#parentSelect');
            if (mainCategory.checked){
                parentSelect.disabled = true;
            }
            else{
                parentSelect.disabled = false;
            }
        }

    </script>

@endsection
