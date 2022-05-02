<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $response = parent::toArray($request);
        $response['obfuscate_email'] = isset($this->email_valid) && $this->email_valid != '' ? $this->obfuscateEmail($this->email_valid) : '';

        return $response;
    }

    private function obfuscateEmail($input, $show = 3)
    {
        $arr = explode('@', $input);

        $email = substr($arr[0], 0, $show) . str_repeat('*', strlen($arr[0]) - $show);
        $host = substr($arr[1], 0, $show) . str_repeat('*', strlen($arr[1]) - $show);

        return $email . '@' . $host;
    }
}
