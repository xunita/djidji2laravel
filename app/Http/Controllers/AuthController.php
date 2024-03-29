<?php

namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;



class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','refresh']]);
    }


    public function register(Request $request)
    {   
        if($request->hasFile('pic')){
            $file=$request->file('pic');
            $extension=$file->getClientOriginalExtension();
            $pp="user.{$extension}";
            
            if($request->has('type'))
           { 
               $user = User::create([
                'email'    => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'Nom'=>$request->input('name'),
                'Prenom'=>$request->input('surname'),
                'tel'=>$request->input('tel'),
                'type'=>$request->input('type'),
                'pp'=>$pp,
                'ville'=>$request->input('ville'),
            ]   );
            }
            else{
                $user = User::create([
                    'email'    => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'Nom'=>$request->input('name'),
                    'Prenom'=>$request->input('surname'),
                    'tel'=>$request->input('tel'),
                    'pp'=>$pp,
                    'ville'=>$request->input('ville'),
                ]   );
            }
            $new=User::where('email',$request->input('email'))->first();
            $file->storeAs('public/'.$new->id.'//profile/',$pp);
         
        }
        else{
            
            if($request->has('type'))
           { 
               $user = User::create([
                'email'    => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'Nom'=>$request->input('name'),
                'Prenom'=>$request->input('surname'),
                'tel'=>$request->input('tel'),
                'type'=>$request->input('type'),
                'pp'=>'user.png',
                'ville'=>$request->input('ville'),
            ]   );
            }
            else{
                $user = User::create([
                    'email'    => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'Nom'=>$request->input('name'),
                    'Prenom'=>$request->input('surname'),
                    'tel'=>$request->input('tel'),
                    'pp'=>'user.png',
                    'ville'=>$request->input('ville'),
                ]   );
            }
            $new=User::where('email',$request->input('email'))->first();
            Storage::copy('public/images/profile/user.png', 'public/'.$new->id.'//profile/user.png');
            //$file->storeAs('public/'.$new->id.'//profile/',$pp);
        }
        return response(null, Response::HTTP_OK);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        
        $credentials =$request->only('email', 'password');
        if ($token = $this->guard()->attempt($credentials)) {
            $user=User::where('email',$request->input('email'))->first();
            if($user){
                if($user->isblocked==0)
                    return $this->respondWithToken($token);
            }
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(Auth::user());
    }


    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}