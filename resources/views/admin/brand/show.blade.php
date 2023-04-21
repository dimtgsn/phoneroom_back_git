@extends('layouts.admin')
@section('content')
    <h1 class="pt-2 mb-3">{{ $brand->name }}</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    <div>
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">{{ $brand->name }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="container">
                                    <div class="row">
                                        <div class="col pl-4">
                                            @if($brand->image)
                                                <img class="img-fluid" src="{{ asset($brand->image) }}" alt="category">
                                            @else
                                                Нет изображения
                                            @endif
                                        </div>
                                        <div class="col text-left pl-5">
                                            <p>Название - <strong>{{ $brand->name }}</strong></p>
                                            <p>
                                                <strong>Категории привязанные к бренду: <br></strong>
                                                @if(empty($brand->categories[0]) === true)
                                                    Нет
                                                @endif
                                                @foreach($brand->categories as $category )
                                                    <p class="pl-3"><a  href="{{ route('admin.categories.show', $category->slug) }}">{{ $category->name }}</a></p>
                                                @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-5">
                                    <a class="btn mt-5 btn-outline-danger" href="{{ route('admin.brands.index') }}">Назад</a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
