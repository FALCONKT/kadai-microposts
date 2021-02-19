<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    

    
    
    // お気に入りした場合
    public function store(Request $request, $id)
    {
    	//過去形の関数名の方
        \Auth::user()->favorited($id);
        return back();
        //若しくは　
       	//redirect あたり
    }
    
    // お気に入りを辞めた場合
    public function destroy($id)
    {
    	//過去形の関数名の方　unの方
        \Auth::user()->unfavorited($id);
        return back();
        //若しくは　
       	//redirect あたり
    }
}
