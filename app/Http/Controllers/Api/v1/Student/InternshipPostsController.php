<?php

namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\InternshipPostRequest;
use App\Http\Resources\InternshipPostResource;
use App\Http\Resources\PaginationResource;
use App\Models\Favorites;
use App\Models\InternshipPosts;
use App\Traits\EditorContentTrait;
use App\Traits\InternshipPostTrait;
use Illuminate\Support\Facades\Auth;

class InternshipPostsController extends Controller
{
    use InternshipPostTrait;
    use EditorContentTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(InternshipPostRequest $request)
    {
        try {
            $authUser = Auth::guard('students')->user();

            $posts = InternshipPosts::query()
                ->where('draft_or_public', 1)
                ->with(['company:id,name,logo_img,business_industry_id', 'workCategory:id,name', 'company.businessIndustry:id,name'])
                ->withCount([
                    'applications',
                    'favorites',
                    'favorites as is_favourited' => function ($query) use ($authUser) {
                        $query->where('student_id', $authUser->id ?? 0);
                    },
                ])
                ->when($request->input('status'), function ($query, $status) {
                    $query->where('status', $status == 'Y' ? '1' : '0');
                })
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('title', 'LIKE', "%$search%");
                })
                ->when($request->input('date_from'), function ($query) use ($request) {
                    $query->where('public_date', '>=', $request->date_from);
                    $query->where('public_date', '<=', $request->date_to);
                })
                ->when($request->input('work_id'), function ($query, $workId) {
                    $query->where('work_category_id', $workId);
                })
                ->when($request->input('sort_by'), function ($query, $sortBy) use ($request) {
                    $query->orderBy($sortBy, $request->sort_by_order);
                })
                ->orderBy('public_date', 'desc')
                ->paginate($request->input('paginate', 10)); // Default Order;

            // $totalInternshipPosts = InternshipPosts::query()
            // ->where('draft_or_public', 1)->count();

            return $this->sendResponse([
                'data' => InternshipPostResource::collection($posts),
                'paginate' => new PaginationResource($posts),
                'counts' => [
                    'posts' => 0//$totalInternshipPosts
                ]
            ]);

        } catch (\Throwable$th) {
            return $this->sendApiLogsAndShowMessage($th);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InternshipPosts  $internshipPosts
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $authUser = Auth::guard('students')->user();

            $post = InternshipPosts::with([
                'internshipFeaturePosts:id,name',
                'workCategory:id,name',
                'company:id,name,logo_img,business_industry_id',
                'company.businessIndustry:id,name',
                'favorites' => function ($query) use ($authUser) {
                    $query->where('student_id', $authUser->id ?? 0);
                },
            ])->withCount(['applications', 'favorites'])->find($id);

            if (!$post) {
                return $this->sendResponse([
                    'message' => __('messages.data_not_found'),
                ], [], 404);
            }

            $post->is_favourite = $post->favorites->count();

            return $this->sendResponse([
                'message' => __('messages.show_all_success'),
                'data' => new InternshipPostResource($post),
            ]);
        } catch (\Throwable$th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

}
