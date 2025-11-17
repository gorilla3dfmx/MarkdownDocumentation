# What's New in Version 2.0

Version 2.0 brings exciting new features and improvements!

## New Features

### 1. Improved Performance

- **50% faster** routing engine
- Optimized database queries
- Better caching mechanisms

### 2. Enhanced Security

```php
// New security middleware
$app->middleware('security', [
    'csrf' => true,
    'xss_protection' => true,
    'content_type_nosniff' => true,
]);
```

### 3. Modern PHP Support

Now requires **PHP 8.1+** with full support for:

- Named arguments
- Enums
- Readonly properties
- Fibers

### 4. Real-time Features

```php
// WebSocket support
Broadcasting::channel('notifications.{userId}', function($user, $userId) {
    return $user->id === (int) $userId;
});

// Broadcast an event
broadcast(new NotificationSent($notification));
```

### 5. Improved Testing

```php
// New fluent testing API
$this->actingAs($user)
    ->post('/api/posts', ['title' => 'Test'])
    ->assertStatus(201)
    ->assertJson([
        'data' => [
            'title' => 'Test'
        ]
    ]);
```

## Breaking Changes

⚠️ **Important:** This version contains breaking changes.

1. Minimum PHP version is now **8.1**
2. Database driver changes - update your configuration
3. Deprecated methods removed

## Migration Guide

### Update Dependencies

```bash
composer require framework/core:^2.0
```

### Update Configuration

```php
// Old (v1.0)
'driver' => 'mysql',

// New (v2.0)
'driver' => 'mysql8',
```

### Update Code

Replace deprecated methods:

```php
// Old
$user->getAttribute('name');

// New
$user->name;
```

## Upgrade Checklist

- [X] Update PHP to 8.1 or higher
- [ ] Update composer dependencies
- [ ] Update configuration files
- [ ] Run database migrations
- [ ] Update deprecated code
- [X] Run test suite

## Performance Benchmarks

| Operation | v1.0 | v2.0 | Improvement |
|-----------|------|------|-------------|
| Request routing | 2.5ms | 1.2ms | 52% faster |
| Database query | 15ms | 12ms | 20% faster |
| Cache retrieval | 0.8ms | 0.5ms | 37% faster |

## Resources

- [Migration Guide](migration-guide.md)
- [Changelog](changelog.md)
- [API Reference](api/reference.md)
