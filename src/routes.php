<?php

Route::group(['namespace' => 'Sinevia\LaravelMediaManager\Controllers'], function(){
	Route::get('get-media', 'MediaController@anyIndex')->name('getMedia');
	Route::get('get-media-manager', 'MediaController@getMediaManager')->name('getMediaManager');
	Route::post('post-file-upload', 'MediaController@postFileUpload')->name('postFileUpload');
	Route::post('post-directory-create', 'MediaController@postDirectoryCreate')->name('postDirectoryCreate');
	Route::post('post-directory-delete', 'MediaController@postDirectoryDelete')->name('postDirectoryDelete');
	Route::post('post-file-delete', 'MediaController@postFileDelete')->name('postFileDelete');
	Route::post('post-file-rename', 'MediaController@postFileRename')->name('postFileRename');
});