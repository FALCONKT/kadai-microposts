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
        // idの値でユーザを検索して取得
        $user = User::findOrFail($id);

        // ユーザ詳細ビューでそれを表示
        return view('users.show', [
            'user' => $user,
        ]);
    }
    
}
