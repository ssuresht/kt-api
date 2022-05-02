<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaPostRequest;
use App\Http\Resources\MediaPostResource;
use App\Http\Resources\PaginationResource;
use App\Models\MediaPosts;
use App\Traits\EditorContentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaPostsController extends Controller
{
    use EditorContentTrait;
    /**
     * Media Posts List.
     * @group Media Posts
     *
     * @param Request $request
     * @queryParam limit integer optional Data Per Page Limit. Example : 10
     *
     * @return \Illuminate\Http\Response
     * @response 200 {"status":"Success","message":"Media Posts List","code":200,"data":{"current_page":1,"data":[{"id":1,"title":"Post Title","summery":"Post Summery","media_tag_id":1,"seo_slug":"media-slug","seo_ogp":"media-seo-ogp","seo_featured_image":"media-seo_featured_image","description":"media-description","is_draft":1,"page_views":1,"status":1,"created_at":"2022-03-14T08:08:23.000000Z","updated_at":"2022-03-14T08:08:23.000000Z","deleted_at":null}],"first_page_url":"http://localhost:8000/api/media-posts?page=1","from":1,"last_page":1,"last_page_url":"http://localhost:8000/api/media-posts?page=1","links":[{"url":null,"label":"&laquo; Previous","active":false},{"url":"http://localhost:8000/api/media-posts?page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"next_page_url":null,"path":"http://localhost:8000/api/media-posts","per_page":"5","prev_page_url":null,"to":1,"total":1}}
     */
    public function index(MediaPostRequest $request)
    {
        try {
            $application = MediaPosts::withCount(['mediaViews as media_views_count'])->with('mediaTags');
            if($request->input('sort_by') !== 'view_counts' && $request->input('sort_by') !== 'display_order') {
               $application = $application->orderBy($request->input('sort_by'), $request->input('sort_by_order', 'desc'));
            }
                
                $application = $application->when($request->input('is_draft'), function ($query, $isDraft) {
                    $query->where('is_draft', $isDraft == 'Y' ? '1' : '0');
                })
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('title', 'LIKE', "%$search%");
                })->get();
                switch ($request->input('sort_by')) {
                    case 'view_counts': {
                        $application = $application->sortByDesc("media_views_count")->values();
                        break;
                    }

                    case 'display_order': {
                        $application = $application->sort(function($a, $b) {
                            if(!$a->display_order) {
                                return !$b->display_order ? 0 : 1;
                            }
                            if (!$b->display_order) {
                                return -1;
                            }
                            if ($a->display_order === $b->display_order) {
                                return 0;
                            }
                            return $a->display_order < $b->display_order ? -1 : 1;
                        })->values();
                        break;
                    }
                    
                }
                if($request->input('tag_search')) {
                    $tagFilters = json_decode($request->input('tag_search'));
                    if(count($tagFilters) > 0) {
                        $application = $application->filter(function($item, $key) use ($tagFilters) {
                            $item = json_decode(json_encode($item));
                            $tagFound = false;
                            for($i = 0; $i < count($tagFilters); $i++) {
                                for($j = 0; $j < count($item->media_tags); $j++) {
                                    if($tagFilters[$i] == $item->media_tags[$j]->id) {
                                        $tagFound = true;
                                        break;
                                    }
                                }
                                if($tagFound) {
                                    break;
                                }
                            }
                            return $tagFound;
                        });
                    }

                }
                $application = $application->paginate($request->input('paginate', 25));
                $data = MediaPostResource::collection($application);
                $paginate = new PaginationResource($application);
            return $this->sendResponse([
                'data' => $data,
                'paginate' => $paginate,
                'counts' => [
                    'total_opened' => MediaPosts::where('is_draft', 0)->count(),
                    'total_drafted' => MediaPosts::where('is_draft', 1)->count(),
                ],
            ]);
        } catch (\Throwable$th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    /**
     * Media Posts Create.
     * @group Media Posts
     * @param Request $request
     * @bodyParam title string Example: Post Title
     * @bodyParam summery string Example: Post Summery
     * @bodyParam media_tag_id array Example: Post Media Tag
     * @bodyParam seo_slug string required Example: media-slug
     * @bodyParam seo_ogp string required Example: media-seo-ogp
     * @bodyParam seo_featured_image string required Example: media-seo_featured_image
     * @bodyParam description string required Example: media-description
     * @bodyParam is_draft boolean required Example: 1
     * @bodyParam status boolean required Example: 1
     * @return \Illuminate\Http\Response
     * @response 200 {"status":"Success","message":"Created Successfully","code":201,"data":{"title":"Post Title","summery":"Post Summery","media_tag_id":"1","seo_slug":"media-slug","seo_ogp":"media-seo-ogp","seo_featured_image":"media-seo_featured_image","description":"media-description","is_draft":true,"page_views":"1","status":true,"updated_at":"2022-03-14T08:07:25.000000Z","created_at":"2022-03-14T08:07:25.000000Z","id":5}}
     */

    public function store(MediaPostRequest $request)
    {
        try {

            $mediaPost = new MediaPosts();

            if ($file = $request->file('seo_featured_image')) {
                $path = config('constants.media_images_path');
                $imageName = uniqid() . '.' . $file->extension();
                $fullPathName = $path . $imageName;
                Storage::disk('s3')->putFileAs($path, $file, $imageName);
                $mediaPost->seo_featured_image = $fullPathName;
            }

            $mediaPost->title = $request->title;
            $mediaPost->summery = $request->summery;
            $mediaPost->seo_slug = $request->seo_slug;
            $mediaPost->seo_ogp = $request->seo_ogp;
            $mediaPost->seo_meta_description = $request->seo_meta_description;
            $mediaPost->display_order = $request->display_order;
            $mediaPost->is_draft = $request->is_draft ?? 0;
            $mediaPost->public_date = now();
            $mediaPost->status = 1;

            $mediaPost->save();

            $mediaPost->mediaTags()->sync($request->media_tag_id);

            // Update Content
            $imagePath = config('constants.media_images_content_path');
            $mediaPost->description = $this->updateContentImages($request->description, $mediaPost, $imagePath);
            $mediaPost->save();

            return $this->sendResponse([
                'message' => __('messages.saved_success'),
                'data' => new MediaPostResource($mediaPost),
            ]);
        } catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    /**
     * Media Posts View.
     * @group Media Posts
     *
     * @urlParam id integer required Example: 1
     *
     * @return \Illuminate\Http\Response
     * @response 200 {"status":"Success","message":"Media Post View","code":200,"data":{"id":1,"title":"Post Title","summery":"Post Summery","media_tag_id":1,"seo_slug":"media-slug","seo_ogp":"media-seo-ogp","seo_featured_image":"media-seo_featured_image","description":"media-description","is_draft":1,"page_views":1,"status":1,"created_at":"2022-03-14T08:08:23.000000Z","updated_at":"2022-03-14T08:08:23.000000Z","deleted_at":null}}
     * */

    public function show($id)
    {
        try {
            $mediaPost = MediaPosts::withCount(['mediaViews as media_views_count'])->with(['mediaTags:id,name'])->findOrFail($id);

            return $this->sendResponse([
                'message' => __('messages.show_all_success'),
                'data' => new MediaPostResource($mediaPost),
            ]);
        } catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    /**
     * Media Posts Update.
     * @group Media Posts
     *
     * @param Request $request
     *
     * @bodyParam title string Example: Post Title
     * @bodyParam summery string Example: Post Summery
     * @bodyParam media_tag_id string Example: Post Media Tag
     * @bodyParam seo_slug array required Example: media-slug
     * @bodyParam seo_ogp string required Example: media-seo-ogp
     * @bodyParam seo_featured_image string required Example: media-seo_featured_image
     * @bodyParam description string required Example: media-description
     * @bodyParam is_draft boolean required Example: 1
     * @bodyParam status boolean required Example: 1
     *
     * @return \Illuminate\Http\Response
     * @response 200 {"status":"Success","message":"Updated Successfully","code":201,"data":{"student_id":"1","company_id":"1","internship_post_id":"1","cancel_status":null,"updated_at":"2022-03-10T16:30:14.000000Z","created_at":"2022-03-10T16:30:14.000000Z","id":1}}
     */
    public function update($id, MediaPostRequest $request)
    {
        try {
            $mediaPost = MediaPosts::findOrFail($id);

            $mediaPost->title = $request->title;
            $mediaPost->summery = $request->summery;
            $mediaPost->seo_slug = $request->seo_slug;
            $mediaPost->seo_ogp = $request->seo_ogp;
            $mediaPost->seo_meta_description = $request->seo_meta_description;
            $mediaPost->display_order = $request->display_order;
            $mediaPost->is_draft = $request->is_draft ?? 0;
            $mediaPost->status = $request->status ?? 0;

            if ($file = $request->file('seo_featured_image')) {
                $path = config('constants.media_images_path');
                $imageName = uniqid() . '.' . $file->extension();
                $fullPathName = $path . $imageName;
                Storage::disk('s3')->putFileAs($path, $file, $imageName);
                $mediaPost->seo_featured_image = $fullPathName;
            }

            $imagePath = config('constants.media_images_content_path');
            $mediaPost->description = $this->updateContentImages($request->description, $mediaPost, $imagePath);
            $mediaPost->save();

            $mediaPost->mediaTags()->sync($request->media_tag_id);

            return $this->sendResponse([
                'message' => __('messages.update_success'),
                'data' => new MediaPostResource($mediaPost),
            ]);
        } catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    /**
     * Media Posts Delete.
     * @group Media Posts
     *
     * @urlParam id integer required Example: 1
     *
     * @return \Illuminate\Http\Response
     * @response 200  {"status":"Success","message":"Deleted Successfully","code":200,"data":[]}
     * */

    public function destroy($id)
    {
        try {
            MediaPosts::findOrFail($id)->delete();
            return $this->sendResponse(['message' => 'Post deleted']);
        } catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }
}
