<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Role;
use App\Models\Post;




use App\Http\Resources\PostCollections;

class UserController extends Controller
{
    public function index(){
        // $users = User::with('district', 'roles')->latest()->get();
        // return response()->json(['users' => $users]);
        // return response()->json(User::latest()->get());

        // $users = User::all();
        $users = User::with('posts')->get();

        return response()->json(['users' => $users]);

        // return new PostCollections($users);
   }

   public function store(Request $request){
       $validator = Validator::make($request->all(), [
           'name' => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users',
           'password' => 'required|string|min:8',
           'role' => 'required|string|in:admin,user', // Add role validation rule
       ]);

       if ($validator->fails()) {
           return response()->json(['error' => $validator->errors()], 422);
       }

       $role = Role::where('name', $request->role)->firstOrFail();

       $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => Hash::make($request->password),
       ]);

       // Attach the role to the user
       $user->roles()->attach($role);

       $token = $user->createToken('usersCreatedToken')->plainTextToken;

       return response()->json(['token' => $token], 201);
   }

    //  Login user
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }
}
