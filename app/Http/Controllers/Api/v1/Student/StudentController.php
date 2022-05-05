<?php

namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Actions\GetInternalId;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\InternshipPostResource;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\StudentsResource;
use App\Mail\EmailChangeMail;
use App\Mail\SignUpRequestMail;
use App\Models\InternshipPosts;
use App\Models\Students;
use App\Models\SignupToken;
use App\Traits\InternshipPostTrait;
use FFI\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\StudentEmailChangeRequest;
class StudentController extends Controller
{
    use InternshipPostTrait;

    public function login(StudentRequest $request)
    {
        $requestedData = $request->validated();
        $student = Students::with('educationFacility')->where('email_valid', '=', $requestedData['email'])->where('status',1)->first();

        try {
            if($student->status == 3){
                 return $this->sendError(__('message.membership_cancelled'), 401);
            }

            if ($student && Hash::check($requestedData['password'], $student->password)) {
                $token = $student->createToken('student')->plainTextToken;

                return $this->sendResponse([
                    'token' => $token,
                    'student' => new StudentsResource($student),
                ]);
            }

            return $this->sendError(__('message.invalid_email_password'), 401);
        } catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse([
            'logout' => 'Success',
        ]);
    }

    public function storeTokenCheck(Request $request)
    {

        try {
            $requestedData = $request;
            $token = $requestedData['token'];
            $signupToken = SignupToken::where('token', $token)->first();
            if ($signupToken) {
                if ($signupToken->is_used == 0) {
                    $student = Students::where('email_invalid', $signupToken->email)->first();
                    if ($student) {
                        $student->email_valid = $student->email_invalid;
                        $student->save();
                        return $this->sendResponse([
                            'message' => __('messages.record_created_successfully'),
                            'data' => new StudentsResource($student),
                        ]);
                    }
                    return $this->sendError(__('messages.email_already_registered'));
                }
            }
            return $this->sendError(__('messages.data_not_found'));
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function signup(StudentRequest $request)
    {
        try {

            $requestedData = $request->validated();
            $student = Students::where('email_valid', $requestedData['email_valid'])->first();
            $student->family_name = $requestedData['family_name'];
            $student->first_name = $requestedData['first_name'];
            $student->family_name_furigana = $requestedData['family_name_furigana'];
            $student->first_name_furigana = $requestedData['first_name_furigana'];
            $student->email_valid = $requestedData['email_valid'];
            $student->education_facility_id = $requestedData['education_facility_id'];
            $student->graduate_year = $requestedData['graduate_year'];
            $student->graduate_month = $requestedData['graduate_month'];
            $student->status = $requestedData['status'];
            $student->password = bcrypt($requestedData['password']);
            $student->save();
            $student->student_internal_id = GetInternalId::get_internal_student_id($student->id, $student->created_at);
            $student->save();
            $signupToken = SignupToken::where('email', $student->email_valid)->first();
            $signupToken->is_used = true;
            $signupToken->save();

            return $this->sendResponse([
                'message' => __('messages.update_success'),
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * Student View.
     * @group Students
     * @urlParam id integer required Example: 1
     * @return \Illuminate\Http\Response
     * @response 200 {"data":{"message":"messages.data_found","data":{"id":1,"family_name":"minhaz","first_name":"Ajamaef","family_name_furigana":"demo","first_name_furigana":"demo2","email_valid":"demo@gtmail.com","email_invalid":"demo@gtmail.com","is_email_approved":1,"education_facility_id":1,"university_name":"rrrrrr","graduate_year":2020,"graduate_month":"02","self_introduction":"30","status":1,"created_at":"2022-03-17T17:53:49.000000Z","updated_at":"2022-03-17T17:53:49.000000Z","deleted_at":null,"education_facilities":{"id":1,"name":"sdfds","type":0,"created_at":null,"updated_at":null,"deleted_at":null}}}}
     * */

    public function show($id)
    {
        try {
            $student = Students::with('educationFacility')->findOrFail($id);
            if ($student) {
                return $this->sendResponse([
                    'message' => __('messages.data_found'),
                    'data' => new StudentsResource($student),
                ]);
            }
            return $this->sendError(__('messages.data_not_found'));
        } catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    /**
     * Student Create.
     * @group Students
     * @param Request $request
     * @bodyParam family_name string required Example: Rayhan
     * @bodyParam first_name string required Example: Raju
     * @bodyParam family_name_furigana string required Example: ASD
     * @bodyParam first_name_furigana string required Example: XYZ
     * @bodyParam email_valid email required Example: devraju.bd@gmail.com
     * @bodyParam email_invalid email required Example: devraju.bd@gmail.com
     * @bodyParam password string required Example: 12345678
     * @bodyParam is_email_approved boolean required Example: 1
     * @bodyParam education_facility_id integer Example: 1
     * @bodyParam university_name string Example: Dhaka University
     * @bodyParam graduate_year year Example: 2016
     * @bodyParam graduate_month string Example: 02
     * @bodyParam self_introduction string Example: Hello World
     * @bodyParam status integer  Example: 1
     * @bodyParam is_admin_read integer  0 for unread and 1 for read Example:
     * @return \Illuminate\Http\Response
     * @response 200 {"status":"Success","message":"Created Successfully","code":201,"data":{"family_name":"Rayhan","first_name":"nbxgcjzbtafuiu","family_name_furigana":"ktllbobqzviftazunfozppr","first_name_furigana":"rlyenpaddbu","email_valid":"ariane.corwin@example.net","email_invalid":"herzog.joy@example.org","is_email_approved":true,"education_facility_id":"1","university_name":"commodi","graduate_year":null,"graduate_month":null,"self_introduction":"culpa","status":null,"updated_at":"2022-03-10T16:30:14.000000Z","created_at":"2022-03-10T16:30:14.000000Z","id":1}}
     */

    public function store(StudentRequest $request)
    {

        try {
            #NOTE: Validate input request
            $requestedData = $request->validated();

            $student = new Students();
            $student->email_invalid = $requestedData['email_invalid'];
            $student->is_email_approved = 0;
            $student->save();

            $token = Str::random(60);
            $signupToken = new SignupToken();
            $signupToken->email = $requestedData['email_invalid'];
            $signupToken->token = $token;
            $signupToken->save();
            $signUpRequestUrl = "https://stg.kotonaru.co.jp/top?token=" . $token;
            $data = [
                'student' => $student,
                'url' => $signUpRequestUrl,
            ];

            Mail::to($student->email_invalid)->send(new SignUpRequestMail($data));
            return $this->sendResponse([
                'message' => __('messages.record_created_successfully'),
                'data' => new StudentsResource($student),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Student Update.
     * @group Students
     * @param Request $request
     * @urlParam id integer required Example: 1
     * @bodyParam family_name string required Example: Rayhan
     * @bodyParam first_name string required Example: Raju
     * @bodyParam family_name_furigana string required Example: ASD
     * @bodyParam first_name_furigana string required Example: XYZ
     * @bodyParam email_valid email required Example: devraju.bd@gmail.com
     * @bodyParam email_invalid email required Example: devraju.bd@gmail.com
     * @bodyParam password string required Example: 12345678
     * @bodyParam is_email_approved boolean required Example: 1
     * @bodyParam education_facility_id integer Example: 1
     * @bodyParam graduate_year year Example: 2016
     * @bodyParam graduate_month string Example: 02
     * @bodyParam self_introduction string Example: Hello World
     * @bodyParam status boolean required Example: 1
     * @return \Illuminate\Http\Response
     * @response 200 {"data":{"message":"messages.update_success","data":{"id":1,"family_name":"ddd","first_name":"ddd","family_name_furigana":"dddd","first_name_furigana":"dddd","email_valid":"dd@gmail.com","email_invalid":"dddd@gmail.com","is_email_approved":"1","education_facility_id":"2","university_name":"rrr","graduate_year":"2022","graduate_month":"02","self_introduction":"erererer","status":"1","created_at":"2022-03-17T17:53:49.000000Z","updated_at":"2022-03-17T18:08:25.000000Z","deleted_at":null}}}
     *
     **/

    public function update(StudentRequest $request, $id)
    {
        try {
            $requestedData = $request->validated();
            $student = Students::findOrFail($id);
            $student->family_name = $requestedData['family_name'] ?? $student->family_name;
            $student->first_name = $requestedData['first_name'] ?? $student->first_name;
            $student->family_name_furigana = $requestedData['family_name_furigana'] ?? $student->family_name_furigana ;
            $student->first_name_furigana = $requestedData['first_name_furigana'] ?? $student->first_name_furigana ;
            $student->email_invalid = $requestedData['email_invalid'] ?? $student->email_invalid;
            $student->education_facility_id = $requestedData['education_facility_id'] ?? $student->education_facility_id;
            $student->graduate_year = $requestedData['year'] ?? $student->graduate_year ;
            $student->graduate_month = $requestedData['month'] ?? $student->month ;
            $student->self_introduction = $requestedData['self_introduction'] ?? $student->self_introduction;
            $student->status = $requestedData['status'] ?? $student->status ;
            $student->save();
            $student->student_internal_id = GetInternalId::get_internal_student_id($student->id, $student->created_at);
            $student->save();

     if ( isset ( $requestedData['email_invalid'])) {

        $token = Str::random(60);
        $emailChangeToken = new StudentEmailChangeRequest();
        $emailChangeToken->email = $requestedData['email_invalid'];
        $emailChangeToken->token = $token;
        $emailChangeToken->save();
           #NOTE: local test link
          // $emailChangeTokenUrl = "http://localhost:8080/mypage?token=" . $token;
           #NOTE: staging test link
         $emailChangeTokenUrl =  $_ENV['STUDENT_EMAIL_CHANGE_MYPAGE'].'=' . $token;
        $data = [
            'student' => $student,
            'url' => $emailChangeTokenUrl,
        ];
        Mail::to( $requestedData['email_invalid'])->send( new EmailChangeMail($data));
       }
            return $this->sendResponse([
                'message' => __('messages.update_success'),
                'data' => new StudentsResource($student),
            ]);
        }
         catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    //Email Change Request from Student from my profile
    public function storeTokenEmailChange(Request $request)
    {

        try {
            #NOTE: Validate input request
            $requestedData = $request;
            $token = $requestedData['token'];
            $requestEmailToken = StudentEmailChangeRequest::where('token', $token)->first();
            if ($requestEmailToken) {
                if ($requestEmailToken->is_used == 0) {
                    $student = Students::where('email_invalid', $requestEmailToken->email)->first();
                    if ($student) {
                        $student->email_valid = $student->email_invalid;
                        $student->save();           
                                     
                        #NOTE: token table Update
                        $requestEmailToken->is_used= 1;
                        $requestEmailToken->save();
                        return $this->sendResponse([
                            'message' => __('messages.record_created_successfully'),
                        ]);
                    }
                    return $this->sendError(__('messages.invalid_token'));
                }
            }
            return $this->sendError(__('messages.data_not_found'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Student Delete.
     * @group Students
     * @urlParam id integer required Example: 1
     * @return \Illuminate\Http\Response
     * @response 200  {"data":{"message":"messages.deleted_success"}}
     **/

    public function delete($id)
    {
        try {
            // Delete the logged-in admin
            Students::find($id)->delete();

            return $this->sendResponse([
                'message' => __('messages.deleted_success'),
            ]);
        } catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    public function studentIntershipPosts(Request $request)
    {
        try {
            // get all internship posts with work category
            $posts = $this->getInternshipPostListing($request);

            return $this->sendResponse([
                'message' => __('messages.show_all_success'),
                'data' => InternshipPostResource::collection($posts),
                'paginate' => new PaginationResource($posts),
                'counts' => [
                    'total_opened' => InternshipPosts::where('status', 0)->where('draft_or_public', $request->draft_or_public == 'draft' ? 0 : 1)->count(),
                    'total_ended' => InternshipPosts::where('status', 1)->where('draft_or_public', $request->draft_or_public == 'draft' ? 0 : 1)->count(),
                ],
            ]);
        } catch (Exception $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }

    public function updateMembership(Request $request, $userId)
    {
        try {

            $data = [
                'status' => $request->status,
                'reason_for_withdrawal' => $request->reason
            ];

            Students::where('id', $userId)->update($data);

            return $this->sendResponse([
                'message' => __('Update Membership.'),
            ]);
        } catch (\Throwable $th) {
            return $this->sendApiLogsAndShowMessage($th);
        }
    }
}
