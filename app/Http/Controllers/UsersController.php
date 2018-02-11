<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests;

class UsersController extends Controller
{
    //权限过滤
    public function __construct()
    {
        $this->middleware('auth', [            
            'except' => ['show', 'create', 'store','index']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
     
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    //注册
    public function create()
    {
    	return view('users.create'); 
    }
    
    //个人信息显示
     public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    //处理数据
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

         $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
         
        session()->flash('success', '欢迎，您将在这里开启一段梦一般的旅程~');
        return redirect()->route('users.show', [$user]);
    }

    //编辑信息
    public function edit(User $user)
    {   
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }
    
    /**
    (update)
    首先，我们将用户密码验证的 required 规则换成 nullable，这意味着当用户提供空白密码时也会通过验证，因此我们需要对传入的 password 进行判断，当其值不为空时才将其赋值给 data，避免将空白密码保存到数据库中
    */
    public function update(User $user,Request $request)
    {
       $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
}
 