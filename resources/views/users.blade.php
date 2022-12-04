@extends('layout')
@section('title')
    Главная
@endsection
@section('main_content')
<div class="container">
    @foreach ($results as $user)
    <div class="list-group w-auto">
        <a href="/profile/{{$user->id}}" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true">
          <img src={{$user->avatar ? asset('/storage/'.$user->avatar) : "https://bootdey.com/img/Content/avatar/avatar1.png"}} alt="twbs" width="32" height="32" class="rounded-circle flex-shrink-0">
          <div class="d-flex gap-2 w-100 justify-content-between">
            <div>
              @if(!$user->firstname && !$user->lastname)
              <h6 class="mb-0">Нет имени</h6>
              @else
                <h6 class="mb-0">{{$user->firstname . ' ' . $user->lastname}}</h6>
              @endif
              <p class="mb-0 opacity-75">{{$user->email}}</p>
            </div>
            <small class="opacity-50 text-nowrap">{{$user->name}}</small>
          </div>
        </a>
    </div>
    @endforeach
</div>
@endsection