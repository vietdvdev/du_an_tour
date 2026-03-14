<?php
namespace App\Core;

use App\Core\Database;
use PDO;
use InvalidArgumentException;

class Validator
{
    protected array $data = [];
    protected array $rules = [];
    protected array $messages = [];
    protected array $attributes = [];
    protected array $errors = [];
    protected bool $validated = false;

    public static function make(array $data, array $rules, array $messages = [], array $attributes = []): self
    {
        $v = new self();
        $v->data = $data;
        $v->rules = $rules;
        $v->messages = $messages;
        $v->attributes = $attributes;
        return $v;
    }

    public function passes(): bool
    {
        return $this->validate();
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        if (!$this->validated) $this->validate();
        // Trả về dữ liệu hợp lệ theo keys đã có trong rules
        $out = [];
        foreach (array_keys($this->rules) as $field) {
            $val = $this->getValue($this->data, $field);
            if ($val !== null) $out[$field] = $val;
        }
        return $out;
    }

    /* ================== CORE ================== */

    protected function validate(): bool
    {
        if ($this->validated) return empty($this->errors);
        $this->errors = [];

        foreach ($this->rules as $field => $ruleSet) {
            $bail = false;
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', (string)$ruleSet);

            // detect bail
            if (in_array('bail', $rules, true)) {
                $bail = true;
                $rules = array_values(array_filter($rules, fn($r) => $r !== 'bail'));
            }

            $hasSometimes = in_array('sometimes', $rules, true);
            if ($hasSometimes) {
                $rules = array_values(array_filter($rules, fn($r) => $r !== 'sometimes'));
                if (!$this->hasKey($this->data, $field)) {
                    // không có field => bỏ qua validate
                    continue;
                }
            }

            $value = $this->getValue($this->data, $field);
            $isNullable = in_array('nullable', $rules, true);
            if ($isNullable) {
                $rules = array_values(array_filter($rules, fn($r) => $r !== 'nullable'));
                if ($value === null || $value === '') {
                    // cho phép rỗng → bỏ qua các rule khác
                    continue;
                }
            }

            foreach ($rules as $rule) {
                [$name, $param] = $this->parseRule($rule);
                $ok = $this->runRule($name, $field, $value, $param);
                if (!$ok) {
                    $this->addError($field, $name, $param);
                    if ($bail) break;
                }
            }
        }

        $this->validated = true;
        return empty($this->errors);
    }

    protected function parseRule(string $rule): array
    {
        $name = $rule;
        $param = null;
        if (strpos($rule, ':') !== false) {
            [$name, $param] = explode(':', $rule, 2);
        }
        return [strtolower(trim($name)), $param];
    }

    protected function runRule(string $name, string $field, $value, $param): bool
    {
        switch ($name) {
            case 'required':
                return !($value === null || $value === '');
            case 'string':
                return is_string($value);
            case 'integer':
                return (is_int($value) || (is_string($value) && preg_match('/^-?\d+$/', $value)));
            case 'numeric':
                return is_numeric($value);
            case 'boolean':
                return in_array($value, [true,false,1,0,'1','0','true','false','on','off','yes','no'], true);
            case 'email':
                return $value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return $value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'date':
                return $this->isDate($value);
            case 'before':
                return $this->compareDate($field, $value, $param, '<');
            case 'after':
                return $this->compareDate($field, $value, $param, '>');
            case 'min':
                return $this->checkMin($value, $param);
            case 'max':
                return $this->checkMax($value, $param);
            case 'size':
                return $this->checkSize($value, $param);
            case 'between':
                return $this->checkBetween($value, $param);
            case 'in':
                return $this->checkIn($value, $param, true);
            case 'not_in':
                return $this->checkIn($value, $param, false);
            case 'regex':
                return $this->checkRegex($value, $param);
            case 'confirmed':
                $other = $this->getValue($this->data, $field . '_confirmation');
                return $value === $other;
            case 'array':
                return is_array($value);
            case 'exists':
                return $this->checkExists($field, $value, $param);
            case 'unique':
                return $this->checkUnique($field, $value, $param);
            case 'nullable':
            case 'sometimes':
            case 'bail':
                return true;
            default:
                throw new InvalidArgumentException("Unknown validation rule: {$name}");
        }
    }

    /* ================== RULE HELPERS ================== */

    protected function isDate($v): bool
    {
        if ($v === null || $v === '') return false;
        return strtotime((string)$v) !== false;
    }

    protected function compareDate(string $field, $value, ?string $param, string $op): bool
    {
        if (!$this->isDate($value)) return false;
        if (!$param) return false;

        // param có thể là tên field khác hoặc giá trị ngày
        $other = $this->getValue($this->data, $param);
        $otherVal = $other ?? $param;
        if (!$this->isDate($otherVal)) return false;

        $a = strtotime((string)$value);
        $b = strtotime((string)$otherVal);

        return $op === '<' ? ($a < $b) : ($a > $b);
    }

    protected function lengthOf($v): ?int
    {
        if (is_string($v)) return mb_strlen($v);
        if (is_array($v))  return count($v);
        if (is_numeric($v)) return (int)$v;
        return null;
    }

    protected function checkMin($v, $param): bool
    {
        $n = (int)$param;
        $len = $this->lengthOf($v);
        return $len !== null && $len >= $n;
    }

    protected function checkMax($v, $param): bool
    {
        $n = (int)$param;
        $len = $this->lengthOf($v);
        return $len !== null && $len <= $n;
    }

    protected function checkSize($v, $param): bool
    {
        $n = (int)$param;
        $len = $this->lengthOf($v);
        return $len !== null && $len === $n;
    }

    protected function checkBetween($v, $param): bool
    {
        [$min, $max] = array_map('trim', explode(',', (string)$param, 2));
        $len = $this->lengthOf($v);
        return $len !== null && $len >= (int)$min && $len <= (int)$max;
    }

    protected function checkIn($v, $param, bool $positive): bool
    {
        $set = array_map('trim', explode(',', (string)$param));
        $in = in_array((string)$v, array_map('strval', $set), true);
        return $positive ? $in : !$in;
    }

    protected function checkRegex($v, ?string $pattern): bool
    {
        if ($v === null || $v === '') return false;
        if (!$pattern) return false;
        // Cho phép truyền regex kiểu /.../i hoặc không có delimiter
        if ($pattern[0] !== '/' || substr($pattern, -1) !== '/') {
            $pattern = '/' . str_replace('/', '\/', $pattern) . '/';
        }
        return (bool)preg_match($pattern, (string)$v);
    }

    protected function checkExists(string $field, $value, ?string $param): bool
    {
        // exists:table,column
        if (!$param || strpos($param, ',') === false) return false;
        [$table, $column] = array_map('trim', explode(',', $param, 2));
        $sql = "SELECT 1 FROM `{$table}` WHERE `{$column}` = ? LIMIT 1";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$value]);
        return (bool)$stmt->fetchColumn();
    }

    protected function checkUnique(string $field, $value, ?string $param): bool
    {
        // unique:table,column[,exceptId[,idColumn]]
        if (!$param) return false;
        $parts = array_map('trim', explode(',', $param));
        $table = $parts[0] ?? null;
        $column = $parts[1] ?? $field;
        $except = $parts[2] ?? null;
        $idCol  = $parts[3] ?? 'id';
        if (!$table) return false;

        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?";
        $bind = [$value];
        if ($except !== null && $except !== '') {
            $sql .= " AND `{$idCol}` <> ?";
            $bind[] = $except;
        }
        $sql .= " LIMIT 1";
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($bind);
        return ((int)$stmt->fetchColumn()) === 0;
    }

    /* ================== ERRORS & MESSAGES ================== */

    protected function addError(string $field, string $rule, $param = null): void
    {
        $keySpecific = $field . '.' . $rule;
        $attr = $this->attributes[$field] ?? $field;

        // Ưu tiên: messages[field.rule] > messages[rule]
        $msg = $this->messages[$keySpecific] ?? $this->messages[$rule] ?? $this->defaultMessage($rule);

        // thay thế :attribute, :value, :min, :max, :size, :other, :values
        $rep = [
            ':attribute' => $attr,
            ':value'     => is_array($param) ? implode(',', $param) : (string)($param ?? ''),
        ];

        if (is_string($param) && strpos($param, ',') !== false) {
            [$p1, $p2] = array_map('trim', explode(',', $param, 2));
            $rep[':min'] = $p1;
            $rep[':max'] = $p2;
        } else {
            $rep[':min'] = (string)($param ?? '');
            $rep[':max'] = (string)($param ?? '');
        }

        $rep[':size'] = (string)($param ?? '');
        $rep[':other'] = (string)($param ?? '');
        $rep[':values'] = (string)($param ?? '');

        $msg = strtr($msg, $rep);

        $this->errors[$field][] = $msg;
    }

    protected function defaultMessage(string $rule): string
    {
        // Thông điệp tiếng Việt ngắn gọn
        return match ($rule) {
            'required'  => 'Trường :attribute là bắt buộc.',
            'string'    => ':attribute phải là chuỗi.',
            'integer'   => ':attribute phải là số nguyên.',
            'numeric'   => ':attribute phải là số.',
            'boolean'   => ':attribute phải là true/false.',
            'email'     => ':attribute không đúng định dạng email.',
            'url'       => ':attribute không đúng định dạng URL.',
            'date'      => ':attribute không phải ngày hợp lệ.',
            'before'    => ':attribute phải trước :other.',
            'after'     => ':attribute phải sau :other.',
            'min'       => ':attribute phải tối thiểu :min.',
            'max'       => ':attribute không được vượt quá :max.',
            'size'      => ':attribute phải bằng :size.',
            'between'   => ':attribute phải trong khoảng :min đến :max.',
            'in'        => ':attribute phải nằm trong danh sách cho phép.',
            'not_in'    => ':attribute không được nằm trong danh sách này.',
            'regex'     => ':attribute không đúng định dạng.',
            'confirmed' => ':attribute không trùng khớp xác nhận.',
            'array'     => ':attribute phải là mảng.',
            'exists'    => ':attribute không tồn tại.',
            'unique'    => ':attribute đã tồn tại.',
            default     => ':attribute không hợp lệ.',
        };
    }

    /* ================== DATA ACCESS ================== */

    protected function getValue(array $data, string $key)
    {
        // hỗ trợ dot-notation: user.email
        if (strpos($key, '.') === false) {
            return $data[$key] ?? null;
        }
        $segments = explode('.', $key);
        $cur = $data;
        foreach ($segments as $seg) {
            if (!is_array($cur) || !array_key_exists($seg, $cur)) {
                return null;
            }
            $cur = $cur[$seg];
        }
        return $cur;
    }

    protected function hasKey(array $data, string $key): bool
    {
        if (strpos($key, '.') === false) {
            return array_key_exists($key, $data);
        }
        $segments = explode('.', $key);
        $cur = $data;
        foreach ($segments as $seg) {
            if (!is_array($cur) || !array_key_exists($seg, $cur)) return false;
            $cur = $cur[$seg];
        }
        return true;
    }
}
