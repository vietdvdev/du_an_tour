<?php
namespace App\Core;

use App\Middleware\MiddlewareInterface;

class Router
{
    /** @var array<string, array<int, array{pattern:string, path:string, action:mixed, middleware:array}>> */
    protected array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /** @var array<int, array{prefix?:string, middleware?:array}> */
    protected array $groupStack = [];

    /** Registry tên route: ['home.index' => '/'] */
    protected array $routeNames = [];

    /** Tên chờ gán (fallback nếu gọi name() trước get()/post()) */
    protected ?string $pendingName = null;

    /** Ghi nhớ đường dẫn (fullPath) vừa add gần nhất để gán tên khi gọi ->name() */
    protected ?string $lastAddedPath = null;

    /* =====================
     *  Public API
     * ===================== */

    public function get(string $path, $action, array $middleware = []): self
    {
        $this->add('GET', $path, $action, $middleware, $this->pendingName);
        $this->pendingName = null;
        return $this;
    }

    public function post(string $path, $action, array $middleware = []): self
    {
        $this->add('POST', $path, $action, $middleware, $this->pendingName);
        $this->pendingName = null;
        return $this;
    }

    /**
     * Nhóm route có chung prefix/middleware
     * Example:
     * $router->group(['prefix' => '/users', 'middleware' => [Auth::class]], function(Router $r){
     *     $r->get('', [UserController::class, 'index'])->name('users.index');
     * });
     */
    public function group(array $attrs, callable $callback): void
    {
        $this->groupStack[] = $attrs;
        $callback($this);
        array_pop($this->groupStack);
    }

    /** Đặt tên cho route vừa thêm gần nhất; nếu chưa add route nào thì lưu tạm để gán cho lần add tiếp theo */
    public function name(string $name): self
    {
        if ($this->lastAddedPath) {
            $this->routeNames[$name] = $this->lastAddedPath;
            $this->pendingName = null;
            return $this;
        }
        $this->pendingName = $name; // gọi name() trước get()/post()
        return $this;
    }

    /** Tạo URL từ tên route; thay {param} hoặc {param:regex} bằng giá trị */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->routeNames[$name])) {
            throw new \RuntimeException("Route name [$name] không tồn tại.");
        }
        $path = $this->routeNames[$name];

        foreach ($params as $key => $value) {
            $path = preg_replace('/\{'.$key.'(:[^}]+)?\}/', (string)$value, $path);
        }
        return $path;
    }

    /** Khớp request hiện tại và dispatch vào controller */
    public function dispatch(Request $request): Response
    {
        $method = $request->method;
        $uri    = rtrim($request->uri, '/') ?: '/';

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                // gom named params
                foreach ($matches as $k => $v) {
                    if (!is_int($k)) {
                        $request->params[$k] = $v;
                    }
                }

                // chạy pipeline middleware rồi gọi action
                return $this->runMiddleware($route['middleware'], $request, function ($req) use ($route) {
                    return $this->invoke($route['action'], $req);
                });
            }
        }

        return Response::make('<h1>404 Not Found</h1>', 404);
    }

    /* =====================
     *  Internals
     * ===================== */

    protected function add(
        string $method,
        string $path,
        $action,
        array $middleware = [],
        ?string $name = null
    ): void {
        // chuẩn hoá path
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . ltrim($path, '/');
        }

        // gộp prefix & middleware từ group
        $prefix = '';
        $groupMiddleware = [];
        foreach ($this->groupStack as $g) {
            $p = $g['prefix'] ?? '';
            if ($p) {
                if ($p[0] !== '/') $p = '/' . ltrim($p, '/');
                $prefix .= rtrim($p, '/');
            }
            if (!empty($g['middleware']) && is_array($g['middleware'])) {
                $groupMiddleware = array_merge($groupMiddleware, $g['middleware']);
            }
        }

        $fullPath = rtrim($prefix . ($path === '/' ? '' : $path), '/') ?: '/';

        // build regex pattern: {id:\d+} -> (?P<id>\d+), {slug} -> (?P<slug>[^/]+)
        $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)(:([^}]+))?\}/', function ($m) {
            $name = $m[1];
            $pat  = isset($m[3]) ? $m[3] : '[^/]+';
            return '(?P<' . $name . '>' . $pat . ')';
        }, $fullPath);

        $pattern = '#^' . $regex . '$#';

        $this->routes[$method][] = [
            'pattern'    => $pattern,
            'path'       => $fullPath,
            'action'     => $action,
            'middleware' => array_merge($groupMiddleware, $middleware),
        ];

        if ($name) {
            $this->routeNames[$name] = $fullPath;
        }

        // ghi nhớ route vừa add để name() gán đúng
        $this->lastAddedPath = $fullPath;
    }

    protected function runMiddleware(array $middleware, Request $request, callable $destination): Response
    {
        if (empty($middleware)) {
            return $destination($request);
        }

        $pipeline = array_reverse($middleware);
        $next = $destination;

        foreach ($pipeline as $mw) {
            $next = function (Request $req) use ($mw, $next) {
                $instance = is_string($mw) ? new $mw() : $mw;
                if (!($instance instanceof MiddlewareInterface)) {
                    // cho phép callable đơn giản như middleware
                    if (is_callable($instance)) {
                        return $instance($req, $next);
                    }
                    throw new \RuntimeException('Middleware must implement MiddlewareInterface or be callable.');
                }
                return $instance->handle($req, $next);
            };
        }

        return $next($request);
    }

    /**
     * Gọi action:
     * - Closure callable
     * - "Class@method"
     * - [ClassName::class, 'method'] hoặc [$instance, 'method']
     */
    protected function invoke($action, Request $request): Response
    {
        // 1) Closure / callable trực tiếp
        if (is_callable($action) && !is_array($action)) {
            $result = $action($request);
        }
        // 2) "Class@method"
        elseif (is_string($action) && strpos($action, '@') !== false) {
            [$class, $method] = explode('@', $action, 2);
            $controller = new $class();
            $result = $controller->{$method}($request);
        }
        // 3) [ClassName::class, 'method'] hoặc [$instance, 'method']
        elseif (is_array($action) && count($action) === 2) {
            [$clsOrObj, $method] = $action;
            $controller = is_string($clsOrObj) ? new $clsOrObj() : $clsOrObj;
            $result = $controller->{$method}($request);
        } else {
            throw new \RuntimeException('Invalid route action');
        }

        // Chuẩn hoá kết quả về Response
        if ($result instanceof Response) {
            return $result;
        }
        if (is_array($result) || is_object($result)) {
            return (new Response())->json($result);
        }
        return Response::make((string)$result);
    }
}
