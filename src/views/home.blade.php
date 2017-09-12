@extends('media-manager::layout')

@section('title', 'Media Manager')

@section('content')

<?php if (session('flash_success')) { ?>
    <div class="alert alert-success">
        {{ session('flash_success') }}
    </div>
<?php } ?>

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<h3>
    Media Manager
    <button class="btn btn-default pull-right" data-toggle="modal" data-target="#ModalUploadFile">
        <span class="fa fa-upload"></span>
        Upload file
    </button>
    <button class="btn btn-info pull-right" data-toggle="modal" data-target="#ModalDirectoryCreate">
        <span class="fa fa-plus-circle"></span>
        New directory
    </button>
</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th style="width:1px;"></th>
            <th style="">Directory/File Name</th>
            <th style="width:100px;">Size</th>
            <th style="width:100px;">Modified</th>
            <th style="width:220px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($currentDirectory != '') { ?>
            <tr>
                <td>
                    <span class="fa fa-folder"></span>
                </td>
                <td>
                    <a href="{!! route('getMediaManager') !!}?current_dir=<?php echo urlencode($parentDirectory); ?>">
                        parent
                        <span class="fa fa-level-up"></span>
                    </a>
                </td>
                <td></td>
                <td></td>
                <td>

                </td>
            </tr>
        <?php } ?>
        <?php foreach ($directoryList as $dir) { ?>
            <tr>
                <td>
                    <span class="fa fa-folder"></span>
                </td>
                <td>
                    <a href="{!! route('getMediaManager') !!}?current_dir=<?php echo urlencode($dir['dir_path']); ?>">
                        <?php echo $dir['dir_name']; ?>
                    </a>
                </td>
                <td><?php echo $dir['dir_size']; ?></td>
                <td style="font-size: 11px;">
                    <?php echo date('Y-m-d H:i:s', $dir['dir_last_modified']); ?>
                </td>
                <td>
                    <button class="btn btn-danger" onclick="modalDirectoryDeleteShow('<?php echo $dir['dir_name']; ?>')">
                        <span class="fa fa-remove"></span>
                        Delete
                    </button>
                </td>
            </tr>
        <?php } ?>
        <?php foreach ($fileList as $file) { ?>
            <tr>
                <td>
                    <span class="fa fa-file"></span>
                </td>
                <td>
                    <?php echo $file['file_name']; ?>
                    <div>
                        URL: <a href="<?php echo $file['file_url'] ?>" target="_blank"><?php echo $file['file_url'] ?></a>
                    </div>
                </td>
                <td><?php echo $file['file_size']; ?></td>
                <td style="font-size: 11px;">
                    <?php echo date('Y-m-d H:i:s', $file['file_last_modified']); ?>
                </td>
                <td>      
                    <a href="<?php echo $file['file_url'] ?>" target="_blank" class="btn btn-success btn-xs">
                        <span class="glyphicon glyphicon-eye-open"></span>
                        View
                    </a> 
                    <!--
                    <button type="button" onclick="return showFileUrl('https://sinevia-file.s3.amazonaws.com/pages/website_2015011108310101/20150927043503-photo.jpg');" class="btn btn-primary btn-xs">
                        <span class="glyphicon glyphicon-link"></span>
                        Link
                    </button>                    
                    -->
                    <button class="btn btn-warning btn-xs" onclick="modalFileRenameShow('<?php echo $file['file_name']; ?>')">
                        <span class="glyphicon glyphicon-pencil"></span>
                        Rename
                    </button>
                    <button class="btn btn-danger btn-xs" onclick="modalFileDeleteShow('<?php echo $file['file_name']; ?>')">
                        <span class="glyphicon glyphicon-remove-sign"></span>
                        Delete
                    </button>
                    <button onclick="fileSelectedUrl('<?php echo $file['file_url'] ?>')" class="btn btn-primary btn-xs btn-select" type="button">
                        Select
                        <span class="glyphicon glyphicon-chevron-right"></span>
                    </button>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php //echo $files->render(); ?>

<!-- START: Modal Upload File -->
<div class="modal fade" id="ModalUploadFile" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">File Upload</h4>
            </div>
            <div class="modal-body">
                <form id="FormFileUpload" name="FormFileUpload" action="{!! route('postFileUpload') !!}" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="current_dir" value="<?php echo $currentDirectory; ?>" />
                    <input type="file" name="upload_file" value="" />
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="FormFileUpload.submit();">Start Upload</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Modal Upload File -->

<!-- START: Modal Directory Create -->
<div class="modal fade" id="ModalDirectoryCreate" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">New Directory</h4>
            </div>
            <div class="modal-body">
                <form id="FormDirectoryCreate" name="FormDirectoryCreate" action="{!! route('postDirectoryCreate') !!}" method="POST">
                    <div class="form-group">
                        <label>Directory name</label>
                        <input type="text" class="form-control" name="create_dir" value="" />
                    </div>
                    <input type="hidden" name="current_dir" value="<?php echo $currentDirectory; ?>" />
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="FormDirectoryCreate.submit();">Create Directory</button>
            </div>
        </div>
    </div>
</div>
<script>
    function modalDirectoryDeleteShow(directoryName) {
        $('#FormDirectoryDelete input[name="delete_dir"]').val(directoryName);
        $('#ModalDirectoryDelete').modal({show: true});
    }
</script>
<!-- END: Modal Directory Delete -->

<!-- START: Modal Directory Delete -->
<div class="modal fade" id="ModalDirectoryDelete" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Confirm Directory Delete</h4>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to delete this folder
                    and all the files in it?
                </p>
                <p class="text-danger">
                    This operation is final and CANNOT BE undone!
                </p>
                <form id="FormDirectoryDelete" name="FormDirectoryDelete" action="{!! route('postDirectoryDelete') !!}" method="POST">
                    <input type="hidden" name="current_dir" value="<?php echo $currentDirectory; ?>" />
                    <input type="hidden" name="delete_dir" value="" />
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="FormDirectoryDelete.submit();">Delete Directory</button>
            </div>
        </div>
    </div>
</div>
<script>
    function modalDirectoryDeleteShow(directoryName) {
        $('#FormDirectoryDelete input[name="delete_dir"]').val(directoryName);
        $('#ModalDirectoryDelete').modal({show: true});
    }
</script>
<!-- END: Modal Directory Delete -->

<!-- START: Modal File Delete -->
<div class="modal fade" id="ModalFileDelete" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Confirm File Delete</h4>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to delete this file?
                </p>
                <p class="text-danger">
                    This operation is final and CANNOT BE undone!
                </p>
                <form id="FormFileDelete" name="FormFileDelete" action="{!! route('postFileDelete') !!}" method="POST">
                    <input type="hidden" name="current_dir" value="<?php echo $currentDirectory; ?>" />
                    <input type="hidden" name="delete_file" value="" />
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="FormFileDelete.submit();">Delete File</button>
            </div>
        </div>
    </div>
</div>
<script>
    function modalFileDeleteShow(fileName) {
        $('#FormFileDelete input[name="delete_file"]').val(fileName);
        $('#ModalFileDelete').modal({show: true});
    }
</script>
<!-- END: Modal File Delete -->

<!-- START: Modal File Rename -->
<div class="modal fade" id="ModalFileRename" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">File Rename</h4>
            </div>
            <div class="modal-body">
                <form id="FormFileRename" name="FormFileRename" action="{!! route('postFileRename') !!}" method="POST">
                    <div class="form-group">
                        <label>New Name</label>
                        <input name="new_file" value="" class="form-control" />
                    </div>
                    <input type="hidden" name="current_dir" value="<?php echo $currentDirectory; ?>" />
                    <input type="hidden" name="rename_file" value="" />
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="FormFileRename.submit();">Rename File</button>
            </div>
        </div>
    </div>
</div>
<script>
    function modalFileRenameShow(fileName) {
        $('#FormFileRename input[name="new_file"]').val(fileName);
        $('#FormFileRename input[name="rename_file"]').val(fileName);
        $('#ModalFileRename').modal({show: true});
    }
</script>
<!-- END: Modal File Rename -->

<script>
    $('.btn-select').hide();
    
    var openerArgs = {};
    function fileSelectedUrl(selectedFileUrl) {
        if (window.opener === null) {
            return true;
        }
        window.opener.postMessage({msg: 'media-manager-file-selected', url: selectedFileUrl, args: openerArgs}, '*');
        window.close();
    }
    function messageReceived(event) {
        var data = event.data;
        openerArgs = data;
        console.log(data);
        $('.btn-select').show();
    }
    window.addEventListener("message", messageReceived, false);
    if (window.opener !== null) {
        window.opener.postMessage({msg: 'media-manager-loaded'}, '*');
    }
</script>


@endsection
