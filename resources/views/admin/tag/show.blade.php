@extends('layouts.admin')
@section('content')
    <h1 class="pt-2 mb-3">{{ $tag->name }}</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-6">
                    <div>
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">{{ $tag->name }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="container">
                                    <div class="row">
                                        <div class="col pl-4">
                                            @if($tag->image)
                                                <img class="img-fluid" src="{{ asset($tag->image) }}" alt="Tag Image">
                                            @else
                                                Нет изображения
                                            @endif
                                        </div>
                                        <div class="col text-left pl-5">
                                            <p>Название - <strong>{{ $tag->name }}</strong></p>
                                            <p>
                                                <strong>Товары привязанные к тегу: <br></strong>
                                                @if(empty($tag->products[0]) === true)
                                                    Нет
                                                @endif
                                                @foreach($tag->products as $product )
                                                    <p class="pl-3"><a  href="{{ route('admin.products.show', $product->slug) }}">{{ $product->name }}</a></p>
                                                @endforeach
                                        </div>
                                    </div>
                                </div>

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
