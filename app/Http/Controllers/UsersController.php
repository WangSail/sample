<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests;
use Mail;
use Auth;
class UsersController extends Controller
{
    //权限过滤
    public function __construct()
    {
        $this->middleware('auth', [            
            'except' => ['show', 'create', 'store','index','confirmEmail']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    //用户的激活操作
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
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
        $statuses=$user->statuses()
                        ->orderBy('created_at', 'desc')
                        ->paginate(30);
                        //compact方法可以同时接收多个参数
        return view('users.show', compact('user','statuses'));
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
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');

        // session()->flash('success', '欢迎，您将在这里开启一段梦一般的旅程~');
        // return redirect()->route('users.show', [$user]);
    }
     //邮件发送
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@yousails.com';
        $name = 'Aufree';
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";
        /** 
        第一个参数是包含邮件消息的视图名称。
        第二个参数是要传递给该视图的数据数组。
        最后是一个用来接收邮件消息实例的闭包回调，我们可以在该回调中自定义邮件消息的发送者、接收者、邮件主题等信息。
        */
        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
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

     public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

     public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }
}
 