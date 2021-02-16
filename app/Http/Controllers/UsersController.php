<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\User; // 追加
// Modelを使用する　名前空間指定

class UsersController extends Controller
{
    
    // Userの一覧を返す
    public function index()
    {
        //User一覧をidの降順で取得
        $users = User::orderBy('id', 'desc')->paginate(10);

        //User一覧Viewでそれを表示
        return view('users.index', [
            'users' => $users,
        ]);
    }
    
    
    // Userの詳細を返す
    public function show($id)
    {
        // idの値でUserを検索して取得
        $user = User::findOrFail($id);

        // 追加
        // 他の各種Modelの件数を取得
        $user->loadRelationshipCounts();
        // $user =LogInUser　が　他のMpdel=ここではMicropostsのModel内での
        // 件数を取得している
        
        // Userの投稿一覧を作成日時の降順で取得
        $microposts = $user->microposts()->orderBy('created_at', 'desc')->paginate(10);


        // User詳細Viewでそれ　らを表示
        return view('users.show', [
            'user' => $user,
            
            // 追加
            'microposts' => $microposts,
        ]);
    }
    
}
