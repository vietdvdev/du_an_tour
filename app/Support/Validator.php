<?php
namespace App\Support;

use App\Core\Database;

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $messages;
    protected array $errors = [];

    /** @var array<string, callable> */
    protected static array $customRules = [];
    /** @var array<string, callable> */
    protected static array $customRuleMessages = [];

    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data     = $data;
        $this->rules    = $rules;
        $this->messages = $messages;
        $this->validate();
    }

    /* ---------- Public API ---------- */

    public static function register(string $name, callable $callback, ?callable $messageResolver = null): void
    {
        // $callback($value, array $params, array $data): bool|string
        self::$customRules[$name] = $callback;
        if ($messageResolver) {
            self::$customRuleMessages[$name] = $messageResolver; // ($field, $params) => string
        }
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /* ---------- Core ---------- */

    protected function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;

            // Hỗ trợ mảng rule
            $rules = is_array($ruleString) ? $ruleString : explode('|', (string)$ruleString);

            foreach ($rules as $rule) {
                [$name, $paramStr] = $this->parseRule($rule);
                $params = $paramStr === null ? [] : explode(',', $paramStr);

                // REQUIRED (đặc biệt: luôn check đầu tiên nếu có)
                if ($name === 'required') {
                    if ($this->isEmpty($value)) {
                        $this->addError($field, 'required', "Trường $field là bắt buộc.");
                        // Nếu required fail, các rule khác (trừ sometimes) không cần check nữa
                        continue;
                    }
                    // Nếu có required và đã có giá trị => tiếp tục rules khác
                    continue;
                }

                // Nếu không required và value “trống” => bỏ qua các rule còn lại
                if ($this->isEmpty($value)) {
                    continue;
                }

                switch ($name) {
                    case 'string':
                        if (!is_string($value)) {
                            $this->addError($field, 'string', "Trường $field phải là chuỗi.");
                        }
                        break;

                    case 'array':
                        if (!is_array($value)) {
                            $this->addError($field, 'array', "Trường $field phải là mảng.");
                        }
                        break;

                    case 'numeric':
                        if (!is_numeric($value)) {
                            $this->addError($field, 'numeric', "Trường $field phải là số.");
                        }
                        break;

                    case 'integer':
                        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                            $this->addError($field, 'integer', "Trường $field phải là số nguyên.");
                        }
                        break;

                    case 'boolean':
                        if (!in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true)) {
                            $this->addError($field, 'boolean', "Trường $field phải là boolean.");
                        }
                        break;

                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field, 'email', "Email không hợp lệ.");
                        }
                        break;

                    case 'url':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $this->addError($field, 'url', "URL không hợp lệ.");
                        }
                        break;

                    case 'ip':
                        if (!filter_var($value, FILTER_VALIDATE_IP)) {
                            $this->addError($field, 'ip', "Địa chỉ IP không hợp lệ.");
                        }
                        break;

                    case 'date':
                        if (strtotime((string)$value) === false) {
                            $this->addError($field, 'date', "Ngày không hợp lệ.");
                        }
                        break;

                    case 'min':
                        $min = (int)($params[0] ?? 0);
                        if (mb_strlen((string)$value) < $min) {
                            $this->addError($field, 'min', "Trường $field phải có ít nhất $min ký tự.");
                        }
                        break;

                    case 'max':
                        $max = (int)($params[0] ?? PHP_INT_MAX);
                        if (mb_strlen((string)$value) > $max) {
                            $this->addError($field, 'max', "Trường $field không được vượt quá $max ký tự.");
                        }
                        break;

                    case 'in':
                        if (!in_array((string)$value, $params, true)) {
                            $this->addError($field, 'in', "Trường $field phải nằm trong: " . implode(', ', $params) . ".");
                        }
                        break;

                    case 'not_in':
                        if (in_array((string)$value, $params, true)) {
                            $this->addError($field, 'not_in', "Trường $field không được thuộc: " . implode(', ', $params) . ".");
                        }
                        break;

                    case 'same':
                        $other = $params[0] ?? null;
                        if ($other === null || (($this->data[$other] ?? null) !== $value)) {
                            $this->addError($field, 'same', "Trường $field phải giống $other.");
                        }
                        break;

                    case 'different':
                        $other = $params[0] ?? null;
                        if ($other === null || (($this->data[$other] ?? null) === $value)) {
                            $this->addError($field, 'different', "Trường $field phải khác $other.");
                        }
                        break;

                    case 'confirmed':
                        $cf = $field . '_confirmation';
                        if (($this->data[$cf] ?? null) !== $value) {
                            $this->addError($field, 'confirmed', "Xác nhận $field không khớp.");
                        }
                        break;

                    case 'regex':
                        $pattern = $this->extractRegex($paramStr);
                        if ($pattern === null || @preg_match($pattern, (string)$value) !== 1) {
                            $this->addError($field, 'regex', "Trường $field không đúng định dạng.");
                        }
                        break;

                    case 'unique':
                        [$table, $column, $exceptId, $idColumn] = $this->parseUniqueExists($params);
                        if ($this->dbCount($table, $column, $value, $exceptId, $idColumn) > 0) {
                            $this->addError($field, 'unique', "Giá trị của $field đã tồn tại.");
                        }
                        break;

                    case 'exists':
                        [$table, $column] = $this->parseTableColumn($params);
                        if ($this->dbCount($table, $column, $value) === 0) {
                            $this->addError($field, 'exists', "Giá trị của $field không tồn tại.");
                        }
                        break;

                    case 'file':
                        if (!$this->isUploadedFile($value)) {
                            $this->addError($field, 'file', "Trường $field phải là tệp tải lên hợp lệ.");
                            break;
                        }
                        // Nếu kèm mimes hoặc file_max, chúng sẽ chạy ở rule tương ứng
                        break;

                    case 'mimes':
                        if ($this->isUploadedFile($value)) {
                            $allowed = array_map('strtolower', $params); // jpg,png,webp...
                            $ext = strtolower(pathinfo($value['name'] ?? '', PATHINFO_EXTENSION));
                            $mimeOk = $this->checkMimeByFinfo($value['tmp_name'] ?? '') ?: $ext;
                            // chấp nhận nếu đuôi hoặc mime suy ra khớp danh sách
                            if (!in_array($ext, $allowed, true) && !in_array($mimeOk, $allowed, true)) {
                                $this->addError($field, 'mimes', "Tệp $field phải có định dạng: " . implode(', ', $allowed) . ".");
                            }
                        }
                        break;

                    case 'file_max':
                        // file_max:<kilobytes>
                        if ($this->isUploadedFile($value)) {
                            $maxKb = (int)($params[0] ?? 0);
                            $sizeKb = isset($value['size']) ? (int)ceil((int)$value['size'] / 1024) : 0;
                            if ($maxKb > 0 && $sizeKb > $maxKb) {
                                $this->addError($field, 'file_max', "Kích thước $field tối đa {$maxKb}KB.");
                            }
                        }
                        break;

                    case 'custom':
                        // custom:<ruleName>:param1,param2
                        $customName = $params[0] ?? null;
                        $customParams = array_slice($params, 1);
                        if ($customName && isset(self::$customRules[$customName])) {
                            $result = call_user_func(self::$customRules[$customName], $value, $customParams, $this->data);
                            if ($result === false) {
                                // Thông điệp mặc định cho custom
                                $msg = self::$customRuleMessages[$customName] ?? null;
                                $text = $msg ? $msg($field, $customParams) : "Trường $field không hợp lệ.";
                                $this->addError($field, "custom.$customName", $text);
                            } elseif (is_string($result)) {
                                // cho phép trả về chuỗi lỗi tùy ý
                                $this->addError($field, "custom.$customName", $result);
                            }
                        }
                        break;

                    default:
                        // Bỏ qua rule không biết (hoặc bạn muốn quăng lỗi tùy ý)
                        break;
                }
            }
        }
    }

    /* ---------- Helpers ---------- */

    protected function parseRule(string $rule): array
    {
        $pos = strpos($rule, ':');
        if ($pos === false) return [$rule, null];
        return [substr($rule, 0, $pos), substr($rule, $pos + 1)];
    }

    protected function isEmpty($v): bool
    {
        if ($v === null) return true;
        if (is_string($v) && trim($v) === '') return true;
        if (is_array($v) && empty($v)) return true;
        return false;
    }

    protected function addError(string $field, string $rule, string $defaultMessage): void
    {
        // Thử lấy message tùy chỉnh theo key "field.rule"
        $key = $field . '.' . $rule;
        $msg = $this->messages[$key] ?? null;

        // Nếu không có, thử "field.ruleName" cho custom.<name>
        if ($msg === null && str_starts_with($rule, 'custom.')) {
            $msg = $this->messages[$key] ?? null;
        }

        $this->errors[$field][] = $msg ?? $defaultMessage;
    }

    protected function extractRegex(?string $paramStr): ?string
    {
        if ($paramStr === null) return null;
        // Hỗ trợ dạng regex:/^...$/ hoặc regex:#...#i
        $first = substr($paramStr, 0, 1);
        $last  = substr($paramStr, -1);
        if ($first && $last && $first === $last && !ctype_alnum($first)) {
            return $paramStr;
        }
        // Nếu người dùng chỉ truyền pattern không có delimiter, tự bao bằng '/'
        return '/' . str_replace('/', '\/', $paramStr) . '/';
    }

    protected function parseTableColumn(array $params): array
    {
        $table  = $params[0] ?? '';
        $column = $params[1] ?? 'id';
        return [$table, $column];
    }

    protected function parseUniqueExists(array $params): array
    {
        // unique:table,column[,exceptId[,idColumn]]
        $table    = $params[0] ?? '';
        $column   = $params[1] ?? 'id';
        $exceptId = $params[2] ?? null;
        $idColumn = $params[3] ?? 'id';
        return [$table, $column, $exceptId, $idColumn];
    }

    protected function dbCount(string $table, string $column, $value, $exceptId = null, string $idColumn = 'id'): int
    {
        $pdo = Database::pdo();
        if ($exceptId !== null && $exceptId !== '') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = :v AND {$idColumn} <> :id");
            $stmt->execute(['v' => $value, 'id' => $exceptId]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = :v");
            $stmt->execute(['v' => $value]);
        }
        return (int)$stmt->fetchColumn();
    }

    protected function isUploadedFile($value): bool
    {
        return is_array($value)
            && isset($value['tmp_name'], $value['name'], $value['size'])
            && is_uploaded_file($value['tmp_name']);
    }

    protected function checkMimeByFinfo(string $tmpPath): ?string
    {
        if (!is_file($tmpPath)) return null;
        if (function_exists('finfo_open')) {
            $f = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($f, $tmpPath);
            finfo_close($f);
            // Trả về phần subtype nếu muốn so khớp cùng mimes rule dựa trên đuôi
            // Ở đây mình trả về chính mime (vd: image/jpeg), rule mimes chấp nhận đuôi hoặc mime
            return $mime;
        }
        return null;
    }
}
