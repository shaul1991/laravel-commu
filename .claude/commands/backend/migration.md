# /backend:migration

데이터베이스 마이그레이션을 생성한다.

## Arguments
- name: 마이그레이션명 (예: create_users_table)

## Database Rules

### 약한 결합 (Soft Reference) 정책
Foreign Key 제약조건을 사용하지 않는다.

```php
// ❌ 사용하지 않음
$table->foreignId('user_id')->constrained('users')->onDelete('cascade');

// ✅ 약한 결합 방식 사용
$table->uuid('user_id')->comment('users 테이블의 id 참조');
$table->unsignedBigInteger('category_id')->comment('categories 테이블의 id 참조');
```

### SoftDeletes 필수 사용
일반 테이블에는 항상 `$table->softDeletes()`를 추가한다.

**예외 케이스** (SoftDeletes 미적용):
- 중간 관계 테이블 (pivot tables)
- 로그/히스토리 테이블 (insert only)
- 임시 데이터 테이블

## Instructions
1. 테이블 구조를 설계한다
2. php artisan make:migration으로 마이그레이션을 생성한다
3. 컬럼 정의 시 약한 결합 정책을 준수한다 (foreignId 사용 금지)
4. 참조 컬럼에는 반드시 comment()로 참조 테이블을 명시한다
5. 일반 테이블에는 softDeletes()를 추가한다
6. down() 메서드로 롤백을 구현한다
