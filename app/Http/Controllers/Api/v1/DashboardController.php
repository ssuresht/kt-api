<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InternshipPostResource;
use App\Http\Resources\MediaPostResource;
use App\Traits\InternshipPostTrait;
use App\Traits\MediaPostTrait;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use InternshipPostTrait;
    use MediaPostTrait;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id)
    {
        try {
            $request->request->add(['sort_by' => 'created_at', 'sort_by_order' => 'desc']); // TODO: temp request

            $internshipPosts = $this->getInternshipPostListing($request, 5);
            $lastWeekMediaPosts = $this->getPopularMediaPostByLastWeek();
            $allPerioMediaPosts = $this->getPopularMediaPostByLastWeek(3, false);

            return $this->sendResponse([
                'internship_posts' => InternshipPostResource::collection($internshipPosts),
                'ranking_intern_posts' => $this->dashboardPostRanks(),
                'popular_media_posts' => MediaPostResource::collection($lastWeekMediaPosts),
                'popular_all_media_posts' => MediaPostResource::collection($allPerioMediaPosts),
            ]);
        } catch (\Throwable$th) {
            $this->sendApiLogsAndShowMessage($th);
        }
    }
}
