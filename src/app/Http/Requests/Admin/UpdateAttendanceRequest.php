<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_start' => ['nullable', 'date_format:H:i'],
            'work_end'   => ['nullable', 'date_format:H:i'],
            'break1_start' => ['nullable', 'date_format:H:i'],
            'break1_end' => ['nullable', 'date_format:H:i'],
            'break2_start' => ['nullable', 'date_format:H:i'],
            'break2_end' => ['nullable', 'date_format:H:i'],
            'note'       => ['required'],
            'work_end' => [
                function ($attribute, $value, $fail) {
                    $ws = $this->input('work_start');
                    $we = $this->input('work_end');

                    if (!$ws || !$we) {
                        return;
                    }
                    if ($ws >= $we) {
                        $fail('出勤時間もしくは退勤時間が不適切な値です');
                    }
                }
            ],

            'break1_start' => [$this->breakStartRule('break1_start', 'break1_end')],
            'break1_end' => [$this->breakEndRule('break1_start', 'break1_end')],
            'break2_start' => [$this->breakStartRule('break2_start', 'break2_end')],
            'break2_end' => [$this->breakEndRule('break2_start', 'break2_end')],
        ];

    }

    private function breakStartRule(string $startKey, string $endKey): \Closure
    {
        return function ($attribute, $value, $fail) use ($startKey, $endKey) {
            $ws = $this->input('work_start');
            $we = $this->input('work_end');
            $bs = $this->input($startKey);
            $be = $this->input($endKey);

            if (!$bs) {
                return;
            }

            if (!$ws || !$we || $ws >= $we) {
                return;
            }

            if ($bs < $ws || $bs > $we) {
                $fail('休憩時間が不適切な値です');
                return;
            }

            if ($be && $bs > $be) {
                $fail('休憩時間が不適切な値です');
            }
        };
    }

    private function breakEndRule(string $startKey, string $endKey): \Closure
    {
        return function ($attribute, $value, $fail) use ($startKey, $endKey) {
            $ws = $this->input('work_start');
            $we = $this->input('work_end');
            $bs = $this->input($startKey);
            $be = $this->input($endKey);

            if (!$be) {
                return;
            }

            if (!$ws || !$we || $ws >= $we) {
                return;
            }

            if ($be < $ws) {
                $fail('休憩時間が不適切な値です');
                return;
            }

            if ($be > $we) {
                $fail('休憩時間もしくは退勤時間が不適切な値です');
                return;
            }

            if ($bs && $bs > $be) {
                $fail('休憩時間が不適切な値です');
            }
        };
    }
    public function messages(): array
    {
        return [
            'work_start.before_or_equal' => '出勤時間が不適切な値です',
            'break1_start.before_or_equal' => '休憩時間が不適切な値です',
            'break2_start.before_or_equal' => '休憩時間が不適切な値です',

            'break1_end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'break2_end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',

            'note.required' => '備考を記入してください',
        ];
    }

    protected function prepareForValidation(): void
    {
        $keys = [
            'work_start',
            'work_end',
            'break1_start',
            'break1_end',
            'break2_start',
            'break2_end',
        ];

        $data = [];

        foreach ($keys as $key) {
            $value = $this->input($key);

            if (is_string($value) && strlen($value) >= 5) {
                // "09:00:00" -> "09:00"
                $data[$key] = substr($value, 0, 5);
            }
        }

        $this->merge($data);
    }
}
