<?php
namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Controllers\Controller;
use App\Models\Feedbacks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbacksController extends Controller {
    public function __invoke()
    {
        $authUser = Auth::guard('students')->user();
        $reviews = $this->generateRawSelectSql(["super_power_review", "growth_idea_review"]);
        $feedbacks = DB::table('feedbacks')->select(DB::raw($reviews))
                    ->where('student_id',$authUser->id)
                    ->groupBy('company_id')
                    ->first();
        $companies = Feedbacks::select('company_id')->with('companies')->where('student_id', $authUser->id)->distinct()->get();
        $comments = [];
        foreach($companies as $company) {
            $comment = Feedbacks::select('super_power_comment', 'growth_idea_comment','posted_month', 'id')
                       ->where('company_id', $company->company_id)
                       ->where('student_id', $authUser->id)
                       ->get();
            $temp = ['company_info' => $company->companies, 'comments' => $comment];
            array_push($comments,$temp);
        }
        return $this->sendResponse([
            'feedbacks' => $feedbacks,
            'comments' => $comments
        ]);
    }

    public function generateRawSelectSql($columnNames) {
        $reviews = config("constants.reviews_option");
        $statement = [];
        foreach($columnNames as $columnName) {
            foreach($reviews as $element) {
                array_push($statement,"SUM(IF({$columnName} = {$element['id']}, 1, 0)) AS {$columnName}_{$element['id']}");
            }
        }
        return implode(",", $statement);
    }
}