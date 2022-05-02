<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Feedbacks;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class FeedbacksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Feedbacks::all();

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $feedback = new Feedbacks();
            $feedback->student_id = $request->student_id;
            $feedback->company_id = $request->company_id;
            $feedback->internship_post_id = $request->internship_post_id;
            $feedback->super_power_review = $request->super_power_review;
            $feedback->super_power_comment = $request->super_power_comment;
            $feedback->growth_idea_review = $request->growth_idea_review;
            $feedback->growth_idea_comment = $request->growth_idea_comment;
            $feedback->posted_month = date('Y-m', strtotime($request->posted_month));
            $feedback->save();
        }
        catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($feedback, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Feedbacks  $feedbacks
     * @return \Illuminate\Http\Response
     */
    public function show(Request $id)
    {
        try {
            return Feedbacks::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Feedback not found'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Feedbacks  $feedbacks
     * @return \Illuminate\Http\Response
     */
    public function edit(Feedbacks $feedbacks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Feedbacks  $feedbacks
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feedbacks $feedbacks)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Feedbacks  $feedbacks
     * @return \Illuminate\Http\Response
     */
    public function destroy(Feedbacks $feedbacks)
    {
        //
    }
}
