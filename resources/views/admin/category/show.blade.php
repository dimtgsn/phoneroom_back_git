@extends('layouts.admin')
@section('content')

    <h1 class="pt-2 mb-3">{{ $category->name }}</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col">
                    <div>
                        <div class="card card-danger">
                            @if( $parentCategory )
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <a href="{{ route('admin.categories.show', $parentCategory->slug) }}">{{ $parentCategory->name }}</a>
                                    </h3>
                                </div>
                                <div class="pl-5 pt-3 card-heade w-50">
                                    <h3 class="card-title text-red"><strong>{{ $category->name }}</strong></h3>
                                </div>
                                <div class="card-body">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-4">
                                                @if($category->image)
                                                    <img class="img-fluid" src="{{ asset($category->image) }}" alt="category">
                                                @else
                                                    Нет изображения
                                                @endif
                                            </div>
                                            <div class="col text-left offset-1">
                                                <p>Название - <strong>{{ $category->name }}</strong></p>
                                                <strong>Бренды привязанные к категории: </strong><br>
                                                @if(empty($category->brands[0]) === true)
                                                    Нет
                                                @endif
                                                @foreach($category->brands as $category_brand )
                                                    <p class="pl-3"><a  href="{{ route('admin.brands.show', $category_brand->slug) }}">{{ $category_brand->name }}</a></p>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-5">
                                        <a class="btn mt-5 btn-outline-danger" href="{{ route('admin.categories.index') }}">Назад</a>
                                    </div>
                                </div>
                            @else
                                <div class="card-header">
                                    <h3 class="card-title">{{ $category->name }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-4">
                                                <img class="img-fluid" src="{{ asset($category->image) }}" alt="category">
                                            </div>
                                            <div class="col text-left offset-1">
                                                <p>Название - <strong>{{ $category->name }}</strong></p>
                                                <p>
                                                    <strong>Бренды привязанные к категории: </strong><br>
                                                    @if(empty($category->brands[0]) === true)
                                                        Нет
                                                    @endif
                                                    @foreach($category->brands as $category_brand )
                                                        <p class="pl-3"><a  href="{{ route('admin.brands.show', $category_brand->slug) }}">{{ $category_brand->name }}</a></p>
                                                    @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-5">
                                        <a class="btn mt-5 btn-outline-danger" href="{{ route('admin.categories.index') }}">Назад</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
