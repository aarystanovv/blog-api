<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use ImageUploadTrait;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:create_posts')->only('store');
        $this->middleware('permission:edit_posts')->only('update');
        $this->middleware('permission:delete_posts')->only('destroy');
    }

    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Get list of posts",
     *     tags={"Posts"},
     *     @OA\Response(
     *         response="200",
     *         description="Successfully retrieved list of posts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Post")
     *         )
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */

    public function index()
    {
        $posts = Post::with(['author', 'categories', 'tags'])->paginate(10);
        return PostResource::collection($posts);
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Create a new post",
     *     tags={"Posts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", example="Test Post"),
     *             @OA\Property(property="content", type="string", example="This is a test post"),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={1,2}),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1}),
     *             @OA\Property(property="featured_image", type="string", example="image.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Post created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */

    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();

        $data['user_id'] = $user->id;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->uploadImage($request->file('featured_image'), 'posts');
        }

        $post = Post::create($data);

        if (isset($data['categories'])) {
            $post->categories()->attach($data['categories']);
        }

        if (isset($data['tags'])) {
            $post->tags()->attach($data['tags']);
        }

        return new PostResource($post);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     summary="Get a single post by ID",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to fetch",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully retrieved post",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function show($id)
    {
        $post = Post::with(['author', 'categories', 'tags'])->findOrFail($id);
        return new PostResource($post);
    }
    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     summary="Update a post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", example="Updated Post"),
     *             @OA\Property(property="content", type="string", example="Updated content for the post"),
     *             @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={1}),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1}),
     *             @OA\Property(property="featured_image", type="string", example="updated_image.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Post updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function update(UpdatePostRequest $request, $id)
    {
        $post = Post::findOrFail($id);

        // Проверка на право редактирования
        if (!Auth::user()->can('edit_posts') && $post->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $this->uploadImage($request->file('featured_image'), 'posts');
        }

        $post->update($data);

        if (isset($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }

        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        return new PostResource($post);
    }
    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="Delete a post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Post deleted successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Проверка на право удаления
        if (!Auth::user()->can('delete_posts') && $post->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return response()->noContent();
    }


}
