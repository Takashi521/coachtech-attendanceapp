```mermaid
erDiagram
    users ||--o{ attendances : has_many
    attendances ||--o{ attendance_breaks : has_many
    users ||--o{ correction_requests : applies
    correction_requests ||--o{ correction_request_breaks : has_many
    users ||--o{ correction_requests : approves

    users {
      bigint id PK
      string name
      string email
      string role
    }

    attendances {
      bigint id PK
      bigint user_id FK
      date work_date
      time work_start_time
      time work_end_time
      string status
      text note
      UNIQUE user_id, work_date
    }
