@extends('layouts.admin')
@section('content')
    <h1 class="pt-2 mb-3">Написать ответ:</h1>
    <hr>
    <section class="h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-md-left w-100 h-100">
                <div class="card-wrapper col-8">
                    <div>
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Ответ к отзыву №{{ $comment->id }}</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.comments.store', $comment->id) }}" class="pt-3 my-login-validation" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-4">
                                        <h5>Автор ответа: <b>{{ Auth::user()->first_name }} ({{ Auth::user()->position->name }})</b></h5>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="comment" class="form-label">Текст ответа</label>
                                        <textarea name="comment" class="form-control @error('comment') is-invalid @enderror"
                                                  id="comment" required autocomplete="comment" autofocus>{{old('comment')}}</textarea>

                                        @error("comment")
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mt-5">
                                        <button type="submit" class="btn w-25 btn-info btn-block">
                                            Создать
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
