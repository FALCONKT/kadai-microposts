<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MicropostsController extends Controller
{
    public function index()
    {
        $data = [];
        if (\Auth::check()) { // 認証済みの場合
            
            // 認証済みUserを取得
            $user = \Auth::user();
            
            // Userの投稿の一覧を作成日時の降順で取得
            // （後のChapterで他Userの投稿も取得するように変更しますが、
            // 現時点ではこのUserの投稿のみ取得します）
            // →microposts()　→　feed_microposts()　へ変更
            $microposts = $user->feed_microposts()->orderBy('created_at', 'desc')->paginate(10);


            $data = [
                'user' => $user,
                'microposts' => $microposts,
            ];
        }

        // WelcomeViewでそれらを表示
        return view('welcome', $data);
    }

    // 投稿保存　Action
    public function store(Request $request)
    {
        // Varidation
        $request->validate([
            'content' => 'required|max:255',
        ]);

        // 認証済みUser（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->microposts()->create([
            'content' => $request->content,
        ]);

        // 前のURLへRedirectさせる
        return back();
    }
    
    // 投稿削除Button
    public function destroy($id)
    {
        // idの値で投稿を検索して取得
        $micropost = \App\Micropost::findOrFail($id);

        // 認証済みユーザ（閲覧者）がその投稿の所有者である場合は、投稿を削除
        if (\Auth::id() === $micropost->user_id) {
            $micropost->delete();
        }

        // 前のURLへRedirectさせる
        return back();
    }
    
}



