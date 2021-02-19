@if (Auth::id() != $user->id)
    @if (Auth::user()->is_favoriting($micropost->id))
        {!! Form::open(['route' => ['favorites.unfavorite',$micropost->id], 'method' => 'delete']) !!}
         {!! Form::submit('お気に入り', ['class' => "btn btn-success btn-block"]) !!}
        {!! Form::close() !!}
    @else
        {!! Form::open(['route' => ['favorites.favorite', $micropost->id]]) !!}
        {!! Form::submit('お気に入りしていない', ['class' => "btn btn-light btn-block"]) !!}
        {!! Form::close() !!}
    @endif
@endif