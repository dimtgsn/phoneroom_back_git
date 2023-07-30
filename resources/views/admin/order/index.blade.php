@extends('layouts.admin')

@section('content')
    <div>
        <h2 class="mt-2 mb-3">Заказы:</h2>
        <hr>
        <div class="col-12 mt-5">
            <table id="example2" class="table table-dark table-responsive-lg table-hover dataTable dtr-inline" aria-describedby="example2_info">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">Id</th>
                    <th scope="col">Пользователь</th>
                    <th scope="col">Статус</th>
                    <th scope="col">Описание</th>
                    <th scope="col">Тип доставки</th>
                    <th scope="col">Адрес доставки</th>
                    <th scope="col">Дата доставки</th>
                    <th scope="col">Список товаров</th>
                    <th scope="col">Дата создания</th>
                    <th scope="col">Итоговая цена</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $o)
                    <tr>
                        <th scope="row">{{ $o->id }}</th>
                        <th scope="col">{{ $o->user->first_name }}<br>{{ $o->user->phone }}</th>
                        <th scope="col">
                            @if($o->status_id === 5)
                                <a href="{{ route('admin.orders.choose_delivery', $o->id) }}">{{ $o->status->name }}</a>
                            @else
                                {{ $o->status->name }}
                            @endif
                        </th>
                        <th scope="col">{{ $o->description }}</th>
                        <th scope="col">
                            @if($o->status_id === 5)
                                Доставка не выбрана
                            @else
                                {{ $o->delivery->name }}
                                <br>
                                {{ $o->delivery->type }}
                            @endif
                        </th>
                        <th scope="col">{{ $o->ship_address }}</th>
                        <th scope="col">
                            @if($o->status_id !== 5)
                                {{ $o->delivery_date }}
                            @endif
                        </th>
                        <th scope="col">
                            <ul>
                                @foreach($products as $order_id => $all_product)
                                    @if($o->id === $order_id)
                                        @foreach($all_product as $product)
                                            <li>
                                                {{ $product[0]['product_name'] ?? $product[0]['name'] }}
                                                <br>
                                                Кол-во - {{ $product[1] }}
                                                <br>
                                                Цена(за 1 ед.) - {{ $product[2] }}
                                                <br>
                                            </li>
                                            <br>
                                        @endforeach
                                    @endif
                                @endforeach
                            </ul>
                        </th>
                        <th scope="col">{{ $o->created_at }}</th>
                        <th scope="col">{{ $o->total }}</th>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection