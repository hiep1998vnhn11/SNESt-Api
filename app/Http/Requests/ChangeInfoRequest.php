<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'profile_background_path' => 'string|min:1',
            'profile_photo_path' => 'string|min:1',
            'birthday' => 'date',
            'phone_number' => 'string|min:8|max:15',
            'live_at' => 'string|min:1',
            'from' => 'string|min:1',
            'link_to_social' => 'string|min:1',
            'url' => 'string|min:1|max:35',
            'story' => 'string|min:1|max:105',
            'story_privacy' => [Rule::in(['public', 'friend', 'private'])],
            'locale' => 'string|min:1',
            'name' => 'string|min:1|max:60'
        ];
    }
}
