<?php

namespace App\Traits;

use App\Models\InternshipPosts;
use Carbon\Carbon;

trait InternshipPostTrait
{
    private function getInternshipPostListing($request, $authUser = null, $limit = 0)
    {
        $posts = InternshipPosts::query()
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
            ->when($request->input('draft_or_public'), function ($query, $draft_or_public) {
                $query->where('draft_or_public', $draft_or_public == 'draft' ? 0 : 1);
            })
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'LIKE', "%$search%");
                $query->orWhere('internal_internship_id', 'LIKE', "%$search%");
                $query->orWhereHas('company', function($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%");
                });
            })
            ->when($request->input('date_from'), function ($query) use ($request) {
                $query->where('public_date', '>=', $request->date_from);
                $query->where('public_date', '<=', $request->date_to);
            })
            ->when($request->input('work_category_ids'), function ($query, $workCategoryIds) {
                if (is_array($workCategoryIds)) {
                    return $query->whereIn('work_category_id', $workCategoryIds);
                }

                return $query->where('work_category_id', $workCategoryIds);

            })
            ->when($request->input('business_industry_ids'), function ($query, $businessIndustoryIds) {
                $query->whereHas('company', function ($query) use ($businessIndustoryIds) {
                    $query->whereHas('businessIndustry', function ($where) use ($businessIndustoryIds) {
                        if (is_array($businessIndustoryIds)) {
                            return $where->whereIn('business_industries.id', $businessIndustoryIds);
                        }

                        return $where->where('business_industries.id', $businessIndustoryIds);
                    });
                });
            })
            ->when($request->input('internship_feature_list'), function ($query, $internshipFeatureList) {
                $query->whereHas('internshipFeaturePosts', function ($query) use ($internshipFeatureList) {
                    $query->whereIn('internship_features.id', $internshipFeatureList);
                });
            })
            ->when($request->input('period'), function ($query, $period) {
                $query->whereIn('period', $period);
            })
            ->when($request->input('workload'), function ($query, $workload) {
                $query->whereIn('workload', $workload);
            })
            ->when($request->input('target_grade'), function ($query, $targetGrade) {
                $query->whereIn('target_grade', $targetGrade);
            })
            ->when($request->input('sort_by'), function ($query, $sortBy) use ($request) {
                $query->orderBy($sortBy, $request->sort_by_order);
            })
            ->orderBy('public_date', 'desc'); // Default Order

        if ($limit > 0) {
            $posts = $posts->limit($limit);
            return $posts->get();
        }

        $posts = $posts->paginate($request->input('paginate', 25));
        return $posts;
    }

    public function dashboardPostRanks()
    {
        $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        return [
            'end_date' => now()->format('Y-m-d'),
            'start_date' => $startDate,
            'total_application' => InternshipPosts::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->where('status', 0)->count(), // status 0 is assumed for hiring status
            'rank_1' => $rank1 = $this->getInternshipApplicationCount($startDate, $endDate, ['value' => '0', 'operator' => '=']), // InternshipPosts::whereDate('created_at', '>=', $subMonth)->whereDate('created_at', '<=', $currentDate)->whereHas('applications', null, '=', 0)->count(),
            'rank_2' => $rank2 = $this->getInternshipApplicationCount($startDate, $endDate, ['value' => '1', 'operator' => '>='], ['value' => '2', 'operator' => '<=']), //InternshipPosts::whereDate('created_at', '>=', $subMonth)->whereDate('created_at', '<=', $currentDate)->whereHas('applications', null, '>=', 1)->whereHas('applications', null, '<=', 2)->count(),
            'rank_3' => $rank3 = $this->getInternshipApplicationCount($startDate, $endDate, ['value' => '3', 'operator' => '>='], ['value' => '5', 'operator' => '<=']), //InternshipPosts::whereDate('created_at', '>=', $subMonth)->whereDate('created_at', '<=', $currentDate)->whereHas('applications', null, '>=', 3)->whereHas('applications', null, '<=', 5)->count(),
            'rank_4' => $rank4 = $this->getInternshipApplicationCount($startDate, $endDate, ['value' => '5', 'operator' => '>=']), //InternshipPosts::whereDate('created_at', '>=', $subMonth)->whereDate('created_at', '<=', $currentDate)->whereHas('applications', null, '>=', 5)->count(),
            'rank_total' => $totalRank = $rank1 + $rank2 + $rank3 + $rank4,
            'percentage' => $totalRank > 0 ? [
                'rank_1' => round(($rank1 / $totalRank) * 100),
                'rank_2' => round(($rank2 / $totalRank) * 100),
                'rank_3' => round(($rank3 / $totalRank) * 100),
                'rank_4' => round(($rank4 / $totalRank) * 100),
            ] : [],
        ];
    }

    private function getInternshipApplicationCount($startDate, $endDate, array $countRangeStart, array $countRangeEnd = [])
    {
        $result = InternshipPosts::whereHas('applications', function ($query) use ($startDate, $endDate) {
            $query->whereDate('created_at', '>=', $startDate);
            $query->whereDate('created_at', '<=', $endDate);
        }, $countRangeStart['operator'], $countRangeStart['value']);

        /**
         * If end of count range required.
         */
        if (!empty($countRangeEnd) && isset($countRangeEnd['operator']) && isset($countRangeEnd['value'])) {
            $result->whereHas('applications', function ($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate);
                $query->whereDate('created_at', '<=', $endDate);
            }, $countRangeStart['operator'], $countRangeStart['value']);
        }

        return $result->count();
    }
}
