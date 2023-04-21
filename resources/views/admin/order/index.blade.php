@extends('layouts.admin')

@section('content')
    <div>
        <h2 class="mt-2 mb-3">Заказы:</h2>
        <hr>
        <div class="row mt-5">
            <table class="table table-striped table-dark">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">Id</th>
                    <th scope="col">Id пользователя</th>
                    <th scope="col">Статус</th>
                    <th scope="col">Количество</th>
                    <th scope="col">Описание</th>
                    <th scope="col">Адрес доставки</th>
                    <th scope="col">Суммарная цена</th>
                    <th scope="col">Дата регистрации</th>
                    <th scope="col">Дата последнего изменения</th>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $o)
                    <tr>
                        <th scope="row">{{ $o->id }}</th>
                        <th scope="col">{{ $o->user_id }}</th>
                        <th scope="col">{{ $o->staus }}</th>
                        <th scope="col">{{ $o->quantity }}</th>
                        <th scope="col">{{ $o->description }}</th>
                        <th scope="col">{{ $o->ship_address }}</th>
                        <th scope="col">{{ $o->total_price }}</th>
                        <th scope="col">{{ $o->created_at }}</th>
                        <th scope="col">{{ $o->updated_at ?? 'Изменений нет' }}</th>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection