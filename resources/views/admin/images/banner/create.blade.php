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
                                <form method="POST" action="{{ route('admin.banner_images.store') }}" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-4">
                                        <label for="paths" class="form-label">Изображения</label>
                                        <div class="add_image_banner d-block btn btn-secondary">Добавить новое изображение</div>
                                        <h5 class="error-limit-images_banner text-danger mt-2 text-bold text-md"></h5>
                                        @for($i=0;$i<10;$i++)
                                            @if($i === 0)
                                                <div class="image_banner input-group mt-4">
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
                                                    <div class="remove_image_banner d-inline-block btn btn-danger">&#10008;</div>
                                                </div>
                                            @else
                                                <div class="image_banner d-none input-group mt-4">
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
                                                    <div class="remove_image_banner d-inline-block btn btn-danger">&#10008;</div>
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
                                    <a class="btn btn-outline-danger" href="{{ route('admin.banner_images.index') }}">Назад</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const button_add_banner = document.querySelector('.add_image_banner');
        const images_banner = document.querySelectorAll('.image_banner');
        const button_remove_banner = document.querySelectorAll('.remove_image_banner');
        const errorLimitImagesBanner = document.querySelector('.error-limit-images_banner');
        let count_banner = 1;
        const addImageBanner = () => {
            count_banner = 1;
            for (let i = 0; i < images_banner.length; i++) {
                if(images_banner[i].classList.contains('d-none')){
                    images_banner[i].classList.remove('d-none');
                    break;
                }
                else{
                    count_banner++;
                }
            }
            if(count_banner === 10){
                errorLimitImagesBanner.innerHTML = 'Достигнут лимит в 10 изображений';
            }
        };

        const removeImageBanner = ({target}) => {
            const parent  = target.parentElement;
            console.log(parent);
            parent.classList.add('d-none');
            parent.querySelector('.custom-file-input').setAttribute('disabled', "1");
            if (count_banner === 10){
                errorLimitImagesBanner.innerHTML = '';
            }
        };
        button_add_banner.addEventListener('click', addImageBanner, false);
        for (let i = 0; i < button_remove_banner.length; i++) {
            button_remove_banner[i].addEventListener('click', removeImageBanner, false)
        }
    </script>
@endsection
