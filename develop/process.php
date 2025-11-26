<?php include(__DIR__ . '/../basic/header.html'); ?>
<style>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
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

  .chartWrapper {
    position: relative;
  }

  .chartWrapper>canvas {
    position: absolute;
    left: 0;
    top: 0;
    pointer-events: none;
  }

  .chartAreaWrapper {
    width: 1200px;
    overflow-x: scroll;
  }
</style>

<script src="/dropzone/dist/dropzone.js"></script>
<link rel="stylesheet" href="/dropzone/dist/dropzone.css">
<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<link rel="stylesheet" href="/vendor/select-pure/dist/select-pure.css">
<link href="/static/css/chart_rocess_inside.css" rel="stylesheet" />

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
              研發站
            </div>
            <div class="card-body">
              <div class="row">
                <label class="col-form-label col-1">相似度結果</label>
                <div class="col table-responsive row">
                  <div class="col-sm-auto form-group row">
                    <label class="col-form-label col-auto">相似度門檻：40%</label>
                    <label class="col-form-label col-auto">參考數量：10張</label>
                  </div>
                  <table class="table table-borderlress">
                    <tbody>
                      <tr>
                        <td width=50%><img src="/fileCrop/1510" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
                        <td width=50%>前沖棒</td>
                        <td class="text-nowrap">這是研發站來的註記</td>
                      </tr>
                      <tr>
                        <td width=50%><img src="/fileCrop/1510" class="figure-img img-fluid img-thumbnail rounded" alt="..."></td>
                        <td width=50%>後沖棒</td>
                        <td class="text-nowrap">這是研發站來的註記</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="row">
                <label class="col-form-label col-auto">材質：</label>
                <label class="col-form-label col-auto">MIL-TIP、R3-MIL60R、R4-MIL60R</label>
              </div>
            </div>
          </div>
          <div class="card shadow mb-4 form-group">
            <div class="card-header">
              製圖站
            </div>
            <div class="card-body">

              <div class="row">
                <label class="col-form-label col-1">相似度結果</label>
                <div class="col table-responsive row">
                  <div class="col-sm-auto form-group row">
                    <label class="col-form-label col-auto">相似度門檻：70%</label>
                    <label class="col-form-label col-auto">參考數量：20張</label>
                  </div>
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
              生管站
            </div>
            <div class="card-body">
              <div class="col-sm-auto form-group row">
                <label class="col-form-label col-auto">相似度門檻：50%</label>
                <label class="col-form-label col-auto">參考數量：10張</label>
              </div>
              <div class="row">
                <label class="col-form-label col-auto">外包建議：</label>
                <label class="col-form-label col-auto">生管的外包建議</label>
              </div>
              <div class="row">
                <label class="col-form-label col-auto">外包成本：</label>
                <label class="col-form-label col-auto">15000</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> -->
</div>

<div class="row">
  <div id="chart_rocess_inside"></div>
  <!--   <div class="col-12 mb-4">
    <div class="card shadow mb-4 h-100">
      <div class="card-header">廠內製程數量趨勢圖
			<i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="查看廠內製程趨勢"></i>

      </div>
      <div class="card-body">
        <div class="row">
          <div class="col">

            <label class="row">目前廠內狀況</label>
            <a class="btn btn-primary" data-toggle="collapse" href="#collapseAll" role="button" aria-expanded="false" aria-controls="collapseAll">篩選</a>
            <div class="row">
              <div class="col">
                <div class="collapse multi-collapse" id="collapseAll">
                  <div class="card card-body">
                    <div id="allmultipleselect">

                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <label class="col-form-label col-3">起</label>
              <div class="col-3">
                <input type="datetime-local" name="allOutsourcer" data-type="start" class="form-control" />
              </div>
              <label class="col-form-label col-3">迄</label>
              <div class="col-3">
                <input type="datetime-local" name="allOutsourcer" data-type="end" class="form-control" />
              </div>
            </div>
            <div class="row overflow-auto" >
            
              <canvas id="BarChart1"  width="12000"></canvas>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div> -->
</div>
<div class="row">
  <!-- search -->
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header">
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-12 col-xl-12">
            <div class="card shadow mb-4">
              <div class="card-group d-flex flex-row flex-nowrap overflow-auto" id="cardTextBox"></div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8 col-md-8 col-lg-8 col-xl-8">
            <div class="card shadow mb-4">
              <div class="card-header">
                客戶圖面
                <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="查看研發註記"></i>

              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-12"><button type="button" class="btn btn-primary" data-type="false" onclick="showTextBoxAll(this)" id="btnshowTextBoxAll">隱藏標記</button></div>
                </div>
                <div class="row">
                  <div id="divpaint" class="col-12 overflow-auto">
                    <!-- <canvas id="bcPaintCanvas" class="border border-dark rounded" ></canvas> -->
                  </div>
                  <canvas id="bcPaintCanvas" class="border border-dark rounded"></canvas>
                </div>

              </div>
            </div>
          </div>
          <div class="col-4 col-md-4 col-lg-4 col-xl-4">
            <div class="card shadow mb-4">
              <div class="card-header">
                製程成本
                <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="填寫製程加工成本"></i>

              </div>
              <div class="card-body">
                <div class="form-group row">
                  <label for="inputTotal" class="col-sm-4 col-form-label">加工總成本</label>
                  <div class="col-sm-8">
                    <input type="number" disabled class="form-control" id="topinputTotal">
                  </div>
                  <div name="divtotal" data-type="top">
                    <button type="button" class="btn btn-secondary" name="btnaddcost">+</button>
                  </div>

                </div>

              </div>
            </div>

            <div class="form-group row col-12 ">
              <a type="button" href="#selectThreshold" class="btn btn-primary float-right">前往相似度比對區</a>
            </div>
            <div class="card shadow mb-4">
              <div class="card-header">
                製程追加成本
                <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="各製程追加成本及註記"></i>

              </div>
              <div class="card-body">
                <div class="form-group row">
                  <label for="inputTotal" class="col-sm-4 col-form-label">追加總成本</label>
                  <div class="col-sm-8">
                    <input type="number" disabled class="form-control" id="inputreview">
                  </div>
                  <button type="button" class="btn btn-secondary" name="" onclick="inclickAddProcess()">+</button>

                </div>
                <ul id="divreview"></ul>

              </div>
            </div>
            <div class="form-group row col-12">
              <button type="button" class="btn btn-primary float-right" onclick="buttonPass()">下一步</button>
            </div>
            <div class="form-group row col-12">
              <label class="col-form-label col-sm-auto">註記：</label>
              <div class="col" name="divFileComment" data-type="top">
                <textarea class="form-control" name="inputFileComment" rows="3"></textarea>
              </div>
            </div>
          </div>
          <div id="WeightConverter" readonly="false"></div>

        </div>
      </div>
    </div>
  </div>
</div>
<!-- <div class="row">
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header">
        報價單摘要
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>客戶圖縮圖</th>
                <th>相似客戶圖</th>
                <th>訂單資訊</th>
                <th>註記</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td width=10%>
                  <img class="img-thumbnail" id="imgThumbnail">
                </td>
                <td width=10% id="tdThumbnailDest">尚未找到相似的廠內圖</td>
                <td>
                  <p>客戶圖號：<span id="spanFileId">1</span></p>
                  <p>開單時間：<span id="spanUploadTime">2021/05/27</span></p>
                </td>
                <td id="tdComment">尚未有任何註記</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div> -->
<div class="row">
  <div class="col-12 mb-4 pb-5">
    <div class="card shadow mb-4 pb-5 h-100">
      <div class="card-header">製程修改
        <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="選擇要呈現的廠內圖數量可設定要呈現出的數量"></i>
      </div>
      <div class="card-body">
        <div class="row rows-col-1 rows-col-md-2">
          <div class="col">
            <div class="form-group row">
              <div class="col-sm-auto form-group">
                <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="註記的部分會在生管階段時看到，勾選的相似圖部分會顯示在各部門相似度結果，點選下一步後可送至生管進行製程確認"></i>
              </div>

              <div class="col-sm-auto form-group row">
                <label class="col-form-label col-auto">相似度門檻：</label>
                <div class="col-auto">
                  <select class="form-control" id="selectThreshold">
                    <option value="0">0%</option>
                    <option value="10">10%</option>
                    <option value="20">20%</option>
                    <option value="30">30%</option>
                    <option value="40">40%</option>
                    <option value="50">50%</option>
                    <option value="60">60%</option>
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
                <label class="col-form-label col-auto">，篩選結果：10 / 20 張</label>
              </div>
            </div>
          </div>

        </div>
        <div class="d-flex overflow-auto" id="divStationMaterial">
        </div>
        <datalist id="datalistOutresourcer">
          <option value="線切割"></option>
          <option value="放電"></option>
          <option value="其他加工費用"></option>
        </datalist>
        <div class="form-group row" id="divImage">
        </div>
        <div>
          <!-- <button type="button" class="btn btn-primary float-right" onclick="buttonPass()">下一步</button> -->
        </div>
      </div>
    </div>
  </div>
</div>
<?php include(__DIR__ . '/../basic/footer.html'); ?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="/vendor/select-pure/dist/select-pure.bundle.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>


<!-- <link rel="stylesheet" href="/resources/demos/style.css"> -->

<!-- <script src="https://code.jquery.com/jquery-1.12.4.js"></script> -->
<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> -->
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
  let modifyprocesslabelArr = ['加工順序', '製程代號', '製程名稱', '註記', '製程成本', '外包成本', '廠商']


  $(document).on('input', '[name="inputFileComment"]', function() {

    if ($(this).closest('[name="divFileComment"]').data('type') == "top") {
      $('[name="divFileComment"][data-type="down"]').html('');
      $('[name="divFileComment"][data-type="top"]').children().clone().appendTo('[name="divFileComment"][data-type="down"]');
    } else {
      $('[name="divFileComment"][data-type="top"]').html('');
      $('[name="divFileComment"][data-type="down"]').children().clone().appendTo('[name="divFileComment"][data-type="top"]');
    }
    insaveFileComment();

  });

  function inclickAddProcess() {
    $('[name="btnAddProcess"]').click();
  }

  function insaveFileComment() {
    $.ajax({
      url: `/file/file_comment`,
      type: 'post',
      data: {
        file_id: id,
        module_id: module_id,
        comment: $('[name="divFileComment"][data-type="top"]').find('[name="inputFileComment"]').val(),

      },
      success: function(response) {

      }
    });
  }

  let tmpcanvasarr = []
  let tmpmarkarr = []
  let tmpcanvasorg = ''

  function getcanvas() {

    $.ajax({
      url: `/file/file_comment/canvas`,
      type: 'get',
      data: {
        file_id: id,
        module_id: module_id,

      },
      success: function(response) {

        $(response).each(function() {
          $('[name="inputFileComment"]').val(this.comment)
        })
      }
    });
    $.ajax({
      url: `/file/file_comment/canvas`,
      type: 'get',
      data: {
        file_id: id,
        module_id: 2,

      },
      success: function(response) {

        var image = new Image()
        image.onload = function(e) {

          const tmpcanvas = document.getElementById('bcPaintCanvas');
          $('#bcPaintCanvas').css('background-image', `url(/file/${file_id_dest})`);
          $('#bcPaintCanvas').css('background-size', `100% 100%`);
          tmpcanvas.height = $('#divpaint').width() / e.path[0].width * e.path[0].height;
          tmpcanvas.width = $('#divpaint').width();
          $(response).each(function() {
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
        image.src = `/file/${file_id_dest}`;
      },
      complete: function(e) {
        $.ajax({
          url: `/file/file_comment/textbox`,
          type: 'get',
          data: {
            file_id: id,
          },
          success: function(response) {
            let tmpXArr = []
            let tmpYArr = []
            let tmpwidthArr = []
            let tmpheightArr = []
            $(response).each(function() {
              tmpcanvasarr.push(this.canvas);
              tmpmarkarr.push(this.mark);
              tmpXArr.push(parseInt(this.x || '0'));
              tmpYArr.push(parseInt(this.y || '0'));
              tmpwidthArr.push(parseInt(this.width || '0'));
              tmpheightArr.push(parseInt(this.height || '0'));
            })
            $(tmpcanvasarr).each(function(i) {
              $('#cardTextBox').append(`
                  <div class="card" style="min-width:200px">
                    <div class="card-body">
                      <button type="button" class="card-title btn btn-link" name="buttonTextBox" onclick="showTextBox(${i})">${i+1}</h5>
                      <input type="text" class="form-control" name="inputTextBox" data-id="${i}" data-x="${tmpXArr[i]}" data-y="${tmpYArr[i]}" data-width="${tmpwidthArr[i]}"  data-height="${tmpheightArr[i]}"  value="${tmpmarkarr[i]}" disabled />
                    </div>
                  </div>
                `);
            })
            setTimeout(function() {
              showTextBoxAll($('#btnshowTextBoxAll'))
              showTextBoxAll($('#btnshowTextBoxAll'))

            }, 5000);
          }
        });
      }
    });
  }

  function showTextBox(i) {
    let tmpcanvas = document.getElementById("bcPaintCanvas");
    let ctx = tmpcanvas.getContext("2d");
    let image = new Image();
    let element = $(`[name="inputTextBox"][data-id="${i}"]`)
    let tmpX = $(element).data('x')
    let tmpY = $(element).data('y')
    let tmptext = $(element).val();
    let tmpwidth = $(element).data('width');
    let tmpheight = $(element).data('height');
    let ratio = tmpcanvas.width / tmpwidth
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
    if ($('#btnshowTextBoxAll').data('type') == 'false') {
      let image = new Array();
      image[0] = new Image();
      image[0].onload = function() {
        ctx.clearRect(0, 0, tmpcanvas.width, tmpcanvas.height);
        ctx.drawImage(image[0], 0, 0, tmpcanvas.width, tmpcanvas.height);
      };
      image[0].src = tmpcanvasorg

      $(tmpcanvasarr).each(function(index, value) {
        let element = $(`[name="inputTextBox"][data-id="${index}"]`)
        let tmpX = $(element).data('x')
        let tmpY = $(element).data('y')
        let tmptext = $(element).val();
        let tmpwidth = $(element).data('width');
        let tmpheight = $(element).data('height');
        let ratio = tmpcanvas.width / tmpwidth

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

      $('#btnshowTextBoxAll').data('type', 'true')

    } else {
      let image = new Image();
      image.onload = function() {
        ctx.clearRect(0, 0, tmpcanvas.width, tmpcanvas.height);
        ctx.drawImage(image, 0, 0, tmpcanvas.width, tmpcanvas.height);
      };
      image.src = tmpcanvasorg

      $('#btnshowTextBoxAll').data('type', 'false')

    }
  }

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
  var file_id_dest = url.searchParams.get("id");
  var module_id;
  var module_name = '技術';
  var allState = [];
  let orig_tech_width = 200;
  let itemno;
  

  $(function() {
    $('#WeightConverter').attr('file_id',id);
    import("/static/weightConverter/js/main.a12e174d.js");
    getInfo();
    getModule();
    getAllOutsourcer();
    $('[data-toggle="tooltip"]').tooltip();

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
      // getmodifyprocess()
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
          itemno = this.itemno
        })
        $(`<label>${itemno=="001"?"新圖":"舊圖"}</label>`).insertAfter("#btnshowTextBoxAll");
        // 

      }
    });
  }

  /*   $(document).on('change', '[name="allOutsourcer"]', function() {
      getAllOutsourcer()
    }); */
  function getAllOutsourcer() {
    $('#chart_rocess_inside').attr('file_id', id);
    import('/static/js/chart_rocess_inside.js')
  }
  /* 
    function getAllOutsourcer() {
      let start = $('[name="allOutsourcer"][data-type="start"]').val().replace("T", " ")
      let end = $('[name="allOutsourcer"][data-type="end"]').val().replace("T", " ")
      $.ajax({
        url: `/modifyprocess/all`,
        type: 'get',
        data: {
          start: start,
          end: end,
          id: id,
          type: 'inside'
        },
        success: function(response) {
          responseall = response;
          let datasets = new Object();
          let finishdatasets = new Object();
          let allmultipleselectObj = [];

          let names = [];
          $.each(response.finish, function() {

            names.indexOf(this.製程名稱) == -1 ? names.push(this.製程名稱) : '';
            if (!finishdatasets.hasOwnProperty(this.製程名稱)) {
              finishdatasets[this.製程名稱] = [];
              for (let index = 0; index < response.length; index++) {
                finishdatasets[this.製程名稱][index] = 0;
              }
            }
            finishdatasets[this.製程名稱][names.indexOf(this.製程名稱)] = this.count;


            let tmpObj = {
              "label": this.製程名稱,
              "value": this.製程代號,
            }
            let addbool = false;
            $(allmultipleselectObj).each(function() {
              if (this.label == tmpObj.label) {
                addbool = true;
                console.log('same')
                return false;
              }
            })
            if (addbool == true) return;

            allmultipleselectObj.push(tmpObj)
          });
          console.log(finishdatasets)


          $.each(response.unfinish, function() {
            names.indexOf(this.name) == -1 ? names.push(this.name) : '';
            if (!datasets.hasOwnProperty(this.name)) {
              datasets[this.name] = [];
              for (let index = 0; index < response.length; index++) {
                datasets[this.name][index] = 0;
              }
            }
            datasets[this.name][names.indexOf(this.name)] = this.count;


            let tmpObj = {
              "label": this.name,
              "value": this.code,
            }
            let addbool = false;
            $(allmultipleselectObj).each(function() {
              if (this.label == tmpObj.label) {
                addbool = true;
                return false;
              }
            })
            if (addbool == true) return;
            // console.log(tmpObj)
            allmultipleselectObj.push(tmpObj)
          });

          // console.log(datasets);
          // console.log(names);
          allOutsourcerChart.destroy();
          allOutsourcerdata = {
            labels: names,
            // labels: [],
            datasets: []
          }
          allOutsourcerconfig = {
            type: 'bar',
            data: allOutsourcerdata,
            options: {
              "responsive": true,
              aspectRatio: 0.75,
              "animation": {
                "duration": 1,
                "onComplete": function() {
                  var chartInstance = this.chart,
                    ctx = chartInstance.ctx;

                  ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                  ctx.textAlign = 'center';
                  ctx.textBaseline = 'bottom';

                  this.data.datasets.forEach(function(dataset, i) {
                    var meta = chartInstance.controller.getDatasetMeta(i);
                    meta.data.forEach(function(bar, index) {
                      var data = dataset.data[index];
                      ctx.fillText(data, bar._model.x, bar._model.y - 5);
                    });
                  });
                }
              },
              scales: {
                xAxes: [{
                  time: {
                    unit: 'date'
                  },
                  gridLines: {
                    display: false,
                    drawBorder: false
                  },
                  ticks: {
                    maxTicksLimit:  names.length,
                    // maxTicksLimit: names.length > 20 ? 20 : names.length,
                  }
                }],
                yAxes: [{
                  ticks: {
                    beginAtZero: true
                  }
                }]
              }
            },
          };
          allOutsourcerChart = new Chart(
            document.getElementById('BarChart1'),
            allOutsourcerconfig
          );
          $.each(datasets, function(key, value) {
            var newDataset = {
              label: key + '(已處理)',
              data: value,
              backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
              borderWidth: 1,
              barPercentage: 0.5,
              barThickness: 6,
              maxBarThickness: 8,
              minBarLength: 2,
              stack: key
            }
            allOutsourcerdata.datasets.push(newDataset);
            // historyOutsourcerdata.labels.push(this.name);
            allOutsourcerChart.update();
          });
          $.each(finishdatasets, function(key, value) {
            var newDataset = {
              label: key + '(未處理)',
              data: value,
              backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
              borderWidth: 1,
              barPercentage: 0.5,
              barThickness: 6,
              maxBarThickness: 8,
              minBarLength: 2,
              stack: key
            }
            allOutsourcerdata.datasets.push(newDataset);
            // historyOutsourcerdata.labels.push(this.name);
            allOutsourcerChart.update();
          });

          $('#allmultipleselect').html('');
          let temperarymultipleselect = new SelectPure(`#allmultipleselect`, {
            options: allmultipleselectObj,
            multiple: true,
            autocomplete: true,
            icon: "fa fa-times",
            inlineIcon: false,
            value: [],
            onChange: value => {
              filterall(value)
            },
            classNames: {
              select: "select-pure__select",
              dropdownShown: "select-pure__select--opened",
              multiselect: "select-pure__select--multiple",
              label: "select-pure__label overflow-auto",
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

          return

        }
      });
    } */
  /* 
    function filterall(val) {
      allbool = val;
      let datasets = new Object();
      let finishdatasets = new Object();
      let allmultipleselectObj = [];
      let names = [];
      // responsehistory = response.finish

      // $.each(responsehistory.unfinish, function() {
      //   names.indexOf(this.name) == -1 ? names.push(this.name) : '';
      //   if (!datasets.hasOwnProperty(this.outsourcer)) {
      //     datasets[this.outsourcer] = [];
      //     for (let index = 0; index < response.length; index++) {
      //       datasets[this.outsourcer][index] = 0;
      //     }
      //   }
      //   datasets[this.outsourcer][names.indexOf(this.name)] += 1;


      // });

      $.each(responseall.unfinish, function() {
        if (allbool == []) {
          names.indexOf(this.name) == -1 ? names.push(this.name) : '';
          if (!datasets.hasOwnProperty(this.name)) {
            datasets[this.name] = [];
            for (let index = 0; index < responseall.length; index++) {
              datasets[this.name][index] = 0;
            }
          }
          datasets[this.name][names.indexOf(this.name)] = this.count;


        } else {
          if (allbool.includes(this.code)) {
            names.indexOf(this.name) == -1 ? names.push(this.name) : '';
            if (!datasets.hasOwnProperty(this.name)) {
              datasets[this.name] = [];
              for (let index = 0; index < responseall.length; index++) {
                datasets[this.name][index] = 0;
              }
            }
            datasets[this.name][names.indexOf(this.name)] = this.count;

          }
        }



      });

      $.each(responseall.finish, function() {
        if (allbool == []) {
          names.indexOf(this.製程名稱) == -1 ? names.push(this.製程名稱) : '';
          if (!finishdatasets.hasOwnProperty(this.製程名稱)) {
            finishdatasets[this.製程名稱] = [];
            for (let index = 0; index < responseall.length; index++) {
              finishdatasets[this.製程名稱][index] = 0;
            }
          }
          finishdatasets[this.製程名稱][names.indexOf(this.製程名稱)] = this.count;


        } else {
          if (allbool.includes(this.製程代號)) {
            names.indexOf(this.製程名稱) == -1 ? names.push(this.製程名稱) : '';
            if (!finishdatasets.hasOwnProperty(this.製程名稱)) {
              finishdatasets[this.製程名稱] = [];
              for (let index = 0; index < responseall.length; index++) {
                finishdatasets[this.製程名稱][index] = 0;
              }
            }
            finishdatasets[this.製程名稱][names.indexOf(this.製程名稱)] = this.count;

          }
        }



      });

      console.log('infilter')
      console.log(names)
      console.log(datasets)

      allOutsourcerChart.destroy();
      allOutsourcerdata = {
        labels: names,
        datasets: []
      }
      allOutsourcerconfig = {
        type: 'bar',
        data: allOutsourcerdata,
        options: {
          "responsive": true,
          aspectRatio: 0.75,
          "animation": {
            "duration": 1,
            "onComplete": function() {
              var chartInstance = this.chart,
                ctx = chartInstance.ctx;

              ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
              ctx.textAlign = 'center';
              ctx.textBaseline = 'bottom';

              this.data.datasets.forEach(function(dataset, i) {
                var meta = chartInstance.controller.getDatasetMeta(i);
                meta.data.forEach(function(bar, index) {
                  var data = dataset.data[index];
                  ctx.fillText(data, bar._model.x, bar._model.y - 5);
                });
              });
            }
          },
          scales: {
            xAxes: [{
              categoryPercentage: 1.0,
              barPercentage: 1.0,
              time: {
                unit: 'date'
              },
              gridLines: {
                display: false,
                drawBorder: false
              },
              ticks: {
                autoSkip: true,
                maxTicksLimit: 20
              }
            }],
            yAxes: [{
              ticks: {
                beginAtZero: true
              }
            }]
          }
        },
      };
      allOutsourcerChart = new Chart(
        document.getElementById('BarChart1'),
        allOutsourcerconfig
      );
      $.each(datasets, function(key, value) {
        console.log(this)
        var newDataset = {
          label: key + '(已處理)',
          data: value,
          backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
          borderWidth: 1,
          barPercentage: 0.5,
          barThickness: 6,
          maxBarThickness: 8,
          minBarLength: 2,
          stack: key
        }
        allOutsourcerdata.datasets.push(newDataset);
        // historyOutsourcerdata.labels.push(this.name);
        allOutsourcerChart.update();
      });
      $.each(finishdatasets, function(key, value) {
        console.log(this)
        var newDataset = {
          label: key + '(未處理)',
          data: value,
          backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
          borderWidth: 1,
          barPercentage: 0.5,
          barThickness: 6,
          maxBarThickness: 8,
          minBarLength: 2,
          stack: key
        }
        allOutsourcerdata.datasets.push(newDataset);
        // historyOutsourcerdata.labels.push(this.name);
        allOutsourcerChart.update();
      });

    }
   */
  // function getAllOutsourcer(){
  //   let start = $('[name="allOutsourcer"][data-type="start"]').val().replace("T" , " ")
  //   let end = $('[name="allOutsourcer"][data-type="end"]').val().replace("T" , " ")
  //   $.ajax({
  //     url: `/modifyprocess/all`,
  //     type: 'get',
  //     data:{
  //       start:start,
  //       end:end,
  //       id:id,
  //       type:'inside'
  //     },
  //     success: function(response) {
  //       allOutsourcerChart.destroy();
  //       allOutsourcerdata = {
  //           labels: ["數量"],
  //           datasets: []
  //       }
  //       allOutsourcerconfig = {
  //         type: 'bar',
  //         data: allOutsourcerdata,
  //         options: {
  //           "responsive":true,
  //           aspectRatio: 1, 
  //           "animation": {
  //             "duration": 1,
  //             "onComplete": function() {
  //               var chartInstance = this.chart,
  //                 ctx = chartInstance.ctx;

  //               ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
  //               ctx.textAlign = 'center';
  //               ctx.textBaseline = 'bottom';

  //               this.data.datasets.forEach(function(dataset, i) {
  //                 var meta = chartInstance.controller.getDatasetMeta(i);
  //                 meta.data.forEach(function(bar, index) {
  //                   var data = dataset.data[index];
  //                   ctx.fillText(data, bar._model.x, bar._model.y - 5);
  //                 });
  //               });
  //             }
  //           },
  //           scales: {
  //             xAxes: [{
  //               time: {
  //                 unit: 'date'
  //               },
  //               gridLines: {
  //                 display: false,
  //                 drawBorder: false
  //               },
  //               ticks: {
  //                 maxTicksLimit: 7,
  //               }
  //             }],
  //             yAxes: [{
  //                 ticks: {
  //                   beginAtZero: true,
  //                 }
  //               }]
  //           }
  //         },
  //       };
  //       allOutsourcerChart = new Chart(
  //         document.getElementById('BarChart1'),
  //         allOutsourcerconfig
  //       );
  //       $.each(response,function(){
  //         console.log(this)
  //         var newDataset = {
  //           label: this.name,
  //           data: [this.count],
  //           backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
  //           borderWidth: 1,
  //         }
  //         allOutsourcerdata.datasets.push(newDataset);
  //         // historyOutsourcerdata.labels.push(this.name);
  //         allOutsourcerChart.update();
  //       });


  //     }
  //   });
  // }

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
          }

        })
        getcanvas();
        getDiscriptOther();
      }
    });

    $.ajax({
      url: '/file/tech_width',
      type: 'get',
      data: {
        progress_id: 8,
      },
      dataType: 'json',
      success: function(response) {
        $(response).each(function() {
          orig_tech_width = this.width || '200';
        })
      }
    });
    // $.ajax({
    //   url: `/file/outsourcer/list`,
    //   type: 'get',
    //   data: {

    //   },
    //   success: function(response) {
    //     $(response).each(function(){
    //      $('#datalistOutresourcer').append(`<option value="${this.module_id == 1 ?this.name:''}">`)

    //     })
    //   }
    // });
  }

  function divreviewsortable() {
    $(`#divreview`).sortable({
      revert: true,
      stop: function(event, ui) {

      },
      update: function(event, ui) {
        var nextPosition;
        var index = ui.item.find('[name="btndeleteprocess"]').data('num');
        // console.log(ui.item.find('[name="btndeleteprocess"]').data('num'))
        let currPosition = $(`[name="liprocess"]:eq(${index})`);

        if (ui.item.next().find('[name="btndeleteprocess"]').data('num') === undefined) {
          nextPosition = $('[name="liprocess"]:last');

          currPosition.insertAfter(nextPosition);
        } else {
          index = ui.item.next().find('[name="btndeleteprocess"]').data('num')
          nextPosition = $(`[name="liprocess"]:eq(${index})`);

          currPosition.insertBefore(nextPosition);
        }

        inSave()
      }
    });
  }

  function getDiscriptOther() {
    window.sharedVariable = {
      file_id: id,
      module_name: '技術',
      module_id: module_id
    };
    $("#discriptOther").load(`/discript/newother`);
  }

  var modifyprocess = new Object();
  $(document).on('click', '[name="btndeleteprocess"]', function() {
    let deletenum = $(this).data('num');
    $(`[name="btndeleteprocess"][data-num="${deletenum}"]`).closest('.input-group').remove();
    $(`[name="liprocess"]:eq(${deletenum})`).find('button.close').click();
  });

  function addindivreview(tmpprocess) {
    let sum = 0;
    $('#divreview').html('');
    $(tmpprocess).each(function(index, value) {
      $('#divreview').append(`
      <li class="ui-state-default">
      <div class="input-group mb-3">
        <input type="text" class="form-control" data-type="name" data-num=${index} placeholder="製程名稱" value="${value.name}" >
        <input type="number" class="form-control" data-type="cost" data-num=${index} placeholder="追加製程成本" value="${value.cost}" >
        <input type="text" class="form-control" data-type="mark" data-num=${index} placeholder="註記" value="${value.mark}" >
        <div class="input-group-append">
          <button class="btn btn-outline-danger" type="button" name="btndeleteprocess" data-num=${index}>x</button>
        </div>
      </div>
      </li>
      `)
      if (this.cost != '') {
        sum += parseInt(this.cost);

      }
    })
    divreviewsortable()
    console.log(sum)
    $('#inputreview').val(sum)
  }

  function getmodifyprocess(zero) {
    $.ajax({
      url: `/modifyprocess`,
      type: 'get',
      data: {
        file_id: id,
      },
      success: function(response) {

        modifyprocess = response;
        addindivreview(response);
        addmodifyprocess();



      }
    });
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

  $(document).on('change', '#selectAmount,#selectThreshold', function() {
    getResultComponents()
  });

  $(document).on('click', '[name="btnzoom"]', function() {
    let tmpwidth = $(this).closest('th').css('min-width').slice(0, -2);
    tmpwidth = parseInt(tmpwidth)
    console.log(tmpwidth)
    if ($(this).data('type') == "zoomin") {
      tmpwidth += 50;
      $("#tmpTable tr th:nth-child(1)").css('min-width', tmpwidth + 'px');
    } else if ($(this).data('type') == "zoomout") {
      tmpwidth -= 50;
      if (tmpwidth > 0) {
        $("#tmpTable tr th:nth-child(1)").css('min-width', tmpwidth + 'px');
      }
    }
    savezoom(tmpwidth);
  });

  function savezoom(tmpwidth) {
    clearTimeout(timeoutInSave)
    timeoutInSave = setTimeout(function() {
      $.ajax({
        url: '/file/tech_width',
        type: 'post',
        data: {
          // file_id: id,
          progress_id: 8,
          tech_width: tmpwidth
        },
        dataType: 'json',
        success: function(response) {

        }
      });
    }, 1000);

  }

  var focusID, focusItemID;

  function inputFocus(resID, resItemID) {
    console.log('22')
    focusID = resID;
    focusItemID = resItemID;
  }

  let crops_arr = new Object();
  var processArr = [];
  var processesArr = new Object();

  function getResultComponents() {
    $.ajax({
      url: `/processes/crop/${file_id_dest}`,
      type: 'get',
      success: function(response) {
        let zero = false;
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
            <div class="col-12">
              <a class="btn btn-light" style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 100%;min-width: 1px;" data-toggle="collapse" href="#divCollapse_${key+1}" role="button" aria-expanded="false" aria-controls="multiCollapseExample1">零件${key+1}</a>
            </div>
            <div class="col">
              <div class="collapse multi-collapse show" id="divCollapse_${key+1}">
                <div class="card">
                  <div class="card-body">
                    <div class="form-group row">
                      <label class="col-form-label col-auto">零件視角圖</label>
                      <div class="col-auto">
                      ${crops.html()}
                      </div>
                      <div class="table-responsive overflow-auto">
                        <table  id="tmpTable"  class="table table-borderless" style="width:auto">
                          <thead>
                            <tr>
                              <th class="text-nowrap" name="thzoom" style="min-width:${orig_tech_width}px">客戶原圖/相似圖
                                <div class="btn-group" role="group" aria-label="Basic example">
                                  <button type="button" class="btn btn-secondary" name="btnzoom" data-type="zoomin">+</button>
                                  <button type="button" class="btn btn-secondary" name="btnzoom" data-type="zoomout">-</button>
                                </div>
                              </th>
                              <th class="text-nowrap" style="width:50vw">視角圖（請左右滑動）</br>
                                <input type="checkbox" id="togglePic" data-toggle="toggle" data-on="顯示" data-off="隱藏">
                              </th>
                              <th class="text-nowrap">零件名稱</th>
                              <th class="text-nowrap">製程資訊</th>
                              <th class="text-nowrap" width=30%>註記</th>
                              <th class="text-nowrap" width=30%>製程成本</th>
                              <th class="text-nowrap" width=20%>加工成本</th>
                              <th class="text-nowrap">平均相似度</th>
                              <th class="text-nowrap">勾選</th>
                            </tr>
                          </thead>
                          <tbody id="divImage_${value}">
                          </tbody>
                          <tfoot>
                            <tr id="tr_multiCollapseExample_${value}">
                              <td colspan=5>
                                <div class="collapse multi-collapse show" id="divImage_multiCollapseExample_${value}">
                                  <button class="btn btn-secondary" type="button" name="btnAddProcess" onclick="inAddProcess(${value})">新增製程</button>
                                  <ul class="d-inline-flex list-inline list-unstyled overflow-auto inline-flex" name="draggableUl"  id="collapseDetail_${value}" data-component_id="${value}"">
                                  </ul>
                                </div>
                              </td>
                              <td colspan=1>
                                <label class="col-form-label col-auto">製程成本</label>
                                <div class="form-group row">
                                  <label for="inputTotal" class="col-sm-4 col-form-label">加工總成本</label>
                                  <div class="col-sm-8">
                                    <input type="number" disabled  class="form-control" id="inputTotal">
                                  </div>
                                </div>
                                <div name="divtotal" data-type="down" class="overflow-auto" style="height:300px">
                                  <button type="button" class="btn btn-secondary" name="btnaddcost">+</button>
                                </div>
                                <div class="form-group row col-12">
                                  <label class="col-form-label col-sm-auto">註記：</label>
                                  <div class="col" name="divFileComment" data-type="down">
                                    <textarea class="form-control" name="inputFileComment"  rows="3" ></textarea>
                                  </div>
                                </div>
                                <div class="row">
                                <a type="button" href="#divpaint" class="btn btn-primary float-right" >前往相似度比對區</a>
                                </div>
                                <div class="row">

                                <button type="button" class="btn btn-primary float-right" onclick="buttonPass()">下一步</button>
                                </div>

                                
                              </td>
                            </tr>
                              
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            `);
          compareObj[value] = [];
        });
        processinterval = [];
        $(function() {
          $('#togglePic').bootstrapToggle({
            on: '顯示',
            off: '隱藏',
            size: 'small',
            onstyle: 'success',
            offstyle: 'danger'
          });
        })
        if (processArr.length == 0) {
          console.log('inMatch')
          zero = true;
          $('#divImage').append(`
            <div class="col-12">
              <a class="btn btn-light" style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 100%;min-width: 1px;" data-toggle="collapse" href="#divCollapse_${1}" role="button" aria-expanded="false" aria-controls="multiCollapseExample1">零件${1}</a>
            </div>
            <div class="col">
              <div class="collapse multi-collapse show" id="divCollapse_${1}">
                <div class="card">
                  <div class="card-body">
                    <div class="form-group row">
                      <label class="col-form-label col-auto">零件視角圖</label>
                      <div class="col-auto">
                      ${crops.html()}
                      </div>
                      <div class="table-responsive overflow-auto">
                        <table  id="tmpTable"  class="table table-borderless" style="width:auto">
                          <thead > 
                            <tr>
                              <th class="text-nowrap" name="thzoom" style="min-width:${orig_tech_width}px">客戶原圖/相似圖
                                <div class="btn-group" role="group" aria-label="Basic example">
                                  <button type="button" class="btn btn-secondary" name="btnzoom" data-type="zoomin">+</button>
                                  <button type="button" class="btn btn-secondary" name="btnzoom" data-type="zoomout">-</button>
                                </div>
                              </th>
                              <th class="text-nowrap" style="width:50vw">視角圖（請左右滑動）</br>
                                <input type="checkbox" id="togglePic" data-toggle="toggle" data-on="顯示" data-off="隱藏">
                              </th>
                              <th class="text-nowrap">零件名稱</th>
                              <th class="text-nowrap">製程資訊</th>
                              <th class="text-nowrap" width=30%>註記</th>
                              <th class="text-nowrap" width=30%>製程成本</th>
                              <th class="text-nowrap" width=20%>加工成本</th>
                              <th class="text-nowrap">平均相似度</th>
                              <th class="text-nowrap">勾選</th>
                            </tr>
                          </thead>
                          <tbody id="divImage_${0}">
                          </tbody>
                          <tfoot>
                            <tr id="tr_multiCollapseExample_${0}">
                              <td colspan=4>
                                <div class="collapse multi-collapse show" id="divImage_multiCollapseExample_${0}">
                                  <div class = "row">
                                    <button class="btn btn-secondary" type="button" name="btnAddProcess" onclick="inAddProcess(${0})">新增製程</button>
                                  </div>
                                  <ul class="d-inline-flex list-inline list-unstyled overflow-auto inline-flex" name="draggableUl"  id="collapseDetail_${0}" data-component_id="${0}"">
                                  </ul>
                                </div>
                              </td>
                              <td colspan=2>
                                <label class="col-form-label col-auto">製程成本</label>
                                <div class="form-group row">
                                  <label for="inputTotal" class="col-sm-2 col-form-label">加工總成本</label>
                                  <div class="col-sm-10">
                                    <input type="number" disabled  class="form-control" id="inputTotal">
                                  </div>
                                </div>
                                <div name="divtotal" data-type="down" class="overflow-auto" style="height:300px">
                                  <button type="button" class="btn btn-secondary" name="btnaddcost">+</button>
                                </div>
                                <div class="form-group row col-12">
                                  <label class="col-form-label col-sm-auto">註記：</label>
                                  <div class="col" name="divFileComment" data-type="down">
                                    <textarea class="form-control" name="inputFileComment"  rows="3" ></textarea>
                                  </div>
                                </div>
                                <div class="row">
                                <a type="button" href="#divpaint" class="btn btn-primary float-right" >前往相似度比對區</a>
                                </div>
                                <div class="row">

                                <button type="button" class="btn btn-primary float-right" onclick="buttonPass()">下一步</button>
                                </div>

                                
                              </td>
                            </tr>
                              
                          </tfoot>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            `);
          getmodifyprocess(zero);
          $(`[id*=collapseDetail_${response.id}]`).sortable({
            revert: true,
            stop: function(event, ui) {
              $('.ui-state-default.list-inline-item.col-auto').each(function(index) {
                $(this).find('input').eq(0).val('00' + (index + 1) + '0');
              })
              console.log(ui)
              inSave();
            }
          });
          getprocess_cost([]);
          inbtnaddcost();
        }
        $.each(processArr, function(key, value) {
          process_id = processArr[key];
          processinterval[process_id] = setTimeout(process_resultMatch(process_id), 3000)
        })

        function process_resultMatch(process_id) {
          $.ajax({
            url: `/components/Match/${process_id}`,
            type: 'get',
            data: {
              threshold: $('#selectThreshold').val(),
              amount: $('#selectAmount').val(),
              module_name: '技術',
            },
            success: function(response) {
              let otherFileidArr = [];
              processesArr[response.id] = response.process.result;
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
                      $(crops).append(`<td><img src="/fileCrop/${this.crop_id}" style="height:100px;width:auto" onclick="getConfidence(${this.crop_id},${this.source})" class="figure-img img-fluid img-thumbnail rounded w-auto" alt="..." /></td>`);
                  })
                }
                let file_id = id
                let process_obj = $("<div></div>");
                otherFileidArr.push(responseItem.id)
                if ($(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id}`).length == 0) {
                  var $boolAppend = false;
                  var $tmpAppend = `
                    <tr name="divImageResultMatch${response.id}" id="divImageResultMatch_${responseItem.id}" data-avg="${Number.parseFloat(responseItem.avg).toFixed(2)}" data-process_id="${response.id}" data-crop_id="${responseItem.id}" >
                      
                      <td>
                        <div class="row">
                        <img src="/file/${file_id}" data-type="two" data-img2="${file_id}" class="figure-img img-fluid img-thumbnail rounded col-6" alt="..." />
                        <img src="/file/${responseItem.fileID}" data-type="two" data-img2="${file_id}" class="figure-img img-fluid img-thumbnail rounded col-6" alt="..." />
                        </div>
                      </td>
                      <td>
                        <div class="collapse " name="collapsePic">
                          <table style="width:50vw">
                          ${crops[0].outerHTML}
                          </table>
                        </div>
                      </td>
                      <td width=20%>
                        ${title}
                      </td>
                      <td>
                        <p>
                          <a class="btn btn-light" href="#divImage_multiCollapseExample_${response.id}" style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 100%;min-width: 1px;" onclick="getProcesses(${response.id},${responseItem.id})">製程帶入</a>
                        </p>
                      </td>
                      <td>
                        <div class="form-inline">
                          <input value="${responseItem.comment||''}" onfocus="inputFocus(${response.id},${responseItem.id})" type="text" value="${comment!=null?comment:''}" class="form-control" name="inputComment" data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"/>
                        </div>
                      </td>
                      <td  style="min-width:250px">
                        <div class="form-inline">
                          <input name="inputProcess" value="${responseItem.process}" placeholder="追加製程成本" onfocus="inputFocus(${response.id},${responseItem.id})" type="number" value="${comment!=null?comment:''}" class="form-control" name="inputComment" data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"/>
                        </div>
                       
                      </td>
                      <td  style="min-width:250px" >
                        <p>
                          <a class="btn btn-light" style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 100%;min-width: 1px;" data-file_id="${responseItem.id}" name="btnBringinCost">加工帶入</a>
                        </p>
                        <div name="divbringin"></div>

                      </td>
                      <td>相似度：${Number.parseFloat(responseItem.avg).toFixed(2)}%</td>
                      <td><input title="以ctrl點選可取消" type="radio" class="form-control" ${responseItem.comment!=null?'checked':''} name="inputCheck"  data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"/></td>
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
                  }
                }
                // if(index==0){
                // let tmpIndex = 0;
                // $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="tdDetail"]`).html(``);


                // let process = "";
                // console.log(modifyprocess.length)
                // if (modifyprocess.length == 0) {
                //   $(response.process.result[index].processes).each(function() {
                //     // console.log('inin')
                //     // $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="collapseBtn"]`).show();
                //     // let row = this;

                //     // var liDetail = '';
                //     // $.each(row, function(key, value) {
                //     //   if (key == "零件名稱")
                //     //     return
                //     //   liDetail += `
                //     //         <div class=" form-group row text-nowrap">
                //     //               <label class="col-form-label col-auto col-md-5" for="">${key}</label>
                //     //               <input class="form-control col-md"  data-input="draggableInput"  data-type="${key=="加工順序"?'num':(key=="製程代號"?'code':'name')}" value="${value}">
                //     //         </div>`;
                //     //   // if(tmpIndex > 2){

                //     //   // }else{
                //     //   //   $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="tdDetail"]`).append(`
                //     //   //     <p class="text-nowrap">${key}：${value}</p>
                //     //   //   `);
                //     //   // }
                //     //   // tmpIndex++;
                //     // })
                //     $(`#collapseDetail_${response.id}`).append(`
                //       <li class="ui-state-default list-inline-item col-auto" name="liprocess">
                //         <button type="button" class="close col-12 text-left" aria-label="Close" onclick="inDeleteLi(this)">
                //           <span aria-hidden="true">&times;</span>
                //         </button>
                //         ${liDetail}
                //         <div class=" form-group row text-nowrap">
                //               <label class="col-form-label col-auto col-md-5" for="">註記</label>
                //               <input class="form-control col-md" data-input="draggableInput"  data-type="mark"  value="">
                //         </div>
                //         <div class=" form-group row text-nowrap">
                //               <label class="col-form-label col-auto col-md-5" for="">製程成本</label>
                //               <input class="form-control col-md" data-input="draggableInput" data-type="cost" name="inputCost" value="">
                //         </div>
                //         <p>歷史追加成本：</p>
                //         <p>2017-05-22：1100</p>
                //         <p>2018-04-23：1400</p>
                //         <p>2019-03-24：1600</p>
                //         <p>2020-05-24：2000</p>
                //       </li>
                //     `);
                //   });
                // }

                // $(`#divImage_${response.id}`).append($(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id}`));
                // $(`#divImage_${response.id}`).append($(`#tr_${response.id}_multiCollapseExample${responseItem.id}`));

              })

              getmodifyprocess(zero)

              // else{
              //   inSave();
              // }


              $(response.status).each(function() {
                if (this.status == "stop") {
                  clearTimeout(processinterval[response.id]);
                } else {
                  processinterval[response.id] = setTimeout(process_resultMatch(response.id), 3000)
                }
              })



              // $( "#draggable" ).draggable({
              //   connectToSortable: "#sortable",
              //   helper: "clone",
              //   revert: "invalid"
              // });
              // $( "ul, li" ).disableSelection();
              console.log('otherFileidArr' + otherFileidArr)
              getprocess_cost(otherFileidArr);
              // getprocess_cost_now();
              inbtnaddcost();


            }
          })
        }
      }
    })

  }

  function addmodifyprocess(zero) {
    let tmpcomponent_id
    if (modifyprocess.length > 0) {
      $.each(modifyprocess, function(key, value) {
        tmpcomponent_id = (zero == true ? 0 : value['component_id'])
        $(`#collapseDetail_${tmpcomponent_id}`).append(`
              <li class="ui-state-default list-inline-item col-auto" name="liprocess">
                <button type="button" class="close col-12 text-left" aria-label="Close" onclick="inDeleteLi(this)">
                  <span aria-hidden="true">&times;</span>
                </button>
                <div class=" form-group row text-nowrap">
                    ${key==0?'<label class="col-form-label col-auto  col-md-5" for="">加工順序</label>':''}
                    <input class="form-control col-md" data-input="draggableInput"  data-type="num"  id="" value="${value['num']}">
                </div>
                <div class=" form-group row text-nowrap">
                    ${key==0?'<label class="col-form-label col-auto  col-md-5" for="">製程代號</label>':''}
                    <input class="form-control col-md" data-input="draggableInput" data-type="code" id="" value="${value['code']}">
                </div>
                <div class=" form-group row text-nowrap">
                    ${key==0?'<label class="col-form-label col-auto  col-md-5" for="">製程名稱</label>':''}
                    <input class="form-control col-md" data-input="draggableInput"data-type="name" id="" value="${value['name']}">
                </div>
                <div class=" form-group row text-nowrap">
                    ${key==0?'<label class="col-form-label col-auto  col-md-5" for="">註記</label>':''}
                    <input class="form-control col-md" data-input="draggableInput" data-type="mark" id="" value="${value['mark']}">
                </div>
                <div class=" form-group row text-nowrap">
                  ${key==0?'<label class="col-form-label col-auto  col-md-5" for="">製程成本</label>':''}
                  <input type="number" class="form-control col-md" data-input="draggableInput" data-type="cost" name="inputCost"  value="${value['cost']}">
                </div>
                <div class=" form-group row text-nowrap" hidden>
                  ${key==0?'<label class="col-form-label col-auto  col-md-5" for="">外包成本</label>':''}
                  <input  class="form-control col-md" data-input="draggableInput" data-type="outsourcer_cost"   value="${value['outsourcer_cost'] || ''}">
                </div>
                <div class=" form-group row text-nowrap" hidden>
                  ${key==0?'<label class="col-form-label col-auto  col-md-5" for="">廠商</label>':''}
                  <input  class="form-control col-md" data-input="draggableInput" data-type="outsourcer"   value="${value['outsourcer']}">
                </div>
                
              </li>
            `);

      });
      $(`[id*=collapseDetail_${tmpcomponent_id}]`).sortable({
        revert: true,
        placeholder: "ui-state-highlight",
        stop: function(event, ui) {
          $('.ui-state-default.list-inline-item.col-auto').each(function(index) {
            $(this).find('input').eq(0).val('00' + (index + 1) + '0');
          })
          inSave();
        }
      });
    }

  }
  $(document).on('input', '#divreview input', function() {
    let modifytype = $(this).data('type')
    let modifynumber = $(this).data('num')
    console.log(modifytype, modifynumber)

    $(`[name="liprocess"]:eq(${modifynumber})`).find(`[data-type="${modifytype}"]`).val($(this).val());
    inSave()
  });
  $(document).on('input', 'input[data-input="draggableInput"]', function() {
    inSave()
  });
  $(document).on('click', '[name="btnAddProcess"]', function() {
    inAddProcess($(this).data('component_id'), $(this).data('process_id'));
    $('#divreview').append(`
      <li class="ui-state-default">
          <div class="input-group mb-3">
            <input type="text" class="form-control" data-type="name" data-num=${$('[name="btndeleteprocess"]').length} placeholder="製程名稱" value="" >
            <input type="number" class="form-control" data-type="cost" data-num=${$('[name="btndeleteprocess"]').length} placeholder="追加製程成本" value="="" >
            <input type="text" class="form-control" data-type="mark" data-num=${$('[name="btndeleteprocess"]').length} placeholder="註記" value="" >
            <div class="input-group-append">
                <button class="btn btn-outline-danger" type="button" name="btndeleteprocess" data-num=${$('[name="btndeleteprocess"]').length}>x</button>
              </div>
          </div>
        </li>
        `)
  })
  divreviewsortable()

  $(document).on('click', '[name="btnBringinCost"]', function() {
    bringinCost(this);
  })
  $(document).on('change', '[name="inputaddcost"]', function() {
    if ($(this).closest('[name="divtotal"]').data('type') == 'top') {
      $('[name="divtotal"][data-type="down"]').html('');
      $('[name="divtotal"][data-type="top"]').children().clone().appendTo('[name="divtotal"][data-type="down"]')
    } else if ($(this).closest('[name="divtotal"]').data('type') == 'down') {
      $('[name="divtotal"][data-type="top"]').html('');
      $('[name="divtotal"][data-type="down"]').children().clone().appendTo('[name="divtotal"][data-type="top"]')
    }
    insaveprocess_cost();
  })
  $(document).on('click', '[name="deleteprocess_cost"]', function() {

    if ($(this).closest('[name="divtotal"]').data('type') == 'top') {
      $(this).closest('[name="addcost"]').remove();
      $('[name="divtotal"][data-type="down"]').html('');
      $('[name="divtotal"][data-type="top"]').children().clone().appendTo('[name="divtotal"][data-type="down"]')
    } else if ($(this).closest('[name="divtotal"]').data('type') == 'down') {
      console.log($(this).closest('[name="divtotal"]').data('type'))
      $(this).closest('[name="addcost"]').remove();
      $('[name="divtotal"][data-type="top"]').html('');
      $('[name="divtotal"][data-type="down"]').children().clone().appendTo('[name="divtotal"][data-type="top"]')
    }
    insaveprocess_cost();

  })

  function bringinCost(tmpelement) {
    $(tmpelement).closest('td').find('[name="divaddcost"]').each(function() {
      $(`
        <div class="input-group mb-3" name="addcost">
          <input type="text" class="form-control col-6" value="${$(this).data('name')}" name="inputaddcost"  list="datalistOutresourcer" data-type="name" placeholder="加工名稱" >
          <input type="number" class="form-control col-6" value="${$(this).data('cost')}" name="inputaddcost" data-type="cost" placeholder="加工成本" >
          <div class="input-group-append">
          <button type="button" class="btn btn-danger" name="deleteprocess_cost">-</button>
          </div>
        </div>`).insertBefore('[name="btnaddcost"]');
    })
    insaveprocess_cost();

  }

  function getprocess_cost_now() {
    $.ajax({
      url: '/process_cost',
      type: 'get',
      data: {
        file_id: id,
        other: [1]
      },
      dataType: 'json',
      success: function(response) {
        let tmphtml = ''
        $.each(response.now, function() {
          tmphtml += ` <div class="input-group mb-3" name="addcost">
              <input type="text" class="form-control col-6" value="${this.name}" name="inputaddcost" data-type="name"  list="datalistOutresourcer" placeholder="加工名稱" >
              <input type="number" class="form-control col-6" value="${this.cost}"  name="inputaddcost" data-type="cost" placeholder="加工成本" >
              <div class="input-group-append">
              <button type="button" class="btn btn-danger" name="deleteprocess_cost">-</button>
              </div>
            </div>`;
          // $(`

          //   <div class="input-group mb-3" name="addcost">
          //     <input type="text" class="form-control col-6" value="${this.name}" name="inputaddcost" data-type="name" placeholder="加工名稱" >
          //     <input type="number" class="form-control col-6" value="${this.cost}"  name="inputaddcost" data-type="cost" placeholder="加工成本" >
          //     <div class="input-group-append">
          //     <button type="button" class="btn btn-danger" name="deleteprocess_cost">-</button>
          //     </div>
          //   </div>

          // `).insertBefore('[name="btnaddcost"]')
        })
        $('[name="btnaddcost"]').each(function() {
          $(tmphtml).insertBefore(this)
        });
        let tmpTotal = 0;
        $('[name="inputaddcost"][data-type="cost"]').each(function() {
          tmpTotal += parseInt($(this).val());
        })
        $('#inputTotal').val(tmpTotal)
        $('#topinputTotal').val(tmpTotal)
      }
    });

  }

  function getprocess_cost(fileidArr) {
    $.ajax({
      url: '/process_cost',
      type: 'get',
      data: {
        file_id: id,
        other: fileidArr
      },
      dataType: 'json',
      success: function(response) {
        $(`[name="btnBringinCost"]`).closest('td').find('[name="divbringin"]').html(``);
        $.each(response.other, function() {
          $(`[name="btnBringinCost"][data-file_id="${this.file_id}"]`).closest('td').find('[name="divbringin"]').append(`
            <div class="input-group mb-3" name="divaddcost" data-name="${this.name}" data-cost="${this.cost}"> 
              <input disabled type="text" class="form-control col-6" value="${this.name}" name="inputaddcost" data-type="name" list="datalistOutresourcer"  placeholder="加工名稱" >
              <input disabled type="number" class="form-control col-6" value="${this.cost}"  name="inputaddcost" data-type="cost" placeholder="加工成本" >
            </div>
          `)
        });


        let tmphtml = ''
        $.each(response.now, function() {
          tmphtml += ` <div class="input-group mb-3" name="addcost">
              <input type="text" class="form-control col-6" value="${this.name}" name="inputaddcost" data-type="name"  list="datalistOutresourcer" placeholder="加工名稱" >
              <input type="number" class="form-control col-6" value="${this.cost}"  name="inputaddcost" data-type="cost" placeholder="加工成本" >
              <div class="input-group-append">
              <button type="button" class="btn btn-danger" name="deleteprocess_cost">-</button>
              </div>
            </div>`;
          // $(`

          //   <div class="input-group mb-3" name="addcost">
          //     <input type="text" class="form-control col-6" value="${this.name}" name="inputaddcost" data-type="name" placeholder="加工名稱" >
          //     <input type="number" class="form-control col-6" value="${this.cost}"  name="inputaddcost" data-type="cost" placeholder="加工成本" >
          //     <div class="input-group-append">
          //     <button type="button" class="btn btn-danger" name="deleteprocess_cost">-</button>
          //     </div>
          //   </div>

          // `).insertBefore('[name="btnaddcost"]')
        })
        $('[name="btnaddcost"]').each(function() {
          $(tmphtml).insertBefore(this)
        });

        let tmpTotal = 0;
        $('[name="divtotal"][data-type="down"]').find('[name="inputaddcost"][data-type="cost"]').each(function() {
          // if(Math.floor(parseInt($(this).val())) == id && $.isNumeric(parseInt($(this).val()))) {
          //   tmpTotal+=parseInt($(this).val());
          // }
          if ($(this).val() != '') {
            tmpTotal += parseInt($(this).val());
          }
        })
        $('#inputTotal').val(tmpTotal)
        $('#topinputTotal').val(tmpTotal)

      }
    });

  }

  function inbtnaddcost() {
    $(document).on('click', '[name="btnaddcost"]', function() {
      $('[name="btnaddcost"]').each(function() {
        $(`
          <div class="input-group mb-3" name="addcost">
            <input type="text" class="form-control col-6"  name="inputaddcost" data-type="name" list="datalistOutresourcer" placeholder="加工名稱" >
            <input type="number" class="form-control col-6"  name="inputaddcost" data-type="cost" placeholder="加工成本" >
            <div class="input-group-append">
            <button type="button" class="btn btn-danger" name="deleteprocess_cost">-</button>
            </div>
          </div>`).insertBefore(this);
      })

    })
  }

  function insaveprocess_cost() {
    // $('[name="divtotal"][data-type="top"]')
    let processingArr = [];
    $('[name="divtotal"][data-type="down"]').find('[name="addcost"]').each(function() {
      let tmpObj = new Object;
      tmpObj['name'] = $(this).find('[name="inputaddcost"][data-type="name"]').val()
      tmpObj['cost'] = $(this).find('[name="inputaddcost"][data-type="cost"]').val()
      console.log($(this).find('[name="inputaddcost"][data-type="name"]').val())
      console.log($(this).find('[name="inputaddcost"][data-type="cost"]').val())
      if (tmpObj['name'] != '' || tmpObj['cost'] != '') {
        processingArr.push(tmpObj)
      }
    });

    $.ajax({
      url: '/process_cost',
      type: 'post',
      data: {

        file_id: id,
        arr: processingArr
      },
      dataType: 'json',
      success: function(response) {}
    });

    let tmpTotal = 0;
    $('[name="divtotal"][data-type="down"]').find('[name="inputaddcost"][data-type="cost"]').each(function() {
      // if(Math.floor(parseInt($(this).val())) == id && $.isNumeric(parseInt($(this).val()))) {
      //   tmpTotal+=parseInt($(this).val());
      // }
      if ($(this).val() != '') {
        tmpTotal += parseInt($(this).val());

      }
    })
    $('#inputTotal').val(tmpTotal)
    $('#topinputTotal').val(tmpTotal)


  }

  function getProcesses(response_id, responseItem_id) {
    $(processesArr[response_id]).each(function() {
      let process = this
      if (process.id == responseItem_id) {
        $(`#collapseDetail_${response_id}`).html(``);
        let tmpcount = 0;
        $(process.processes).each(function() {
          let row = this;
          var liDetail = '';

          $.each(row, function(key, value) {

            if (key == "零件名稱")
              return
            liDetail += `
                  <div class=" form-group row text-nowrap">
                        ${tmpcount==0?'<label class="col-form-label col-auto  col-md-5" for="">'+key+'</label>':''}
                        <input class="form-control col-md"  data-input="draggableInput"  data-type="${key=="加工順序"?'num':(key=="製程代號"?'code':'name')}" value="${value}">
                  </div>`;
            // if(tmpIndex > 2){

            // }else{
            //   $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="tdDetail"]`).append(`
            //     <p class="text-nowrap">${key}：${value}</p>
            //   `);
            // }
            // tmpIndex++;
          })
          $(`#collapseDetail_${response_id}`).append(`
            <li class="ui-state-default list-inline-item col-auto" name="liprocess">
              <button type="button" class="close col-12 text-left" aria-label="Close" onclick="inDeleteLi(this)">
                <span aria-hidden="true">&times;</span>
              </button>
              ${liDetail}
             
              <div class=" form-group row text-nowrap">
                    ${tmpcount==0?'<label class="col-form-label col-auto  col-md-5" for="">註記</label>':''}
                    <input class="form-control col-md" data-input="draggableInput"  data-type="mark"  value="">
              </div>
              <div class=" form-group row text-nowrap">
                    ${tmpcount==0?'<label class="col-form-label col-auto  col-md-5" for="">製程成本</label>':''}
                    <input type="number" class="form-control col-md" data-input="draggableInput" data-type="cost" name="inputCost" value="">
              </div>
              <div class=" form-group row text-nowrap" hidden>
                ${tmpcount==0?'<label class="col-form-label col-auto  col-md-5" for="">外包成本</label>':''}
                <input  class="form-control col-md" data-input="draggableInput" data-type="outsourcer_cost"   value="">
              </div>
              <div class=" form-group row text-nowrap" hidden>
                ${tmpcount==0?'<label class="col-form-label col-auto  col-md-5" for="">廠商</label>':''}
                <input  class="form-control col-md" data-input="draggableInput" data-type="outsourcer"   value="">
              </div>
            </li>
          `);
          /* 
              <p>歷史追加成本：</p>
              <p>2017-05-22：1100</p>
              <p>2018-04-23：1400</p>
              <p>2019-03-24：1600</p>
              <p>2020-05-24：2000</p>
           */
          tmpcount++;
        });
        inSave();
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
  let timeoutInSave = null

  function inSave() {
    $('[name="liprocess"]').each(function(index, value) {
      $(this).find('input').eq(0).val('00' + (index + 1) + '0');
      $(this).find("label").remove()
    })
    $('[name="liprocess"]:eq(0)').find('div').each(function(index, value) {
      $(this).prepend(`<label class="col-form-label col-auto  col-md-5" for="">${modifyprocesslabelArr[index]}</label>`)
    })
    clearTimeout(timeoutInSave)
    timeoutInSave = setTimeout(function() {
      var modifyArr = []
      let sum = 0
      $('#divreview').html('');
      $('[name="liprocess"]').each(function(index, value) {
        // console.log(this)
        var tmpObj = new Object();
        tmpli = this;
        $(tmpli).find('input[data-input="draggableInput"]').each(function(index, value) {
          // console.log($(this).data('type'),$(this).val())
          tmpObj[$(this).attr('data-type')] = $(this).val();
        });
        // tmpObj['outsourcer'] = '';
        tmpObj['component_id'] = $(tmpli).closest('[name="draggableUl"]').data('component_id');
        tmpObj['process_id'] = $(tmpli).closest('[name="draggableUl"]').data('process_id');

        $('#divreview').append(`
        <li class="ui-state-default">
        <div class="input-group mb-3">
          <input type="text" class="form-control" data-type="name" data-num=${index} placeholder="製程名稱" value="${tmpObj['name']}" >
          <input type="number" class="form-control" data-type="cost" data-num=${index} placeholder="追加製程成本" value="${tmpObj['cost']}" >
          <input type="text" class="form-control" data-type="mark" data-num=${index} placeholder="註記" value="${tmpObj['mark']}" >

          <div class="input-group-append">
              <button class="btn btn-outline-danger" type="button" name="btndeleteprocess" data-num=${index}>x</button>
            </div>
        </div>
        </li>
        `)
        if (tmpObj['cost'] != '') {
          sum += parseInt(tmpObj['cost']);
        }

        modifyArr.push(tmpObj)
      });
      divreviewsortable()

      $('#inputreview').val(sum)


      $.ajax({
        url: '/modifyprocess',
        type: 'post',
        data: {
          id: id,
          arr: modifyArr
        },
        dataType: 'json',
        success: function(response) {
          if (response.status == 'success') {


          }
        }
      });
    }, 1000);
  }

  function inDeleteLi(tmpbutton) {
    // console.log($(tmpbutton).closest('li'))
    $(tmpbutton).closest('li').remove();
    $('.ui-state-default.list-inline-item.col-auto').each(function(index) {
      $(this).find('input').eq(0).val('00' + (index + 1) + '0');
    })
    inSave()
  }
  $(document).on('input', '[name="inputCost"]', function() {
    let cost = 0;
    $(this).closest('ul').find('[name="inputCost"]').each(function() {
      cost += parseInt($(this).val()) || 0;
    })
    $(`[name="inputCheck"]:checked`).closest('tr').find('[name="inputProcess"]').val(cost)
    $('[name=inputCheck]:checked').change()
  })

  function inAddProcess(component_id, process_id) {
    // console.log(process_id,component_id)

    let tmpfirst = true
    if ($('[name="liprocess"]').length > 0) {
      tmpfirst = false;
    }
    var liDetail = `
    <li class="ui-state-default list-inline-item col-auto ui-sortable-handle" name="liprocess">
      <button type="button" class="close col-12 text-left" aria-label="Close" onclick="inDeleteLi(this)">
        <span aria-hidden="true">×</span>
      </button>
      <div class=" form-group row text-nowrap">
          ${tmpfirst?'<label class="col-form-label col-auto  col-md-5" for="">加工順序</label>':''}
          <input class="form-control col-md" data-input="draggableInput" data-type="num" value="">
      </label></div>
      <div class=" form-group row text-nowrap">
          ${tmpfirst?'<label class="col-form-label col-auto  col-md-5" for="">製程代號</label>':''}
          <input class="form-control col-md" data-input="draggableInput" data-type="code" value="">
      </div>
      <div class=" form-group row text-nowrap">
          ${tmpfirst?'<label class="col-form-label col-auto  col-md-5" for="">製程名稱</label>':''}
          <input class="form-control col-md" data-input="draggableInput" data-type="name" value="">
      </div>
      
      <div class=" form-group row text-nowrap">
          ${tmpfirst?'<label class="col-form-label col-auto  col-md-5" for="">註記</label>':''}
          <input class="form-control  col-md" data-input="draggableInput" data-type="mark" value="">
      </div>
      <div class=" form-group row text-nowrap">
          ${tmpfirst?'<label class="col-form-label col-auto  col-md-5" for="">製程成本</label>':''}
          <input type="number" class="form-control  col-md" data-input="draggableInput" data-type="cost" name="inputCost" value="">
      </div>
      <div class=" form-group row text-nowrap" hidden>
        ${tmpfirst?'<label class="col-form-label col-auto  col-md-5" for="">外包成本</label>':''}
        <input type="number" class="form-control col-md" data-input="draggableInput" data-type="outsourcer_cost"   value="">
      </div>
      <div class=" form-group row text-nowrap" hidden>
        ${tmpfirst?'<label class="col-form-label col-auto  col-md-5" for="">廠商</label>':''}
        <input type="number" class="form-control col-md" data-input="draggableInput" data-type="outsourcer"   value="">
      </div>
     
    </li>
    `;
    $(`#collapseDetail_${component_id}`).append(`
        ${liDetail}
    `);
    $('.ui-state-default.list-inline-item.col-auto').each(function(index) {
      console.log(index + 1)
      $(this).find('input').eq(0).val('00' + (index + 1) + '0');
    })
    // $(`#collapseDetail_${component_id}_${process_id}`).sortable({
    //   revert: true
    // });

    inSave();


  }




  function nextpage() {
    console.log('nextpage')

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

  function sendemail(modules) {
    let content = `
      報價編號${id} ${module_name}部門已完成填寫
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
        console.log('in')
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
    $.ajax({
      url: `/notify/finish/module`,
      type: 'get',
      data: {
        finish: module_id,
        file_id: file_id,


      },
      dataType: 'json',
      success: function(response) {

        var moduleArr = []
        $.each(response, function() {
          moduleArr.push(this.notify)
        })
        // console.log(moduleArr)

        console.log(allState)
        // if (allState.includes('已刻度確認') && moduleArr.length > 0) {
        if (moduleArr.length > 0) {
          sendemail(moduleArr)
        } else {
          nextpage()
        }
      }
    })
  }

  var timeoutInputCheck = null
  var tmpselectcomponent = null;
  // $(document).on('click', '[name=inputCheck]:checked', function() {
  //   console.log('hascheck')
  // });
  $(document).on('click', '[name=inputCheck]', function() {
    let element = this;
    console.log(element == tmpselectcomponent)
    if (element == tmpselectcomponent) {
      $(this).prop('checked', false);
      tmpselectcomponent = null;
      $.ajax({
        url: `/components/comment`,
        type: 'delete',
        data: {
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          module_name: '技術'
        },
      })
    } else {
      tmpselectcomponent = this;
      if ($(element).prop('checked')) {
        clearTimeout(timeoutInputCheck);
        timeoutInputCheck = setTimeout(function() {
          $.ajax({
            url: `/components/comment`,
            type: 'post',
            data: {
              process_id: $(element).attr('data-process_id'),
              crop_id: $(element).attr('data-crop_id'),
              confidence: $(element).attr('data-confidence'),
              comment: $(element).closest('tr').find('[name=inputComment]').val(),
              process: $(element).closest('tr').find('[name=inputProcess]').val(),
              material: '',
              stuff: '',
              module_name: '技術'
            },
          })
        }, 1000)
      }
      $('[name=inputCheck]:not(:checked)').each(function() {
        let element = this;
        $.ajax({
          url: `/components/comment`,
          type: 'delete',
          data: {
            process_id: $(element).attr('data-process_id'),
            crop_id: $(element).attr('data-crop_id'),
            module_name: '技術'
          },
        })
      })
    }

  })
  $(document).on('input', '[name=inputComment],[name=inputProcess]', function() {
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
          process: $(element).closest('tr').find('[name=inputProcess]').val(),
          material: '',
          stuff: '',
          module_name: '技術'
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
        module_name: '技術'
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
        })

        $('#list-tab-business').html(``);
        let list_tab = $(`<ul class="list-group list-group-horizontal w-100"></ul>`);
        let list_color = null;
        $(response.state).each(function(index) {
          if (this.module_name == module_name) {
            allState.push(this.progress)
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
                    <li class="list-group-item list-group-item${this['update_time']!=null?this.module_color:''} flex-fill text-nowrap ${location.href.indexOf(this.url+'?')!=-1?'active':''}"  ${this.redirect?``:`onclick="javascript:location.href='${this['url']}?id=${file_id}&file_id_dest=${file_id_dest}'"`}>${this['progress']}</li>
                  </ul>
                </div>
              </div>
            `);
          } else {
            $(list_tab).find('ul').append(`
              <li class="list-group-item list-group-item${this['update_time']!=null?this.module_color:''} flex-fill text-nowrap ${location.href.indexOf(this.url+'?')!=-1?'active':''}"  ${this.redirect?``:`onclick="javascript:location.href='${this['url']}?id=${file_id}&file_id_dest=${file_id_dest}'"`}>${this['progress']}</li>
            `);
          }
          // if(index==response.state.length-1){
          //   $('#list-tab-other').append($(list_tab)[0].outerHTML);
          // }
        })
        // $('#divStation').empty();
        // $(response.station).each(function() {
        //   let row = this;
        //   let tr = $(`<tbody></tbody>`);
        //   $(this.station).each(function(index) {
        //     let information = $(`<div></div>`);
        //     $.each(this, function(key, value) {
        //       if (key != "crop_id" && value != null)
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
<!-- 
<script>
  // $.ajax({
  //   url: `/modifyprocess/all`,
  //   type: 'get',
  //   data:{
  //   },
  //   dataType: 'json',
  //   success: function(response) {
  //     // let labels = new Array("數量");
  //     // modifyprocesschartdata = { 
  //     //   labels: labels,
  //     //   datasets: []};
  //     let datasets = [];
  //     let max = 0;
  //     $.each(response,function(){
  //       if(this.count>max) max = this.count
  //       var newDataset = {
  //           label:this.name,
  //           data: [this.count],
  //           backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
  //           borderWidth: 1,
  //           datalabels: {
  //             anchor: 'end',
  //             align: 'start',
  //           }
  //           // stack: 'Stack 0'
  //         }
  //         datasets.push(newDataset);
  //     });
  //     getProcessChart(datasets,max);
  //   }
  // });
  // function getProcessChart(datasets,max){
  let labels = new Array("數量");
  // "星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"
  var allOutsourcerdata = {
    labels: labels,
    datasets: []
    // [
    // //   {
    // //   label: '碳化鎢研磨(已處理)',
    // //   data: [65, 59, 80, 81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 0'
    // // },{
    // //   label: '碳化鎢研磨(未處理)',
    // //   data: [65, 59, 80, 81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 0'
    // // },
    // // {
    // //   label: '碳化鎢研磨(未成單)',
    // //   data: [65, 59, 80, 81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 0'
    // // },
    // // {
    // //   label: '切料(已處理)',
    // //   data: [59, 56, 55, 80, 81, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 1'
    // // },{
    // //   label: '切料(未處理)',
    // //   data: [59, 56, 55, 80, 81, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 1'
    // // },{
    // //   label: '切料(未成單)',
    // //   data: [59, 56, 55, 80, 81, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 1'
    // // },{
    // //   label: 'CNC銲接後(已處理)',
    // //   data: [80, 81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 2'
    // // },{
    // //   label: 'CNC銲接後(未處理)',
    // //   data: [80, 81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 2'
    // // },{
    // //   label: 'CNC銲接後(未成單)',
    // //   data: [80, 81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 2'
    // // },{
    // //   label: '定位塗藥(已處理)',
    // //   data: [81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 3'
    // // },{
    // //   label: '定位塗藥(未處理)',
    // //   data: [81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 3'
    // // },{
    // //   label: '定位塗藥(未成單)',
    // //   data: [81, 56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 3'
    // // },{
    // //   label: '熱處理(已處理)',
    // //   data: [56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 4'
    // // },{
    // //   label: '熱處理(未處理)',
    // //   data: [56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 4'
    // // },{
    // //   label: '熱處理(未成單)',
    // //   data: [56, 55, 40,35],
    // //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
    // //   borderWidth: 1,
    // //   stack: 'Stack 4'
    // // }
    // ]
  };
  let allOutsourcerconfig = {

  };
  var allOutsourcerChart = new Chart(
    document.getElementById('BarChart1'),
    allOutsourcerconfig
  );
  // }
</script>
 -->
<link href="/static/weightConverter/css/main.72d64a8f.css" rel="stylesheet" />
