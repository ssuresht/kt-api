<?php

namespace App\Http\Controllers;

use App\Models\BusinessIndustries;
use App\Models\Company;
use App\Models\InternshipFeatures;
use App\Models\InternshipPosts;
use App\Models\Students;
use App\Models\WorkCategories;
use App\Models\Applications;

class MasterController extends Controller
{
    public function index()
    {
        $totalCompanies = Company::count();
        $totalCompaniesnotApproved = Company::where('status', '=', 0)->count();
        $workCategories = WorkCategories::all();
        $businessIndustories = BusinessIndustries::all();
        $internPosts = InternshipPosts::where('status', 0)->count();
        $total_students = Students::count();
        $totalUnreadApplications = Applications::where('is_admin_read', '=', 0)->count();

        $data = [
            'total_companies' => $totalCompanies,
            'total_companies_not_approved' => $totalCompaniesnotApproved,
            'work_categories' => $workCategories,
            'business_industories' => $businessIndustories,
            'internship_feature_list' => InternshipFeatures::all(),
            'period' => config('constants.period'),
            'wage' => config('constants.wage'),
            'workload' => config('constants.workload'),
            'target_grade' => config('constants.target_grade'),
            'educational_facility_type' => config('constants.educational_facility_type'),
            'reviews_option' => config('constants.reviews_option'),
            'internship_posts_count' => $internPosts,
            'total_students'        => $total_students,
            'total_unread_applications'  => $totalUnreadApplications,
        ];
        return $this->sendResponse($data);
    }
}
