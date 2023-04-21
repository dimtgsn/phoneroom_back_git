@extends('layouts.admin')
@section('content')

    <h1 class="pt-2 mb-3">Изменить изображения:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    <div class="">
                        <div class="card card-danger">
                            <div class="card-body">
                                <form action="{{ route('admin.banner_images.update') }}" method="post" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                    @csrf
                                    @method('patch')
                                    <div class="form-group mb-4" id="wrapp-main-images">
                                        <label for="paths" class="form-label">Изображения:</label>
                                        <div class="add_image d-block btn btn-secondary">Добавить новое изображение</div>
                                        @foreach($images as $img)
                                            <div id="edit-wrapp">
                                                <div class="image-main d-flex flex-column input-group mt-4">
                                                    <div class="w-25 mb-2 d-flex align-items-center">
                                                        <label for="positions[]" class="form-label">Картинка</label>
                                                        <input value="{{ $img->position }}" name="positions[{{$img->id}}]" type="number"
                                                               class="form-control col-6 ml-1 @error('positions') is-invalid @enderror"
                                                               min="1"
                                                               max="{{ $positonLast }}"
                                                               id="positions"
                                                               autocomplete="positions" required autofocus>
                                                    </div>
                                                    @error("positions")
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                    @enderror

                                                    <img src="{{ asset($img->path) }}" class="col-7 p-0 edit_image_banner" alt="Картинка {{ $img->position }}">
                                                    <div class="btn_edit_image_banner col-7 mt-2 btn btn-danger">Изменить изображение</div>
                                                </div>
                                                <div class="input-group mt-3">
                                                    <div class="custom-file d-none" style="cursor:pointer;">
                                                        <label for="paths[]" class="custom-file-label">Выберите файл</label>
                                                        <input type="file" name="paths[{{$img->id}}]" class="custom-file-input" id="paths">

                                                        @error("paths")
                                                        <span class="invalid-feedback" role="alert">
                                                         <strong>{{ $message }}</strong>
                                                    </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-group mt-3">
                                        <button type="submit" class="btn w-25 btn-danger btn-block">
                                            Сохранить
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
        let btn_edit_image_banner = document.querySelectorAll('.btn_edit_image_banner');

        const editImageBanner = ({target}) => {
            let editWrappBanner = target.parentElement.parentElement;
            let editImageBanner = editWrappBanner.querySelector('.edit_image_banner');
            let cs_file_banner = editWrappBanner.querySelector('.custom-file');
            console.log(editWrappBanner);
            console.log(target);
            if(cs_file_banner.classList.contains('d-none')){
                cs_file_banner.classList.remove('d-none');
                editImageBanner.classList.add('d-none');
                target.innerHTML = 'Отменить изменения';
            }
            else{
                cs_file_banner.classList.add('d-none');
                editImageBanner.classList.remove('d-none');
                target.innerHTML = 'Изменить изображение';
            }
        };

        for (let i = 0; i < btn_edit_image_banner.length; i++) {
            btn_edit_image_banner[i].addEventListener('click', editImageBanner, false)
        }
    </script>
@endsection
