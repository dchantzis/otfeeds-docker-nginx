<?php


namespace App\Http\Validators;

use App\Http\ApiRequestParameters;
use Illuminate\Support\Facades\Validator;

class LogRequestValidator
{

    /**
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate(array $data)
    {
        return Validator::make(
            $data,
            $this->rules()
        );
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ApiRequestParameters::IP_TRACE_ID => 'nullable|int|exists:feeds_ip_trace,id',
            ApiRequestParameters::LIMIT => 'nullable|int',
            ApiRequestParameters::START_DATE => 'nullable|date',
            ApiRequestParameters::END_DATE => 'nullable|date',
        ];
    }

}
