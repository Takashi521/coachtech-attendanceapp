<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_start' => ['required', 'date_format:H:i'],
            'work_end' => ['required', 'date_format:H:i'],

            'break1_start' => ['nullable', 'date_format:H:i'],
            'break1_end' => ['nullable', 'date_format:H:i'],

            'break2_start' => ['nullable', 'date_format:H:i'],
            'break2_end' => ['nullable', 'date_format:H:i'],

            'note' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v): void {
            $workStart = $this->hmToMinutes($this->input('work_start'));
            $workEnd = $this->hmToMinutes($this->input('work_end'));

            if ($workStart === null || $workEnd === null) {
                return;
            }

            if ($workStart >= $workEnd) {
                $v->errors()->add('work_start', '出勤時間もしくは退勤時間が不適切な値です');
                return;
            }

            // 休憩の共通チェック
            $this->validateBreak(
                $v,
                $workStart,
                $workEnd,
                $this->input('break1_start'),
                $this->input('break1_end')
            );

            $this->validateBreak(
                $v,
                $workStart,
                $workEnd,
                $this->input('break2_start'),
                $this->input('break2_end')
            );
        });
    }

    private function validateBreak($v, int $workStart, int $workEnd, ?string $breakStartHm, ?string $breakEndHm): void
    {
        $bs = $this->hmToMinutes($breakStartHm);
        $be = $this->hmToMinutes($breakEndHm);

        if ($bs === null && $be === null) {
            return;
        }

        // 2) 休憩開始が出勤前 or 退勤後
        if ($bs !== null && ($bs < $workStart || $bs > $workEnd)) {
            $v->errors()->add('break1_start', '休憩時間が不適切な値です');
            return;
        }

        // 3) 休憩終了が退勤より後
        if ($be !== null && $be > $workEnd) {
            $v->errors()->add('break1_end', '休憩時間もしくは退勤時間が不適切な値です');
            return;
        }

        // 休憩開始/終了の前後（仕様に無いけど破綻防止として）
        if ($bs !== null && $be !== null && $bs >= $be) {
            $v->errors()->add('break1_end', '休憩時間が不適切な値です');
        }
    }

    private function hmToMinutes(?string $hm): ?int
    {
        if (!$hm) {
            return null;
        }

        try {
            $t = Carbon::createFromFormat('H:i', $hm);
        } catch (\Throwable $e) {
            return null;
        }

        return ((int) $t->format('H')) * 60 + (int) $t->format('i');
    }
}

