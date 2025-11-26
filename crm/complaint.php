<?php include(__DIR__ . '/../basic/header.html'); ?>
<link rel="stylesheet" href="/dropzone/dist/dropzone.css">
<link type="text/css" rel="stylesheet" href="https://bobkovalex.github.io/Basic-Canvas-Paint/resources/css/bcPaint.css">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<div class="container-fluid">
    <div class="card shadow mb-2">
        <div class="card-header">會議系統</div>
        <div class="card-body">
            <div class="row">
                <div class="form-group form-inline col-10 h-100">
                    <label>會議類型:</label>
                    <select class="custom-select mx-3 my-3">
                        <option selected disabled value="">產銷會議</option>
                    </select>
                    <label>會議時間:</label>
                    <input id="dateStart" type="date" class="mx-2 form-control">
                </div>
                <div class="form-group form-inline col-2 mb-2">
                    <button class="btn btn-secondary active w-100 ml-1">新增客訴內容</button>
                </div>

                <div class="form-group mb-2 mx-2">
                    <button class="btn btn-secondary active w-100 ml-1">案1</button>
                </div>
                <div class="form-group mb-2 mx-2">
                    <button class="btn btn-secondary w-100 ml-1">+</button>

                </div>
                <div class="form-group  form-inline col-12 h-100">
                    <div class="col-2 h-100">
                        <form action="/crm/file" class="dropzone h-100" id="uploadDropzone" method='post' style="min-width:200px"></form>
                    </div>
                    <textarea class="form-control mx-2" id="exampleFormControlTextarea1" rows="5">MAIL內容原文</textarea>
                    <textarea class="form-control mx-2" id="exampleFormControlTextarea1" rows="5">MAIL內容英文翻譯</textarea>
                    <textarea class="form-control mx-2" id="exampleFormControlTextarea1" rows="5">MAIL內容中文翻譯</textarea>

                </div>
                <div class="form-group  form-inline col-12 h-100">
                    <div class="col-2">
                        <img src="/img/Logo.png" class="img-fluid img-thumbnail rounded float-left" alt="...">
                    </div>
                    <textarea class="form-control mx-2" id="exampleFormControlTextarea1" rows="5">客戶圖文字內容</textarea>
                    <div class="col-2">
                        <img src="/img/Logo.png" class="img-fluid img-thumbnail rounded float-left" alt="...">
                    </div>
                    <textarea class="form-control mx-2" id="exampleFormControlTextarea1" rows="5">廠內圖文字內容</textarea>
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
    <div class="card shadow mb-2">
        <div class="card-header">待追蹤事項</div>
        <div class="card-body">
            <div class="form-group form-inline">
                <li><a class="btn btn-link">事項1</a></li>
                <label>品管:XXX:</label>
            </div>
            <div class="form-group form-inline">
                <li><a class="btn btn-link">事項2</a></li>
                <label>技術:XXX:</label>
            </div>
        </div>
    </div>
    <div class="card shadow mb-2">
        <div class="card-header">各部門內部會議</div>
        <div class="card-body">
            <div class="row">
                <div class="form-group form-inline col-12 h-100">
                    <label>部門:</label>
                    <select class="custom-select mx-3 my-3">
                        <option selected disabled value="">資訊</option>
                    </select>
                    <label>發起人:</label>
                    <select class="custom-select mx-3 my-3">
                        <option selected disabled value="">XXX</option>
                    </select>
                    <label>參與者:</label>
                    <select class="custom-select mx-3 my-3">
                        <option selected disabled value="">XXX</option>
                    </select>
                    <label>會議時間:</label>
                    <input id="dateStart" type="date" class="mx-2 form-control">
                </div>
                <div class="card shadow mb-2">
                    <div class="card-header">會議紀錄</div>
                    <div class="card-body">
                        <div id="summernote2"></div>
                    </div>
                </div>
            </div>
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
    $('#summernote2').summernote({
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