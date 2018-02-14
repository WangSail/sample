<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;

class SessionsController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    
    // 启动
    public function create()
    {
    	return view('session.create');
    }

    //验证
    public function store(Request $request)
    {
       $credentials = $this->validate($request, [
           'email' => 'required|email|max:255',
           'password' => 'required'
       ]);
       if (Auth::attempt($credentials,$request->has('remember'))) {
            if(Auth::user()->activated) {
          //登陆成功后相关的操作
             	session()->flash('success','欢迎回来！');
             	return redirect()->intended(route('users.show', [Auth::user()]));
             } else {
               Auth::logout();
               session()->flash('warning','你的账号未激活，请检查邮件中的注册邮件进行激活');
               return redirect('/');
             }
             } else {
              //登陆失败后相关的操作
              session()->flash('danger','很抱歉，您的密码与邮箱不匹配！');
              return redirect()->back();
              }
              //注册后自动登陆
              Auth::login($user);
              session()->flash('success','欢迎，您将开启一段梦一般的旅程~~');
              return redirect()->route('users.show',['$user']);
    }

    //退出
    public function destroy()
    {
    	Auth::logout();
    	session()->flash('success','您已成功退出！');
    	return redirect('login');
    }
}
