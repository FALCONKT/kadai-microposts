<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserFollowController extends Controller
{
    /**
     * ユーザをフォローするアクション。
     *
     * @param  $id  相手ユーザのid
     * @return \Illuminate\Http\Response
     */
    public function store($id)
    {
        // 認証済みUSer（閲覧者）が、 idのUSerをfollowする
        \Auth::user()->follow($id);
        // 前のURLへRedirectさせる
        return back();
    }

    /**
     * ユーザをアンフォローするアクション。
     *
     * @param  $id  相手ユーザのid
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // 認証済みUser（閲覧者）が、 idのUSerをanfollowする
        \Auth::user()->unfollow($id);
        // 前のURLへRedirectさせる
        return back();
    }



}
