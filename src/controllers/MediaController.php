<?php

namespace Sinevia\LaravelMediaManager\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Storage;

class MediaController extends Controller
{

    public $disk = null;
    public $filesRootDir = null;
    public $filesRootUrl = null;
    public $fileManagerRootDir = null;
    public $fileManagerRootUrl = null;

    public function __construct()
    {
        // $this->user = \App\Helpers\AppHelper::getUser('admin');
        // if ($this->user == null) {
        //     die('User authentication needed to use this service');
        //     exit;
        // }
        $this->disk = 'media_manager';
//        $rootDir = trim(request('root_dir', ''));
        //        if ($rootDir == '') {
        //            die('Root directory is required');
        //        }
        $this->filesRootDir = public_path('media'); //public_path() . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
        $this->filesRootUrl = url('/') . '/media/';
//        $rootDir = trim(request('root_dir', ''));
        //        if ($rootDir == '') {
        //            die('Root directory is required');
        //        }
        //        $rootDir = trim($rootDir, '/');
        //        $rootDir = trim($rootDir, '.');
        //        $this->fileManagerRootDir = $this->filesRootDir . $rootDir . DIRECTORY_SEPARATOR;
        //        $this->fileManagerRootUrl = $this->filesRootUrl . $rootDir . '/';
        //
        //        $dirExists = Storage::disk($this->disk)->exists($this->fileManagerRootDir);
        //
        //        if($dirExists==false){
        //            $result = Storage::disk($this->disk)->makeDirectory($this->fileManagerRootDir);
        //        }
    }

    /**
     * @return string
     */
    public function anyIndex()
    {
        return $this->getMediaManager();
    }

    /**
     * Home page
     * @return string
     */
    public function getMediaManager()
    {
        // dd('Hello');
        $currentDirectory = request('current_dir', '');
        $currentDirectory = trim($currentDirectory, '/');
        $currentDirectory = trim($currentDirectory, '.');
        $parentDirectory = '';
        if ($currentDirectory != '') {
            $parentDirectory = dirname($currentDirectory);
        }
        $parentDirectory = trim($parentDirectory, '/');
        $parentDirectory = trim($parentDirectory, '.');
        $directories = Storage::disk($this->disk)->directories($currentDirectory);
        $files = Storage::disk($this->disk)->files($currentDirectory);
        $directoryList = array();
        foreach ($directories as $dir) {
            $directoryList[] = [
                'dir_path' => $dir,
                'dir_name' => pathinfo($dir, PATHINFO_BASENAME),
                'dir_size' => Storage::disk($this->disk)->size($dir),
                'dir_last_modified' => Storage::disk($this->disk)->lastModified($dir),
            ];
        }
        $fileList = array();
        foreach ($files as $file) {
            $fileList[] = [
                'file_path' => $file,
                'file_url' => $this->filesRootUrl . '' . $file,
                'file_name' => pathinfo($file, PATHINFO_BASENAME),
                'file_size' => Storage::disk($this->disk)->size($file),
                'file_last_modified' => Storage::disk($this->disk)->lastModified($file),
            ];
        }
        
        return view('media-manager::home', get_defined_vars());
    }

    public function postDirectoryCreate()
    {
        $rules = array(
            //'current_dir' => 'required',
            'create_dir' => 'required',
        );
        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }
        $currentDirectory = request('current_dir', '');
        $createDirectory = trim(request('create_dir', ''));
        /* Skip Root Directory */
        if ($currentDirectory != '') {
            $currentDirectoryExists = Storage::disk($this->disk)->exists($currentDirectory);
            if ($currentDirectoryExists == false) {
                return redirect()->back()->withErrors('Current directory DOES NOT exist.')->withInput();
            }
        }
        $createDirectoryPath = $currentDirectory . '/' . $createDirectory;
        $createDirectoryExists = Storage::disk($this->disk)->exists($createDirectoryPath);
        if ($createDirectoryExists == true) {
            return redirect()->back()->withErrors('Directory "' . $createDirectory . '" ALREADY exists.')->withInput();
        }
        $result = Storage::disk($this->disk)->makeDirectory($createDirectoryPath);
        if ($result == true) {
            return redirect(route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory))->with('flash_success', 'Directory "' . $createDirectory . '" successfully created');
        }
        return redirect()->back()->withErrors('Creating directory "' . $createDirectory . '" failed.')->withInput();
    }

    public function postDirectoryDelete()
    {
        $rules = array(
            //'current_dir' => 'required',
            'delete_dir' => 'required',
        );
        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }
        $currentDirectory = request('current_dir', '');
        $deleteDirectory = request('delete_dir', '');
        $currentDirectoryExists = Storage::disk($this->disk)->exists($currentDirectory);
        if ($currentDirectoryExists == false) {
            return redirect()->back()->withErrors('Current directory DOES NOT exist.')->withInput();
        }
        $deleteDirectoryPath = $currentDirectory . '/' . $deleteDirectory;
        $deleteDirectoryExists = Storage::disk($this->disk)->exists($deleteDirectoryPath);
        if ($deleteDirectoryExists == false) {
            return redirect()->back()->withErrors('Directory "' . $deleteDirectory . '" DOES NOT exist.')->withInput();
        }
        $result = Storage::disk($this->disk)->deleteDirectory($deleteDirectoryPath);
        if ($result == true) {
            return redirect(route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory))->with('flash_success', 'Directory "' . $deleteDirectory . '" successfully deleted');
        }
        return redirect()->back()->withErrors('Deleting directory "' . $deleteDirectory . '" failed.')->withInput();
    }

    public function postFileDelete()
    {
        $rules = array(
            //'current_dir' => 'required',
            'delete_file' => 'required',
        );
        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        $currentDirectory = request('current_dir', '');
        $deleteFile = request('delete_file', '');
        $currentDirectoryExists = Storage::disk($this->disk)->exists($currentDirectory);
        if ($currentDirectoryExists == false) {
            return redirect()->back()->withErrors('Current directory DOES NOT exist.')->withInput();
        }
        $deleteFilePath = $currentDirectory . '/' . $deleteFile;
        $deleteFileExists = Storage::disk($this->disk)->exists($deleteFilePath);
        if ($deleteFileExists == false) {
            return redirect()->back()->withErrors('File "' . $deleteFile . '" DOES NOT exist.')->withInput();
        }
        $result = Storage::disk($this->disk)->delete($deleteFilePath);
        if ($result == true) {
            return redirect(route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory))->with('flash_success', 'File "' . $deleteFile . '" successfully deleted');
        }
        return redirect()->back()->withErrors('Deleting file "' . $deleteFile . '" failed.')->withInput();
    }

    public function postFileUpload()
    {
        $rules = [
            //'current_dir' => 'required',
            'upload_file' => 'required',
        ];
        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }
        if (\Request::file('upload_file')->isValid() == false) {
            return redirect()->back()->withErrors('Upload file is invalid.')->withInput(\Request::all());
        }
        $currentDirectory = request('current_dir', '');
        $currentDirectoryExists = Storage::disk($this->disk)->exists($currentDirectory);
        if ($currentDirectoryExists == false) {
            die('Current directory DOES NOT exist');
        }
        $allowedExtensions = array(
            'gif', 'jpg', 'jpeg', 'png', 'svg',
            'txt',
            'doc', 'docx', 'xls', 'xlsx',
            'csv',
        );
        $extension = \Request::file('upload_file')->getClientOriginalExtension();
        $fileName = \Request::file('upload_file')->getClientOriginalName();
        $destinationPath = $currentDirectory;
        if (in_array($extension, $allowedExtensions) == false) {
            return redirect()->back()->withErrors('Only ' . implode(',', $allowedExtensions) . ' files are allowed.')->withInput(\Request::all());
        }
        try {
            $file = \Request::file('upload_file');
            Storage::disk($this->disk)->put($destinationPath . '/' . $fileName, \File::get($file));
            //\Request::file('upload_file')->move($destinationPath, $fileName);
            $redirectUrl = route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory);
            return redirect($redirectUrl)->with('flash_success', 'File "' . $fileName . '" successfully uploaded');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('File upload failed.')->withInput(\Request::all());
        }
    }
}
