@extends('layouts.admin')
@section('content')

    <h1 class="pt-2 mb-3">Добавить изображение:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    <div class="">
                        <div class="card card-danger">
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.main_images.store') }}" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-4" id="wrapp_main-images">
                                        <label for="paths" class="form-label">Изображения</label>
                                        <div class="add_image_main d-block btn btn-secondary">Добавить новое изображение</div>
                                        <h5 class="error-limit-images text-danger mt-2 text-bold text-md"></h5>
                                        @for($i=0;$i<10;$i++)
                                            @if($i === 0)
                                                <div class="image_main input-group mt-4">
                                                    <div class="custom-file">
                                                        <label for="paths[]" style="cursor:pointer;" class="custom-file-label">Выберите файл</label>
                                                        <input style="cursor:pointer;" type="file" name="paths[{{$i}}]" class="custom-file-input" required>

                                                        @error("paths")
                                                        <span class="invalid-feedback" role="alert">
                                                     <strong>{{ $message }}</strong>
                                                </span>
                                                        @enderror
                                                    </div>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">Загрузка</span>
                                                    </div>
                                                    <div class="remove_image_main d-inline-block btn btn-danger">&#10008;</div>
                                                </div>
                                            @else
                                                <div class="image_main d-none input-group mt-4">
                                                    <div class="custom-file">
                                                        <label for="paths[]" style="cursor:pointer;" class="custom-file-label">Выберите файл</label>
                                                        <input style="cursor:pointer;" type="file" name="paths[{{$i}}]" class="custom-file-input" >

                                                        @error("paths")
                                                        <span class="invalid-feedback" role="alert">
                                                     <strong>{{ $message }}</strong>
                                                </span>
                                                        @enderror
                                                    </div>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">Загрузка</span>
                                                    </div>
                                                    <div class="remove_image_main d-inline-block btn btn-danger">&#10008;</div>
                                                </div>
                                            @endif
                                        @endfor
                                    </div>
                                    <div class="form-group mt-3">
                                        <button type="submit" class="btn w-25 btn-danger btn-block">
                                            Создать
                                        </button>
                                    </div>
                                </form>
                                <div class="pt-4">
                                    <a class="btn btn-outline-danger" href="{{ route('admin.main_images.index') }}">Назад</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const button_add_main = document.querySelector('.add_image_main');
        const images_main = document.querySelectorAll('.image_main');
        const button_remove_main = document.querySelectorAll('.remove_image_main');
        const errorLimitImages = document.querySelector('.error-limit-images');
        let count = 1;
        const addImageMain = () => {
            count = 1;
            for (let i = 0; i < images_main.length; i++) {
                if(images_main[i].classList.contains('d-none')){
                    images_main[i].classList.remove('d-none');
                    break;
                }
                else{
                    count++;
                }
            }
            if(count === 10){
                errorLimitImages.innerHTML = 'Достигнут лимит в 10 изображений';
            }
        };

        const removeImageMain = ({target}) => {
            const parent  = target.parentElement;
            console.log(parent);
            parent.classList.add('d-none');
            parent.querySelector('.custom-file-input').setAttribute('disabled', "1");
            if (count === 10){
                errorLimitImages.innerHTML = '';
            }
        };
        button_add_main.addEventListener('click', addImageMain, false);
        for (let i = 0; i < button_remove_main.length; i++) {
            button_remove_main[i].addEventListener('click', removeImageMain, false)
        }
    </script>
@endsection
