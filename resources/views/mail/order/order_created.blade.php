<x-mail::message>
# Ваш заказ с номером №{{ $order->id + 1234 }} успешно создан и находится в обработке!

Пользователь: <b> {{ $order->user->profile->last_name.' '.$order->user->first_name.' '.$order->user->profile->middle_name }}</b>

Список товаров: <ul>
    @foreach($order_products as $product)
        <li>
            <h2>{{ $product['product']['name'] }}, <br>Цена покупки - {{ $product['price'] }}₽, <br>Количество - {{ $product['quantity'] }}шт</h2>
        </li>
    @endforeach
</ul>
Адрес доставки: <b>{{ $order->zip.', '.$order->ship_address }}</b>

<br>Дата доставки и трек номер заказа будут известны после обработки и будут направлены вам в следующем письме.
<br> <br>Также вы можете вся информация по заказу находится в вашем личном кабинете.
<x-mail::button :url="''">
Перейти в личный кабинет
</x-mail::button>

С уважением,<br>
{{ config('app.name') }}
</x-mail::message>
