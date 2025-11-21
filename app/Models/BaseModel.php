<?php
namespace App\Models;

use App\Core\DB;

abstract class BaseModel
{
    protected string $table;
    protected string $primaryKey = 'id';


    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Khởi tạo Query Builder cho bảng hiện tại.
     * Đã là public để gọi từ Controller.
     */
    public function builder(): \App\Core\QueryBuilder
    {
        return DB::table($this->table);
    }

    /** Lấy tất cả */
    public function all(): array
    {
        return $this->builder()
            ->orderBy($this->primaryKey, 'DESC')
            ->get();
    }

    /** Tìm theo khoá chính */
    public function find($id): ?array
    {
        return $this->builder()
            ->where($this->primaryKey, $id)
            ->first();
    }

    /** where đơn giản: cột = giá trị (trả về nhiều dòng) */
    public function where(string $column, $value): array
    {
        return $this->builder()
            ->where($column, $value)
            ->get();
    }

    /** where đơn giản, trả về 1 dòng đầu tiên */
    public function firstWhere(string $column, $value): ?array
    {
        return $this->builder()
            ->where($column, $value)
            ->first();
    }

    /** Tạo mới, trả về id vừa insert */
    public function create(array $data): int
    {
        return $this->builder()->insert($data);
    }

    /** Cập nhật theo khoá chính, trả về true/false */
    public function update($id, array $data): bool
    {
        $affected = $this->builder()
            ->where($this->primaryKey, $id)
            ->update($data);
        return $affected > 0;
    }

    /** Xoá theo khoá chính, trả về true/false */
    public function delete($id): bool
    {
        $deleted = $this->builder()
            ->where($this->primaryKey, $id)
            ->delete();
        return $deleted > 0;
    }

    /** Phân trang cơ bản (không filter) */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $total = $this->builder()->count();

        $rows = $this->builder()
            ->orderBy($this->primaryKey, 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        return [
            'data'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => (int)ceil($total / $perPage),
        ];
        }
}
