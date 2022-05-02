<?php

namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaPostRequest;
use App\Http\Resources\MediaPostResource;
use App\Http\Resources\PaginationResource;
use App\Models\MediaPosts;

class MediaPostsController extends Controller {
    public function index(MediaPostRequest $request)
    {
        try {
            $application = MediaPosts::withCount(['mediaViews as media_views_count'])->with('mediaTags');
			$application->orderByRaw( '-display_order desc');
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

}