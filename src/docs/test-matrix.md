# Test Matrix (Manual Case ID → Automated Test)

- ID1 認証（一般ユーザー）: tests/Feature/Auth/RegisterValidationTest.php
- ID2 ログイン（一般ユーザー）: tests/Feature/Auth/LoginValidationTest.php
- ID3 ログイン（管理者）: tests/Feature/Admin/AdminLoginTest.php
- ID4-8 打刻フロー（日付/ステータス/出勤/休憩/退勤）: tests/Feature/Attendance/StampFlowTest.php
- ID9 勤怠一覧（月次・前月翌月・0:00非表示）: tests/Feature/Attendance/MonthlyListTest.php
- ID10 勤怠詳細表示: tests/Feature/Attendance/DetailViewTest.php
- ID11 勤怠詳細修正/申請: tests/Feature/Attendance/CorrectionRequestTest.php
- ID12 管理者 日次勤怠一覧: tests/Feature/Admin/DailyAttendanceTest.php
- ID13 管理者 勤怠修正: tests/Feature/Admin/AdminAttendanceEditTest.php
- ID14 管理者 スタッフ一覧/スタッフ別月次: tests/Feature/Admin/StaffListMonthlyTest.php
- ID15 管理者 申請一覧/承認: tests/Feature/Admin/AdminApprovalTest.php
- (応用) ID16 メール認証: tests/Feature/Auth/EmailVerificationTest.php