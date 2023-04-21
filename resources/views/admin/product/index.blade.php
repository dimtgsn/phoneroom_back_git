@extends('layouts.admin')

@section('content')
    <div>
        <h1 class="text-left">Товары:</h1>
        <div class="row w-100 pt-2 justify-content-between">
            <div class="col">
                <a class="btn d-inline-block btn-primary" href="{{ route('admin.products.create') }}">Добавить товар</a>
            </div>
        </div>
        <hr>

        <div class="row product-cart-index">
            @foreach($products as $key => $product)
            <div class="col-12" id="accordion{{ $product->id }}">
                    <div class="card card-primary card-outline">
                        <a class="d-block w-100 collapsed" data-toggle="collapse{{ $product->slug }}" href="{{ route('admin.products.show', $product->slug) }}" aria-expanded="false">
                            <div class="card-header">
                                <h4 class="card-title d-inline-block">
                                    {{ $key+1 }}. {{ $product->name }}
                                </h4>
                                <a class="d-inline-block pl-2" href="{{ route('admin.products.edit', $product->slug) }}"><i class="fa fa-solid fa-pen"></i></a>
                            </div>
                        </a>
                        <form class="d-inline-block" action="{{ route('admin.products.destroy', $product->slug) }}" method="post">
                            @csrf
                            @method('delete')
                            <button class="text-primary btn d-inline-block ml-2" type="submit"><i class="fa fa-solid fa-trash"></i></button>
                        </form>
                        <div id="{{ $product->slug }}" class="collapse{{ $product->slug }}" data-parent="#accordion{{ $product->id }}" style="">
                            <div class="card-body">
                                <section class="card d-flex" style="overflow-x: auto;flex-direction: row;padding: 20px;">
                                    @if(count($product->variants))
                                        @foreach($product->variants as $variant)
                                            <div class="card--content mr-5">
                                                <div class="row" style="flex-direction: row-reverse;">
                                                    <div class="col">
                                                        <a class="d-inline-block " href="{{ route('admin.products.variant_edit', [$product->slug, json_decode($variant->variants_json, true)['slug']]) }}"><i class="fa fa-solid fa-pen"></i></a>
                                                        <img src="{{ asset(json_decode($variant->variants_json, true)['image']) }}" alt="ProductVariant" width="200" height="200">
                                                    </div>
                                                    <div class="col">
                                                        <b><p class="name pt-2 d-inline-block">{{  json_decode($variant->variants_json, true)['product_name']  }}</p></b>
                                                        <h2 class="text-md">Категория - {{  $product->category->name  }}</h2>
                                                        <h2 class="text-md">На складе - {{  json_decode($variant->variants_json, true)['units_in_stock'] }}</h2>
                                                        <p class="">Цена: {{  json_decode($variant->variants_json, true)['price']  }} р.</p>
                                                        <b><a class="d-inline-block" href="{{ route('admin.products.variant_show', [$product->slug, json_decode($variant->variants_json, true)['slug']]) }}">Подробнее</a></b>
                                                        <form class="ml-2 d-inline-block" action="{{ route('admin.products.variant_destroy', [$product->slug, json_decode($variant->variants_json, true)['slug']]) }}" method="post">
                                                            @csrf
                                                            @method('delete')
                                                            <button class="btn btn-danger d-inline-block" type="submit">Удалить</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="card--content mr-5">
                                            <div class="row" style="flex-direction: row-reverse;">
                                                <div class="col">
{{--                                                    <a class="d-inline-block " href="{{ route('admin.products.edit', $product->slug) }}"><i class="fa fa-solid fa-pen"></i></a>--}}
                                                    <img src="{{ asset($product->image) }}" alt="ProductVariant" width="200" height="200">
                                                </div>
                                                <div class="col">
                                                    <b><p class="name pt-2 d-inline-block">{{  $product->name  }}</p></b>
                                                    <h2 class="text-md">Категория - {{  $product->category->name  }}</h2>
                                                    <h2 class="text-md">На складе - {{  $product->units_in_stock  }}</h2>
                                                    <p class="">Цена: {{  $product->price  }} р.</p>
                                                    <b><a class="d-inline-block" href="{{ route('admin.products.show', $product->slug) }}">Подробнее</a></b>
                                                    <form class="ml-2 d-inline-block" action="{{ route('admin.products.destroy', $product->slug) }}" method="post">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn-danger d-inline-block" type="submit">Удалить</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row mt-3">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
@endsection