<?php include(__DIR__ . '/../basic/header.html'); ?>
<link rel="stylesheet" href="/dropzone/dist/dropzone.css">
<link type="text/css" rel="stylesheet" href="https://bobkovalex.github.io/Basic-Canvas-Paint/resources/css/bcPaint.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<div class="container-fluid">
    <div class="card shadow mb-2">
        <div class="card-header">客訴討論</div>
        <div class="card-body">
            <div class="row">
                <div class="form-group row col-auto h-100">
                    <div class="overflow-auto h-100">
                        <form action="/crm/file" class="dropzone h-100" id="uploadDropzone" method='post' style="min-width:200px"></form>
                    </div>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-secondary active w-100 ml-1">案1</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-secondary w-100 ml-1">案2</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-secondary w-100 ml-1">案3</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-secondary w-100 ml-1">案4</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-secondary w-100 ml-1">案5</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-secondary w-100 ml-1">案6</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow mb-2">
        <div class="card-header">附加檔案</div>
        <div class="card-body">
            <div class="row">
                <div class="form-group row col-auto h-100">
                    <div class="overflow-auto h-100">
                        <form action="/crm/file" class="dropzone h-100" id="uploadFileDropzone" method='post' style="min-width:200px"></form>
                    </div>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-outline-secondary w-100 ml-1">檔案1</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-outline-secondary w-100 ml-1">檔案2</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-outline-secondary w-100 ml-1">檔案3</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-outline-secondary w-100 ml-1">檔案4</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-outline-secondary w-100 ml-1">檔案5</button>
                </div>
                <div class="form-group row col">
                    <button class="btn btn-outline-secondary w-100 ml-1">檔案6</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow mb-2">
        <div class="card-header">圖片標記</div>
        <div class="card-body">
            <div id="bcPaint"></div>
        </div>
    </div>
    <div class="card shadow mb-2">
        <div class="card-header">會議紀錄</div>
        <div class="card-body">
            <div id="summernote"></div>
        </div>
    </div>
</div>
<?php include(__DIR__ . '/../basic/footer.html'); ?>
<script src="/dropzone/dist/dropzone.js"></script>
<script type="text/javascript" src="https://bobkovalex.github.io/Basic-Canvas-Paint/resources/js/bcPaint.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script type="text/javascript">
    $('#bcPaint').bcPaint();
    $('#summernote').summernote({
      height: 300
    });
</script>
<script>

Dropzone.options.uploadDropzone = {
    dictDefaultMessage: '拖曳Email至此 或 點此選擇檔案',
    addRemoveLinks: true,
    maxFiles: 1,
    acceptedFiles: '.pdf,.jpg',
    timeout: 300000,
    /*milliseconds*/
    init: function() {
        this.on("success", function(file, response) {
            
          }),
          this.on("removedfile", function(file) {
            
        });
    },
    success: function(file, response) {
        
    }
};
Dropzone.options.uploadFileDropzone = {
    dictDefaultMessage: '拖曳檔案至此 或 點此選擇檔案',
    addRemoveLinks: true,
    maxFiles: 1,
    acceptedFiles: '.pdf,.jpg',
    timeout: 300000,
    /*milliseconds*/
    init: function() {
        this.on("success", function(file, response) {
            
          }),
          this.on("removedfile", function(file) {
            
        });
    },
    success: function(file, response) {
        
    }
};
</script>