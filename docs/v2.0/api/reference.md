# API Reference

Complete API documentation for Version 2.0.

## Application

### `Application` Class

Main application class.

#### Methods

##### `run()`

Start the application.

```php
public function run(): void
```

**Example:**

```php
$app = new Application();
$app->run();
```

##### `register($provider)`

Register a service provider.

```php
public function register(ServiceProvider $provider): void
```

**Parameters:**
- `$provider` (ServiceProvider): The service provider to register

## Router

### `Router` Class

Handles HTTP routing.

#### Methods

##### `get($uri, $action)`

Register a GET route.

```php
public function get(string $uri, $action): Route
```

**Parameters:**
- `$uri` (string): The route URI
- `$action` (string|Closure): Controller action or closure

**Returns:** Route instance

**Example:**

```php
$router->get('/users/{id}', 'UserController@show');
```

##### `post($uri, $action)`

Register a POST route.

```php
public function post(string $uri, $action): Route
```

##### `put($uri, $action)`

Register a PUT route.

```php
public function put(string $uri, $action): Route
```

##### `delete($uri, $action)`

Register a DELETE route.

```php
public function delete(string $uri, $action): Route
```

##### `middleware($name, $middleware)`

Register middleware.

```php
public function middleware(string $name, Closure $middleware): void
```

## Database

### `DB` Class

Database query builder.

#### Methods

##### `table($table)`

Start a query on a table.

```php
public static function table(string $table): QueryBuilder
```

**Example:**

```php
$users = DB::table('users')->where('active', true)->get();
```

##### `select($query, $bindings)`

Execute a SELECT query.

```php
public static function select(string $query, array $bindings = []): array
```

##### `insert($table, $data)`

Insert data into a table.

```php
public static function insert(string $table, array $data): bool
```

**Example:**

```php
DB::insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

##### `update($table, $data, $where)`

Update table data.

```php
public static function update(string $table, array $data, array $where): int
```

**Returns:** Number of affected rows

##### `delete($table, $where)`

Delete from table.

```php
public static function delete(string $table, array $where): int
```

## Cache

### `Cache` Class

Caching functionality.

#### Methods

##### `get($key, $default)`

Retrieve item from cache.

```php
public static function get(string $key, $default = null): mixed
```

##### `put($key, $value, $ttl)`

Store item in cache.

```php
public static function put(string $key, $value, int $ttl = 3600): bool
```

**Parameters:**
- `$key` (string): Cache key
- `$value` (mixed): Value to cache
- `$ttl` (int): Time to live in seconds

##### `remember($key, $ttl, $callback)`

Get or store cached value.

```php
public static function remember(string $key, int $ttl, Closure $callback): mixed
```

**Example:**

```php
$users = Cache::remember('users.all', 3600, function() {
    return User::all();
});
```

##### `forget($key)`

Remove item from cache.

```php
public static function forget(string $key): bool
```

##### `flush()`

Clear all cached items.

```php
public static function flush(): bool
```

## Validation

### `Validator` Class

Input validation.

#### Methods

##### `make($data, $rules)`

Create a new validator instance.

```php
public static function make(array $data, array $rules): Validator
```

**Example:**

```php
$validator = Validator::make($request->all(), [
    'email' => 'required|email',
    'password' => 'required|min:8',
]);
```

#### Available Rules

- `required` - Field must be present and not empty
- `email` - Field must be valid email
- `min:n` - Field must be at least n characters
- `max:n` - Field must not exceed n characters
- `numeric` - Field must be numeric
- `integer` - Field must be an integer
- `array` - Field must be an array
- `unique:table,column` - Field must be unique in database
- `confirmed` - Field must have matching confirmation field

---

For more examples, see the [Examples](../examples.md) page.
