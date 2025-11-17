# Basic Concepts

Understanding the core concepts will help you work more effectively with the framework.

## Architecture

The framework follows a **Model-View-Controller (MVC)** pattern:

- **Models**: Handle data and business logic
- **Views**: Render the user interface
- **Controllers**: Handle user input and coordinate between models and views

## Components

### Router

The router maps URLs to controller actions:

```php
$router->get('/users/{id}', 'UserController@show');
```

### Middleware

Middleware provides a convenient mechanism for filtering HTTP requests:

```php
$app->middleware('auth', function($request, $next) {
    if (!Auth::check()) {
        return redirect('/login');
    }
    return $next($request);
});
```

### Database

The framework includes a built-in query builder:

```php
$users = DB::table('users')
    ->where('active', true)
    ->orderBy('name')
    ->get();
```

## Best Practices

1. **Keep controllers thin** - Move business logic to service classes
2. **Use dependency injection** - Don't create dependencies manually
3. **Follow naming conventions** - Use descriptive, consistent names
4. **Write tests** - Aim for high test coverage

## Code Examples

### Creating a Controller

```php
class UserController extends Controller {
    public function index() {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function show($id) {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }
}
```

### Creating a Model

```php
class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email'];

    public function posts() {
        return $this->hasMany(Post::class);
    }
}
```

## Summary

You've learned the basic concepts of the framework. Next, explore:

- [Advanced Features](advanced/features.md)
- [Database Guide](database.md)
