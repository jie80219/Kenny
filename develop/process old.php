<?php include(__DIR__ . '/../basic/header.html'); ?>

<script src="/dropzone/dist/dropzone.js"></script>
<link rel="stylesheet" href="/dropzone/dist/dropzone.css">
<div class="row">
  <!-- search -->
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-body d-flex overflow-auto">
        <div class="flex-nowrap d-flex justify-content-center" id="list-tab">
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
              </tr>
            </thead>
            <tbody>
              <tr>
                <td width=10%>
                  <img class="img-thumbnail" id="imgThumbnail"></td>
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
</div>
<div class="row">
  <div class="col-12 mb-4">
    <div class="card shadow mb-4 h-100">
      <div class="card-header">零件比對</div>
      <div class="card-body">
        <div class="row rows-col-1 rows-col-md-2">
          <div class="col">
            <ul>
              <li>選擇要呈現的廠內圖數量</li>
              <li>可設定要呈現出的數量</li>
            </ul>
            <div class="form-group row">
              <div class="col-sm-auto form-group row">
                <label class="col-form-label col-auto">相似度門檻：</label>
                <div class="col-auto">
                  <select class="form-control" id="selectThreshold">
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
              <li>註記的部分會在生管階段時，看到所留下的註記</li>
              <li>勾選的部分會在業務階段時，看到所留下的相似零件</li>
              <li>按下一步後可送至製圖</li>
            </ul>
            <button type="button" class="btn btn-primary float-right" onclick="buttonPass()">下一步</button>
          </div>
        </div>
        <div class="form-group row" id="divImage">
        </div>
      </div>
    </div>
  </div>
</div>
<?php include(__DIR__ . '/../basic/footer.html'); ?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!-- <link rel="stylesheet" href="/resources/demos/style.css"> -->

<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
  $(function() {
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
    function getComment(){
        $.ajax({
            url:`/file/comment`,
            type:'get',
            data:{
                file_id:id,
                file_id_dest:file_id_dest
            },
            success:function(response){
                if(response.length==0)
                    $('#tdComment').html(`尚未有任何註記`)
                else
                    $('#tdComment').html(``)
                $(response).each(function(){
                    $('#tdComment').append(`
                        <p>${this.module_name}：${this.comment}</p>
                    `);
                })
            }
        })
    }

  $(document).on('change','#selectAmount,#selectThreshold',function(){
    getResultComponents()
  });
  var focusID,focusItemID;

  function inputFocus(resID , resItemID){
    console.log('22')
    focusID = resID;
    focusItemID = resItemID;
  }
  function getResultComponents() {
    var processArr = [];
    $.ajax({
      url: `/processes/crop/${file_id_dest}`,
      type: 'get',
      success: function(response) {
        processArr = response.process
        var compareObj = new Object();
        
        $('#divImage').html(``)
        $.each(response.process, function(key, value) {
          process.push(value)
          $('#divImage').append(`
            <div class="card col-12">
              <div class="card-body">
                <div class="form-group row">
                  <label class="col-form-label col-12">零件${key+1}</label>
                  <div class="table-responsive">
                    <table class="table table-borderless" width=100%>
                      <thead>
                        <tr>
                          <th>客戶原圖</th>
                          <th>廠內圖</th>
                          <th>零件圖</th>
                          <th width=30%>製程資訊</th>
                          <th width=30%>註記</th>
                          <th width=20% class="text-nowrap">估價</th>
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
        });
        processinterval=[];
        $.each(processArr, function(key, value) {
          process_id = processArr[key];
          processinterval[process_id] = setTimeout(process_resultMatch(process_id), 3000)
        })
        function process_resultMatch(process_id){
          $.ajax({
            url: `/components/Match/${process_id}`,
            type: 'get',
            data: {
              threshold: $('#selectThreshold').val(),
              amount: $('#selectAmount').val(),
            },
            success: function(response) {
              // console.log($(`#divImage_${focusID}  #divImageResultMatch_${focusItemID} td [name="inputComment"]`))
              if($(`#divImage_${focusID}  #divImageResultMatch_${focusItemID} td [name="inputComment"]`).length == 1){
                $(`#divImage_${focusID}  #divImageResultMatch_${focusItemID} td [name="inputComment"]`).focus();
              }
              let selector_not = ``;
              $(response.result).each(function () {
                selector_not += `:not(#divImageResultMatch_${this.id})`;
              });
              
              $(`#divImage_${response.id} [id*=divImageResultMatch_]${selector_not}`).each(function(){
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
                    if (this.module_name == '研發') {
                      comment = this.comment
                    } else if (this.module_name != null && this.comment != null) {
                      comment_other += `<p>${this.module_name}：${this.comment}</p>`;
                    }
                  })
                }
                let file_id = id
                let process_obj = $("<div></div>");
                if ($(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id}`).length == 0) {
                  var $boolAppend = false;
                  var $tmpAppend = `
                    <tr name="divImageResultMatch${response.id}" id="divImageResultMatch_${responseItem.id}" data-avg="${Number.parseFloat(responseItem.avg).toFixed(2)}" >
                      <td width=20%>
                        <img src="/file/${id}" class="figure-img img-fluid img-thumbnail rounded" alt="..." />
                      </td>
                      <td width=20%>
                        <img src="/file/${file_id_dest}" class="figure-img img-fluid img-thumbnail rounded" alt="..." />
                      </td> 
                      <td width=20%>
                        <img src="/fileCrop/${responseItem.id}" class="figure-img img-fluid img-thumbnail rounded" alt="..." />
                      </td>
                      <td >
                        <div name="tdDetail">
                          <p class="text-nowrap">查無製程</p>
                        </div>
                        <p>
                          <a class="btn btn-light" name="collapseBtn" style="display:none;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;width: 100%;min-width: 1px;" data-toggle="collapse" href="#divImage_${response.id}_multiCollapseExample${responseItem.id}" role="button" aria-expanded="false" aria-controls="multiCollapseExample1">查看更多資訊</a>
                        </p>
                        <div class="col">
                          <div class="collapse multi-collapse" id="divImage_${response.id}_multiCollapseExample${responseItem.id}">
                            <button class="btn btn-secondary" type="button" name="btnAddProcess" data-component_id="${response.id}"  data-process_id="${responseItem.id}" onclick="inAddProcess(${response.id},${responseItem.id})">新增製程</button>
                            <ul class="" id="collapseDetail"  >
                            </ul>
                          </div>
                        </div>
                      </td>
                      <td>
                        ${comment_other}
                        <div class="form-inline">
                          <input  onfocus="inputFocus(${response.id},${responseItem.id})" type="text" value="${comment!=null?comment:''}" class="form-control" name="inputComment" data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"/>
                        </div>
                      </td>
                      <td>
                        <div class="form-inline">
                          ${comment_other}<input  onfocus="inputFocus(${response.id},${responseItem.id})" type="text" value="${comment!=null?comment:''}" class="form-control" name="inputComment" data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"/>
                        </div>
                      </td>
                      <td>相似度：${Number.parseFloat(responseItem.avg).toFixed(2)}%</td>
                      <td><input type="checkbox" class="form-control" ${comment!=null?'checked':''} name="inputCheck"  data-process_id="${response.id}" data-crop_id="${responseItem.id}" data-confidence="${Number.parseFloat(responseItem.avg).toFixed(2)}"/></td>
                    </tr>
                  `;
                  // if($(`[name="divImageResultMatch${response.id}"]`).length == 0){
                  //   $(`#divImage_${response.id}`).append($tmpAppend);
                  //   $boolAppend = true;
                  // }else{
                    $(`[name="divImageResultMatch${response.id}"]`).each(function(){
                      if($(this).data('avg') <= Number.parseFloat(responseItem.avg).toFixed(2)){
                        $($tmpAppend).insertBefore((this))
                        $boolAppend = true;
                        return false; 
                      }
                    });
                  // }
                  if(!$boolAppend){
                    $(`#divImage_${response.id}`).append($tmpAppend);
                  }
                  

                  
                }
                if(index==0){
                    // let tmpIndex = 0;
                    $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="tdDetail"]`).html(``);
                    $(response.process).each(function(){
                      console.log('inin')
                      $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} [name="collapseBtn"]`).show();
                      let row = this;

                      var liDetail = '';
                      $.each(row,function(key,value){
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

                      $(`#divImage_${response.id} #divImageResultMatch_${responseItem.id} #divImage_${response.id}_multiCollapseExample${responseItem.id} #collapseDetail`).append(`
                        <li class="ui-state-default form-group row">
                          <button type="button" class="close col-12 text-left" aria-label="Close" onclick="inDeleteLi(this)">
                            <span aria-hidden="true">&times;</span>
                          </button>
                          ${liDetail}
                        </li>
                      `);
                    });
                  }
                $(`#divImage_${response.id}`).append($(`#divImage_${response.id}  #divImageResultMatch_${responseItem.id}`));

              })
              $(response.status).each(function() {
                if (this.status == "stop") {
                  clearTimeout(processinterval[response.id]);
                }else{
                  processinterval[response.id] = setTimeout(process_resultMatch(response.id), 3000)
                }
              })
              $( '#collapseDetail' ).sortable({
                revert: true
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

  function inDeleteLi(tmpbutton){
    // console.log($(tmpbutton).closest('li'))
    $(tmpbutton).closest('li').remove();
  }

  function inAddProcess(component_id,process_id){
    // console.log(process_id,component_id)
    var liDetail = `
    <li class="ui-state-default form-group row ui-sortable-handle">
      <button type="button" class="close" aria-label="Close" onclick="inDeleteLi(this)">
        <span aria-hidden="true">×</span>
      </button>
      <div class=" form-group row text-nowrap">
            <label class="col-form-label col-auto col-md-5" for="">加工順序</label>
            <input class="form-control col-md-6" id="" value="">
      </div>
      <div class=" form-group row text-nowrap">
            <label class="col-form-label col-auto col-md-5" for="">製程代號</label>
            <input class="form-control col-md-6" id="" value="">
      </div>
      <div class=" form-group row text-nowrap">
            <label class="col-form-label col-auto col-md-5" for="">製程名稱</label>
            <input class="form-control col-md-6" id="" value="">
      </div>
    </li>
    `;
    $(`#divImage_${component_id} #divImageResultMatch_${process_id} #divImage_${component_id}_multiCollapseExample${process_id} #collapseDetail`).append(`
      
        ${liDetail}
    `);
    // $( '#collapseDetail' ).sortable({
    //             revert: true
    //           });
  }

  
  function buttonPass() {
    let file_id = id;
    $('#basicModal').find('.modal-header').text(``);
    $('#basicModal').find('.modal-body').text(`請稍等...`);
    $('#basicModal').find('.modal-footer').text(``);
    $('#basicModal').modal('show');
    $.ajax({
      url:'/file/progress',
      type:'post',
      data:{
        url:window.location.href,
        id:id
      },
      dataType:'json',
      success:function(response){
        $(response).each(function(){
          window.location.href = `${this.url}?id=${file_id}&file_id_dest=${file_id_dest}`
        })
      }
    })
  }
  $(document).on('change','[name=inputCheck]',function(){
    let element = this;
    if($(this).prop('checked')){
      $.ajax({
        url:`/components/comment`,
        type:'post',
        data:{
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          confidence:$(element).attr('data-confidence'),
          comment:$(element).closest('tr').find('[name=inputComment]').val(),
          module_name:'研發'
        },
      })
    }else{
      $.ajax({
        url:`/components/comment`,
        type:'delete',
        data:{
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          module_name:'研發'
        },
      })
    }
  })
  $(document).on('input','[name=inputComment]',function(){
    let element = this;
    if($(element).closest('tr').find('[name=inputCheck]').prop('checked')){
      $.ajax({
        url:`/components/comment`,
        type:'post',
        data:{
          process_id: $(element).attr('data-process_id'),
          crop_id: $(element).attr('data-crop_id'),
          confidence:$(element).attr('data-confidence'),
          comment:$(element).closest('tr').find('[name=inputComment]').val(),
          module_name:'研發'
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
    dataType: 'json',
    success: function(response) {
      $(response.file_information).each(function() {
        $('#spanUploadTime').text(this.upload_time)
        $('#spanFileId').text(this.order_name)
        $('#imgThumbnail').attr('src',`/file/${this.id}`)
        $('#tdThumbnailDest').html(`
            <img src="/file/${file_id_dest}" class="img-thumbnail" />
        `)
      })

      $('#list-tab').html(``);
      let list_tab = $(`<ul class="list-group list-group-horizontal w-100"></ul>`);
      let list_color = null;
      $(response.state).each(function(index) {
        if(index==0 || list_color != this.module_color){
          list_color = this.module_color;
          if(index!=0)
            $('#list-tab').append($(list_tab)[0].outerHTML);
          list_tab = $(`
            <div class="alert alert${this.module_color} form-group" role="alert">
              <p class="alert-heading text-center">${this.module_name}</p>
              <div class="list-group list-group-horizontal" role="tablist">
                <ul class="list-group list-group-horizontal">
                </ul>
              </div>
            </div>
          `);
        }
        $(list_tab).find('ul').append(`
          <li class="list-group-item list-group-item${this['update_time']!=null?this.module_color:''} flex-fill text-nowrap ${location.href.indexOf(this.url+'?')!=-1?'active':''}" onclick="javascript:location.href='${this['url']}?id=${file_id}&file_id_dest=${file_id_dest}'">${this['progress']}</li>
        `);
        if(index==response.state.length-1){
          $('#list-tab').append($(list_tab)[0].outerHTML);
        }
      })
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