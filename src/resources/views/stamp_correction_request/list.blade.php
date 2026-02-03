@extends('layouts.app')

@section('content')
<div class="request-page">
    <div class="request-container">
        <h1 class="attendance-page-title">
            <span class="attendance-page-title__bar"></span>
            申請一覧
        </h1>

        <div class="request-tabs">
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}"
                class="request-tab {{ $tab === 'pending' ? 'is-active' : '' }}">
                承認待ち
            </a>

            <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}"
                class="request-tab {{ $tab === 'approved' ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>

        <div class="request-table-card">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    @php
                    $label = $req->status === 'approved' ? '承認済み' : '承認待ち';

                    $name = $req->user?->name ?? '-';

                    $workDate = $req->attendance?->work_date;
                    $target = $workDate ? \Carbon\Carbon::parse($workDate)->format('Y/m/d') : '-';

                    $reason = $req->requested_note ?? '-';

                    $applied = $req->created_at ? $req->created_at->format('Y/m/d') : '-';
                    @endphp

                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $name }}</td>
                        <td>{{ $target }}</td>
                        <td>{{ $reason }}</td>
                        <td>{{ $applied }}</td>
                        <td>
                            @if($isAdmin)
                            <a class="request-detail-link"
                                href="{{ route('stamp_correction_request.approve', ['id' => $req->id]) }}">
                                詳細
                            </a>
                            @else
                            <a class="request-detail-link"
                                href="{{ route('stamp_correction_request.show', ['id' => $req->id]) }}">
                                詳細
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="request-empty">
                            表示する申請がありません。
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="request-pagination">
            {{ $requests->appends(['tab' => $tab])->links() }}
        </div>
    </div>
</div>
@endsection