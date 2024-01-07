<?php

namespace App\Http\Controllers;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

// use App\Http\Resources\PostCollections;

class PostController extends Controller
{
    public function index(){
        $posts = Post::with('categories')->get();
                // $users = User::with('district', 'roles')->latest()->get();
        // return response()->json(['users' => $users]);
        // return response()->json(User::latest()->get());

        return response()->json(['posts' => $posts]);

        // return new PostCollections($users);
    }


    public function store(Request $request)
    {
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'descriptions' => 'required|string', // Assuming descriptions is a text column
                'image' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
                'image_alt' => 'required|string',
                'category_ids' => 'required|array', // Assuming category_ids is an array of category IDs
            ]);

            // Handle image upload
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName);

            // Begin a database transaction
            DB::beginTransaction();

            try {
                // Create a new post
                $post = Post::create([
                    'title' => $validatedData['title'],
                    'descriptions' => $validatedData['descriptions'],
                    'image' => $imageName,
                    'image_alt' => $validatedData['image_alt'],
                ]);

                // Attach categories to the post
                $post->categories()->attach($validatedData['category_ids']);

                // Commit the transaction
                DB::commit();

                return response()->json($post, 201);
            } catch (\Exception $e) {
                // Rollback the transaction if an exception occurs
                DB::rollBack();
                return response()->json(['error' => 'Post creation failed'], 500);
            }
        } catch (ValidationException $e) {
            // Validation error occurred
            return response()->json(['error' => $e->errors()], 400);
        }
    }


    // public function store(Request $request)
    // {
    //     try {
    //         // Validate incoming request data
    //         $validatedData = $request->validate([
    //             'title' => 'required|string|max:255',
    //             'descriptions' => 'required|string',
    //             'image' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
    //             'image_alt' => 'required|string',
    //             'category_ids' => 'required|array', // Assuming category_ids is an array of category IDs
    //         ]);

    //         // Handle image upload
    //         $image = $request->file('image');
    //         $imageName = time() . '_' . $image->getClientOriginalName();
    //         $image->move(public_path('images'), $imageName);

    //         // Begin a database transaction
    //         DB::beginTransaction();

    //         try {
    //             // Create a new post
    //             $post = Post::create([
    //                 'title' => $validatedData['title'],
    //                 'descriptions' => $validatedData['descriptions'],
    //                 'image' => $imageName,
    //                 'image_alt' => $validatedData['image_alt'],
    //             ]);

    //             // Attach categories to the post
    //             $post->categories()->attach($validatedData['category_ids']);

    //             // Commit the transaction
    //             DB::commit();

    //             return response()->json($post, 201);
    //         } catch (\Exception $e) {
    //             // Rollback the transaction if an exception occurs
    //             DB::rollBack();
    //             return response()->json(['error' => 'Post creation failed'], 500);
    //         }
    //     } catch (ValidationException $e) {
    //         // Validation error occurred
    //         return response()->json(['error' => $e->errors()], 400);
    //     }
    // }


    public function show($id)
    {
        try {
            $post = Post::findOrFail($id);
            return response()->json($post);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Post not found'], 404);
        }
    }
    
    
    public function category(){
        $categories = Category::all();
                // $users = User::with('district', 'roles')->latest()->get();
        // return response()->json(['users' => $users]);
        // return response()->json(User::latest()->get());

        return response()->json(['categories' => $categories]);

        // return new PostCollections($users);
    }


     // Create new post
    //  public function store(Request $request){
    //     // Validate incoming request data
    //     $validatedData = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'descriptions' => 'required|string',
    //         'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'image_alt' => 'required|string',
    //     ]);

    //     // Get the authenticated user
    //     $user = auth()->user();

    //     // Handle image upload
    //     $image = $request->file('image');
    //     $imageName = time() . '_' . $image->getClientOriginalName();
    //     $image->move(public_path('images'), $imageName);

    //     // Create a new post associated with the authenticated user
    //     $post = new Post([
    //         'title' => $validatedData['title'],
    //         'descriptions' => $validatedData['descriptions'],
    //         'image' => $imageName,
    //         'image_alt' => $validatedData['image_alt'],
    //     ]);

    //     // Save the post for the authenticated user
    //     $user->posts()->save($post);

    //     return response()->json($post, 201);
    // }

}
