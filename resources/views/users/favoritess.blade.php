@if (count($microposts) > 0)
{{-- @if ((isset($microposts) and count($microposts))>0)--}}

    <ul class="list-unstyled">
        @foreach ($microposts as $micropost)
            <li class="media p-2 mt-1 mr-2 border">
                {{-- ユーザのメールアドレスをもとにGravatarを取得して表示 --}}
                <!--<img class="mr-2 rounded" src="{{ Gravatar::get($user->email, ['size' => 50]) }}" alt="">-->
                <!--<div class="media-body">-->
                    
                    <!--<div>-->
                    <!--    {{ $micropost->content }}-->
                    <!--</div>-->
                    
                    <!--<div>-->
                    <!--    {{-- ユーザ詳細ページへのリンク --}}-->
                    <!--    <p>{!! link_to_route('users.show', 'View profile', ['user' => $user->id]) !!}</p>-->
                    <!--</div>-->


                <!--{{-- 投稿の所有者のメールアドレスをもとにGravatarを取得して表示 --}}-->
                <img class="mr-3 rounded " src="{{ Gravatar::get($micropost->user->email, ['size' => 50]) }}" alt="">
                <div class="media-body">

                    <div> 
                        <!--{{-- 投稿の所有者のユーザ詳細ページへのリンク --}}-->
                        {!! link_to_route('users.show', $micropost->user->name, ['user' => $micropost->user->id]) !!}
                        <span class="text-muted">posted at {{ $micropost->created_at }}</span>
                    </div>
                    
                    <div>
                        <!--{{-- 投稿内容 --}}-->
                        <p class="mb-0">{!! nl2br(e($micropost->content)) !!}</p>
                    </div>

                    <div>                    
                    @if (Auth::id() == $micropost->user_id)
                            <!--{{-- 投稿削除ボタンのフォーム --}}-->
                            {!! Form::open(['route' => ['microposts.destroy', $micropost->id], 'method' => 'delete']) !!}
                                {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-sm']) !!}
                            {!! Form::close() !!}
                            
                    @endif
                    </div>
                    
                    <div>
                    <!--{{-- お気に入り　Button　のForm --}}-->
                    @include('user_favorite.favorite_button')
                    </div>

                </div>
            </li>
        @endforeach
    </ul>

    {{-- ページネーションのリンク --}}
    {{ $microposts->links() }}

@endif