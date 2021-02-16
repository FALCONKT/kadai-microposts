<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    
    /**
     * このユーザが所有する投稿。（ Micropostモデルとの関係を定義）
     */
    // 一　対　多　の関係
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    // microposts の大量のDataを読み込む　文字列　microposts　　にて
    public function loadRelationshipCounts()
    {
        $this->loadCount('microposts');
    }
    
    // 関係するModelの件数と投稿の一覧を取得して、Viewに渡します。
    public function show($id)
    {
        // idの値でUserを検索して取得
        $user = User::findOrFail($id);

        // 関係するModelの件数を読み込み
        $user->loadRelationshipCounts();

        // Userの投稿一覧を作成日時の降順で取得
        $microposts = $user->microposts()->orderBy('created_at', 'desc')->paginate(10);

        // User詳細Viewでそれらを表示
        return view('users.show', [
            'user' => $user,
            'microposts' => $microposts,
        ]);
    }
    
}
