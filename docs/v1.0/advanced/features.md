# Advanced Features

Explore the advanced capabilities of the framework.

## Caching

The framework provides multiple cache drivers:

```php
// Store data in cache
Cache::put('key', 'value', 3600);

// Retrieve data from cache
$value = Cache::get('key');

// Remember (get or store)
$users = Cache::remember('users.all', 3600, function() {
    return User::all();
});
```

## Events and Listeners

Subscribe to application events:

```php
// Define an event
class UserRegistered {
    public $user;

    public function __construct($user) {
        $this->user = $user;
    }
}

// Create a listener
class SendWelcomeEmail {
    public function handle(UserRegistered $event) {
        Mail::to($event->user)->send(new WelcomeEmail());
    }
}

// Dispatch the event
event(new UserRegistered($user));
```

## Queue Jobs

Process tasks asynchronously:

```php
// Create a job
class ProcessVideo {
    public function handle() {
        // Process video...
    }
}

// Dispatch to queue
dispatch(new ProcessVideo($video));
```

## File Storage

Work with local and cloud storage:

```php
// Store a file
Storage::put('avatars/user.jpg', $fileContents);

// Get a file
$contents = Storage::get('avatars/user.jpg');

// Delete a file
Storage::delete('avatars/user.jpg');

// Check existence
if (Storage::exists('file.txt')) {
    // File exists
}
```

## Validation

Validate input data:

```php
$validator = Validator::make($request->all(), [
    'name' => 'required|max:255',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
]);

if ($validator->fails()) {
    return redirect()->back()
        ->withErrors($validator)
        ->withInput();
}
```

## API Resources

Transform models into JSON:

```php
class UserResource extends JsonResource {
    public function toArray($request) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

// Use the resource
return UserResource::collection($users);
```

## Performance Tips

- Use **eager loading** to prevent N+1 queries
- Enable **query caching** for frequently accessed data
- Use **queue workers** for heavy processing
- Implement **database indexing** on frequently queried columns
- Enable **OPcache** in production

## Security Features

- **CSRF Protection**: Automatically enabled for POST requests
- **XSS Prevention**: All output is escaped by default
- **SQL Injection Protection**: Use prepared statements
- **Password Hashing**: Use bcrypt or Argon2

---

Next: [API Reference](../api/reference.md)
