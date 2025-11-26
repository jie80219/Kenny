<?php include(__DIR__ . '/basic/header.html'); ?>

<script src="/dropzone/dist/dropzone.js"></script>
<link rel="stylesheet" href="/dropzone/dist/dropzone.css">
<link rel="stylesheet" href="/vendor/select-pure/dist/select-pure.css">
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
        報價單摘要
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>客戶圖縮圖</th>
                <th>廠內圖</th>
                <th>訂單資訊</th>
                <th>註記</th>
                <th>建議成本</th>
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
                <td id="tdCost"></td>
              </tr>
            </tbody>
          </table>
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
                <div class="col table-responsive">
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
      <div class="card-header">零件比對
        <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="選擇要呈現的零件圖數量，可設定要呈現出的數量"></i>
      </div>
      <div class="card-body">
        <div class="row rows-col-1 rows-col-md-2">
          <div class="col">
            <ul>
              <li></li>
              <li></li>
            </ul>
            <div class="form-group row">
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
          <div class="col">

            <i class="fas fa-exclamation-circle float-right mx-2" data-toggle="tooltip" data-placement="top" title="註記的部分會在技術階段時，看到所留下的註記。勾選的部分會在技術階段時，看到所留下的相似零件。按下一步後可送至製圖"></i>
            <button type="button" class="btn btn-primary float-right" onclick="buttonPass()">下一步</button>
          </div>
        </div>
        <div class="form-group row" id="divImage">
        </div>
      </div>
    </div>
  </div>
</div>
<?php include(__DIR__ . '/basic/footer.html'); ?>
<script src="/vendor/select-pure/dist/select-pure.bundle.min.js"></script>
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
  var file_id_dest = url.searchParams.get("file_id_dest");
  var module_id;
  var module_name = '製圖';
  var allState = [];


  $(function() {
    $('[data-toggle="tooltip"]').tooltip()
    getModule();
    getDiscriptOther();
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
    }
  });

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
        console.log(module_id)
      }
    });
  }

  function getDiscriptOther() {
    window.sharedVariable = {
      file_id: id,
      module_name: '製圖'
    };
    $("#discriptOther").load(`/discript/other`);
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


  $(document).on('change', '#selectAmount,#selectThreshold', function() {
    getResultComponents()
  });
  var focusID, focusItemID;

  function inputFocus(resID, resItemID) {
    console.log('22')
    focusID = resID;
    focusItemID = resItemID;
  }
  let crops_arr = new Object();

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
                  <div class="overflow-auto">
                    <table class="table table-borderless" width=100%>
                      <thead>
                        <tr>
                          
                          <th class="text-nowrap">客戶原圖</th>
                          <th class="text-nowrap" style="width:50vw">視角圖（請左右滑動）</th>
                          <th class="text-nowrap" width=30%>註記</th>
                          <th class="text-nowrap">平均相似度</th>
                          <th class="text-nowrap">勾選</th>
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
        });
        processinterval = [];
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
              module_name: '製圖',
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
                      $(crops).append(`<td><img src="/fileCrop/${this.crop_id}" style="height:100px;width:auto" onclick="getConfidence(${this.crop_id},${this.source})" class="figure-img img-fluid img-thumbnail rounded w-auto" alt="..." /></td>`);
                  })
                }
                console.log(crops)
                let file_id = id
                let process_obj = $("<div></div>");
                if ($(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id}`).length == 0) {
                  var $boolAppend = false;
                  var $tmpAppend = `
                    <tr name="divImageResultMatch${response.id}" id="divImageResultMatch_${responseItem.id}" data-avg="${Number.parseFloat(responseItem.avg).toFixed(2)}" >
                     
                      <td width=20%>
                        <img src="/file/${responseItem.fileID}" data-type="two" data-img2="${file_id}"   class="figure-img img-fluid img-thumbnail rounded" alt="..." />
                      </td>
                      <td>
                        <table style="width:50vw;">
                        ${crops[0].outerHTML}
                        </table>
                      </td>
                      <td>
                        <div class="form-inline">
                          <input value="${responseItem.comment||''}" onfocus="inputFocus(${response.id},${responseItem.id})" type="text" value="${comment!=null?comment:''}" class="form-control" name="inputComment" data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"/>
                        </div>
                      </td>
                      <td>相似度：${Number.parseFloat(responseItem.avg).toFixed(2)}%</td>
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
                  }



                }
                // if(index==0){
                // let tmpIndex = 0;
                $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="tdDetail"]`).html(``);


                let process = "";
                $(response.process.result[index].processes).each(function() {
                  console.log('inin')
                  $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="collapseBtn"]`).show();
                  let row = this;

                  var liDetail = '';
                  $.each(row, function(key, value) {
                    if (key == "零件名稱")
                      return
                    liDetail += `
                          <div class=" form-group row text-nowrap">
                                <label class="col-form-label col-auto col-md-5" for="">${key}</label>
                                <input class="form-control col-md-6" id="" value="${value}">
                          </div>`;
                    // if(tmpIndex > 2){

                    // }else{
                    //   $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="tdDetail"]`).append(`
                    //     <p class="text-nowrap">${key}：${value}</p>
                    //   `);
                    // }
                    // tmpIndex++;
                  })
                  $(`#collapseDetail_${response.id}_${responseItem.id}`).append(`
                      <li class="ui-state-default list-inline-item col-auto">
                        <button type="button" class="close col-12 text-left" aria-label="Close" onclick="inDeleteLi(this)">
                          <span aria-hidden="true">&times;</span>
                        </button>
                        ${liDetail}
                        <div class=" form-group row text-nowrap">
                              <label class="col-form-label col-auto col-md-5" for="">註記</label>
                              <input class="form-control col-md-6" id="" value="">
                        </div>
                        <div class=" form-group row text-nowrap">
                              <label class="col-form-label col-auto col-md-5" for="">製程成本</label>
                              <input class="form-control col-md-6" name="inputCost" value="">
                        </div>
                        <p>歷史追加成本：</p>
                        <p>2017-05-22：1100</p>
                        <p>2018-04-23：1400</p>
                        <p>2019-03-24：1600</p>
                        <p>2020-05-24：2000</p>
                      </li>
                    `);
                });
                // }
                $(`#divImage_${response.id}`).append($(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id}`));
                $(`#divImage_${response.id}`).append($(`#tr_${response.id}_multiCollapseExample${responseItem.id}`));

              })
              $(response.status).each(function() {
                if (this.status == "stop") {
                  clearTimeout(processinterval[response.id]);
                } else {
                  processinterval[response.id] = setTimeout(process_resultMatch(response.id), 3000)
                }
              })
              $(`[id*=collapseDetail_${response.id}]`).sortable({
                revert: true,
                stop: function(event, ui) {
                  $('.ui-state-default.list-inline-item.col-auto').each(function(index) {
                    $(this).find('input').eq(0).val('00' + (index + 1) + '0');
                  })
                }
              });
              // $('[name="btnAddProcess"]').on('click',function(){
              //   inAddProcess($(this).data('component_id'),$(this).data('process_id'));
              // });
              // $( "#draggable" ).draggable({
              //   connectToSortable: "#sortable",
              //   helper: "clone",
              //   revert: "invalid"
              // });
              // $( "ul, li" ).disableSelection();
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
        if (allState.includes('已刻度修改') && moduleArr.length > 0) {
          sendemail(moduleArr)
        } else {
          nextpage()
        }
      }
    })
  }
  $(document).on('change', '[name=inputCheck]', function() {
    let element = this;
    if ($(this).prop('checked')) {
      $.ajax({
        url: `/components/comment`,
        type: 'post',
        data: {
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          confidence: $(element).attr('data-confidence'),
          comment: $(element).closest('tr').find('[name=inputComment]').val(),
          material: '',
          stuff: '',
          process: '',
          module_name: '製圖'

        },
      })
    } else {
      $.ajax({
        url: `/components/comment`,
        type: 'delete',
        data: {
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          module_name: '製圖'
        },
      })
    }
  })
  $(document).on('input', '[name=inputComment]', function() {
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
          material: '',
          stuff: '',
          process: '',
          module_name: '製圖'
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
        module_name: '製圖'
      },
      dataType: 'json',
      success: function(response) {
        // allState = response.state;
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


  Dropzone.options.uploadDropzone = {
    dictDefaultMessage: '比對資料放置處',
    addRemoveLinks: true,
    maxFiles: 1,
    acceptedFiles: 'image/*',
    init: function() {
      this.on("success", function(file, response) {
          let file_id = response['file_id'];
          $('#divRecognitionText').html(`
            <div id="draggable" class="draggable ui-widget-content" style="border-color:black;border-width:1px;background-color: transparent;">
            </div>
            <img src="/file/${file_id}" onload="loadimg()" class="figure-img img-fluid img-thumbnail rounded" alt="..." />
          `);
          // getRecognitionText();
          // let process_id = null;
          // $.each(response['process'],function(key,value){
          //   if(key=='process_id'){
          //     process_id = value;
          //   }
          // });
          // if(process.length!=0){
          //   $(process).each(function(index){
          //     clearInterval(process[index])
          //   })
          //   $('#divImage').empty();
          // }
          // console.log(process)
          // process[process_id] = setInterval(()=>{
          //   // console.log(process_id)

          //   $.ajax({
          //     url:`/components/Match/${process_id}`,
          //     type:'get',
          //     success:function(response){
          //       $(response).each(function(){
          //         if($(`#divImageResultMatch_${this.id}`).length==0){
          //           $('#divImage').append(`
          //             <div class="form-group row col-sm-4" id="divImageResultMatch_${this.id}">
          //               <div class="col-12">
          //                 <img src="/file/${this.id}" class="figure-img img-fluid img-thumbnail rounded" alt="..." />
          //               </div>
          //               <label class="col-form-label col-12">${responseItem.avg}</label>
          //             </div>
          //           `);

          //         }


          //       })
          //     //   $('#tableCrops').find('tbody').empty();
          //     //   let components_list = $(`<select></select>`);
          //     //   $(response['crops']).each(function(index){
          //     //     $(components_list).append(`
          //     //       <option value="${(index+1)}" ${(index==0?"selected":'')}>${(index+1)}</option>
          //     //     `);
          //     //   })
          //     //   $(response['crops']).each(function(index){
          //     //     $('#tableCrops').find('tbody').append(`
          //     //       <tr>
          //     //         <td><img src="/fileCrop/${this.id}" class="img-fluid img-thumbnail"/></td>
          //     //         <td><select class="form-control" name="selectCrops" data-id="${this.id}">${components_list.html()}</td>
          //     //       </tr>
          //     //     `);
          //     // });
          //     }
          //   })
          // },2000)
          $('#tableCrops').find('tbody').empty();
          let components_list = $(`<select></select>`);
          $(response['crops']).each(function(index) {
            $(components_list).append(`
              <option value="${(index+1)}" ${(index==0?"selected":'')}>${(index+1)}</option>
            `);
          })
          $(response['crops']).each(function(index) {
            $('#tableCrops').find('tbody').append(`
              <tr>
                <td><img src="/fileCrop/${this.id}" class="img-fluid img-thumbnail"/></td>
                <td><select class="form-control" name="selectCrops" data-id="${this.id}">${components_list.html()}</td>
              </tr>
            `);
          });
        }),
        this.on("removedfile", function(file) {

          if (process.length != 0) {
            clearInterval(processinterval)
            $('#tableCrops tbody').html('');
            $.ajax({
              url: '/process/stop',
              type: 'patch',
              dataType: 'json',
              data: {
                id: process
              },
              success: function(response) {
                $('#divImage').html('');

              }
            })
          }
          let response = JSON.parse(file['xhr']['response']);
          $id = null;
          $.each(response['process'], (key, value) => {
            if (key == 'process_id') {
              $id = value;
            }
          })
          $.ajax({
            url: '/file',
            type: 'delete',
            dataType: 'json',
            data: {
              id: $id
            },
            success: function(response) {}
          })
        });
    },
    success: function(file, response) {}
  };
</script>