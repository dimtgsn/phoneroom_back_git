@extends('layouts.admin')

@section('content')
    <div>
        <h2 class="mt-2 mb-3">Доставка:</h2>
        <hr>
        <section class="h-100">
            <div class="container-fluid h-100">
                <div class="row justify-content-md-left w-100 h-100">
                    <div class="card-wrapper col-10">
                        <div class="card card-dark">
                            <div class="card-header">
                                <h3 class="card-title text-bold">Заказ №{{ $order->id }}. Выбор доставки</h3>
                                <br>
                                <div class="address"><span class="text-bold">Адрес доставки</span>: {{ $order->ship_address }}</div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="order card card-light">
                                    <div class="card-header text-bold">Подробнее</div>
                                    <div class="d-flex flex-wrap">
                                        @foreach($products as $product)
                                            <div class="card-body w-25">
                                                <img class="w-25 mb-1" src="{{ asset($product['product']['image']) }}" alt="Product image">
                                                <h2 class="text-sm d-inline-block mb-1">Товар - <strong>{{ $product['product']['name'] }}</strong></h2>
                                                <br>
                                                <h3 class="text-sm d-inline-block">Количество - {{ $product['quantity'] }}</h3>
                                                <br>
                                                <h3 class="text-sm d-inline-block">Цена на момент создания заказа - {{ $product['price'] }} ₽</h3>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="deliveries d-flex mt-3">
                                    <div class="boxbery d-flex flex-column col-6">
                                        <h3 class="card-title text-lg"><span class="text-bold">Boxberry</span> курьером:</h3>
{{--                                        <p class="cost mt-2 text-md">Стоимость доставки - {{ $delivery['price'] }}</p>--}}
{{--                                        <p class="cost mt-2 text-md">Срок доставки - {{ $delivery['delivery_period'] }}</p>--}}
{{--                                        <h3 class="cost mt-4 text-md text-bold">Стоимость доставки - {{ $delivery['price'] }} ₽</h3>--}}
{{--                                        <h3 class="cost mt-1 text-md text-bold">Срок доставки - {{ $delivery['delivery_period'] }} дней</h3>--}}
                                        <form method="POST" action="{{ route('admin.orders.parsel_create', [$order->id, 1]) }}" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group mt-3">
                                                <button type="submit" class="btn w-50 btn-primary btn-block">
                                                    Выбрать
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="pochta_ru d-flex flex-column col-6">
                                        <h3 class="card-title text-lg"><span class="text-bold">Почта России</span> курьером:</h3>
                                        <h3 class="cost mt-4 text-md text-bold">Стоимость доставки - 363 ₽</h3>
                                        <h3 class="cost mt-1 text-md text-bold">Срок доставки - 5 дней</h3>

                                        <form method="POST" action="{{ route('admin.orders.parsel_create', [$order->id, 2]) }}" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group mt-3">
                                                <button type="submit" class="btn w-50 btn-primary btn-block">
                                                    Выбрать
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="pt-5">
                                    <a class="btn mt-5 btn-outline-primary" href="{{ route('admin.orders.index') }}">Назад</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection