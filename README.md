# Laravel Media Manager

## Installation ##


- Manual. To dev-master (for development only)
```
"repositories": [
   {
       "type": "vcs",
       "url": "https://github.com/Sinevia/laravel-media-manager.git"
   }
],
"require": {
    "Sinevia/laravel-media-manager": "dev-master"
},

```

## Configuration ##

Step 1. Create a new directory in your public directory called media
/public/media

Step 2. Create a new entry in your filesystems.php config file

```
'media_manager' => [
    'driver' => 'local',
    'root' => public_path('media'),
    'visibility' => 'public',
],
```

Step 3. Add a new entry in your routers file

```
Route::group(['middleware' => ['admin-only-middleware']], function () {
    AdvancedRoute::controller('/media', '\Sinevia\LaravelMediaManager\Controllers\MediaController');
});
```
