# /backend:model

Eloquent 모델과 마이그레이션을 생성한다.

## Arguments
- name: 모델명

## Database Rules

### SoftDeletes 필수 사용
일반 모델에는 항상 `SoftDeletes` trait을 적용한다.

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}
```

**예외 케이스** (SoftDeletes 미적용):
- Pivot 모델
- Log/History 모델
- 임시 데이터 모델

### 약한 결합 정책
마이그레이션에서 Foreign Key를 사용하지 않는다. Eloquent 관계는 정상적으로 정의한다.

```php
// Migration (약한 결합)
$table->uuid('user_id')->comment('users 테이블의 id 참조');

// Model (Eloquent 관계는 정상 정의)
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

## Instructions
1. 모델 스키마를 설계한다
2. 마이그레이션 파일을 생성한다 (약한 결합 정책 준수)
3. 모델 파일에 SoftDeletes trait을 추가한다 (예외 케이스 제외)
4. Eloquent 관계를 정의한다
