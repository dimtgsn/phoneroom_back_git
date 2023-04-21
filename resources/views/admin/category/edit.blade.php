@extends('layouts.admin')
@section('content')

    <h1 class="pt-2 mb-3">Изменить категорию:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    {{--                    <div class="brand">--}}
                    {{--                        <img class="" src="{{ asset("dist/img/Group 771.svg") }}">--}}
                    {{--                    </div>--}}

                    <div class="">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">{{ $category->name }}</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.categories.update', $category->slug) }}" method="post" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                    @csrf
                                    @method('patch')
                                    <div class="form-group mb-4">
                                        <label for="name" class="form-label">Название</label>
                                        <input value="{{ $category->name }}" placeholder="{{ $category->name }}" name="name" type="text"
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
                                                <label for="image" class="custom-file-label">{{ $category->image }}</label>
                                                <input value="{{ $category->image }}" type="file" name="image" class="custom-file-input" id="image">

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
{{--                                    <div class="form-group mb-4">--}}
{{--                                        <div class="custom-control custom-checkbox">--}}
{{--                                            <input onclick="onOrOffCheckbox()" checked class="custom-control-input custom-control-input-danger" type="checkbox" id="mainCategory" >--}}
{{--                                            <label for="mainCategory" class="custom-control-label">Главная категория</label>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    @if(empty($category->brands[0]) === true)--}}
                                    <div class="form-group" data-select2-id="80">
                                        <label for="brands" class="form-label">Бренды этой категории</label>
                                        <div class="select2-purple" data-select2-id="79">
                                            <select name="brands_id[]" class="select2 select2-hidden-accessible" multiple="" data-placeholder="Выберите бренды" data-dropdown-css-class="select2-purple" style="width: 100%;" data-select2-id="15" tabindex="-1" aria-hidden="true">
                                                @foreach($brands as $i => $brand )
                                                    @if(empty($category->brands[0]) !== true)
                                                        @foreach($category->brands as $category_brand )
                                                            <option {{ ($category_brand->name === $brand->name) ? 'selected':''}} value="{{ $brand->id }}"  data-select2-id="{{$i + 82}}">{{ $brand->name }}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="{{ $brand->id }}" data-select2-id="{{$i + 82}}">{{ $brand->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        @if($parentCategory)
                                            <p>Включена в категорию -  <a href="{{ route('admin.categories.show', $parentCategory->slug) }}">{{ $parentCategory->name }}</a></p>
                                            <div class="btn btn-outline-dark" onclick="showSelectParentCategory()">Сменить главную категорию</div>

                                        @else
                                            <p><b>Главная категория</b></p>
                                            <div class="btn btn-outline-dark" onclick="showSelectParentCategory()">Включить в категорию</div>
                                        @endif
                                    </div>
                                    <div class="form-group select-parent-category d-none mb-4">
                                        <label disabled="" for="parent_id" class="form-label mr-2">Относится к</label>

                                        <select name="parent_id" class="custom-select form-control-border"
                                                aria-label="parent_id" id="parentSelect" >
                                            @foreach($categories as $i => $c)
                                                @if($i === 0)
                                                    <option
                                                            value="">
                                                    </option>
                                                    @continue(true)
                                                @endif
                                                 @if($c->name === $category->name)
                                                    @continue(true)
                                                @endif
                                                <option
                                                        value="{{ $c->id }}">{{ $c->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mt-5">
                                        <button type="submit" class="btn w-25 btn-danger btn-block">
                                            Изменить
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
        function showSelectParentCategory() {
            const selectParentCategory = document.querySelector('.select-parent-category');
            selectParentCategory.classList.add('d-block');
        }

    </script>

@endsection
