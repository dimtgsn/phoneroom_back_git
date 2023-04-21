@extends('layouts.admin')
@section('content')
    <h1 class="pt-2 mb-3">Изменить товар:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    <div class="">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">{{ $product->name }}</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.products.update', $product->slug) }}" method="post" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                    @csrf
                                    @method('patch')
                                    <div class="form-check mb-4">
                                        <input checked name="published" type="checkbox"
                                               class="form-check-input @error('published') is-invalid @enderror"
                                               id="published"
                                               autocomplete="published" autofocus>
                                        <label for="published" class="form-check-label"><b>Доступен для продажи</b></label>
                                        *(по умолчанию доступен)

                                        @error("published")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="name" class="form-label">Название</label>
                                        <input value="{{ $product->name }}" placeholder="{{ $product->name }}" name="name" type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               id="name"
                                               autocomplete="name" autofocus>

                                        @error("name")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="country" class="form-label">Страна</label>
                                        <span>*Мой склад</span>
                                        <input value="{{ $product->country }}" placeholder="{{ $product->country }}" name="country" type="text"
                                               class="form-control @error('country') is-invalid @enderror"
                                               id="country"
                                               autocomplete="country" autofocus>

                                        @error("country")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="image" class="form-label">Изображение</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <label for="image" class="custom-file-label">Выберите файл</label>
                                                <input value="{{ $product->image }}" type="file" name="image" class="custom-file-input" id="image">

                                                @error("image")
                                                <span class="invalid-feedback" role="alert">
                                                     <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="input-group-append">
                                                <span class="input-group-text">Загрузка</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="form-label">Дополнительные изображения</label>
                                        <br>
                                        @for($i=1; $i<=4; $i++)
                                            <label class="form-label">Позиция {{ $i }}</label>
                                            <div class="input-group mb-3">
                                                <div class="custom-file">
                                                    <label for="path_{{$i}}" class="custom-file-label">Выберите файл</label>
                                                    <input type="file" name="path_{{$i}}" class="custom-file-input" id="path_{{$i}}">
                                                    @error("path")
                                                    <span class="invalid-feedback" role="alert">
                                                         <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror
                                                </div>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">Загрузка</span>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="description" class="form-label">Описание</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                                  id="description" autocomplete="description" autofocus>{{ $product->description }}</textarea>

                                        @error("description")
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="price" class="form-label">Цена продажи</label>
                                        <input value="{{ $product->price  }}" placeholder="{{ $product->price }}" name="price" type="number"
                                               class="form-control @error('price') is-invalid @enderror"
                                               id="price"
                                               autocomplete="price" autofocus>

                                        @error("price")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="min_price" class="form-label">Минимальная цена</label>
                                        <span>*Мой склад</span>
                                        <input value="{{ $product->min_price }}" placeholder="{{ $product->min_price }}" name="min_price" type="text"
                                               class="form-control @error('min_price') is-invalid @enderror"
                                               id="min_price"
                                               autocomplete="min_price" autofocus>

                                        @error("min_price")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="purchase_price" class="form-label">Закупочная цена</label>
                                        <span>*Мой склад</span>
                                        <input value="{{ $product->purchase_price }}" placeholder="{{ $product->purchase_price }}" name="purchase_price" type="text"
                                               class="form-control @error('purchase_price') is-invalid @enderror"
                                               id="purchase_price"
                                               autocomplete="purchase_price" autofocus>

                                        @error("purchase_price")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="old_price" class="form-label">Зачеркнутая Цена</label>
                                        <span>*Не обязательно</span>
                                        <input value="{{ $product->old_price }}" placeholder="{{ $product->old_price }}" name="old_price" type="number"
                                               class="form-control @error('old_price') is-invalid @enderror"
                                               id="old_price"
                                               autocomplete="old_price" autofocus>

                                        @error("old_price")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="units_in_stock" class="form-label">Количество на складе</label>
                                        <input value="{{ $product->units_in_stock  }}" placeholder="{{ $product->units_in_stock }}" name="units_in_stock" type="number"
                                               class="form-control @error('units_in_stock') is-invalid @enderror"
                                               id="units_in_stock"
                                               autocomplete="units_in_stock" autofocus>

                                        @error("units_in_stock")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="min_balance" class="form-label">Неснижаемый остаток</label>
                                        <span>*Мой склад</span>
                                        <input value="{{ $product->min_balance }}" placeholder="{{ $product->min_balance }}" name="min_balance" type="text"
                                               class="form-control @error('min_balance') is-invalid @enderror"
                                               id="min_balance"
                                               autocomplete="min_balance" autofocus>

                                        @error("min_balance")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group" data-select2-id="80">
                                        <label for="tags" class="form-label">Теги</label>
                                        <div class="select2-purple" data-select2-id="79">
                                            <select name="tags_id[]" class="select2 select2-hidden-accessible" multiple="" data-placeholder="Выберите теги" data-dropdown-css-class="select2-purple" style="width: 100%;" data-select2-id="15" tabindex="-1" aria-hidden="true">
                                                @foreach($tags as $i => $tag )
                                                    @if(empty($product->tags[0]) !== true)
                                                        @foreach($product->tags as $product_tag )
                                                            <option {{ ($product_tag->name === $tag->name) ? 'selected':''}} value="{{ $tag->id }}" data-select2-id="{{$i + 82}}">{{ $tag->name }}</option>
                                                        @endforeach
                                                    @else
                                                    <option value="{{ $tag->id }}" data-select2-id="{{$i + 82}}">{{ $tag->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="rating" class="form-label">Рейтинг</label>
                                        <select name="rating" class="w-25 d-inline-block custom-select form-control-border" aria-label="rating" id="rating">
                                        @for($i=0.0; $i<=5.0; $i+=0.5)
                                                <option {{ ($product->rating == $i) ? 'selected':''}}
                                                        value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                        @error("rating")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="category_id" class="form-label mr-2">Категория</label>
                                        <select name="category_id" class="custom-select form-control-border"
                                                aria-label="category_id" id="category_id" onclick="add_properties( {{ $categories }} )">
                                            @foreach($categories as $i => $category)
                                                @if($i === 0)
                                                    <option
                                                            value="null">
                                                    </option>
                                                    @continue(true)
                                                @endif
                                                <option
                                                        {{ ($product->category_id === $category->id) ? 'selected':''}}
                                                        value="{{ $category->id }}">{{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mb-4" id="wo">
                                        <div class="option">
                                            <label for="options[]" class="form-label d-inline-block mr-2">Опции</label>
                                            <div class="add_option d-inline-block btn btn-secondary">+</div>
                                            @if($product->option)
                                                @if(is_array($product->option->options_json))
                                                    @foreach($product->option->options_json as $idx => $option)
                                                        <div class="options mt-3">
                                                            <input placeholder="Название" value="{{ $option['name'] }}" name="options[{{ $idx }}][name]" type="text"
                                                                   class="form-control w-75 d-inline-block @error('options') is-invalid @enderror"
                                                                   id="options1"
                                                                   autocomplete="options" autofocus list="options">

                                                            <div class="remove_option d-inline-block btn btn-danger mb-1 ml-1">-</div>

                                                            <div class="add_option_value d-inline-block btn mb-1 btn-outline-primary">+</div>
                                                            @foreach($option['values'] as $key => $value)
                                                                <div class="option_values">
                                                                    @if(!is_array($value))
                                                                        <input placeholder="Значение" value="{{ $value }}" name="options[{{ $idx }}][values][]" type="text"
                                                                               class="form-control w-25 mt-3 d-inline-block @error('options') is-invalid @enderror"
                                                                               id="options1"
                                                                               autocomplete="options" autofocus list="options_values">
                                                                        @if(isset($option['values']['colors']))
                                                                            @foreach($option['values']['colors'] as $i => $color)
                                                                                @if($key === $i)
                                                                                    <a id="color_option" class="btn ml-1 mr-3 btn-outline-dark d-inline-block">Изменить цвет</a>
                                                                                    <div id="color_option_div" class="d-none pl-1 pr-3" style="vertical-align: middle;">
                                                                                        <input type="color" id="color" name="options[{{ $idx }}][values][colors][]"
                                                                                               value='{{ $color }}'>
                                                                                    </div>
                                                                                @endif
                                                                            @endforeach
                                                                        @endif
                                                                        <div class="remove_option_value d-inline-block btn btn-outline-danger">-</div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                @else
                                                    @foreach(json_decode($product->option->options_json, true) as $idx => $option)
                                                        <div class="options mt-3">
                                                            <input placeholder="Название" value="{{ $option['name'] }}" name="options[{{ $idx }}][name]" type="text"
                                                                   class="form-control w-75 d-inline-block @error('options') is-invalid @enderror"
                                                                   id="options1"
                                                                   autocomplete="options" autofocus list="options">

                                                            <div class="remove_option d-inline-block btn btn-danger mb-1 ml-1">-</div>

                                                            <div class="add_option_value d-inline-block btn mb-1 btn-outline-primary">+</div>
                                                            @foreach($option['values'] as $key => $value)
                                                                <div class="option_values">
                                                                    @if(!is_array($value))
                                                                        <input placeholder="Значение" value="{{ $value }}" name="options[{{ $idx }}][values][]" type="text"
                                                                               class="form-control w-25 mt-3 d-inline-block @error('options') is-invalid @enderror"
                                                                               id="options1"
                                                                               autocomplete="options" autofocus list="options_values">
                                                                        @if(isset($option['values']['colors']))
                                                                            @foreach($option['values']['colors'] as $i => $color)
                                                                                @if($key === $i)
                                                                                    <a id="color_option" class="btn ml-1 mr-3 btn-outline-dark d-inline-block">Изменить цвет</a>
                                                                                    <div id="color_option_div" class="d-none pl-1 pr-3" style="vertical-align: middle;">
                                                                                        <input type="color" id="color" name="options[{{ $idx }}][values][colors][]"
                                                                                               value='{{ $color }}'>
                                                                                    </div>
                                                                                @endif
                                                                            @endforeach
                                                                        @endif
                                                                        <div class="remove_option_value d-inline-block btn btn-outline-danger">-</div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @else
                                                <div class="options mt-3">
                                                    <input placeholder="Название" value="{{ old('options') }}" name="options[0][name]" type="text"
                                                           class="form-control w-75 d-inline-block @error('options') is-invalid @enderror"
                                                           id="options1" required
                                                           autocomplete="options" autofocus list="options">

                                                    <div class="remove_option d-inline-block btn btn-danger mb-1 ml-1">-</div>

                                                    <div class="add_option_value d-inline-block btn mb-1 btn-outline-primary">+</div>
                                                    <div class="option_values">
                                                        <input placeholder="Значение" value="{{ old('options') }}" name="options[0][values][]" type="text"
                                                               class="form-control w-25 mt-3 d-inline-block @error('options') is-invalid @enderror"
                                                               id="options1" required
                                                               autocomplete="options" autofocus list="options_values">
                                                        <a id="color_option" class="btn ml-1 mr-3 btn-outline-dark d-inline-block">Выбрать цвет</a>
                                                        <div id="color_option_div" class="d-none pl-1 pr-3" style="vertical-align: middle;">
                                                            <input type="color" id="color" name="options[0][values][colors][]"
                                                                   disabled="disabled"
                                                                   value=''>
                                                        </div>
                                                        <div class="remove_option_value d-inline-block btn btn-outline-danger">-</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        @error("options")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-4" id="wr">
                                        <div class="property">
                                            <label for="properties[]" class="form-label d-inline-block mr-2">Характеристики</label>
                                            <div class="add_property d-inline-block btn btn-secondary">+</div>
                                            <div class="btn ml-2 btn-outline-secondary add_main_title">Добавить загаловок</div>
                                            @if($product->property !== null and $product->property->properties_json != false)
                                                @if(is_array($product->property->properties_json))
                                                    @foreach($product->property->properties_json as $property => $val)
                                                        <div class="properties mt-3">
                                                            @if(is_array($val))
                                                                <input placeholder="Заголовок" disabled="disabled" value="{{ $property }}" name="properties[]" type="text"
                                                                       class="form-control w-50 border-danger mb-5 d-none @error('properties') is-invalid @enderror"
                                                                       id="properties_title"
                                                                       autocomplete="properties" autofocus list="properties">
                                                                @foreach($val as $k => $v)
                                                                    <input placeholder="Название" value="{{ $k }}" name="properties[][]" type="text"
                                                                           class="form-control w-50 d-inline-block @error('properties') is-invalid @enderror"
                                                                           id="properties1"
                                                                           autocomplete="properties" autofocus>

                                                                    <input placeholder="Значение" value="{{ $v }}" name="properties[][]" type="text"
                                                                           class="form-control w-50 d-inline-block mt-3 @error('properties') is-invalid @enderror"
                                                                           id="properties1"
                                                                           autocomplete="properties" autofocus>
                                                                    <div class="remove_property d-inline-block btn btn-danger mb-1 ml-1">-</div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    @foreach(json_decode($product->property->properties_json, true) as $property => $val)
                                                        <div class="properties mt-3">
                                                            @if(is_array($val))
                                                                <input placeholder="Заголовок" disabled="disabled" value="{{ $property }}" name="properties[]" type="text"
                                                                       class="form-control w-50 border-danger mb-5 d-none @error('properties') is-invalid @enderror"
                                                                       id="properties_title"
                                                                       autocomplete="properties" autofocus list="properties">
                                                                @foreach($val as $k => $v)
                                                                    <input placeholder="Название" value="{{ $k }}" name="properties[][]" type="text"
                                                                           class="form-control w-50 d-inline-block @error('properties') is-invalid @enderror"
                                                                           id="properties1"
                                                                           autocomplete="properties" autofocus>

                                                                    <input placeholder="Значение" value="{{ $v }}" name="properties[][]" type="text"
                                                                           class="form-control w-50 d-inline-block mt-3 @error('properties') is-invalid @enderror"
                                                                           id="properties1"
                                                                           autocomplete="properties" autofocus>
                                                                    <div class="remove_property d-inline-block btn btn-danger mb-1 ml-1">-</div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @endif
                                            @else
                                                <div class="properties mt-3">
                                                    <input placeholder="Заголовок" disabled="disabled" value="{{ old('properties') }}" name="properties[]" type="text"
                                                           class="form-control w-50 border-danger mb-5 d-none @error('properties') is-invalid @enderror"
                                                           id="properties_title"
                                                           autocomplete="properties" autofocus list="properties">

                                                    <input placeholder="Название" value="{{ old("properties") }}" name="properties[][]" type="text"
                                                           class="form-control w-50 d-inline-block @error('properties') is-invalid @enderror"
                                                           id="properties_name" autocomplete="properties" autofocus list="properties">
                                                    <datalist id="properties">
                                                    </datalist>
                                                    <input placeholder="Значение" value="{{ old("properties") }}" name="properties[][]" type="text"
                                                           class="form-control w-50 d-inline-block mt-3 @error('properties') is-invalid @enderror"
                                                           id="properties1"
                                                           autocomplete="properties" autofocus list="properties_values">
                                                    <datalist id="properties_values">
                                                    </datalist>
                                                    <div class="remove_property d-inline-block btn btn-danger mb-5 ml-1">-</div>
                                                </div>
                                            @endif
                                        </div>
                                        @error("properties")
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="brand_id" class="form-label mr-2">Бренд</label>
                                        <select name="brand_id" class="custom-select form-control-border"
                                                aria-label="brand_id" id="parentSelect">
                                            @foreach($brands as $i => $brand)
                                                @if($i === 0)
                                                    <option
                                                            value="null">
                                                    </option>
                                                    @continue(true)
                                                @endif
                                                <option
                                                        {{ ($product->brand_id === $brand->id) ? 'selected':''}}
                                                        value="{{ $brand->id }}">{{ $brand->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="vat" class="form-label">НДС %</label>
                                        <span>*Мой склад</span>
                                        <select name="vat" class="custom-select form-control-border"
                                                aria-label="vat" id="vat">
                                            <option value="null"></option>
                                            <option {{ ($product->vat === -1) ? 'selected':''}} value="-1">без НДС</option>
                                            <option {{ ($product->vat === 0) ? 'selected':''}} value="0">0%</option>
                                            <option {{ ($product->vat === 10) ? 'selected':''}} value="10">10%</option>
                                            <option {{ ($product->vat === 18) ? 'selected':''}} value="18">18%</option>
                                            <option {{ ($product->vat === 20) ? 'selected':''}} value="20">20%</option>
                                        </select>
                                    </div>
                                    <div class="form-group mt-5">
                                        <button type="submit" class="btn w-25 btn-danger btn-block">
                                            Изменить
                                        </button>
                                    </div>
                                </form>
                                <div class="pt-5">
                                    <a class="btn mt-5 btn-outline-danger" href="{{ route('admin.products.index') }}">Назад</a>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        //properties
        const div = document.querySelector('.property')
        const button_add = document.querySelector('.add_property')
        const button_add_title = document.querySelector('.add_main_title');
        let button_remove = document.querySelectorAll('.remove_property')
        let wp = document.querySelector('#wp')
        let target = document.querySelector('.properties')

        const cloning = () => {
            const child  = target.cloneNode(true)
            if (child.querySelector('#properties_title').classList.contains('d-inline-block')){
                child.querySelector('#properties_title').classList.remove('d-inline-block')
                child.querySelector('#properties_title').disabled = 'disabled'
            }
            wp.appendChild(child);
            button_remove = document.querySelectorAll('.remove_property')
            for (let i = 0; i < button_remove.length; i++) {

                button_remove[i].addEventListener('click', remove_clon, false)
            }
        }

        const remove_clon = ({target}) => {
            const parent  = target.parentElement
            parent.remove()
            button_remove = document.querySelectorAll('.remove_property')
            for (let i = 0; i < button_remove.length; i++) {
                button_remove[i].addEventListener('click', remove_clon, false)
            }
        }

        button_add.addEventListener('click', cloning, false)
        for (let i = 0; i < button_remove.length; i++) {
            button_remove[i].addEventListener('click', remove_clon, false)
        }

        function add_properties(properties) {
            let select = document.querySelector('#category_id');
            let value = select.value;
            if (value !== "null"){
                if (properties[value-1]['properties'][0]){
                    let properties_keys = document.querySelector('#properties');
                    let properties_values = document.querySelector('#properties_values');
                    properties_keys.innerHTML = '';
                    properties_values.innerHTML = '';
                    for (let property of properties[value-1]['properties']) {
                        let properties_json = JSON.parse(property['properties_json']);
                        for(let k in properties_json){
                            properties_keys.innerHTML += `<option value='${k}' />`;
                            properties_values.innerHTML += `<option value='${properties_json[k]}' />`;
                        }
                    }

                }
            }
            // add_options(properties);
        }

        function add_title() {
            let properties = document.querySelectorAll('.properties');
            properties[properties.length-1].querySelector('#properties_title').classList.toggle('d-inline-block');
            properties[properties.length-1].querySelector('#properties_title').disabled = '';
            // target.parentElement.querySelector('#color_option_div').classList.toggle('d-inline-block');
        }
        button_add_title.addEventListener('click', add_title, false)




        //options
        const div2 = document.querySelector('.option')
        const button_add2 = document.querySelector('.add_option')
        let button_add_value = document.querySelectorAll('.add_option_value')
        let button_remove2 = document.querySelectorAll('.remove_option')
        let button_remove_value = document.querySelectorAll('.remove_option_value')
        let wo = document.querySelector('#wo')
        let target2 = document.querySelector('.options')
        let target_value = document.querySelector('.option_values')

        let color_option = document.querySelectorAll('#color_option');
        let color_option_div = document.querySelectorAll('#color_option_div');

        const cloning2 = () => {
            target2 = document.querySelectorAll('.options')
            let child  = target2[target2.length-1].cloneNode(true)
            wo.appendChild(child)
            let child_input = child.getElementsByTagName("input")
            let child_input_last = target2[target2.length-1].getElementsByTagName("input")
            button_remove2 = document.querySelectorAll('.remove_option')
            child_input[0].name = `options[${Number(child_input_last[0].name[8])+1}][name]`
            for (let i = 1; i < child_input.length; i++) {
                child_input[i].name = `options[${Number(child_input_last[1].name[8])+1}][values][]`;
            }
            for (let i = 0; i < button_remove2.length; i++) {
                button_remove2[i].addEventListener('click', remove_clon2, false)
            }
            button_add_value = document.querySelectorAll('.add_option_value')
            for (let i = 0; i < button_add_value.length; i++) {
                button_add_value[i].addEventListener('click', cloning_value, false)
            }
            color_option = document.querySelectorAll('#color_option');
            // console.log(color_option[0]);
            for (let i = 0; i < color_option.length; i++) {
                color_option[i].addEventListener('click', toggle_color, false)
            }
        }

        const cloning_value = ({target}) => {
            let parent  = target.parentElement
            target_value = parent.querySelector('.option_values');
            let child  = target_value.cloneNode(true)
            parent.appendChild(child)
            button_remove_value = document.querySelectorAll('.remove_option_value')
            for (let i = 1; i < button_remove_value.length; i++) {
                button_remove_value[i].addEventListener('click', remove_clon_value, false)
            }
            color_option = document.querySelectorAll('#color_option');
            for (let i = 0; i < color_option.length; i++) {
                color_option[i].addEventListener('click', toggle_color, false)
            }
        }

        const remove_clon2 = ({target}) => {
            const parent  = target.parentElement
            parent.remove()
            button_remove2 = document.querySelectorAll('.remove_option')
            for (let i = 0; i < button_remove2.length; i++) {
                button_remove2[i].addEventListener('click', remove_clon2, false)
            }
            color_option = document.querySelectorAll('#color_option');
            for (let i = 0; i < color_option.length; i++) {
                color_option[i].addEventListener('click', toggle_color, false)
            }
        }

        let remove_clon_value = ({target}) => {
            const parent  = target.parentElement
            parent.remove()
            button_remove_value = document.querySelectorAll('.remove_option_value')
            for (let i = 1; i < button_remove_value.length; i++) {
                button_remove_value[i].addEventListener('click', remove_clon_value, false)
            }
            color_option = document.querySelectorAll('#color_option');
            for (let i = 0; i < color_option.length; i++) {
                color_option[i].addEventListener('click', toggle_color, false)
            }
        }

        button_add2.addEventListener('click', cloning2, false)
        for (let i = 0; i < button_remove2.length; i++) {
            button_remove2[i].addEventListener('click', remove_clon, false)
        }

        for (let i = 0; i < button_add_value.length; i++) {
            button_add_value[i].addEventListener('click', cloning_value, false)
        }

        for (let i = 1; i < button_remove_value.length; i++) {
            button_remove_value[i].addEventListener('click', remove_clon_value, false)
        }

        let toggle_color = ({target}) => {

            target.parentElement.querySelector('#color_option_div').classList.toggle('d-inline-block');
            if (target.parentElement.querySelector('#color_option_div').classList.contains('d-inline-block')){
                target.textContent = 'Удалить цвет';
                target.parentElement.querySelector('#color').disabled = '';
            }
            else {
                target.textContent = 'Выбрать цвет';
                target.parentElement.querySelector('#color').disabled = 'disabled';
            }
        }

        for (let i = 0; i < color_option.length; i++) {
            color_option[i].addEventListener('click', toggle_color, false)
        }
    </script>
@endsection

