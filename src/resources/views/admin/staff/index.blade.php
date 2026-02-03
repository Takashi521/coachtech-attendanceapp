@extends('layouts.app')

@section('content')
<div class="attendance-page admin-staff-page">
    <div class="attendance-list-container">
        <h1 class="attendance-page-title">
            <span class="attendance-page-title__bar"></span>
            スタッフ一覧
        </h1>

        <div class="admin-attendance-table-card">
            <table class="admin-attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td class="admin-attendance-td--name">{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <a class="admin-attendance-detail-link"
                               href="{{ route('admin.staff.attendance', ['user' => $u->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="admin-attendance-empty">データがありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
