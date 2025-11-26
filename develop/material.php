<?php include(__DIR__ . '/../basic/header.html'); ?>
<!-- <script>
$(document).tooltip();
    $('[data-toggle="tooltip"]').tooltip()

</script> -->
  <!-- <style>
  .tooltipsTest {
    display: inline-block;
    width: 5em;
  }
  </style> -->
<style>
  .modal {
    overflow-y: auto;
  }

  .select-wrapper {
    margin: auto;
    max-width: 600px;
    width: calc(100% - 40px);
  }

  .select-pure__select {
    align-items: center;
    background: #f9f9f8;
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.15);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    box-sizing: border-box;
    color: #363b3e;
    cursor: pointer;
    display: flex;
    font-size: 16px;
    font-weight: 500;
    justify-content: left;
    min-height: 44px;
    padding: 5px 10px;
    position: relative;
    transition: 0.2s;
    width: 100%;
  }

  .select-pure__options {
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.15);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    box-sizing: border-box;
    color: #363b3e;
    display: none;
    left: 0;
    max-height: 221px;
    overflow-y: scroll;
    position: absolute;
    top: 50px;
    width: 100%;
    /* position:fixed; */
    z-index: 99999;
  }

  .select-pure__select--opened .select-pure__options {
    display: block;
  }

  .select-pure__option {
    background: #fff;
    border-bottom: 1px solid #e4e4e4;
    box-sizing: border-box;
    height: 44px;
    line-height: 25px;
    padding: 10px;

  }

  .select-pure__option--selected {
    color: #e4e4e4;
    cursor: initial;
    pointer-events: none;
  }

  .select-pure__option--hidden {
    display: none;
  }

  .select-pure__selected-label {
    background: #5e6264;
    border-radius: 4px;
    color: #fff;
    cursor: initial;
    display: inline-block;
    margin: 5px 10px 5px 0;
    padding: 3px 7px;
  }

  .select-pure__selected-label:last-of-type {
    margin-right: 0;
  }

  .select-pure__selected-label i {
    cursor: pointer;
    display: inline-block;
    margin-left: 7px;
  }

  .select-pure__selected-label i:hover {
    color: #e4e4e4;
  }

  .select-pure__autocomplete {
    background: #f9f9f8;
    border-bottom: 1px solid #e4e4e4;
    border-left: none;
    border-right: none;
    border-top: none;
    box-sizing: border-box;
    font-size: 16px;
    outline: none;
    padding: 10px;
    width: 100%;
  }
</style>
<link rel="stylesheet" href="/css/compare-norecog.css">

<script src="/dropzone/dist/dropzone.js"></script>

<link rel="stylesheet" href="/dropzone/dist/dropzone.css">
<link rel="stylesheet" href="/vendor/select-pure/dist/select-pure.css">
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<link type="text/css" rel="stylesheet" href="/css/bcPaint.css">

<div class="row">
  <!-- search -->
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-body d-flex overflow-auto">
        <div class="d-flex align-self-center" id="list-tab-business">
          <ul class="list-group list-group-horizontal w-100">
            <li class="list-group-item flex-fill w-100">上傳圖檔</li>
            <li class="list-group-item flex-fill w-100">查詢歷史訂單</li>
            <li class="list-group-item flex-fill w-100">全圖比對</li>
            <li class="list-group-item flex-fill w-100">零件分類</li>
            <li class="list-group-item flex-fill w-100">零件比對</li>
            <li class="list-group-item flex-fill w-100">刻度圈選</li>
            <li class="list-group-item flex-fill w-100">刻度修改</li>
          </ul>
        </div>
        <div class="form-group" id="list-tab-other">
        </div>
        <div class="d-flex align-self-center" id="list-tab-end">
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- search -->
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header">
        材質確認
        <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="確認材質、鍍鈦、硬度"></i>

        <!-- <i id = "tooltip-1" class="fas fa-exclamation-circle" title="確認材質、鍍鈦、硬度"></i> -->
      </div>
      <div class="card-body">
        <!-- <div class="row">
          <div class="col-12 col-md-12 col-lg-12 col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-body" id="divtextBoxCard">
                </div>
            </div>
          </div>
        </div> -->
        <div class="row">
          <div class="col-8 col-md-8 col-lg-8 col-xl-8">
            <div class="card shadow mb-4">
              <div class="card-header">
                客戶圖面
                </div>
                <div class="card-body">
                <div class="col-12"><button type="button" class="btn btn-primary" data-type="false" id="btnshowTextBoxAll" onclick="showTextBoxAll(this)">所有標記</button></div>
                <div class="row">
                  <div id="divpaint" class="col-12"></div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-12 col-lg-12 col-xl-12">
              <div class="card shadow mb-4">
                <form name="formtextBoxCard">
                  <div class="card-body" id="divtextBoxCard" name="divlock">
                  </div>

                  <button class="btn btn-primary" type="submit" id="btnformtextBoxCard" hidden></button>
                </form>
              </div>

            </div>
          </div>
          <div class="col-4 col-md-4 col-lg-4 col-xl-4">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
              <label class="btn btn-outline-danger">
                <input type="radio" name="lockoption" data-type="lock" checked><i class="fa fa-lock" aria-hidden="true"></i>
              </label>
              <label class="btn btn-outline-danger">
                <input type="radio" name="lockoption" data-type="unlock"><i class="fa fa-unlock" aria-hidden="true"></i>
              </label>
            </div>
            <div class="card shadow mb-4" name="divlock">
              <div class="card-header">
                材質辨識
              </div>
              <div class="card-body">
                <div class="form-group row">
                  <div class="form-group row col-12">
                    <label class="col-form-label col-sm-auto">材質：</label>
                    <div class="col" id="noresultMaterial"></div>
                  </div>
                  
                  <div class="form-group row col-12">
                    <label class="col-form-label col-sm-auto">硬度：</label>
                    <div class="col" id="noresultHardness"></div>
                  </div>
                  <div class="form-group row col-12">
                    <label class="col-form-label col-sm-auto">鍍鈦：</label>
                    <div class="col" id="noresultTitanizing"></div>
                  </div>
                  <div class="form-group row col-12">
                    <label class="col-form-label col-sm-auto">註記：</label>
                    <div class="col" id="">
                      <textarea class="form-control" id="inputFileCommentMain" rows="3"></textarea>
                    </div>
                  </div>
                  <div class="form-group row col-12 ">
                    <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#exampleModal" data-type="nextstep">下一步</button>
                  </div>
                </div>

              </div>
            </div>
            <div class="card shadow mb-4" name="divlock">
              <div class="card-body">
                <div class="form-group row col-12 ">
                  <a type="button" href="#selectThreshold" class="btn btn-primary float-right">前往相似度比對區</a>
                </div>
                <div class="form-group row col-12 ">
                  <button type="button" class="btn btn-info float-right">編輯</button>
                </div>
                <div id="divpalette">
                </div>
                <div class="col-12 bg-light rounded pt-4 text-center" id="bcPaint-palette">
                  <h6 class="bg-dark rounded p-3 mb-4 text-white font-weight-normal">工具</h6>
                </div>

                <div class="row">
                  <div class="col-12 form-group" id="divFunction"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="row" id="discriptOther">
  <!-- search -->
  <!-- <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header">
        各部門結果摘要
      </div>
      <div class="card-body">
        <button class="btn btn-primary">設定通知</button>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 card-deck" id="divStation">
          <div class="card shadow mb-4 form-group">
            <div class="card-header">
              製圖站
            </div>
            <div class="card-body">
              <div class="row">
                <label class="col-form-label col-1">相似度結果</label>
                <div class="col table-responsive">
                  <table class="table table-borderlress">
                    <tbody>
                      <tr>
                        <td width=50%><img src="/fileCrop/6174" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
                        <td width=50%>前沖棒</td>
                        <td class="text-nowrap">這是製圖站來的註記</td>
                      </tr>
                      <tr>
                        <td width=50%><img src="/fileCrop/6174" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
                        <td width=50%>後沖棒</td>
                        <td class="text-nowrap">這是製圖站來的註記</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="card shadow mb-4 form-group">
            <div class="card-header">
              技術站
            </div>
            <div class="card-body">
              <div class="row">
                <label class="col-form-label col-1">相似度結果</label>
                <div class="col table-responsive">
                  <table class="table table-borderlress">
                    <tbody>
                      <tr>
                        <td width=50%><img src="/fileCrop/6174" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
                        <td width=50%>前沖棒</td>
                        <td class="text-nowrap">這是技術站來的註記</td>
                      </tr>
                      <tr>
                        <td width=50%><img src="/fileCrop/6174" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
                        <td width=50%>後沖棒</td>
                        <td class="text-nowrap">這是技術站來的註記</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="row">
                <label class="col-form-label col-auto">追加製程成本：</label>
                <label class="col-form-label col-auto">5000</label>
              </div>
            </div>
          </div>
          <div class="card shadow mb-4 form-group">
            <div class="card-header">
              生管站
            </div>
            <div class="card-body">
              <div class="row">
                <label class="col-form-label col-1">相似度結果</label>
                <div class="col table-responsive">
                  <table class="table table-borderlress">
                    <tbody>
                      <tr>
                        <td width=50%><img src="/fileCrop/1510" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
                        <td width=50%>前沖棒</td>
                        <td class="text-nowrap">這是生管站來的註記</td>
                      </tr>
                      <tr>
                        <td width=50%><img src="/fileCrop/1510" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
                        <td width=50%>後沖棒</td>
                        <td class="text-nowrap">這是生管站來的註記</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="row">
                <div class="col-auto row">
                  <label class="col-form-label col-auto">追加外包成本：</label>
                  <label class="col-form-label col-auto">10000</label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> -->
</div>
<div class="row">
  <div class="col-12 mb-4">
    <div class="card shadow mb-4 h-100">
      <div class="card-header">材質確認
        <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="選擇要呈現的零件圖數量，可設定要呈現出的數量"></i>
      </div>
      <div class="card-body">
        <div class="row rows-col-1 rows-col-md-2">
          <div class="col">
            <div class="form-group row">
              <div class="col-sm-auto form-group row">
                <label class="col-form-label col-auto">相似度門檻：</label>
                <div class="col-auto">
                  <select class="form-control" id="selectThreshold">
                    <option value="0">0%</option>
                    <option value="40">40%</option>
                    <option value="50">50%</option>
                    <option value="60">60%</option>
                    <option value="70">70%</option>
                    <option value="80">80%</option>
                    <option value="90">90%</option>
                    <option value="100">100%</option>
                  </select>
                </div>
              </div>
              <div class="col-sm-auto form-group row">
                <label class="col-form-label col-auto">參考數量：</label>
                <div class="col-auto">
                  <select class="form-control" id="selectAmount">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="30">30</option>
                    <option value="40">40</option>
                    <option value="50">50</option>
                  </select>
                </div>
                <label class="col-form-label col-auto">張</label>
              </div>
            </div>
          </div>
          <div class="col">
            <ul>
              <li>註記的部分會在技術階段時，看到所留下的註記</li>
              <li>勾選的部分會在技術階段時，看到所留下的相似零件</li>
              <li>按下一步後可送至製圖</li>
            </ul>
            <!-- <button type="button" class="btn btn-primary float-right" onclick="buttonPass()">下一步</button> -->
            <!-- <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#exampleModal" data-type="nextstep">下一步</button> -->
          </div>
        </div>
        <div class="d-flex overflow-auto" id="divStationMaterial">
        </div>
        <div class="form-group row" id="divImage">
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<?php include(__DIR__ . '/../basic/footer.html'); ?>

<script src="/vendor/select-pure/dist/select-pure.bundle.min.js"></script>
<script src="/js/enlarge-element.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script type="text/javascript" src="/js/bcPaint.js"></script>
<script>
$(function() {
    $('[data-toggle="tooltip"]').tooltip()
    
  });
</script>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script> -->



<script>
  
  var setting = {
    "lengthChange": true,
    "destroy": true,
    "info": true,
    "searching": false,
    "order": [],
    "language": {
      "processing": "處理中...",
      "loadingRecords": "載入中...",
      "lengthMenu": "顯示 _MENU_ 項結果",
      "zeroRecords": "沒有符合的結果",
      "info": "顯示第 _START_ 至 _END_ 項結果，共 _TOTAL_ 項",
      "infoEmpty": "顯示第 0 至 0 項結果，共 0 項",
      "infoFiltered": "(從 _MAX_ 項結果中過濾)",
      "infoPostFix": "",
      "search": "搜尋:",
      "paginate": {
        "first": "第一頁",
        "previous": "上一頁",
        "next": "下一頁",
        "last": "最後一頁"
      },
      "aria": {
        "sortAscending": ": 升冪排列",
        "sortDescending": ": 降冪排列"
      }
    }
  }
  let process = [];

  function sortObject(obj) {
    var arr = [];
    for (var prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        arr.push({
          'key': prop,
          'value': obj[prop]
        });
      }
    }
    arr.sort(function(a, b) {
      return a.value - b.value;
    });
    //arr.sort(function(a, b) { a.value.toLowerCase().localeCompare(b.value.toLowerCase()); }); //use this to sort as strings
    return arr; // returns array
  }

  var url = new URL(window.location.href);
  var id = url.searchParams.get("id");
  // var file_id_dest = url.searchParams.get("file_id_dest");
  var file_id_dest = url.searchParams.get("id");
  var module_id;
  var newmodule_id;
  var module_name = '研發';
  var quotationMaterial;
  var materialArr;
  var titanizingArr;
  var hardnessArr;
  var materialObj = new Object();
  var moduleArr;
  let instanceMaterial, instanceTitanizing, instanceYear;
  let year, hardness, material, titanizing
  let firsttime = true;
  let common_material;
  let common_titanizing;
  let common_hardness;
  let file_comment = '';
  let itemno;
  let redo = false






  $(function() {
    // $('[data-toggle="tooltip"]').tooltip()
    getModule()
    getFilter()
    getInfo();
    $('[name="divlock"]').hide();
    if (file_id_dest == null) {
      $('#basicModal').find('.modal-header').text(`系統訊息`);
      $('#basicModal').find('.modal-body').text(`請從全圖比對進入`);
      $('#basicModal').find('.modal-footer').html(`
            <button type="button" class="btn btn-secondary"onclick="javascript:location.href='/file/compare?id=${id}'">前往</button>
        `);
      $('#basicModal').modal('show')
    } else {
      getListState(id);
      // getResult(id);
      getComment();
      getResultComponents()
      $('#imgThumbnail').EnlargeElement($('#imgThumbnail').width() * 3, $('#imgThumbnail').height() * 3);
    }





  });

  function getInfo() {
    $.ajax({
      url: `/file/information`,
      type: 'get',
      data: {
        file_id: id,
      },
      dataType: 'json',
      success: function(response) {
        $.each(response, function() {
          var nowdt = new Date();
          var lockdt = new Date(this.lock);
          if (lockdt.getTime() < nowdt.getTime()) {
            console.log('inunlock')
            $('[name="lockoption"][data-type="unlock"]').click();
          }
          itemno = this.itemno

        })
        $( `<label>${itemno=="001"?"新圖":"舊圖"}</label>`).insertAfter( "#btnshowTextBoxAll"   );

      }
    });
  }
  let tmpcanvasorg = ''
  let tmpcanvasarr = []

  function showTextBox(i) {
    let tmpcanvas = document.getElementById("bcPaintCanvas");
    let ctx = tmpcanvas.getContext("2d");
    let image = new Image();
    let element = $(`[name="inputTextBox"][data-id="${i}"]`)
    let tmptext = $(`[name="buttonTextBox"][data-id="${i}"]`).find('[name="inputTextBox"]').val();
    let tmpX = $(element).data('x')
    let tmpY = $(element).data('y')
    let tmpwidth = $(element).data('width');
    let tmpheight = $(element).data('height');
    let ratio = tmpcanvas.width / tmpwidth
    console.log(tmptext)

    image.onload = function() {
      ctx.clearRect(0, 0, tmpcanvas.width, tmpcanvas.height);
      ctx.drawImage(image, 0, 0, tmpcanvas.width, tmpcanvas.height);
      ctx.font = "15px Arial";
      var textwidth = ctx.measureText(tmptext).width;
      var textheight = ctx.measureText(tmptext).height;
      ctx.fillStyle = '#f50';
      ctx.fillRect(tmpX * ratio, tmpY * ratio - parseInt("Arial", 15), textwidth, parseInt("Arial", 15));
      ctx.fillStyle = '#000';
      ctx.fillText(tmptext, tmpX * ratio, tmpY * ratio);
    };

    if (i == null) {
      image.src = tmpcanvasorg
    } else {
      image.src = tmpcanvasarr[i]
    }
  }

  function showTextBoxAll(tmpbtn) {
    let tmpcanvas = document.getElementById("bcPaintCanvas");
    let ctx = tmpcanvas.getContext("2d");
    if ($(tmpbtn).data('type') == 'false') {
      let image = new Array();
      image[0] = new Image();
      image[0].onload = function() {
        ctx.clearRect(0, 0, tmpcanvas.width, tmpcanvas.height);
        ctx.drawImage(image[0], 0, 0, tmpcanvas.width, tmpcanvas.height);
      };
      image[0].src = tmpcanvasorg

      $(tmpcanvasarr).each(function(index, value) {
        let element = $(`[name="buttonTextBox"][data-id="${index}"]`).find('[name="inputTextBox"]')
        let tmpX = $(element).data('x')
        let tmpY = $(element).data('y')
        let tmptext = $(element).val();
        let tmpwidth = $(element).data('width');
        let tmpheight = $(element).data('height');
        let ratio = tmpcanvas.width / tmpwidth
        console.log(tmptext)
        image[index + 1] = new Image();
        image[index + 1].onload = function() {
          // ctx.clearRect(0, 0, tmpcanvas.width, tmpcanvas.height);
          ctx.drawImage(image[index + 1], 0, 0, tmpcanvas.width, tmpcanvas.height);

          ctx.font = "15px Arial";

          var textwidth = ctx.measureText(tmptext).width;
          var textheight = ctx.measureText(tmptext).height;
          ctx.fillStyle = '#f50';
          ctx.fillRect(tmpX * ratio, tmpY * ratio - parseInt("Arial", 15), textwidth, parseInt("Arial", 15));
          ctx.fillStyle = '#000';

          ctx.fillText(tmptext, tmpX * ratio, tmpY * ratio);
        };
        image[index + 1].src = value
      });

      $(tmpbtn).data('type', 'true')

    } else {
      let image = new Image();
      image.onload = function() {
        ctx.clearRect(0, 0, tmpcanvas.width, tmpcanvas.height);
        ctx.drawImage(image, 0, 0, tmpcanvas.width, tmpcanvas.height);
      };
      image.src = tmpcanvasorg
      $(tmpbtn).data('type', 'false')

    }

  }


  function getcanvas() {

    $.ajax({
      url: `/file/file_comment/textbox`,
      type: 'get',
      data: {
        file_id: id,
      },
      success: function(response) {

        let tmpmarkarr = []
        let tmpXarr = []
        let tmpYarr = []
        let tmpwidthArr = []
        let tmpheightArr = []
        $(response).each(function() {
          tmpcanvasarr.push(this.canvas);
          tmpmarkarr.push(this.mark);
          tmpXarr.push(parseInt(this.x || '0'));
          tmpYarr.push(parseInt(this.y || '0'));
          tmpwidthArr.push(parseInt(this.width || '0'));
          tmpheightArr.push(parseInt(this.height || '0'));
        })
        $('#divpaint').bcPaint({
            // default color
            defaultColor: '000000',
            // default color set
            colors: [
              '000000', '444444', '999999', 'DDDDDD', '6B0100', 'AD0200',
              '6B5E00', 'FFE000', '007A22', '00E53F', '000884', '000FFF'
            ],
            // extend default set
            addColors: []

          },
          tmpcanvasarr,
          tmpmarkarr,
          tmpXarr,
          tmpYarr,
          tmpwidthArr,
          tmpheightArr
        );


        var image = new Image()
        image.onload = function(e) {

          const tmpcanvas = document.getElementById('bcPaintCanvas');
          $('#bcPaintCanvas').css('background-image', `url(/file/${file_id_dest})`);
          $('#bcPaintCanvas').css('background-size', `100% 100%`);
          tmpcanvas.height = $('#divpaint').width() / e.path[0].width * e.path[0].height;
          tmpcanvas.width = $('#divpaint').width();
        }
        image.src = `/file/${file_id_dest}`;
        getfile_comment();
      }
    });
  }

  function getfile_comment() {
    $.ajax({
      url: `/file/file_comment/canvas`,
      type: 'get',
      data: {
        file_id: id,
        module_id: module_id,

      },
      success: function(response) {
        $(response).each(function() {
          $('#inputFileCommentMain').val(this.comment)
          file_comment = this.comment;
          let tmpcanvas = document.getElementById("bcPaintCanvas");
          let ctx = tmpcanvas.getContext("2d");

          let image = new Image();
          image.onload = function() {
            ctx.drawImage(image, 0, 0, tmpcanvas.width, tmpcanvas.height);
          };
          image.src = this.canvas
          tmpcanvasorg = this.canvas
        })
      }
    });
  }

  function getcommon_material() {
    $.ajax({
      url: `/common`,
      type: 'get',
      success: function(response) {
        common_material = response.material;
        $(document).find('[name="divMaterial"]').find(`.select-pure__option`).hide()
        $(document).find('#noresultMaterial').find(`.select-pure__option`).hide()
        // noresultMaterial
        $.each(common_material, function() {
          $(document).find('[name="divMaterial"]').find(`.select-pure__option:contains("${this.name}")`).show()
          $(document).find('#noresultMaterial').find(`.select-pure__option:contains("${this.name}")`).show()
        })
        $(document).find('[name="divTitanizing"]').find(`.select-pure__option`).hide()
        $(document).find('#noresultTitanizing').find(`.select-pure__option`).hide()

        common_titanizing = response.titanizing;
        $.each(common_titanizing, function() {
          $(document).find('[name="divTitanizing"]').find(`.select-pure__option:contains("${this.name}")`).show()
          $(document).find('#noresultTitanizing').find(`.select-pure__option:contains("${this.name}")`).show()

        })

        common_hardness = response.hardness;
        // console.log(hardness)

        $.each(common_hardness, function() {
          hardness.push(this)

          if (this.common == false) {

            $(document).find('[name="divHardness"]').find(`.select-pure__option[data-value="${this.value}"]`).hide()
            $(document).find('#noresultHardness').find(`.select-pure__option[data-value="${this.value}"]`).hide()
          }
        });
        // console.log(hardness)



        // $.each(response.material,function(){
        //   common_material.push(this.name);
        // })
        // $.each(response.titanizing,function(){
        //   common_titanizing.push(this.name);
        // })
        // console.log(common_material)
        // console.log(common_titanizing)

      }
    })
  }

  function getFilter() {
    $.ajax({
      url: `/business/filter/year`,
      type: 'get',
      async: false,
      success: function(response) {
        year = response;


      }
    })
    $.ajax({
      url: `/business/filter/material`,
      type: 'get',
      async: false,
      success: function(response) {
        material = response


      }
    })
    $.ajax({
      url: `/business/filter/titanizing`,
      type: 'get',
      async: false,
      success: function(response) {
        // titanizing = [];

        // $.each(response,function(){
        //   let tmpobj = this;
        //   $.each(common_titanizing,function(){

        //     if((tmpobj.label).indexOf(this.name) >= 0 ){
        //       titanizing.push(tmpobj);
        //     }
        //   })

        // })
        titanizing = response;
        titanizing = titanizing.filter(function(obj) {
          return obj.label != 'NO PVD';
        });
        titanizing.unshift(0, 1, {
          'value': '032',
          'label': 'NO PVD'
        });

        // titanizing=[];
        // titanizing.push({ 'label': 'NO PVD','value': '032'})
        // $(response).each(function(){
        //   if(this.value != '032'){
        //     let tmpObj = new Object;
        //     tmpObj['lable'] = this.label.toString()
        //     tmpObj['value'] = this.value.toString()
        //     titanizing.push(tmpObj)
        //   }

        // })
        // console.log(titanizing)

      }
    })
    $.ajax({
      url: `/business/filter/hardness`,
      type: 'get',
      async: false,
      success: function(response) {
        hardness = response;
      }
    })
  }

  $(document).on('input', '#inputFileCommentMain', function() {
    console.log(newmodule_id)
    file_comment = $('#inputFileCommentMain').val()
    $.ajax({
      url: `/file/file_comment`,
      type: 'post',
      data: {
        file_id: id,
        module_id: newmodule_id,
        comment: file_comment,

      },
      success: function(response) {

      }
    });
  });

  $(document).on('show.bs.modal', '.modal', function() {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function() {
      $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    }, 0);
  });
  $('#exampleModal').on('show.bs.modal', function(e) {

    $('#exampleModal .modal-footer').html(`<button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>`);
    $("#exampleModal .modal-dialog ").attr("class", "modal-dialog modal-xl");

    // $('#exampleModal .modal-footer').html(basicModalFooter);
    var type = $(e.relatedTarget).data('type');
    // console.log(type);

    if (type == 'nextstep') {
      nextstepModal();
    }
  });

  $(document).on('click', '[name="lockoption"]', function() {
    if ($(this).data('type') == "lock") {
      $.ajax({
        url: `/file/lock`,
        type: 'post',
        data: {
          file_id: id,
          lock: true
        },
        success: function(response) {

        }
      })
    } else {
      $.ajax({
        url: `/file/lock`,
        type: 'post',
        data: {
          file_id: id,
          lock: false

        },
        success: function(response) {

        }
      })
      $('[name="divlock"]').show();

    }
  })

  $(document).on('show.bs.modal', '.modal', function() {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function() {
      $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    }, 0);
  });

  function nextstepModal() {
    if(materialArr.length ===0 || titanizingArr.length === 0){
      $("#exampleModal .modal-dialog ").attr("class", "modal-dialog");

      $('#exampleModal .modal-title').html(`確認資訊`); 
      $('#exampleModal .modal-body').html(`請輸入材質與鍍鈦`);
      return;
    }
    $('#exampleModal .modal-title').html(`確認下一步`);
    $('#exampleModal .modal-body').html(`
      <div class="table-responsive">
        <table class="table table-borderless" width="100%">
          <thead>
            <tr>
              <th class="text-nowrap">報價單原圖</th>
              <th class="text-nowrap">客戶原圖</th>
              <th class="text-nowrap">視角圖</th>
              <th width="30%" class="text-nowrap">詳細資訊</th>
             
            </tr>
          </thead>
          <tbody id="modaltbody">
          </tbody>
        </table>
        <div class="form-group row col-12">
          <label class="col-form-label col-sm-auto">註記：</label>
          <div class="col" id="">
            <textarea class="form-control disable"  rows="3" disabled>${file_comment || ''}</textarea>
          </div>
        </div>
      </div>
    `);

    $('#exampleModal .modal-footer').append(`<button type="button" class="btn btn-primary" onclick="buttonPass()">下一步</button>`);
    let element = $('[name="inputCheck"]:checked').closest('tr');
    let target = $("#modaltbody");
    console.log('length' + element.length)
    $(element).each(function() {
      let tds = $(this).children(),
        row = $("<tr></tr>");
      row.append(tds.eq(0).clone()).append(tds.eq(1).clone()).appendTo(target);
      let tmpInfo = '';
      tmpInfo += tds.eq(2).html();
      tmpInfo += tds.eq(3).html();
      tmpInfo += tds.eq(4).html();
      tmpInfo += tds.eq(5).html();
      row.append(`<td>${tmpInfo}</td>`)
    });


    $("#modaltbody").find('[name="divmultipleselect"]').html(``);

    let tmpStr = ''
    tmpStr = '報價單材質：';
    let tmpcount = 0;
    $(materialArr).each(function() {
      let tmpthis = this;
      $(material).each(function() {
        if (this.value == tmpthis) {
          tmpcount += 1;
          tmpStr += `${this.label}、`;
        }
      });
    });
    if (tmpcount > 0) {
      tmpStr = tmpStr.slice(0, -1)
    }
    tmpcount = 0;
    tmpStr += '</br>報價單鍍鈦：';
    $(titanizingArr).each(function() {
      let tmpthis = this;
      $(titanizing).each(function() {
        if (this.value == tmpthis) {
          tmpcount += 1;
          tmpStr += `${this.label}、`;
        }
      });
    });
    if (tmpcount > 0) {
      tmpStr = tmpStr.slice(0, -1)
    }
    tmpcount = 0;
    tmpStr += '</br>報價單硬度：';
    $(hardnessArr).each(function() {
      let tmpthis = this;
      $(hardness).each(function() {
        if (this.value == tmpthis) {
          tmpcount += 1;
          tmpStr += `${this.label}、`;

        }
      });
    });
    if (tmpcount > 0) {
      tmpStr = tmpStr.slice(0, -1)
    }


    $("#modaltbody").find('[name="divmultipleselect"]').each(function() {
      $(this).html(tmpStr);
    });

    $("#modaltbody").find('input').each(function() {
      $(this).replaceWith(function(_, content) {
        return '<p>' + $(this).data('chinese') + '：' + $(this).val() + '</p>';
      });
    });
    if (element.length == 0) {
      $('#exampleModal .modal-body').html(tmpStr)
      $('#exampleModal .modal-body').append(`  
      <div class="form-group row col-12">
        <label class="col-form-label col-sm-auto">註記：</label>
        <div class="col" id="">
          <textarea class="form-control" id="inputFileComment" rows="3" disabled>${file_comment || ''}</textarea>
        </div>
      </div>`)
    }
  }

  function inquotationMaterial() {
    // data-process_id="${response.id}" data-crop_id="${responseItem.id}"
    materialArr = [];
    titanizingArr = [];
    hardnessArr = []
    $.ajax({
      url: `/material`,
      type: 'get',
      data: {
        file_id: id,
      },
      success: function(response) {
        let tmpStr = '';
        $.each(response.material, function() {
          materialArr.push((this.material_id).toString());
        })
        $.each(response.titanizing, function() {
          titanizingArr.push((this.titanizing_id).toString());
        })
        $.each(response.hardness, function() {
          hardnessArr.push((this.hardness_id).toString());
        })
        resetquotationMaterial()
        resetquotationTitanizing()
        resetquotationHardness()

        // $('[name = inputCheck]:checked').closest('tr').each(function() {

        //   tmpprocess_id = $(this).data('process_id');
        //   tmpcrop_id = $(this).data('crop_id');
        //   console.log($(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`),'length')
        //   $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`).prepend(`
        //   <form name="formHandinput">
        //     <div class="form-group row">
        //       <div class="col-sm-10" >
        //         <input type="text" class="form-control" id="" placeholder="手動輸入材質" required>
        //       </div>
        //       <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

        //     </div></form>`)
        //   $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).prepend(`
        //   <form name="formHandinputTitanizing">
        //     <div class="form-group row">
        //       <div class="col-sm-10" >
        //         <input type="text" class="form-control" id="" placeholder="手動輸入鍍鈦" required>
        //       </div>
        //       <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

        //     </div></form>`)
        //     $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationHardness"]`).prepend(`
        //   <form name="formHandinputHardness">
        //     <div class="form-group row">
        //       <div class="col-sm-10" >
        //         <input type="text" class="form-control" id="" placeholder="手動輸入硬度" required>
        //       </div>
        //       <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

        //     </div></form>`)
        //   // if($(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationHardness"]`).length==0)
        //     new SelectPure(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationHardness"]`, {
        //         options: hardness,
        //         onChange: value => {
        //           updatequotationHardness(value)
        //         },
        //       multiple: true,
        //       autocomplete: true,
        //       icon: "fa fa-times",
        //       inlineIcon: false,
        //       classNames: {
        //         select: "select-pure__select",
        //         dropdownShown: "select-pure__select--opened",
        //         multiselect: "select-pure__select--multiple",
        //         label: "select-pure__label",
        //         placeholder: "select-pure__placeholder",
        //         dropdown: "select-pure__options",
        //         option: "select-pure__option",
        //         autocompleteInput: "select-pure__autocomplete",
        //         selectedLabel: "select-pure__selected-label",
        //         selectedOption: "select-pure__option--selected",
        //         placeholderHidden: "select-pure__placeholder--hidden",
        //         optionHidden: "select-pure__option--hidden",
        //       },
        //       value: hardnessArr
        //     });


        //   // titanizing=[
        //   //   {"lable": "AlCrN","value": "022"},
        //   //   {"lable": "AlCrN","value": "021"},
        //   //   {"lable": "AlCrN","value": "023"},

        //   // ];
        //   // if($(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).length==0)


        //     new SelectPure(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`, {
        //       options: titanizing,
        //       onChange: value => {
        //         updatequotationTitanizing(value)
        //       },
        //       multiple: true,
        //       autocomplete: true,
        //       icon: "fa fa-times",
        //       inlineIcon: false,
        //       classNames: {
        //         select: "select-pure__select",
        //         dropdownShown: "select-pure__select--opened",
        //         multiselect: "select-pure__select--multiple",
        //         label: "select-pure__label",
        //         placeholder: "select-pure__placeholder",
        //         dropdown: "select-pure__options",
        //         option: "select-pure__option",
        //         autocompleteInput: "select-pure__autocomplete",
        //         selectedLabel: "select-pure__selected-label",
        //         selectedOption: "select-pure__option--selected",
        //         placeholderHidden: "select-pure__placeholder--hidden",
        //         optionHidden: "select-pure__option--hidden",
        //       },
        //       value: titanizingArr
        //     });
        //   // if($(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`).length==0)
        //     new SelectPure(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`, {
        //       options: material,
        //       onChange: value => {
        //         updatequotationMaterial(value)
        //       },
        //       multiple: true,
        //       autocomplete: true,
        //       icon: "fa fa-times",
        //       inlineIcon: false,
        //       classNames: {
        //         select: "select-pure__select",
        //         dropdownShown: "select-pure__select--opened",
        //         multiselect: "select-pure__select--multiple",
        //         label: "select-pure__label",
        //         placeholder: "select-pure__placeholder",
        //         dropdown: "select-pure__options",
        //         option: "select-pure__option",
        //         autocompleteInput: "select-pure__autocomplete",
        //         selectedLabel: "select-pure__selected-label",
        //         selectedOption: "select-pure__option--selected",
        //         placeholderHidden: "select-pure__placeholder--hidden",
        //         optionHidden: "select-pure__option--hidden",
        //       },
        //       value: materialArr
        //     });

        //   $(document).find(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`).find(`.select-pure__option`).hide()
        //   $.each(common_material,function(){
        //     $(document).find(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`).find(`.select-pure__option:contains("${this.name}")`).show()
        //   })
        //   $(document).find(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).find(`.select-pure__option`).hide()
        //   $.each(common_titanizing,function(){
        //     $(document).find(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).find(`.select-pure__option:contains("${this.name}")`).show()
        //   })
        // });
        // if($(`#noresultMaterial .select-pure__select`).length==0){
        //   $(`#noresultMaterial`).html(`
        //     <form name="formHandinput">
        //       <div class="form-group row">
        //         <div class="col-sm-10" >
        //           <input type="text" class="form-control" id="" placeholder="手動輸入材質" required>
        //         </div>
        //         <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

        //       </div></form>`)
        //   new SelectPure(`#noresultMaterial`, {
        //       options: material,
        //       onChange: value => {
        //         updatequotationMaterial(value)
        //       },
        //       multiple: true,
        //       autocomplete: true,
        //       icon: "fa fa-times",
        //       inlineIcon: false,
        //       classNames: {
        //         select: "select-pure__select",
        //         dropdownShown: "select-pure__select--opened",
        //         multiselect: "select-pure__select--multiple",
        //         label: "select-pure__label",
        //         placeholder: "select-pure__placeholder",
        //         dropdown: "select-pure__options",
        //         option: "select-pure__option",
        //         autocompleteInput: "select-pure__autocomplete",
        //         selectedLabel: "select-pure__selected-label",
        //         selectedOption: "select-pure__option--selected",
        //         placeholderHidden: "select-pure__placeholder--hidden",
        //         optionHidden: "select-pure__option--hidden",
        //       },
        //       value: materialArr
        //     });
        // }

        // if($(`#noresultTitanizing .select-pure__select`).length==0){
        //   $(`#noresultTitanizing`).html(`
        //     <form name="formHandinputTitanizing">
        //       <div class="form-group row">
        //         <div class="col-sm-10" >
        //           <input type="text" class="form-control" id="" placeholder="手動輸入鍍鈦" required>
        //         </div>
        //         <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

        //       </div></form>`)


        //   new SelectPure(`#noresultTitanizing`, {
        //       options: titanizing,
        //       onChange: value => {
        //         updatequotationTitanizing(value)
        //       },
        //       multiple: true,
        //       autocomplete: true,
        //       icon: "fa fa-times",
        //       inlineIcon: false,
        //       classNames: {
        //         select: "select-pure__select",
        //         dropdownShown: "select-pure__select--opened",
        //         multiselect: "select-pure__select--multiple",
        //         label: "select-pure__label",
        //         placeholder: "select-pure__placeholder",
        //         dropdown: "select-pure__options",
        //         option: "select-pure__option",
        //         autocompleteInput: "select-pure__autocomplete",
        //         selectedLabel: "select-pure__selected-label",
        //         selectedOption: "select-pure__option--selected",
        //         placeholderHidden: "select-pure__placeholder--hidden",
        //         optionHidden: "select-pure__option--hidden",
        //       },
        //       value: titanizingArr
        //     });
        // }

        // if($(`#noresultHardness .select-pure__select`).length==0){
        //   $(`#noresultHardness`).html(`
        //     <form name="formHandinputHardness">
        //       <div class="form-group row">
        //         <div class="col-sm-10" >
        //           <input type="text" class="form-control" id="" placeholder="手動輸入硬度" required>
        //         </div>
        //         <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

        //       </div></form>`)
        //   new SelectPure(`#noresultHardness`, {
        //         options: hardness,
        //         onChange: value => {
        //           updatequotationHardness(value)
        //         },
        //       multiple: true,
        //       autocomplete: true,
        //       icon: "fa fa-times",
        //       inlineIcon: false,
        //       classNames: {
        //         select: "select-pure__select",
        //         dropdownShown: "select-pure__select--opened",
        //         multiselect: "select-pure__select--multiple",
        //         label: "select-pure__label",
        //         placeholder: "select-pure__placeholder",
        //         dropdown: "select-pure__options",
        //         option: "select-pure__option",
        //         autocompleteInput: "select-pure__autocomplete",
        //         selectedLabel: "select-pure__selected-label",
        //         selectedOption: "select-pure__option--selected",
        //         placeholderHidden: "select-pure__placeholder--hidden",
        //         optionHidden: "select-pure__option--hidden",
        //       },
        //       value: hardnessArr
        //     });
        // }

        // $(document).find(`#noresultMaterial`).find(`.select-pure__option`).hide()
        // $.each(common_material,function(){
        //   $(document).find(`#noresultMaterial`).find(`.select-pure__option:contains("${this.name}")`).show()
        // })
        // $(document).find(`#noresultTitanizing`).find(`.select-pure__option`).hide()
        // $.each(common_titanizing,function(){
        //   $(document).find(`#noresultTitanizing`).find(`.select-pure__option:contains("${this.name}")`).show()
        // })
        // $(document).find(`#noresultHardness`).find(`.select-pure__option`).hide()
        // $.each(common_hardness,function(){
        //   $(document).find(`#noresultHardness`).find(`.select-pure__option[data-value="${this.value}"]`).show()
        // })
        $.ajax({
          url: '/business/materialRecog',
          type: 'get',
          dataType: 'json',
          data: {
            id: id
          },
          success: function(response) {
            // response = [];
            if ('material' in response && $(document).find('#noresultMaterial').find('.select-pure__selected-label').length == 0) {
              $.each(response['material'], (key, row) => {
                $.each(row, (key, value) => {
                  $(document).find('#noresultMaterial').find(`[data-value="${String(value).padStart(3, '0')}"]`).click();
                  $(document).find('#noresultMaterial').find(`[data-value="${String(value).padStart(3, '0')}"]`).click();
                  return false;
                });
                return false;
              });
            }
            if ('coating' in response && $(document).find('#noresultTitanizing').find('.select-pure__selected-label').length == 0) {
              $.each(response['coating'], (key, row) => {
                $.each(row, (key, value) => {
                  $(document).find('#noresultTitanizing').find(`[data-value="${String(value).padStart(3, '0')}"]`).click();
                  $(document).find('#noresultTitanizing').find(`[data-value="${String(value).padStart(3, '0')}"]`).click();
                  return false;
                });
                return false;
              });
            }
          }
        });
      }
    });
  }


  function getModule() {
    $.ajax({
      url: `/setting/module`,
      type: 'get',
      data: {},
      dataType: 'json',
      success: function(response) {
        $.each(response, function() {
          if (this.name == module_name) {
            module_id = this.id;
            newmodule_id = this.id

          }

        })
        getcanvas();
        console.log(newmodule_id)
        getDiscriptOther();
      }
    });
  }

  function getDiscriptOther() {
    window.sharedVariable = {
      file_id: id,
      module_name: '研發',
      module_id: module_id
    };
    $("#discriptOther").load(`/discript/newother`);
  }

  function getComment() {
    $.ajax({
      url: `/file/comment`,
      type: 'get',
      data: {
        file_id: id,
        file_id_dest: file_id_dest
      },
      success: function(response) {
        if (response.length == 0)
          $('#tdComment').html(`尚未有任何註記`)
        else
          $('#tdComment').html(``)
        $(response).each(function() {
          $('#tdComment').append(`
                        <p>${this.module_name}：${this.comment}</p>
                    `);
        })
      }
    })
  }

  function inSave(value, type) {

    let inSaveArr = []
    $('#dataTable').find(`input[data-type=${type}]:checked`).closest('tr').find('[name="inputMaterialRecog"]').each(function() {
      let inputMaterialRecogvalue = $(this).val()

      if (type == 'material') {
        $(material).each(function() {
          if (this.label == inputMaterialRecogvalue) {
            if (inSaveArr.indexOf(this.value) == -1) {
              inSaveArr.push(this.value)
            }

          }
        })
        updatequotationMaterial(inSaveArr);

      } else if (type == 'titanizing') {
        $(titanizing).each(function() {
          if (this.label == inputMaterialRecogvalue) {
            if (inSaveArr.indexOf(this.value) == -1) {
              inSaveArr.push(this.value)
            }
          }
        })
        updatequotationTitanizing(inSaveArr);

      } else if (type == 'hardness') {
        $(hardness).each(function() {
          if (this.label == inputMaterialRecogvalue) {
            if (inSaveArr.indexOf(this.value) == -1) {
              inSaveArr.push(this.value)
            }
          }
        })
        updatequotationHardness(inSaveArr);

      }

    });

  }


  function updatequotationHardness(valueHardness) {
    hardnessArr = valueHardness;
    resetquotationHardness();
    $.ajax({
      url: `/material/hardness`,
      type: 'post',
      data: {
        file_id: id,
        hardness: hardnessArr
      },
      success: function(response) {

      }
    });
  }

  function updatequotationTitanizing(valueTitanizing) {
    titanizingArr = valueTitanizing;
    resetquotationTitanizing();
    $.ajax({
      url: `/material/titanizing`,
      type: 'post',
      data: {
        file_id: id,
        titanizing: titanizingArr
      },
      success: function(response) {

      }
    });
  }

  function updatequotationMaterial(valueMaterial) {
    console.log(valueMaterial)
    materialArr = valueMaterial;
    resetquotationMaterial();
    $.ajax({
      url: `/material`,
      type: 'post',
      data: {
        file_id: id,
        material: materialArr
      },
      success: function(response) {

      }
    });
  }

  function resetquotationTitanizing() {
    $('[name=inputCheck]:checked').closest('tr').each(function() {
      tmpprocess_id = $(this).data('process_id');
      tmpcrop_id = $(this).data('crop_id');
      $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).html('')
      $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).prepend(`
          <form name="formHandinputTitanizing">
            <div class="form-group row">
              <div class="col-sm-10" >
                <input type="text" class="form-control" id="" placeholder="手動輸入鍍鈦" required>
              </div>
              <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

            </div></form>`)

      new SelectPure(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`, {
        options: titanizing,
        onChange: value => {
          updatequotationTitanizing(value)
        },
        multiple: true,
        autocomplete: true,
        icon: "fa fa-times",
        inlineIcon: false,
        classNames: {
          select: "select-pure__select",
          dropdownShown: "select-pure__select--opened",
          multiselect: "select-pure__select--multiple",
          label: "select-pure__label",
          placeholder: "select-pure__placeholder",
          dropdown: "select-pure__options",
          option: "select-pure__option",
          autocompleteInput: "select-pure__autocomplete",
          selectedLabel: "select-pure__selected-label",
          selectedOption: "select-pure__option--selected",
          placeholderHidden: "select-pure__placeholder--hidden",
          optionHidden: "select-pure__option--hidden",
        },
        value: titanizingArr
      });
    });
    $("[name=inputCheck]:not(:checked)").closest('tr').each(function() {
      tmpprocess_id = $(this).data('process_id');
      tmpcrop_id = $(this).data('crop_id');
      $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).html('')
    });

    $(`#noresultTitanizing`).html(`
          <form name="formHandinputTitanizing">
            <div class="form-group row">
              <div class="col-sm-10" >
                <input type="text" class="form-control" id="" placeholder="手動輸入鍍鈦" required>
              </div>
              <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

            </div></form>`)
    new SelectPure(`#noresultTitanizing`, {
      options: titanizing,
      onChange: value => {
        updatequotationTitanizing(value)
      },
      multiple: true,
      autocomplete: true,
      icon: "fa fa-times",
      inlineIcon: false,
      classNames: {
        select: "select-pure__select",
        dropdownShown: "select-pure__select--opened",
        multiselect: "select-pure__select--multiple",
        label: "select-pure__label",
        placeholder: "select-pure__placeholder",
        dropdown: "select-pure__options",
        option: "select-pure__option",
        autocompleteInput: "select-pure__autocomplete",
        selectedLabel: "select-pure__selected-label",
        selectedOption: "select-pure__option--selected",
        placeholderHidden: "select-pure__placeholder--hidden",
        optionHidden: "select-pure__option--hidden",
      },
      value: titanizingArr
    });
    $('[name="quotationTitanizing"]').find(`.select-pure__option`).each(function() {
      $(this).append(`<button type="button" class="btn btn-danger btn-sm float-right" name="btndeleteoption" data-type="titanizing" onclick="deleteoption(this)">x</button>`);
    })
    $('#noresultTitanizing').find(`.select-pure__option`).each(function() {
      $(this).append(`<button type="button" class="btn btn-danger btn-sm float-right" name="btndeleteoption" data-type="titanizing" onclick="deleteoption(this)">x</button>`);
    })

    $(document).find('[name="divTitanizing"]').find(`.select-pure__option`).hide()
    $(document).find('#noresultTitanizing').find(`.select-pure__option`).hide()

    $.each(common_titanizing, function() {
      $(document).find('[name="divTitanizing"]').find(`.select-pure__option:contains("${this.name}")`).show()
      $(document).find('#noresultTitanizing').find(`.select-pure__option:contains("${this.name}")`).show()

    })
  }

  function resetquotationHardness() {
    $('[name=inputCheck]:checked').closest('tr').each(function() {
      tmpprocess_id = $(this).data('process_id');
      tmpcrop_id = $(this).data('crop_id');

      $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationHardness"]`).html('')

      $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationHardness"]`).prepend(`
          <form name="formHandinputHardness">
            <div class="form-group row">
              <div class="col-sm-10" >
                <input type="text" class="form-control" id="" placeholder="手動輸入硬度" required>
              </div>
              <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

            </div></form>`)

      new SelectPure(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationHardness"]`, {
        options: hardness,
        onChange: value => {
          updatequotationHardness(value)
        },
        multiple: true,
        autocomplete: true,
        icon: "fa fa-times",
        inlineIcon: false,
        classNames: {
          select: "select-pure__select",
          dropdownShown: "select-pure__select--opened",
          multiselect: "select-pure__select--multiple",
          label: "select-pure__label",
          placeholder: "select-pure__placeholder",
          dropdown: "select-pure__options",
          option: "select-pure__option",
          autocompleteInput: "select-pure__autocomplete",
          selectedLabel: "select-pure__selected-label",
          selectedOption: "select-pure__option--selected",
          placeholderHidden: "select-pure__placeholder--hidden",
          optionHidden: "select-pure__option--hidden",
        },
        value: hardnessArr
      });

      $(document).find(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`).find(`.select-pure__option`).hide()
      $.each(common_material, function() {
        $(document).find(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`).find(`.select-pure__option:contains("${this.name}")`).show()
      })
      $(document).find(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).find(`.select-pure__option`).hide()
      $.each(common_titanizing, function() {
        $(document).find(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationTitanizing"]`).find(`.select-pure__option:contains("${this.name}")`).show()
      })
    });
    $("[name=inputCheck]:not(:checked)").closest('tr').each(function() {
      tmpprocess_id = $(this).data('process_id');
      tmpcrop_id = $(this).data('crop_id');
      $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationHardness"]`).html('')
    });

    $(`#noresultHardness`).html(`
          <form name="formHandinputHardness">
            <div class="form-group row">
              <div class="col-sm-10" >
                <input type="text" class="form-control" id="" placeholder="手動輸入硬度" required>
              </div>
              <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

            </div></form>`)
    new SelectPure(`#noresultHardness`, {
      options: hardness,
      onChange: value => {
        updatequotationHardness(value)
      },
      multiple: true,
      autocomplete: true,
      icon: "fa fa-times",
      inlineIcon: false,
      classNames: {
        select: "select-pure__select",
        dropdownShown: "select-pure__select--opened",
        multiselect: "select-pure__select--multiple",
        label: "select-pure__label",
        placeholder: "select-pure__placeholder",
        dropdown: "select-pure__options",
        option: "select-pure__option",
        autocompleteInput: "select-pure__autocomplete",
        selectedLabel: "select-pure__selected-label",
        selectedOption: "select-pure__option--selected",
        placeholderHidden: "select-pure__placeholder--hidden",
        optionHidden: "select-pure__option--hidden",
      },
      value: hardnessArr
    });
    $('[name="quotationHardness"]').find(`.select-pure__option`).each(function() {
      $(this).append(`<button type="button" class="btn btn-danger btn-sm float-right" name="btndeleteoption" data-type="hardness" onclick="deleteoption(this)">x</button>`);

    })
    $('#noresultHardness').find(`.select-pure__option`).each(function() {
      $(this).append(`<button type="button" class="btn btn-danger btn-sm float-right" name="btndeleteoption" data-type="hardness" onclick="deleteoption(this)">x</button>`);
    })
    $.each(common_hardness, function() {
      if (this.common == false) {
        $(document).find('[name="divHardness"]').find(`.select-pure__option[data-value="${this.value}"]`).hide()
        $(document).find('#noresultHardness').find(`.select-pure__option[data-value="${this.value}"]`).hide()
      }
    });

  }

  function resetquotationMaterial() {
    // crop_materials[`process_id_${response.id}_crop_id_${responseItem.id}` ] = materials;

    $('[name=inputCheck]:checked').closest('tr').each(function() {
      tmpprocess_id = $(this).data('process_id');
      tmpcrop_id = $(this).data('crop_id');
      $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`).html(`
      <form name="formHandinput">
            <div class="form-group row">
              <div class="col-sm-10" >
                <input type="text" class="form-control" id="" placeholder="手動輸入材質" required>
              </div>
              <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

            </div></form>`)


      new SelectPure(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`, {
        options: material,
        multiple: true,
        autocomplete: true,
        icon: "fa fa-times",
        inlineIcon: false,
        classNames: {
          select: "select-pure__select",
          dropdownShown: "select-pure__select--opened",
          multiselect: "select-pure__select--multiple",
          label: "select-pure__label",
          placeholder: "select-pure__placeholder",
          dropdown: "select-pure__options",
          option: "select-pure__option",
          autocompleteInput: "select-pure__autocomplete",
          selectedLabel: "select-pure__selected-label",
          selectedOption: "select-pure__option--selected",
          placeholderHidden: "select-pure__placeholder--hidden",
          optionHidden: "select-pure__option--hidden",
        },
        value: crop_materials[`process_id_${tmpprocess_id}_crop_id_${tmpcrop_id}`]
      });

      new SelectPure(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`, {
        options: material,
        onChange: value => {
          updatequotationMaterial(value)
        },
        multiple: true,
        autocomplete: true,
        icon: "fa fa-times",
        inlineIcon: false,
        classNames: {
          select: "select-pure__select",
          dropdownShown: "select-pure__select--opened",
          multiselect: "select-pure__select--multiple",
          label: "select-pure__label",
          placeholder: "select-pure__placeholder",
          dropdown: "select-pure__options",
          option: "select-pure__option",
          autocompleteInput: "select-pure__autocomplete",
          selectedLabel: "select-pure__selected-label",
          selectedOption: "select-pure__option--selected",
          placeholderHidden: "select-pure__placeholder--hidden",
          optionHidden: "select-pure__option--hidden",
        },
        value: materialArr
      });
    });
    $("[name=inputCheck]:not(:checked)").closest('tr').each(function() {
      tmpprocess_id = $(this).data('process_id');
      tmpcrop_id = $(this).data('crop_id');
      $(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`).html('')
      new SelectPure(`#divImage_${tmpprocess_id}  #divImageResultMatch_${tmpcrop_id} [name="quotationMaterial"]`, {
        options: material,
        multiple: true,
        autocomplete: true,
        icon: "fa fa-times",
        inlineIcon: false,
        classNames: {
          select: "select-pure__select",
          dropdownShown: "select-pure__select--opened",
          multiselect: "select-pure__select--multiple",
          label: "select-pure__label",
          placeholder: "select-pure__placeholder",
          dropdown: "select-pure__options",
          option: "select-pure__option",
          autocompleteInput: "select-pure__autocomplete",
          selectedLabel: "select-pure__selected-label",
          selectedOption: "select-pure__option--selected",
          placeholderHidden: "select-pure__placeholder--hidden",
          optionHidden: "select-pure__option--hidden",
        },
        value: crop_materials[`process_id_${tmpprocess_id}_crop_id_${tmpcrop_id}`]
      });
    });
    $(`#noresultMaterial`).html(`
          <form name="formHandinput">
            <div class="form-group row">
              <div class="col-sm-10" >
                <input type="text" class="form-control" id="" placeholder="手動輸入材質" required>
              </div>
              <button type="submit" class="btn btn-primary  col-sm-auto" >確定</button>

            </div></form>`)
    new SelectPure(`#noresultMaterial`, {
      options: material,
      onChange: value => {
        updatequotationMaterial(value)
      },
      multiple: true,
      autocomplete: true,
      icon: "fa fa-times",
      inlineIcon: false,
      classNames: {
        select: "select-pure__select",
        dropdownShown: "select-pure__select--opened",
        multiselect: "select-pure__select--multiple",
        label: "select-pure__label",
        placeholder: "select-pure__placeholder",
        dropdown: "select-pure__options",
        option: "select-pure__option",
        autocompleteInput: "select-pure__autocomplete",
        selectedLabel: "select-pure__selected-label",
        selectedOption: "select-pure__option--selected",
        placeholderHidden: "select-pure__placeholder--hidden",
        optionHidden: "select-pure__option--hidden",
      },
      value: materialArr
    });
    $('[name="quotationMaterial"]').find(`.select-pure__option`).each(function() {
      $(this).append(`<button type="button" class="btn btn-danger btn-sm float-right" name="btndeleteoption" data-type="material" onclick="deleteoption(this)">x</button>`);
      // $(this).append(`<span class="badge badge-light" name="btndeleteoption" data-type="material">x</span>`);
    })
    $('#noresultMaterial').find(`.select-pure__option`).each(function() {
      $(this).append(`<button type="button" class="btn btn-danger btn-sm float-right" name="btndeleteoption" data-type="material" onclick="deleteoption(this)">x</button>`);
      // $(this).append(`<span class="badge badge-light" name="btndeleteoption" data-type="material">x</span>`);
    })
    $(document).find('[name="divMaterial"]').find(`.select-pure__option`).hide()
    $(document).find('#noresultMaterial').find(`.select-pure__option`).hide()
    // noresultMaterial
    $.each(common_material, function() {
      $(document).find('[name="divMaterial"]').find(`.select-pure__option:contains("${this.name}")`).show()
      $(document).find('#noresultMaterial').find(`.select-pure__option:contains("${this.name}")`).show()
    })

  }

  function deletecommonmaterial(tmpmaterial) {
    $.ajax({
      url: `/common/material`,
      type: 'delete',
      data: {
        material: tmpmaterial,

      },
      dataType: 'json',
      success: function(response) {

      }
    });
  }

  function deletecommontitanizing(tmptitanizing) {
    $.ajax({
      url: `/common/titanizing`,
      type: 'delete',
      data: {
        titanizing: tmptitanizing,

      },
      dataType: 'json',
      success: function(response) {

      }
    });
  }

  function deletecommonhardness(tmphardness) {
    $.ajax({
      url: `/common/hardness`,
      type: 'delete',
      data: {
        hardness: tmphardness,

      },
      dataType: 'json',
      success: function(response) {

      }
    });
  }

  function deleteoption(element) {
    // console.log($(element).data('type'))
    // console.log($(element).closest('.select-pure__option').data('value'))
    let deleteoption;

    if ($(element).data('type') == 'material') {
      $(material).each(function() {
        if (this.value == $(element).closest('.select-pure__option').data('value')) {
          deleteoption = this.label;
        }
      })
      deletecommonmaterial(deleteoption)
    } else if ($(element).data('type') == 'titanizing') {
      $(titanizing).each(function() {
        if (this.value == $(element).closest('.select-pure__option').data('value')) {
          deleteoption = this.label;
        }
      })
      deletecommontitanizing(deleteoption)
    } else if ($(element).data('type') == 'hardness') {

      deleteoption = $(element).closest('.select-pure__option').data('value')
      deletecommonhardness(deleteoption)
    }
    // console.log(deleteoption)
    $(element).closest('.select-pure__option').hide();


  }
  // $(document).on('click', '[name="btndeleteoption"]', function(e) {
  //   console.log($(this.data('type')))
  // });


  $(document).on('change', '#togglePic', function() {

    if ($(this).is(':checked')) {
      $("#tmpTable tbody").find("td:nth-child(2)").each(function() {
        // $(this).find('img').show()
        $('[name="collapsePic"]').collapse('show')
      });
    } else {
      $("#tmpTable tbody").find("td:nth-child(2)").each(function() {
        // $(this).find('img').hide()
        $('[name="collapsePic"]').collapse('hide')


      });
    }
  });
  $(document).on('submit', '[name="formHandinput"]', function(e) {
    e.preventDefault();
    // console.log($(this).find('input').val())
    handAddMaterial($(this).find('input').val())
  });
  $(document).on('submit', '[name="formHandinputTitanizing"]', function(e) {
    e.preventDefault();
    // console.log($(this).find('input').val())
    handAddTitanizing($(this).find('input').val())
  });
  $(document).on('submit', '[name="formHandinputHardness"]', function(e) {
    e.preventDefault();
    // console.log($(this).find('input').val())
    handAddHardness($(this).find('input').val())
  });
  $(document).on('change', '#selectAmount,#selectThreshold', function() {
    getResultComponents()
  });

  function handAddHardness(tmphardness) {
    $.ajax({
      url: `/business/hardness`,
      type: 'post',
      data: {
        hardness: tmphardness
      },
      success: function(response) {
        if (response.status == 'success') {
          hardness.push(response);
        }
        if (!hardnessArr.includes((response.value))) {
          hardnessArr.push((response.value));
        }
        updatequotationHardness(hardnessArr);
      }
    });
  }

  function handAddTitanizing(tmpTitanizing) {
    $.ajax({
      url: `/business/titanizing`,
      type: 'post',
      data: {
        titanizing: tmpTitanizing
      },
      success: function(response) {
        if (response.status == 'success') {
          titanizing.push(response);
        }
        if (!titanizingArr.includes((response.value))) {
          titanizingArr.push((response.value));
        }
        updatequotationTitanizing(titanizingArr);
      }
    });
  }


  function handAddMaterial(tmpMaterial) {
    $.ajax({
      url: `/business/material`,
      type: 'post',
      data: {
        material: tmpMaterial
      },
      success: function(response) {
        // console.log(material)
        if (response.status == 'success') {
          material.push(response);
        }
        // console.log(material)
        if (!materialArr.includes((response.value))) {
          materialArr.push((response.value));
        }
        // console.log(materialArr)
        updatequotationMaterial(materialArr);
        // materialArr
      }
    });

  }


  var focusID, focusItemID;

  function inputFocus(resID, resItemID) {
    console.log('22')
    focusID = resID;
    focusItemID = resItemID;
  }
  let crops_arr = new Object();
  let crop_materials = new Object();


  function getResultComponents() {
    var processArr = [];
    $.ajax({
      url: `/processes/crop/${file_id_dest}`,
      type: 'get',
      success: function(response) {
        processArr = response.process
        var compareObj = new Object();
        let crops = $(`<div></div>`);
        $(response.crop).each(function() {
          $(crops).append(`
            <img src="/fileCrop/${this}" style="width:100px;height:100px" class="col figure-img img-thumbnail rounded" alt="..." />
          `);
        })

        $('#divImage').html(``)
        $.each(response.process, function(key, value) {
          process.push(value)
          $('#divImage').append(`
            <div class="card col-12">
              <div class="card-body">
                <div class="form-group row">
                  <label class="col-form-label col-auto">零件${key+1}</label>
                  <div class="col-auto">
                  ${crops.html()}
                  </div>
                  <div class="accordion col-12" id="accordionFilter_${value}" hidden>
                    <div class="card">
                      <div class="card-header" id="accordionHeadingFilter">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFilter_${value}" aria-expanded="false" aria-controls="collapseFilter">
                            篩選設定
                          </button>
                        </h2>
                      </div>
                      <div id="collapseFilter_${value}" class="collapse" aria-labelledby="accordionHeadingFilter" data-parent="#accordionFilter_${value}">
                        <div class="card-body" style="height:350px;">
                          <div class="form-group row">
                            <div class="form-group row col-12">
                              <label class="col-form-label col-sm-auto">年份：</label>
                              <div class="col row" name="divYear">
                              </div>
                            </div>
                            <div class="form-group row col-12">
                              <label class="col-form-label col-sm-auto">材質：</label>
                              <div class="col row" name="divMaterial">
                              </div>
                            </div>
                            <div class="form-group row col-12">
                              <label class="col-form-label col-sm-auto">鍍鈦：</label>
                              <div class="col row" name="divTitanizing">
                              </div>
                            </div>
                            <div class="form-group row col-12">
                              <label class="col-form-label col-sm-auto">硬度：</label>
                              <div class="col row" name="divHardness">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="accordion col-12" id="materialRecog_${value}" hidden>
                    <div class="card">
                      <div class="card-header" id="collapseMaterialRecog_Heading">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseMaterialRecog_${value}" aria-expanded="false" aria-controls="collapseFilter">
                            材質辨識
                          </button>
                        </h2>
                      </div>
                      <div id="collapseMaterialRecog_${value}" class="collapse" aria-labelledby="collapseMaterialRecog_Heading" data-parent="#materialRecog_${value}">
                        
                      </div>
                    </div>
                  </div>
                  <div class="accordion col-12" id="noresult_${value}" style="display:none">
                    <div class="card">
                      <div class="card-header" id="collapseNoresult_Heading">
                        <h2 class="mb-0">
                          <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseNoresult_${value}" aria-expanded="false" aria-controls="collapseFilter">
                            查無結果請點此
                          </button>
                        </h2>
                      </div>
                      <div id="collapseNoresult_${value}" class="collapse" aria-labelledby="collapseNoresult_Heading" data-parent="#noresult_${value}">
                        <div class="card-body" style="height:550px;">
                          <div class="form-group row">
                            <div class="form-group row col-12">
                              <label class="col-form-label col-sm-auto">材質：</label>
                              <div class="col" id="noresultMaterial"></div>
                            </div>
                            <div class="form-group row col-12">
                              <label class="col-form-label col-sm-auto">鍍鈦：</label>
                              <div class="col" id="noresultTitanizing"></div>
                            </div>
                            <div class="form-group row col-12">
                              <label class="col-form-label col-sm-auto">硬度：</label>
                              <div class="col" id="noresultHardness"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                 
                  <div class="table-responsive">
                    <table id="tmpTable" class="table table-borderless" width=100%>
                      <thead>
                        <tr>
                          <th class="text-nowrap">客戶原圖</th>
                          <th class="text-nowrap">視角圖<input type="checkbox"  id="togglePic" data-toggle="toggle" data-on="顯示" data-off="隱藏"></th>
                          <th width=20% class="text-nowrap">註記</th>
                          <th width=20% class="text-nowrap">材質成本</th>
                          <th width=20% class="text-nowrap">原料成本</th>
                          <th>相似度</th>
                          <th>勾選</th>
                        </tr>
                      </thead>
                      <tbody id="divImage_${value}">
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            `);
          compareObj[value] = [];
          window.sharedVariable = {
            file_id: id,
            file_id_dest: id,
          };
          // $(`#collapseMaterialRecog_${value}`).load(`/materialrecog`);
        });
        $(function() {
          $('#togglePic').bootstrapToggle({
            on: '顯示',
            off: '隱藏',
            size: 'small',
            onstyle: 'success',
            offstyle: 'danger'
          });
        })


        instanceYear = new SelectPure('[name="divYear"]', {
          options: year,
          multiple: true,
          autocomplete: true,
          icon: "fa fa-times",
          inlineIcon: false,
          classNames: {
            select: "select-pure__select",
            dropdownShown: "select-pure__select--opened",
            multiselect: "select-pure__select--multiple",
            label: "select-pure__label",
            placeholder: "select-pure__placeholder",
            dropdown: "select-pure__options",
            option: "select-pure__option",
            autocompleteInput: "select-pure__autocomplete",
            selectedLabel: "select-pure__selected-label",
            selectedOption: "select-pure__option--selected",
            placeholderHidden: "select-pure__placeholder--hidden",
            optionHidden: "select-pure__option--hidden",
          },
          onChange: value => {
            process_resultMatch(process_id)
          },
        });
        // console.log(year)
        // console.log(material)

        instanceMaterial = new SelectPure('[name="divMaterial"]', {
          options: material,
          multiple: true,
          autocomplete: true,
          icon: "fa fa-times",
          inlineIcon: false,
          classNames: {
            select: "select-pure__select",
            dropdownShown: "select-pure__select--opened",
            multiselect: "select-pure__select--multiple",
            label: "select-pure__label",
            placeholder: "select-pure__placeholder",
            dropdown: "select-pure__options",
            option: "select-pure__option",
            autocompleteInput: "select-pure__autocomplete",
            selectedLabel: "select-pure__selected-label",
            selectedOption: "select-pure__option--selected",
            placeholderHidden: "select-pure__placeholder--hidden",
            optionHidden: "select-pure__option--hidden",
          },
          onChange: value => {
            process_resultMatch(process_id)
          },
        });


        console.log('in1')




        instanceTitanizing = new SelectPure('[name="divTitanizing"]', {
          options: titanizing,
          multiple: true,
          autocomplete: true,
          icon: "fa fa-times",
          inlineIcon: false,
          classNames: {
            select: "select-pure__select",
            dropdownShown: "select-pure__select--opened",
            multiselect: "select-pure__select--multiple",
            label: "select-pure__label",
            placeholder: "select-pure__placeholder",
            dropdown: "select-pure__options",
            option: "select-pure__option",
            autocompleteInput: "select-pure__autocomplete",
            selectedLabel: "select-pure__selected-label",
            selectedOption: "select-pure__option--selected",
            placeholderHidden: "select-pure__placeholder--hidden",
            optionHidden: "select-pure__option--hidden",
          },
          onChange: value => {
            process_resultMatch(process_id)
          },
        });

        instanceHardness = new SelectPure('[name="divHardness"]', {
          options: hardness,
          multiple: true,
          autocomplete: true,
          icon: "fa fa-times",
          inlineIcon: false,
          classNames: {
            select: "select-pure__select",
            dropdownShown: "select-pure__select--opened",
            multiselect: "select-pure__select--multiple",
            label: "select-pure__label",
            placeholder: "select-pure__placeholder",
            dropdown: "select-pure__options",
            option: "select-pure__option",
            autocompleteInput: "select-pure__autocomplete",
            selectedLabel: "select-pure__selected-label",
            selectedOption: "select-pure__option--selected",
            placeholderHidden: "select-pure__placeholder--hidden",
            optionHidden: "select-pure__option--hidden",
          },
          onChange: value => {
            process_resultMatch(process_id)
          },
        });

        getcommon_material();
        processinterval = [];

        if (firsttime == true) {
          resetquotationMaterial()
          resetquotationTitanizing()
          resetquotationHardness()
          inquotationMaterial()
          firsttime = false;
        }

        $.each(processArr, function(key, value) {
          process_id = processArr[key];
          processinterval[process_id] = setTimeout(process_resultMatch(process_id), 3000)
        })

        function process_resultMatch(process_id) {
          $.ajax({
            url: `/develop/Match/${process_id}`,
            type: 'get',
            data: {
              threshold: $('#selectThreshold').val(),
              amount: $('#selectAmount').val(),
              module_name: '研發',
              year: instanceYear.value(),
              material: instanceMaterial.value(),
              titanizing: instanceTitanizing.value(),
              hardness: instanceHardness.value(),
            },
            success: function(response) {
              // console.log($(`#divImage_${focusID}  #divImageResultMatch_${focusItemID} td [name="inputComment"]`))
              if ($(`#divImage_${focusID}  #divImageResultMatch_${focusItemID} td [name="inputComment"]`).length == 1) {
                $(`#divImage_${focusID}  #divImageResultMatch_${focusItemID} td [name="inputComment"]`).focus();
              }
              let selector_not = ``;
              $(response.result).each(function() {
                selector_not += `:not(#divImageResultMatch_${this.id})`;
              });

              $(`#divImage_${response.id} [id*=divImageResultMatch_]${selector_not}`).each(function() {
                $(this).remove();
              })
              $(response.result).each(function(index) {
                responseItem = this;
                // console.log(response.id)

                // console.log(compareArr.indexOf(responseItem.id))
                let comment = null;
                let comment_other = '';
                if (isJson(this.comment)) {
                  $(JSON.parse(this.comment)).each(function() {
                    if (this.module_name == '技術') {
                      comment = this.comment
                    } else if (this.module_name != null && this.comment != null) {
                      comment_other += `<p>${this.module_name}：${this.comment}</p>`;
                    }
                  })
                }
                let title = "";
                $(response.process.result[index].processes).each(function() {
                  title = this.零件名稱
                })

                let crops = $(`
                    <tr> 
                    </tr> 
                `);
                if (isJson(this.crop_ids)) {
                  $(JSON.parse(this.crop_ids)).each(function() {
                    if (!crops_arr.hasOwnProperty(this.crop_id)) {
                      crops_arr[this.crop_id] = new Object();
                    }
                    if (this.source != null)
                      crops_arr[this.crop_id][this.source] = this.confidence;
                  })
                  $(JSON.parse(this.crop_ids)).each(function() {
                    if ($(crops).find(`[src="/fileCrop/${this.crop_id}"]`).length == 0)
                      $(crops).append(`<td><img style="height:100px;width:auto" src="/fileCrop/${this.crop_id}" onclick="getConfidence(${this.crop_id},${this.source})" class="figure-img img-fluid img-thumbnail rounded w-auto" alt="..." /></td>`);
                  })
                }
                let materials = [];
                $(response.process.result[index].material).each(function() {
                  let row_material = this;
                  $(material).each(function() {
                    if (this.label == row_material.材質) {
                      materials.push(this.value)
                      return false;
                    }
                  })
                })
                // console.log(materials)
                crop_materials[`process_id_${response.id}_crop_id_${responseItem.id}`] = materials;

                let stuff = "";
                $(response.process.result[index].stuff).each(function() {
                  if (this.材料 != null)
                    stuff += this.材料 + " " + this.需領用量 || 0;
                })
                // console.log(crops)
                let file_id = id
                let process_obj = $("<div></div>");
                if ($(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id}`).length == 0) {
                  var $boolAppend = false;
                  var $tmpAppend = `
                    <tr name="divImageResultMatch${response.id}" id="divImageResultMatch_${responseItem.id}" data-process_id="${response.id}" data-crop_id="${responseItem.id}"  data-avg="${Number.parseFloat(responseItem.avg).toFixed(2)}" >
                    
                      <td >
                        
                        <img style="height:auto;min-width:200px" src="/file/${responseItem.fileID}" data-type="two" data-img2="${file_id}" class="figure-img img-fluid img-thumbnail rounded" alt="..." />
                      </td>
                      <td class="text-nowrap">
                        <div class="collapse hide" name="collapsePic">
                          <table style="width:50vw;">
                            
                              ${crops[0].outerHTML}
                            
                          </table>
                        </div>
                      
                      </td>
                      <td>
                        <div class="form-inline">
                          <input value="${responseItem.comment||''}" onfocus="inputFocus(${response.id},${responseItem.id})" type="text" value="${comment!=null?comment:''}" class="form-control" name="inputComment" data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}" data-chinese="註記"/>
                        </div>
                      </td>
                      <td class="text-nowrap">
                        <div name="divmultipleselect">
                          <div class="form-group row col-12">
                            <label class="col-form-label col-sm-auto">報價單材質：</label>
                            <div class="col-12" name="quotationMaterial">
                            </div>
                          </div>
                          <div class="form-group row col-12">
                            <label class="col-form-label col-sm-auto">報價單鍍鈦：</label>
                            <div class="col-12" name="quotationTitanizing">
                            </div>
                          </div>
                          <div class="form-group row col-12">
                            <label class="col-form-label col-sm-auto">報價單硬度：</label>
                            <div class="col-12" name="quotationHardness">
                            </div>
                          </div>
                        </div>
                        <p hidden>原始成本：${Math.floor(Math.random()*10000)}</p>
                        <input style="min-width:200px" value="${responseItem.material||''}" placeholder="追加成本" onfocus="inputFocus(${response.id},${responseItem.id})" type="number" value="${comment!=null?comment:''}" class="form-control" name="inputMaterial" data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}" data-chinese="材質追加成本"/>
                      </td>
                      <td class="text-nowrap">
                        ${stuff!=""?stuff:''}
                        ${response.process.result[index].origin == null?'':'<p>原始成本：'+response.process.result[index].origin+'</p>'}
                        <input style="min-width:200px" value="${responseItem.stuff||''}" placeholder="追加成本" onfocus="inputFocus(${response.id},${responseItem.id})" type="number" value="${comment!=null?comment:''}" class="form-control" name="inputStuff" data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"  data-chinese="原料追加成本"/>
                      </td>
                      <td style="min-width:200px">相似度：${Number.parseFloat(responseItem.avg).toFixed(2)}%</td>
                      <td><input type="checkbox" class="form-control" ${responseItem.comment!=null?'checked':''} name="inputCheck"  data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"/></td>
                    </tr>
                  `;
                  // if($(`[name="divImageResultMatch${response.id}"]`).length == 0){
                  //   $(`#divImage_${response.id}`).append($tmpAppend);
                  //   $boolAppend = true;
                  // }else{
                  $(`[name="divImageResultMatch${response.id}"]`).each(function() {
                    if ($(this).data('avg') <= Number.parseFloat(responseItem.avg).toFixed(2)) {
                      $($tmpAppend).insertBefore((this))
                      $boolAppend = true;
                      return false;
                    }
                  });
                  // }
                  if (!$boolAppend) {
                    $(`#divImage_${response.id}`).append($tmpAppend);
                    new SelectPure(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id} [name="quotationMaterial"]`, {
                      options: material,
                      multiple: true,
                      autocomplete: true,
                      icon: "fa fa-times",
                      inlineIcon: false,
                      value: materials,
                      classNames: {
                        select: "select-pure__select",
                        dropdownShown: "select-pure__select--opened",
                        multiselect: "select-pure__select--multiple",
                        label: "select-pure__label",
                        placeholder: "select-pure__placeholder",
                        dropdown: "select-pure__options",
                        option: "select-pure__option",
                        autocompleteInput: "select-pure__autocomplete",
                        selectedLabel: "select-pure__selected-label",
                        selectedOption: "select-pure__option--selected",
                        placeholderHidden: "select-pure__placeholder--hidden",
                        optionHidden: "select-pure__option--hidden",
                      }
                    });

                    // if(responseItem.comment!=null){
                    //   inquotationMaterial = new SelectPure(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id} [name="quotationMaterial"]`, {
                    //       options: material,
                    //       multiple: true ,
                    //       autocomplete: true,
                    //       icon: "fa fa-times",
                    //       inlineIcon: false ,
                    //       value:materialArr,
                    //       onChange: value => { updatequotationMaterial(value) },
                    //       classNames: {
                    //           select: "select-pure__select",
                    //           dropdownShown: "select-pure__select--opened",
                    //           multiselect: "select-pure__select--multiple",
                    //           label: "select-pure__label",
                    //           placeholder: "select-pure__placeholder",
                    //           dropdown: "select-pure__options",
                    //           option: "select-pure__option",
                    //           autocompleteInput: "select-pure__autocomplete",
                    //           selectedLabel: "select-pure__selected-label",
                    //           selectedOption: "select-pure__option--selected",
                    //           placeholderHidden: "select-pure__placeholder--hidden",
                    //           optionHidden: "select-pure__option--hidden",
                    //         }
                    //   });
                    // }

                  }

                }
                $(`#divImage_${response.id}`).append($(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id}`));

              })
              resetquotationMaterial()
              resetquotationTitanizing()
              resetquotationHardness()

              // inquotationMaterial();


              $(response.status).each(function() {
                if (this.status == "stop") {
                  // clearTimeout(processinterval[response.id]);
                } else {
                  // processinterval[response.id] = setTimeout(process_resultMatch(response.id), 3000)
                }
              })
              clearTimeout(processinterval[response.id]);
            }
          })
        }
      }
    })

  }


  function nextpage() {
    $.ajax({
      url: '/file/progress',
      type: 'post',
      data: {
        url: window.location.href,
        id: id
      },
      dataType: 'json',
      success: function(response) {
        $(response).each(function() {
          window.location.href = `${this.url}?id=${file_id}&file_id_dest=${file_id_dest}`
        })
      }
    })
  }

  function getConfidence(crop_id, source) {
    setTimeout(function() {
      $('#imgModal').find('.modal-body').html(``);
      let crops = $('<div class="card-group"></div>')
      $.each(crops_arr[crop_id], function(key, value) {
        crops.append(`
        <div class="card">
          <img src="/fileCrop/${key}" class="card-img-top" alt="...">
          <div class="card-body text-nowrap">
            ${value.toFixed(2)}%
          </div>
        </div>
        `);
      })
      $('#imgModal').find('.modal-body').append(crops[0].outerHTML);
    }, 2000)
  }

  function sendemail(modules) {
    let content = `
    報價編號${id} 研發部門已${redo?"重新":"完成"}填寫
    檢視連結如下：{部門連結}`;
    $.ajax({
      url: `/business/dispatch/email`,
      type: 'post',
      data: {
        id: id,
        content: content,
        message: content,
        module: modules,
        // deadline:$('#inputDeadline').val().replace('T',' ')
      },
      dataType: 'json',
      success: function(response) {
        // if (moduleArr.length > 0) {
        //   // sendemail(moduleArr)

        // } else {
        nextpage()
        // }
      },
      error: function() {
        nextpage()

      }
    })

  }

  function buttonPass() {
    let file_id = id;
    $('#basicModal').find('.modal-header').text(``);
    $('#basicModal').find('.modal-body').text(`請稍等...`);
    $('#basicModal').find('.modal-footer').text(``);
    $('#basicModal').modal('show');
    console.log(newmodule_id)
    $.ajax({
      url: `/notify/finish/module`,
      type: 'get',
      data: {
        finish: newmodule_id,
        file_id: file_id,

      },
      dataType: 'json',
      success: function(response) {

        moduleArr = []
        $.each(response, function() {
          moduleArr.push(this.notify)
        })
        // console.log(moduleArr)
        sendemail(moduleArr)
      }
    })
  }


  $(document).on('change', '[name=inputCheck]', function() {
    let element = this;
    resetquotationMaterial()
    resetquotationTitanizing()
    resetquotationHardness()
    if ($(this).prop('checked')) {
      $.ajax({
        url: `/components/comment`,
        type: 'post',
        data: {
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          confidence: $(element).attr('data-confidence'),
          comment: $(element).closest('tr').find('[name=inputComment]').val(),
          material: $(element).closest('tr').find('[name=inputMaterial]').val(),
          stuff: $(element).closest('tr').find('[name=inputStuff]').val(),
          module_name: '研發',
          process: ''
        },
      })
    } else {
      $.ajax({
        url: `/components/comment`,
        type: 'delete',
        data: {
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          module_name: '研發'
        },
      })
    }
  })
  $(document).on('input', '[name=inputComment],[name=inputMaterial],[name=inputStuff]', function() {
    let element = this;
    if ($(element).closest('tr').find('[name=inputCheck]').prop('checked')) {
      $.ajax({
        url: `/components/comment`,
        type: 'post',
        data: {
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          confidence: $(element).attr('data-confidence'),
          comment: $(element).closest('tr').find('[name=inputComment]').val(),
          material: $(element).closest('tr').find('[name=inputMaterial]').val(),
          stuff: $(element).closest('tr').find('[name=inputStuff]').val(),
          module_name: '研發',
          process: ''
        },
      })
    }
  })

  function getComponents() {
    $.ajax({
      url: `/business/components`,
      type: 'get',
      dataType: 'json',
      success: function(response) {}
    });
  }


  function getListState(file_id) {
    $.ajax({
      url: `/file/state/${file_id}`,
      type: 'get',
      data: {
        module_name: '研發'
      },
      dataType: 'json',
      success: function(response) {
        $(response.file_information).each(function() {
          $('#spanUploadTime').text(this.upload_time)
          $('#spanFileId').text(this.order_name)
          $('#imgThumbnail').attr('src', `/file/${this.id}`)
          $('#tdThumbnailDest').html(`
              <img src="/file/${file_id_dest}" class="img-thumbnail" />
          `)
          // $('[name="divlock"]').show();



        })

        $('#list-tab-business').html(``);
        let list_tab = $(`<ul class="list-group list-group-horizontal w-100"></ul>`);
        let list_color = null;
        $(response.state).each(function(index) {
          if(this.id==5 && this.update_time!=null && this.later!=false){
            redo = true
          }
          if (index == 0 || list_color != this.module_color) {
            list_color = this.module_color;
            if (index != 0)
              if (this.module_name == '研發')
                $('#list-tab-business').append($(list_tab)[0].outerHTML);
              else
                $('#list-tab-other').append($(list_tab)[0].outerHTML);
            list_tab = $(`
              <div class="alert alert${this.module_color} form-group d-inline-flex col-12" role="alert">
                <span class="col-auto">${this.module_name}</span>
                <div class="list-group list-group-horizontal col" role="tablist">
                  <ul class="list-group list-group-horizontal">
                  </ul>
                </div>
              </div>
            `);
          }
          if (this.progress.indexOf('完成報價') != -1) {
            console.log(100)
            $('#list-tab-end').append(`
              <div class="alert alert${this.module_color} form-group d-inline-flex col-12" role="alert"">
                <span class="col-auto">${this.module_name}</span>
                <div class="list-group list-group-horizontal col" role="tablist">
                  <ul class="list-group list-group-horizontal">
                    <li class="list-group-item list-group-item${this['update_time']!=null?this.module_color:''} flex-fill text-nowrap ${location.href.indexOf(this.url+'?')!=-1?'active':''}" ${this.redirect?``:`onclick="javascript:location.href='${this['url']}?id=${file_id}&file_id_dest=${file_id_dest}'"`}>${this['progress']}</li>
                  </ul>
                </div>
              </div>
            `);
          } else {
            $(list_tab).find('ul').append(`
              <li class="list-group-item list-group-item${this['update_time']!=null?this.module_color:''} flex-fill text-nowrap ${location.href.indexOf(this.url+'?')!=-1?'active':''}" ${this.redirect?``:`onclick="javascript:location.href='${this['url']}?id=${file_id}&file_id_dest=${file_id_dest}'"`}>${this['progress']}</li>
            `);
          }
          // if(index==response.state.length-1){
          // $('#list-tab-other').append($(list_tab)[0].outerHTML);
          // }
        })
        // $('#divStation').empty();
        // $(response.station).each(function(){
        //   let row = this;
        //   let tr = $(`<tbody></tbody>`);
        //   $(this.station).each(function(index){
        //     let information = $(`<div></div>`);
        //     $.each(this,function(key,value){
        //       if(key!="crop_id" && value != null)
        //         $(information).append(`
        //           <p>${key}：${value}</p>
        //         `);
        //     })
        //     $(tr).append(`
        //       <tr>
        //         <td width=50%><img src="/fileCrop/${this.crop_id}" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
        //         <td width=50%>${index%2==0?'前沖棒':'後沖棒'}</td>
        //         <td class="text-nowrap">${information.html()}</td>
        //       </tr>
        //     `)
        //   })
        //   $('#divStation').append(`
        //     <div class="card shadow mb-4 form-group">
        //       <div class="card-header">
        //         ${this.name}
        //       </div>
        //       <div class="card-body">
        //         <div class="row">
        //           <label class="col-form-label col-1">相似度結果</label>
        //           <div class="col table-responsive">
        //             <table class="table table-borderlress">
        //               <tbody>
        //               ${tr.html()}
        //               </tbody>
        //             </table>
        //           </div>
        //         </div>
        //       </div>
        //     </div>
        //   `);
        // })
      }
    })
  }

  function getResult(file_id) {
    let process_id = null;
    $.each(response['process'], function(key, value) {
      if (key == 'process_id') {
        process_id = value;
      }
    });
    if (process.length != 0) {
      $(process).each(function(index) {
        clearInterval(process[index])
      })
      $('#divImage').empty();
    }
    console.log(process)
    process[process_id] = setInterval(() => {
      // console.log(process_id)

      $.ajax({
        url: `/components/Match/${process_id}`,
        type: 'get',
        success: function(response) {
          $(response).each(function() {
            if ($(`#divImageResultMatch_${this.id}`).length == 0) {
              $('#divImage').append(`
              <div class="form-group row col-sm-4" id="divImageResultMatch_${this.id}">
                <div class="col-12">
                  <img src="/file/${this.id}" class="figure-img img-fluid img-thumbnail rounded" alt="..." />
                </div>
                <label class="col-form-label col-12">${responseItem.avg}</label>
              </div>
            `);
            }
          })
          //   $('#tableCrops').find('tbody').empty();
          //   let components_list = $(`<select></select>`);
          //   $(response['crops']).each(function(index){
          //     $(components_list).append(`
          //       <option value="${(index+1)}" ${(index==0?"selected":'')}>${(index+1)}</option>
          //     `);
          //   })
          //   $(response['crops']).each(function(index){
          //     $('#tableCrops').find('tbody').append(`
          //       <tr>
          //         <td><img src="/fileCrop/${this.id}" class="img-fluid img-thumbnail"/></td>
          //         <td><select class="form-control" name="selectCrops" data-id="${this.id}">${components_list.html()}</td>
          //       </tr>
          //     `);
          // });
        }
      })
    }, 2000)
  }
</script>