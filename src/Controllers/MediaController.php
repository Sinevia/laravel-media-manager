<?php

namespace Sinevia\LaravelMediaManager\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Storage;
use Validator;
use File;

class MediaController extends Controller
{
    public $disk = null;
    public $filesRootDir = null;
    public $filesRootUrl = null;
    public $fileManagerRootDir = null;
    public $fileManagerRootUrl = null;

    public function __construct()
    {
        $this->disk = 'media_manager';
        $this->filesRootDir = public_path('media');
        $this->filesRootUrl = url('/') . '/media/';
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
        $currentDirectory = request('current_dir', '');
        $currentDirectory = trim($currentDirectory, '/');
        $currentDirectory = trim($currentDirectory, '.');
        $parentDirectory = '';
        if ($currentDirectory != '') {
            $parentDirectory = dirname($currentDirectory);
        }
        $parentDirectory = trim($parentDirectory, '/');
        $parentDirectory = trim($parentDirectory, '.');
        $directories = $this->getDirectories($this->disk, $currentDirectory);
        $files = $this->getFiles($this->disk, $currentDirectory);
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
            'create_dir' => 'required',
        );
        $validator = Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }
        $currentDirectory = request('current_dir', '');
        $createDirectory = trim(request('create_dir', ''));
        /* Skip Root Directory */
        if ($currentDirectory != '') {
            $currentDirectoryExists = $this->existsDir($this->disk, $currentDirectory);
            if ($currentDirectoryExists == false) {
                return redirect()->back()->withErrors('Current directory DOES NOT exist.')->withInput();
            }
        }
        $createDirectoryPath = $currentDirectory . '/' . $createDirectory;
        $createDirectoryExists = $this->existsDir($this->disk, $createDirectoryPath);
        if ($createDirectoryExists == true) {
            return redirect()->back()->withErrors('Directory "' . $createDirectory . '" ALREADY exists.')->withInput();
        }
        $result = $this->makeDir($this->disk, $createDirectoryPath);
        if ($result == true) {
            return redirect(route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory))->with('flash_success', 'Directory "' . $createDirectory . '" successfully created');
        }
        return redirect()->back()->withErrors('Creating directory "' . $createDirectory . '" failed.')->withInput();
    }

    public function postDirectoryDelete()
    {
        $rules = array(
            'delete_dir' => 'required',
        );
        $validator = Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }
        $currentDirectory = request('current_dir', '');
        $deleteDirectory = request('delete_dir', '');
        $currentDirectoryExists = $this->existsDir($this->disk, $currentDirectory);
        if ($currentDirectoryExists == false) {
            return redirect()->back()->withErrors('Current directory DOES NOT exist.')->withInput();
        }
        $deleteDirectoryPath = $currentDirectory . '/' . $deleteDirectory;
        $deleteDirectoryExists = $this->existsDir($this->disk, $deleteDirectoryPath);
        if ($deleteDirectoryExists == false) {
            return redirect()->back()->withErrors('Directory "' . $deleteDirectory . '" DOES NOT exist.')->withInput();
        }
        $result = $this->removeDir($this->disk, $deleteDirectoryPath);
        if ($result == true) {
            return redirect(route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory))->with('flash_success', 'Directory "' . $deleteDirectory . '" successfully deleted');
        }
        return redirect()->back()->withErrors('Deleting directory "' . $deleteDirectory . '" failed.')->withInput();
    }

    public function postFileDelete()
    {
        $rules = array(
            'delete_file' => 'required',
        );
        $validator = Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }
        $currentDirectory = request('current_dir', '');
        $deleteFile = request('delete_file', '');        
        $currentDirectoryExists = $this->existsDir($this->disk, $currentDirectory);
        if ($currentDirectoryExists == false) {
            return redirect()->back()->withErrors('Current directory DOES NOT exist.')->withInput();
        }
        $deleteFilePath = $currentDirectory . '/' . $deleteFile;
        $deleteFileExists = $this->existsDir($this->disk, $deleteFilePath);
        if ($deleteFileExists == false) {
            return redirect()->back()->withErrors('File "' . $deleteFile . '" DOES NOT exist.')->withInput();
        }
        $result = $this->removeFile($this->disk, $deleteFilePath);
        if ($result == true) {
            return redirect(route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory))->with('flash_success', 'File "' . $deleteFile . '" successfully deleted');
        }
        return redirect()->back()->withErrors('Deleting file "' . $deleteFile . '" failed.')->withInput();
    }

    public function postFileUpload()
    {
        $rules = [
            'upload_file' => 'required',
        ];
        $validator = Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }
        if (\Request::file('upload_file')->isValid() == false) {
            return redirect()->back()->withErrors('Upload file is invalid.')->withInput(\Request::all());
        }
        $currentDirectory = request('current_dir', '');
        $currentDirectoryExists = $this->existsDir($this->disk, $currentDirectory);
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
            $dPath = $destinationPath . '/' . $fileName;
            $this->uploadFile($this->disk, $dPath, File::get($file));
            $redirectUrl = route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory);
            return redirect($redirectUrl)->with('flash_success', 'File "' . $fileName . '" successfully uploaded');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('File upload failed.')->withInput(\Request::all());
        }
    }
    
    public function postFileRename()
    {
        $rules = [
            //'current_dir' => 'required',
            'rename_file' => 'required',
            'new_file' => 'required',
        ];
        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }
        $currentDirectory = request('current_dir', '');
        $renameFile = request('rename_file', '');
        $newFile = request('new_file', '');
        
        $currentDirectoryExists = $this->existsDir($this->disk, $currentDirectory);
        if ($currentDirectoryExists == false) {
            die('Current directory DOES NOT exist');
        }
        
        $renameFilePath = $currentDirectory . '/' . $renameFile;
        $renameFileExists = $this->existsDir($this->disk, $renameFilePath);
        if ($renameFileExists == false) {
            return redirect()->back()->withErrors('File "' . $renameFile . '" DOES NOT exist.')->withInput();
        }
        $newFilePath = $currentDirectory . '/' . $newFile;
        $newFileExists = $this->existsDir($this->disk, $newFilePath);
        if ($newFileExists == true) {
            return redirect()->back()->withErrors('File "' . $newFile . '" ALREADY exists.')->withInput();
        }
        $result = $this->renameFile($this->disk,$renameFilePath, $newFilePath);
        if ($result == true) {
            return redirect(route('getMediaManager') . '?current_dir=' . urlencode($currentDirectory))->with('flash_success', 'File "' . $renameFile . '" successfully renamed');
        }
        return redirect()->back()->withErrors('Renaming file "' . $renameFile . '" failed.')->withInput();
    }

    public function makeDir($disk, $dirName)
    {
        return Storage::disk($disk)->makeDirectory($dirName);
    }

    public function removeDir($disk, $dirName)
    {
        return Storage::disk($disk)->deleteDirectory($dirName);
    }

    public function existsDir($disk, $dirName)
    {
        return Storage::disk($disk)->exists($dirName);
    }

    public function removeFile($disk, $fileName)
    {
        return Storage::disk($disk)->delete($fileName);
    }

    public function uploadFile($disk, $destinationPath, $file)
    {
        return Storage::disk($disk)->put($destinationPath, $file);
    }

    public function renameFile($disk, $oldFile, $newFile)
    {
        return Storage::disk($disk)->move($oldFile, $newFile);
    }

    public function getDirectories($disk, $path)
    {
        return Storage::disk($disk)->directories($path);
    }

    public function getFiles($disk, $path)
    {
        return Storage::disk($disk)->files($path);
    }
}
