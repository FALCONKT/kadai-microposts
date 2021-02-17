
<?php

// =============================================================

// migration
// $ php artisan make:migration create_favorites_table --create=favorites

up() と down()

    public function up()
    {
        //Table名指定
        Schema::create('favorites', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('user_id')->unsigned()->index();

            $table->integer('micropost_id')->unsigned()->index();
            
            $table->timestamps();

            // 外部KEY設定
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->foreign('micropost_id')->references('id')->on('users')->onDelete('cascade');

            // user_idとfollow_idの組み合わせの重複を許さない
            $table->unique(['user_id', 'micropost_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_follow');
    }



// =============================================================

// app/User.php

   // ADD　1
   // User　がお気に入り している　つぶやき
    public function favorite(){
        //UserClassの中の　中間Table名　の中の　user_id　と　micropost_id　を返す
        // 更に　その時間を保存
        return $this->belongsToMany(User::class,'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
    
    // User　がお気に入り されている　つぶやき
    public function favorite_d(){
        //UserClassの中の　中間Table名　の中の　micropost_id　と　user_id　を返す
        // 更に　その時間を保存
        return $this->belongsToMany(User::class,'favorites','micropost_id','user_id')->withTimestamps();
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
        
        // 投稿IDが 自分自身のではないか の確認
        // これではUserID　と　現在のUserIdを比較しているに過ぎない　
        // $its_me = $this->id == $userId;
        
        // そのため　is_favoriting($micropostId)＝Favoriteされた　$micropostId　が　現在の　$micropostId　と等しいか比較
        // $its_me = $this->is_favoriting($micropostId) == $micropostId;
        //既に　存在しているためこの指定は不要
    
        if ($exist || $its_me) {
            // 既に favorite　していれば 何もしない
            return false;
        } else {
            // 未 favorite であれば favorite =　$micropostId　する
            $this->favorite()->attach($micropostId);
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
        
        
        // 投稿IDが 自分自身のではないか の確認
        // これではUserID　と　現在のUserIdを比較しているに過ぎない　
        // $its_me = $this->id == $userId;
        // そのため　is_favoriting($micropostId)＝Favoriteされた　$micropostId　が　現在の　$micropostId　と等しいか比較        // $its_me = $this->id == $micropostId;
       // $its_me = $this->is_favoriting($micropostId) == $micropostId;
        //既に　存在しているためこの指定は不要

    
        if ($exist && !$its_me) {
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
    public function is_favariting($micropostId)
    {
                      //this は　自分のUserのId →　つぶやき関数実行　→
                      //中間TableのつぶやきId=micropost_id　が　実際のつぶやきId＝$micropostId
                      //として　存在しているか
        return $this->favorite()->where('micropost_id', $micropostId)->exists();
    }


    // ADD　2
    // お気に入りの一覧を取得する機能
    // お気に入りの　数　を取得する　favorites　と複数形での関数名称
    // 参考　Follow　Follower　TimeLine用のMicropostを取得するためのMethodを実装
    public function favorites(){

        // 数を返す！！　this は　UserのId　　UserModelから　favorites の数を取ってくる。

        // 現在形の関数名の方を実行　pluck() は与えられた引数のTableのColumn名だけを抜き出す命令　それを配列化する
        // お気に入りしたUserId 配列　は　User　が　favorite している　micropost　の　Id　を　選んで取得し　配列化する     
        // $favorite_user_ids = $this->favorite()->pluck('〇〇.id')->toArray();
        $favorite_user_ids = $this->favorite()->pluck('microposts.id')->toArray();

        // その配列に　自分の micripost(投稿)Id も追加する　自分自身のmicropostも含めるため
        $favorite_user_ids[] = $this->id;

 // microposts Table　の user_id Columnで $follow_user_ids の中にある micropostsのid を含むもの全てを取得して return
        return $this->belongsToMany(Micropost::whereIn('user_id', $favorite_user_ids)）;

    }





// =============================================================

// php artisan tinker 　　Test！！！！！

use App\User

use App\Micropost

$user1 = User::find(1)
// userの一人目のことを取得している

$micropost_any = Micropost::find(5)
// 5としている

$user1->favorited($micropost_any->id)
// favorite 実行

$user1->favorite()->get()
// その中身参照

$user1->unfavorited($micropost_any->id)
// unfavarited　実行

$user1->favorites()->get()
// favorites　Tableから該当 Dataが消える。
// その中身は　空　に

// =============================================================

// /microposts/app/Micropost.php
    
    // userの情報をつなぐ　多　対　多
    public function favorite_users()
    {
        return $this->belongsToMany(User::class);
    }


// =============================================================

// app/Http/Controllers/Controller.php（class部分のみ抜粋） 

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function counts($user) {
        $count_microposts = $user->microposts()->count();
        $count_followings = $user->followings()->count();
        $count_followers = $user->followers()->count();

        // ADD
        // 変数名指定　実際のお気に入り数
        // App/User の　変数user　に　favorites()　関数使用し　を　count()　関数使用する
        // favorites()　はお気に入りした　数　のみ取得している
　      $count_favorites = $user->favorites()->count();

        return [
            'count_microposts' => $count_microposts,
            'count_followings' => $count_followings,
            'count_followers' => $count_followers,

            // ADD
            // 変数名指定　実際のお気に入り数
            // favorites()　経由で来た　先に設定した　変数$count_favorite 　を　文字列として　返す
            'count_favorites' => $count_favorites,
        ];
    }

}

// =============================================================




// app/Http/Controllers/UsersController.php
