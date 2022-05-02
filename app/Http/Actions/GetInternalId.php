<?php

namespace App\Http\Actions;

use App\Models\Company;
use App\Models\Students;
use Illuminate\Support\Facades\Log;


class GetInternalId {


    public static function get_internal_application_id ($student_id, $application_id) {
        return
        'a' .
        str_replace('s', '', Students::findOrFail($student_id)->student_internal_id).
        '-' .
        $application_id;
    }

    public static function get_internal_student_id ($student_id, $student_created_at) {
        return
        'S' .
        date('y', strtotime($student_created_at)) .
        $student_id;
    }

    public static function get_internal_internship_post_id ($internship_post_id, $company_id) {
        return
        'i' .
        str_replace('C', '', Company::findOrFail($company_id)->internal_company_id).
        '-' .
        $internship_post_id;
    }

    public static function get_internal_company_id () {
        $id = 'C' . mt_rand(100000, 999999);
        if (Company::where('internal_company_id', $id)->exists()) {
            return self::get_internal_company_id();
        }
        return $id;
    }
}
