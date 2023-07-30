@extends('layouts.admin')

@section('content')
    <div>
        <h2 class="pt-2 mb-3">Пользователи:</h2>
        @if(Auth::user()->position->name === 'admin')
            <div class="row w-100 pt-2 justify-content-between">
                <div class="col-3 ">
                    <a class="btn d-inline-block btn-dark" href="{{ route('admin.users.create') }}">Добавить модератора</a>
                </div>
{{--                <div class="col-3 text-right">--}}
{{--                    <a class="btn btn-info" href="#">Выгрузить отчет</a>--}}
{{--                </div>--}}
            </div>
        @endif
        <hr>
        <div class="col-12 mt-3">
            <table id="example2" class="table table-responsive-lg table-hover dataTable dtr-inline table table-hover table-striped table-ligth" aria-describedby="example2_info">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">Id</th>
                    <th scope="col">Имя</th>
                    <th scope="col">Роль</th>
                    <th scope="col">Email</th>
                    <th scope="col">Номер телефона</th>
                    <th scope="col">Адрес</th>
                    <th scope="col">Дата регистрации</th>
                    <th scope="col">Дата изменения</th>
                </tr>
                </thead>
                <tbody>
                <span class="d-none">
                    {{ $count = 0 }}
                </span>
                @foreach($users as $u)
                    <tr>
                        <th scope="col">{{ $u->id }}</th>
                        <th scope="col">{{ $u->first_name}}</th>
                        <th scope="col">
                            @if($u->position->name === 'regular')
                                Пользователь
                            @elseif($u->position->name === 'moder')
                                Модератор
                            @else
                                Админ
                            @endif
                        </th>
                        <th scope="col">{{ $u->email ?? 'Не задан' }}</th>
                        <th scope="col">{{ $u->phone }}</th>
                        <th scope="col">{{ $u->profile->address->fullAddress ?? 'Не задан' }}</th>
                        <th scope="col">{{ $u->created_at }}</th>
                        <th scope="col">{{ $u->updated_at ?? 'Изменений нет' }}</th>

                        <span class="d-none">
                            @if($u->position->name === 'moder')
                                {{$count+=1}}
                            @endif
                        </span>

                    </tr>
                @endforeach
                <p>
                    <b>Всего модераторов назначено - {{ $count }}</b>
                </p>
                </tbody>
            </table>

            <div class="row pt-5 pb-2">
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection