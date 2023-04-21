@extends('layouts.admin')

@section('content')

    <div>
        <h2 class="pt-2 mb-3">Банеры на главной странице:</h2>
        <div class="row w-100 pt-2 justify-content-between">
            <div class="col">
                <a class="btn d-inline-block btn-primary w-50" href="{{ route('admin.banner_images.create') }}">Добавить изображение</a>
            </div>
        </div>
        <hr>
        <div>
            <div class="card">
                @if(count($images) !== 0)
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="col-7 card-title">Главная страница - баннеры</h3>
                        <h3 class="col-5 card-title">
                            <a href="{{ route('admin.banner_images.edit') }}">Редактировать</a>
                        </h3>
                    </div>
                    <div class="card-body w-100">
                        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                @for($i=0;$i<count($images);$i++)
                                    <li data-target="#carouselExampleIndicators" data-slide-to="{{$i}}" class=""></li>
                                @endfor
                            </ol>
                            <div class="carousel-inner">
                                @foreach($images as $img)
                                    @if($img->position === 1)
                                        <div class="carousel-item active">
                                            <img class="d-block w-100"
                                                 src="{{ asset($img->path) }}"
                                                 alt="Image slide">
                                        </div>
                                    @else
                                        <div class="carousel-item">
                                            <img class="d-block w-100"
                                                 src="{{ asset($img->path) }}"
                                                 alt="Image slide">
                                        </div>
                                    @endif

                                @endforeach
                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button"
                               data-slide="prev">
                            <span class="carousel-control-custom-icon" aria-hidden="true">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button"
                               data-slide="next">
                            <span class="carousel-control-custom-icon" aria-hidden="true">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>

                @else
                    <h5 class="p-5">Изображений нет</h5>
                @endif
            </div>
        </div>
    </div>
@endsection