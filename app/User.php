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
    
    //Model内　関数経由　Table 関連　microposts の件数を読み込む　文字列　microposts　　にて
    //Model内　関数経由 Table関連 followings を追加
    //Model内　関数経由 Table関連 followers を追加
    //Model内　関数経由 Table関連 favorites を追加
    public function loadRelationshipCounts()
    {
        // microposts の件数　
        // folloingの件数　followerの件数　を取得
        $this->loadCount(['microposts','followings','followers','favorites']);    
        
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
    
    
    // 多　対　多　
    /**
     * このユーザがフォロー中のユーザ。（ Userモデルとの関係を定義）
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    // 多　対　多　
    /**
     * このユーザをフォロー中のユーザ。（ Userモデルとの関係を定義）
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    // followings()　実行　DBへ反映
    /**
     * $userIdで指定されたユーザをフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function follow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 対象が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist || $its_me) {
            // すでにフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }

    // followings()　解除　DBへ反映
    /**
     * $userIdで指定されたユーザをアンフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function unfollow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 対象が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist && !$its_me) {
            // すでにフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }


    // followings()　存在判定
    /**
     * 指定された $userIdのユーザをこのユーザがフォロー中であるか調べる。フォロー中ならtrueを返す。
     *
     * @param  int  $userId
     * @return bool
     */
    public function is_following($userId)
    {
        // フォロー中ユーザの中に $userIdのものが存在するか
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    /**
     * このユーザとフォロー中ユーザの投稿に絞り込む。
     */
    public function feed_microposts()
    {
        // このユーザがフォロー中のユーザのidを取得して配列にする
        $userIds = $this->followings()->pluck('users.id')->toArray();
        // このユーザのidもその配列に追加
        $userIds[] = $this->id;
        // それらのユーザが所有する投稿に絞り込む
        return Micropost::whereIn('user_id', $userIds);
    }
    
    
    
    
    
    // 多　対　多 の関係強化   
    // micropostの情報をつなぐ
    // User　がお気に入り している　つぶやき
    public function favorites(){
        //UserClassの中の　中間Table名　の中の　user_id　と　micropost_id　を返す　第二引数は中間Table名
        // 更に　その時間を保存
        return $this->belongsToMany(Micropost::class,'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
    


    // ！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
    // favarited       unfavorited　　の関係
    // 過去形の関数名称に。
    
    // お気に入りに入れる、お気に入りを解除するという機能
    // ！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
    
    // お気に入りをするための動き　ゆくゆくButton選択　// 選択するのは　$micropostId
    public function favorited($micropostId)
    {
        // 既に favorites　しているか の確認　　関数は　別で定義
        // $this->id はLogInUserのuser_id   User内のthisは全般にUser自身のLogInID
        // $micropostId　は　MicropostController経由で来る
        $exist = $this->is_favoriting($micropostId);
        
        // 投稿IDが 自分自身のではないか の確認    ここではしない　　自分の投稿も評価

        // そのため　is_favoriting($micropostId)＝Favoriteされた　$micropostId　が　現在の　$micropostId　と等しいか比較
        // $its_me = $this->is_favoriting($micropostId) == $micropostId;
        //既に　存在しているためこの指定は不要
    
        if ($exist) {
            // 既に favorite　していれば 何もしない
            return false;
        } else {
            // 未 favorite であれば favorites =　$micropostId　する
            $this->favorites()->attach($micropostId);
            return true;
        }
    }

    // お気に入りを消すための動き　ゆくゆくButton選択　選択するのは　$micropostId
    public function unfavorited($micropostId)
    {
        // 既に favorites　しているか の確認　　関数は　別で定義
        // $this->id はLogInUserのuser_id   User内のthisは全般にUser自身のLogInID
        // $micropostId　は　MicropostController経由で来る
        $exist = $this->is_favoriting($micropostId);
        
        
        // 投稿IDが 自分自身のではないか の確認    ここではしない　　自分の投稿も評価

    
        if ($exist) {
            // 既に favorites していれば favorite　＝　$micropostId を外す
            $this->favorites()->detach($micropostId);
            return true;
        } else {
            // 未 favorites であれば何もしない
            return false;
        }
    }
    
    // お気に入りにしているか確認する
    //is_favoriting 関数定義(独自)　favorited　unfavarited　で使用する。
    // favarite している　=　favarit「ing」 から 関数名設定
    // 投稿をClikcするので$micropostIdが入ってくる。
    public function is_favoriting($micropostId)
    {
                      //this は　自分のUserのId →　つぶやき関数実行　→
                      //中間TableのつぶやきId=micropost_id　が　実際のつぶやきId＝$micropostId
                      //として　存在しているか
        return $this->favorites()->where('micropost_id', $micropostId)->exists();
    }

    // // ADD2
    // // TimeLine用のお気に入りを取得するためのMethod
    // // お気に入りの一覧を取得する機能のための　Model関数
    // // お気に入りの　数　を取得する　favorites　と複数形での関数名称
    // // 参考　Follow　Follower　TimeLine用のMicropostを取得するためのMethodを実装
    // public function favorites(){

    //     // 数を返す！！　this は　UserのId　　UserModelから　favorites の数を取ってくる。

    //     // 現在形の関数名の方を実行　pluck() は与えられた引数のTableのColumn名だけを抜き出す命令　それを配列化する
    //     // お気に入りしたUserId 配列　は　User　が　favorite している　micropost　の　Id　を　選んで取得し　配列化する     
    //     // $favorite_user_ids = $this->favorite()->pluck('〇〇.id')->toArray();
    //     $favorite_user_ids = $this->favorite()->pluck('microposts.id')->toArray();

    //     // その配列に　自分の micripost(投稿)Id も追加する　自分自身のmicropostも含めるため
    //     $favorite_user_ids[] = $this->id;

    //      // microposts Table　の user_id Columnで $follow_user_ids の中にある micropostsのid を含むもの全てを取得して return
    //     return $this->belongsToMany(Micropost::whereIn('user_id', $favorite_user_ids));

    // }
    

    
    
    
}
