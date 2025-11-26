<?php include(__DIR__ . '/basic/header.html'); ?>
<style>
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
                <th>訂單資訊</th>
                <th>註記</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td width=10%>
                  <img class="img-thumbnail" id="imgThumbnail">
                </td>
                <td>
                  <div class="form-group row">
                    <label for="inputitemNo" class="col-xl-auto col-form-label">品號：</label>
                    <input type="text" class="form-control col-xl-6" data-type="itemNo" name="inputitemNo" disabled>
                    <button type="button" class="col-xl-auto btn btn-primary" data-toggle="modal" data-target="#exampleModal2" data-type="selectItemNO">修改</button>
                  </div>
                  <div class="row">
                    <p>客戶圖號：<span id="spanFileId">1</span></p>
                  </div>
                  <div class="row">
                    <p>開單時間：<span id="spanUploadTime">2021/05/27</span></p>
                  </div>
                </td>
                <td id="allcomment">尚未有任何註記</td>
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
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 card-deck" id="divStation">
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
                <div class="col table-responsive">
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
              技術站
            </div>
            <div class="card-body">
              <div class="row">
                <label class="col-form-label col-1">相似度結果</label>
                <div class="col table-responsive row">
                  <div class="col-sm-auto form-group row">
                    <label class="col-form-label col-auto">相似度門檻：50%</label>
                    <label class="col-form-label col-auto">參考數量：10張</label>
                  </div>
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
                <label class="col-form-label col-auto">外包成本：</label>
                <label class="col-form-label col-auto">15000</label>
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
<div class="row" id="appraisalSummary">
  <div class="col">
    <div class="card shadow mb-4">
      <div class="card-header">
        估價摘要
        <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="選擇各部門建議，或選擇智能建議進行報價"></i>
      </div>
      <div class="card-body h-100">
        分為以下兩種計算方式
        <ul>
          <li>依各部門建議之相似零件均價之價格</li>
          <li>以智能辨識最相似之價格</li>
        </ul>
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="pills-develop-tab" data-toggle="pill" href="#pills-develop" role="tab" aria-controls="pills-develop" aria-selected="true">各部門建議</a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="pills-deeplearning-tab" data-toggle="pill" href="#pills-deeplearning" role="tab" aria-controls="pills-deeplearning" aria-selected="false">智能估價建議</a>
          </li>
        </ul>
        <ul>
          <li>請點選零件來檢視價格趨勢</li>
        </ul>
        <div class="tab-content" id="pills-tabContent">
          <div class="tab-pane fade show active" id="pills-develop" role="tabpanel" aria-labelledby="pills-develop-tab">
            <div class="form-group row">
              <div class="col-sm-auto form-group row">
                <label class="col-form-label col-auto">零件相似度門檻：</label>
                <div class="col-auto">
                  <select class="form-control" id="selectThreeshold_develop" name="selectThreeshold">
                    <option value="0">0%</option>
                    <option value="10">10%</option>
                    <option value="20">20%</option>
                    <option value="30">30%</option>
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
                <label class="col-form-label col-auto">各部門建議數量：</label>
                <div class="col-auto">
                  <select class="form-control" id="selectLimit_develop" name="selectLimit">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                    <option value="13">13</option>
                    <option value="14">14</option>
                    <option value="15">15</option>
                    <option value="16">16</option>
                    <option value="17">17</option>
                    <option value="18">18</option>
                    <option value="19">19</option>
                    <option value="20">20</option>
                  </select>
                </div>
                <label class="col-form-label col-auto">篩選結果：</label>
                <label class="col-form-label col-auto" id="labelCount_develop">2</label>
                <label class="col-form-label col-auto">/</label>
                <label class="col-form-label col-auto" id="labelTotal_develop">30</label>
                <label class="col-form-label col-auto">張</label>
              </div>
            </div>
            <div class="overflow-auto">
              <table class="table" style="width:1000px" id="dataTable_develop">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>智能建議金額</th>
                    <th>研發用料成本</th>
                    <th>技術製程成本</th>
                    <th>生管外包成本</th>
                  </tr>
                </thead>

              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="pills-deeplearning" role="tabpanel" aria-labelledby="pills-deeplearning-tab">
            <div class="form-group row">
              <div class="col-sm-auto form-group row">
                <label class="col-form-label col-auto">零件相似度門檻：</label>
                <div class="col-auto">
                  <select class="form-control" id="selectThreeshold_deeplearning" name="selectThreeshold">
                    <option value="0">0%</option>
                    <option value="10">10%</option>
                    <option value="20">20%</option>
                    <option value="30">30%</option>
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
                <label class="col-form-label col-auto">智能辨識建議數量：</label>
                <div class="col-auto">
                  <select class="form-control" id="selectLimit_deeplearning" name="selectLimit">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                    <option value="13">13</option>
                    <option value="14">14</option>
                    <option value="15">15</option>
                    <option value="16">16</option>
                    <option value="17">17</option>
                    <option value="18">18</option>
                    <option value="19">19</option>
                    <option value="20">20</option>
                  </select>
                </div>
                <label class="col-form-label col-auto">篩選結果：</label>
                <label class="col-form-label col-auto" id="labelCount_deeplearning">2</label>
                <label class="col-form-label col-auto">/</label>
                <label class="col-form-label col-auto" id="labelTotal_deeplearning">30</label>
                <label class="col-form-label col-auto">張</label>
              </div>
            </div>
            <div class="overflow-auto">
              <table class="table" style="width:1000px" id="dataTable_deeplearning">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>智能建議金額</th>
                    <th>研發用料成本</th>
                    <th>技術製程成本</th>
                    <th>生管外包成本</th>
                  </tr>
                </thead>

              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
<div class="col-md-12" id="WeightConverter" readonly="true"></div>

  <div class="col-md-8" id="checkQuotaton">
    <!-- <div class="card shadow mb-4 h-100">
      <div class="card-header">
        報價確認
        
      </div>
      <div class="card-body overflow-auto">
        <div class="form-group row ">
            <label class="col-form-label col-12">2021-06-25</label>
            <label class="col-form-label col-auto">報價金額：5000 數量：20 折扣：200</label>
            <label class="col-form-label col-auto">註記：再增加數量，給予優惠</label>
            <label class="col-form-label col-12">2021-06-23</label>
            <label class="col-form-label col-auto">報價金額：4200 數量：15 折扣：100</label>
            <label class="col-form-label col-auto">註記：增加數量</label>
            <label class="col-form-label col-12">2021-06-21</label>
            <label class="col-form-label col-auto">報價金額：3300 數量：10 折扣：0</label>
            <label class="col-form-label col-auto">註記：第一次報價</label>
        </div>
        <form id="formQuotation">
          <div class="row ">
            <div class="form-group row col-6">
              <label class="col-form-label col-auto">報價金額：</label>
              <div class="col">
                <input class="form-control form-control-user"  data-type="cost" name="inputQuotation" placeholder="ex.123456" type="number" autocomplete="off"required>
              </div>
            </div>
            <div class="form-group row col-6">
              <label class="col-form-label col-auto">報價數量：</label>
              <div class="col">
                <input class="form-control form-control-user"  data-type="num" name="inputQuotation" placeholder="ex.10" type="text" autocomplete="off"required>
              </div>
            </div>
            <div class="form-group row col-6">
              <label class="col-form-label col-auto">報價折扣：</label>
              <div class="col">
                <input class="form-control form-control-user"  data-type="discount" name="inputQuotation" placeholder="ex.90" type="text" autocomplete="off" required>
              </div>
              <label class="col-form-label col-auto">%</label>
            </div>
            <div class="form-group row col-6">
              <label class="col-form-label col-auto">報價註記：</label>
              <div class="col">
                <input class="form-control form-control-user"  data-type="descript" name="inputQuotation" placeholder="ex.此報價為初次報價" type="text" autocomplete="off"required>
              </div>
            </div>
            <div class="form-group row col-12">
              <label class="col-form-label col-auto">成單廠商：</label>
              <div class="col">
                <input class="form-control form-control-user"  data-type="outresourcer" name="inputQuotation" placeholder="ex.Ferriere di Stabio" type="text"required autocomplete="off">
              </div>
            </div>
            <div class="form-group row col-12">
              <label class="col-form-label col-auto">交貨日：</label>
              <div class="col">
                <input type="datetime-local" class="form-control" data-type="deadline" name="inputQuotation" required>

              </div>
            </div>
            <div class="form-group col-12">
              <button type="submit" class="btn btn-primary" id="btnQuotation">確認</button>
            </div>
          </div>
        </form>
      </div>
    </div> -->
  </div>
  <div class="col-md-4">
    <div class="card shadow mb-4 h-100">
      <div class="card-header">
        報價單寄送
        <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="輸入廠商mail，並選擇產生報價單勾選要一同寄送的報價單"></i>

      </div>
      <div class="card-body overflow-auto">
        <form id="emailForm">
          <div class="form-group row col-auto">
            <label class="col-form-label col-auto">客戶Email：</label>
            <div class="col-auto">
              <input class="form-control form-control-user" data-type="email" name="inputpdfemail" placeholder="ex.mil@mil.com" type="email" autocomplete="off" required>
            </div>
          </div>
          <!-- <div class="form-group row col-auto">
            <label class="col-form-label col-auto">報價日期：</label>
            <div class="col-auto">
              <input class="form-control form-control-user" data-type="discription" name="" placeholder="ex.mil@mil.com" type="datetime-local" autocomplete="off">
            </div>
          </div> -->
          <!-- <div class="form-group row col-auto">
            <label class="col-form-label col-auto">客戶材質：</label>
            <div class="col-auto">
              <input class="form-control form-control-user" data-type="material" name="inputpdfemail" placeholder="ex.DC53-標準程式"  autocomplete="off" required>
            </div>
          </div>
          <div class="form-group row col-auto">
            <label class="col-form-label col-auto">客戶鍍鈦：</label>
            <div class="col-auto">
              <input class="form-control form-control-user" data-type="titanizing" name="inputpdfemail" placeholder="ex.TiN"  autocomplete="off" required>
            </div>
          </div> -->
          <div class="form-group col-auto form-check-inline">
            <label class="form-check-label">報價格式：</label>
            <div class="form-check">
              <input class="form-check-input" data-type="discription" value="xlsx" name="" type="checkbox" autocomplete="off" checked="checked">
              <label class="form-check-label">Excel</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" data-type="discription" value="pdf" name="" type="checkbox" autocomplete="off">
              <label class="form-check-label">PDF</label>
            </div>
          </div>
          <div class="form-group col-12">
            <button type="submit" class="btn btn-primary" data-type="sendQuotation" id="btnQuotation">寄送</button>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal" data-type="generateQuotation" id="btnGenerateQuotation">產生報價單</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-12 col-md mb-4">
    <div class="row row-cols-1 h-100">
      <div class="col">
        <div class="card shadow h-100">
          <div class="card-header">
            智能辨識結果
            <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="請點選篩選設定，過濾您想要的零件"></i>

          </div>
          <div class="card-body h-100">
            <div class="accordion" id="accordionFilter">
              <div class="card">
                <div class="card-header" id="accordionHeadingFilter">
                  <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
                      篩選設定
                    </button>
                  </h2>
                </div>
                <div id="collapseFilter" class="collapse" aria-labelledby="accordionHeadingFilter" data-parent="#accordionFilter">
                  <div class="card-body overflow-auto pb-4">
                    <div class="form-group row">
                      <div class="form-group row col-6 col-xl-4">
                        <label class="col-form-label col-sm-auto">年份：</label>
                        <div class="col row" id="divYear"></div>
                      </div>
                      <div class="form-group row col-6 col-xl-4">
                        <label class="col-form-label col-sm-auto">材質：</label>
                        <div class="col row" id="divMaterial"></div>
                      </div>
                      <div class="form-group row col-6 col-xl-4">
                        <label class="col-form-label col-sm-auto">鍍鈦：</label>
                        <div class="col row" id="divTitanizing"></div>
                      </div>
                      <div class="form-group row col-6 col-xl-4">
                        <label class="col-form-label col-sm-auto">硬度：</label>
                        <div class="col row" id="divHardness"></div>
                      </div>
                      <div class="form-group row col-6 col-xl-4">
                        <label class="col-form-label col-sm-auto">材料：</label>
                        <div class="col row" id="divStuff"></div>
                      </div>
                      <div class="form-group row col-12">
                        <label class="col-form-label col-sm-auto">客戶：</label>
                        <div class="col row" id="divCustomer"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <table class="table" width=100% id="dataTable_detail">
              <thead>
                <tr>
                  <th></th>
                  <th>零件名稱</th>
                  <th>相似度</th>
                  <th>年份</th>
                  <th>歷史金額</th>
                </tr>
              </thead>
              <tbody>
                <!-- <tr>
                  <th>1</th>
                  <td>42”直下型背光模組</td>
                  <td>2022</td>
                  <td>300</td>
                </tr>
                <tr>
                  <th>2</th>
                  <td>42”中空型背光模組</td>
                  <td>2021</td>
                  <td>200</td>
                </tr> -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md mb-4">
    <div class="row row-cols-1 h-100">
      <div class="col">
        <div class="card shadow h-100">
          <div class="card-header">成本趨勢
            <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="請點選成本種類來檢視價格趨勢"></i>
          </div>
          <div class="card-body overflow-auto">
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="pills-stuff-tab" data-toggle="pill" href="#pills-stuff" role="tab" aria-controls="pills-stuff" aria-selected="true">
                  材料
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="pills-processing-tab" data-toggle="pill" href="#pills-processing" role="tab" aria-controls="pills-processing" aria-selected="false">
                  加工
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="pills-profit-tab" data-toggle="pill" href="#pills-profit" role="tab" aria-controls="pills-profit" aria-selected="false">
                  利潤
                </a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="pills-shipping-tab" data-toggle="pill" href="#pills-shipping" role="tab" aria-controls="pills-shipping" aria-selected="false">
                  運費
                </a>
              </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
              <div class="tab-pane fade show active" id="pills-stuff" role="tabpanel" aria-labelledby="pills-develop-tab">
                <div class="chart-area">
                  <canvas id="myAreaChart"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="card">
  <div class="card-header" id="headingTwo">
    外包廠商
    <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="可選擇時間區間查詢歷史外包情形"></i>
  </div>
  <div class="card-body">
    <!-- <div class="row row-cols-1 row-cols-md-2"> -->
    <div class="col" hidden>
      <label class="row">目前外包狀況</label>
      <div class="row">
        <label class="col-form-label col-3">起</label>
        <div class="col-3">
          <input type="datetime-local" class="form-control" name="temparyOutsourcer" data-type="start" />
        </div>
        <label class="col-form-label col-3">迄</label>
        <div class="col-3">
          <input type="datetime-local" class="form-control" name="temparyOutsourcer" data-type="end" />
        </div>
      </div>
      <div class="row">
        <canvas id="BarChart2"></canvas>
      </div>
    </div>
    <div class="col">
      <label class="row">歷史外包狀況</label>
      <div class="row">
        <label class="col-form-label col-3">起</label>
        <div class="col-3">
          <input type="datetime-local" class="form-control" name="historyOutsourcer" data-type="start" />
        </div>
        <label class="col-form-label col-3">迄</label>
        <div class="col-3">
          <input type="datetime-local" class="form-control" name="historyOutsourcer" data-type="end" />
        </div>
      </div>
      <div class="row">
        <canvas id="BarChart3"></canvas>
      </div>
    </div>
    <!-- </div> -->
    <!-- <form id="formOutsourcer">
      <div class="form-group row col-sm-auto">
        <label class="col-form-label col-auto">外包廠商：</label>
        <div class="col-auto">
          <input id="inputOutsourcer" class="form-control" list="datalistOutresourcer">
          <datalist id="datalistOutresourcer">
            <option value="鼎勝"></option>
            <option value="宇喬"></option>
            <option value="德鑫"></option>
            <option value="衡泰"></option>
            <option value="皇億"></option>
            <option value="全陞"></option>
            <option value="偉程"></option>
          </datalist>
        </div>
        <label class="col-form-label col-auto">外包金額：</label>
        <div class="col-auto">
          <input id="inputOutsourcerAmount" class="form-control">

        </div>
        <button type="submit" class="btn btn-primary">確定</button>
      </div>
    </form> -->
  </div>
</div>
<?php include(__DIR__ . '/basic/footer.html'); ?>
<script src="/vendor/select-pure/dist/select-pure.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
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
  let material = [{
      "value": "133",
      "label": "1.2379道-標"
    },
    {
      "value": "158",
      "label": "1.2767道博"
    },
    {
      "value": "022",
      "label": "1045-AS DWG."
    },
    {
      "value": "123",
      "label": "304不銹鋼"
    },
    {
      "value": "025",
      "label": "4140-AS DWG."
    },
    {
      "value": "069",
      "label": "4140-油淬"
    },
    {
      "value": "089",
      "label": "4140-鹽淬"
    },
    {
      "value": "080",
      "label": "420J2"
    },
    {
      "value": "045",
      "label": "4340-油淬"
    },
    {
      "value": "137",
      "label": "8620"
    },
    {
      "value": "101",
      "label": "AISI303-STAINLESS"
    },
    {
      "value": "018",
      "label": "AS DWG"
    },
    {
      "value": "063",
      "label": "ASP23"
    },
    {
      "value": "098",
      "label": "ASP23-MIL60"
    },
    {
      "value": "093",
      "label": "ASP23-MIL60R"
    },
    {
      "value": "084",
      "label": "ASP23-MIL60S"
    },
    {
      "value": "100",
      "label": "ASP23-油淬"
    },
    {
      "value": "095",
      "label": "ASP23-舊油"
    },
    {
      "value": "138",
      "label": "ASP30"
    },
    {
      "value": "081",
      "label": "ASP60"
    },
    {
      "value": "086",
      "label": "ASP60-MIL60S"
    },
    {
      "value": "097",
      "label": "ASP60-油淬"
    },
    {
      "value": "163",
      "label": "BC6C青銅"
    },
    {
      "value": "132",
      "label": "C1100-紅銅"
    },
    {
      "value": "127",
      "label": "C3604黃銅"
    },
    {
      "value": "168",
      "label": "C90700/PBC2C磷青銅"
    },
    {
      "value": "165",
      "label": "C93200"
    },
    {
      "value": "152",
      "label": "C93210鉛青銅"
    },
    {
      "value": "012",
      "label": "CARBIDE"
    },
    {
      "value": "040",
      "label": "CARBIDE+SKD61"
    },
    {
      "value": "169",
      "label": "CPM 10V"
    },
    {
      "value": "170",
      "label": "CPM-3V"
    },
    {
      "value": "151",
      "label": "CPM-M4"
    },
    {
      "value": "156",
      "label": "Caldie-客帶料"
    },
    {
      "value": "031",
      "label": "DC53+CARBIDE"
    },
    {
      "value": "009",
      "label": "DC53-AS DWG"
    },
    {
      "value": "038",
      "label": "DC53-MIL60"
    },
    {
      "value": "062",
      "label": "DC53-MIL60S"
    },
    {
      "value": "056",
      "label": "DC53-一般程式"
    },
    {
      "value": "135",
      "label": "DC53-局部硬化"
    },
    {
      "value": "064",
      "label": "DC53-局部鹽淬"
    },
    {
      "value": "111",
      "label": "DC53-榮剛"
    },
    {
      "value": "041",
      "label": "DC53-標準程式"
    },
    {
      "value": "126",
      "label": "DIN1.2365"
    },
    {
      "value": "181",
      "label": "DIN1.2367"
    },
    {
      "value": "155",
      "label": "Dievar-客帶料"
    },
    {
      "value": "122",
      "label": "Eberhard"
    },
    {
      "value": "174",
      "label": "G10"
    },
    {
      "value": "175",
      "label": "G15"
    },
    {
      "value": "177",
      "label": "G20"
    },
    {
      "value": "171",
      "label": "G30"
    },
    {
      "value": "115",
      "label": "G4-FMD"
    },
    {
      "value": "176",
      "label": "G40"
    },
    {
      "value": "172",
      "label": "G50"
    },
    {
      "value": "173",
      "label": "G55"
    },
    {
      "value": "082",
      "label": "HAP10"
    },
    {
      "value": "149",
      "label": "K340"
    },
    {
      "value": "141",
      "label": "K890"
    },
    {
      "value": "015",
      "label": "M42-MIL60"
    },
    {
      "value": "052",
      "label": "M42-MIL60R"
    },
    {
      "value": "021",
      "label": "M42-MIL60S"
    },
    {
      "value": "079",
      "label": "M42-低溫油淬(1150)"
    },
    {
      "value": "055",
      "label": "M42-油淬"
    },
    {
      "value": "049",
      "label": "M42-耐沖/油淬"
    },
    {
      "value": "027",
      "label": "M42-耐破裂"
    },
    {
      "value": "070",
      "label": "M42-舊-油"
    },
    {
      "value": "032",
      "label": "M42-舊程式"
    },
    {
      "value": "071",
      "label": "M42-鹽淬"
    },
    {
      "value": "139",
      "label": "M7"
    },
    {
      "value": "001",
      "label": "MIL-TIP"
    },
    {
      "value": "035",
      "label": "MIL-TIP+柄MCR1"
    },
    {
      "value": "017",
      "label": "MIL60"
    },
    {
      "value": "020",
      "label": "MIL60S"
    },
    {
      "value": "013",
      "label": "R3-MIL60R"
    },
    {
      "value": "014",
      "label": "R4-MIL60R"
    },
    {
      "value": "142",
      "label": "S390"
    },
    {
      "value": "019",
      "label": "S45C-AS DWG"
    },
    {
      "value": "108",
      "label": "S45C-水淬"
    },
    {
      "value": "099",
      "label": "S45C-油淬"
    },
    {
      "value": "092",
      "label": "S590-MIL60"
    },
    {
      "value": "118",
      "label": "S590-MIL60R"
    },
    {
      "value": "083",
      "label": "S590-MIL60S"
    },
    {
      "value": "147",
      "label": "S7"
    },
    {
      "value": "051",
      "label": "SAE J403 1018-1022"
    },
    {
      "value": "143",
      "label": "SAE64青銅"
    },
    {
      "value": "121",
      "label": "SAE660青銅"
    },
    {
      "value": "125",
      "label": "SAE841油銅"
    },
    {
      "value": "110",
      "label": "SCM415"
    },
    {
      "value": "144",
      "label": "SCM435"
    },
    {
      "value": "061",
      "label": "SCM440"
    },
    {
      "value": "130",
      "label": "SK5H預硬鋼"
    },
    {
      "value": "106",
      "label": "SKD11-AS DWG"
    },
    {
      "value": "008",
      "label": "SKD11-標準"
    },
    {
      "value": "164",
      "label": "SKD11榮剛"
    },
    {
      "value": "112",
      "label": "SKD11標-榮剛"
    },
    {
      "value": "113",
      "label": "SKD61"
    },
    {
      "value": "010",
      "label": "SKD61-AS DWG"
    },
    {
      "value": "029",
      "label": "SKD61-MIL60"
    },
    {
      "value": "058",
      "label": "SKD61-一般程式"
    },
    {
      "value": "065",
      "label": "SKD61-局部鹽淬"
    },
    {
      "value": "117",
      "label": "SKD61-風扇冷卻"
    },
    {
      "value": "179",
      "label": "SKD61-榮剛"
    },
    {
      "value": "037",
      "label": "SKD61-標準"
    },
    {
      "value": "054",
      "label": "SKD61-標準程式-真空爐"
    },
    {
      "value": "109",
      "label": "SKD61標-榮剛"
    },
    {
      "value": "047",
      "label": "SKH55+SKD61"
    },
    {
      "value": "003",
      "label": "SKH55-AS DWG"
    },
    {
      "value": "002",
      "label": "SKH55-MIL60"
    },
    {
      "value": "024",
      "label": "SKH55-MIL60R"
    },
    {
      "value": "023",
      "label": "SKH55-MIL60S"
    },
    {
      "value": "039",
      "label": "SKH55-PM M4"
    },
    {
      "value": "004",
      "label": "SKH55-油淬"
    },
    {
      "value": "048",
      "label": "SKH55-耐沖/油淬"
    },
    {
      "value": "074",
      "label": "SKH55-舊-油"
    },
    {
      "value": "034",
      "label": "SKH55-舊程式"
    },
    {
      "value": "066",
      "label": "SKH55-鹽淬"
    },
    {
      "value": "050",
      "label": "SKH9+SKD-61"
    },
    {
      "value": "006",
      "label": "SKH9-AS DWG"
    },
    {
      "value": "005",
      "label": "SKH9-MIL60"
    },
    {
      "value": "028",
      "label": "SKH9-MIL60R"
    },
    {
      "value": "107",
      "label": "SKH9-MIL60S"
    },
    {
      "value": "136",
      "label": "SKH9-局部硬化"
    },
    {
      "value": "007",
      "label": "SKH9-油淬"
    },
    {
      "value": "044",
      "label": "SKH9-耐破裂"
    },
    {
      "value": "057",
      "label": "SKH9-標準程式"
    },
    {
      "value": "077",
      "label": "SKH9-舊油"
    },
    {
      "value": "042",
      "label": "SKH9-舊程式"
    },
    {
      "value": "067",
      "label": "SKH9-鹽淬"
    },
    {
      "value": "114",
      "label": "SNCM220"
    },
    {
      "value": "011",
      "label": "SOLID CARBIDE"
    },
    {
      "value": "053",
      "label": "STELITE"
    },
    {
      "value": "073",
      "label": "SUJ2(軸承鋼)-油淬"
    },
    {
      "value": "068",
      "label": "SUS304"
    },
    {
      "value": "060",
      "label": "SUS440C+銅"
    },
    {
      "value": "140",
      "label": "T15-USA"
    },
    {
      "value": "124",
      "label": "TR15C-"
    },
    {
      "value": "148",
      "label": "V4"
    },
    {
      "value": "154",
      "label": "V4-客戶帶料"
    },
    {
      "value": "116",
      "label": "W360"
    },
    {
      "value": "043",
      "label": "YXR3"
    },
    {
      "value": "078",
      "label": "YXR3-MIL60"
    },
    {
      "value": "030",
      "label": "YXR3-油淬-MIL60"
    },
    {
      "value": "033",
      "label": "YXR3-耐破裂"
    },
    {
      "value": "059",
      "label": "YXR3-標準程式"
    },
    {
      "value": "075",
      "label": "YXR3-舊油"
    },
    {
      "value": "026",
      "label": "YXR3-舊程式"
    },
    {
      "value": "076",
      "label": "YXR3-舊鹽淬"
    },
    {
      "value": "036",
      "label": "YXR3-鹽淬-MIL60"
    },
    {
      "value": "090",
      "label": "YXR4-油淬"
    },
    {
      "value": "102",
      "label": "YXR4-舊油"
    },
    {
      "value": "091",
      "label": "不銹鋼316L(美規)"
    },
    {
      "value": "128",
      "label": "不銹鋼420J1"
    },
    {
      "value": "046",
      "label": "中碳鋼-AS DWG."
    },
    {
      "value": "104",
      "label": "日立SGT(SKS3)"
    },
    {
      "value": "088",
      "label": "青銅(JIS CAC406C)"
    },
    {
      "value": "166",
      "label": "青銅C83600"
    },
    {
      "value": "167",
      "label": "青銅C95500"
    },
    {
      "value": "161",
      "label": "客供品"
    },
    {
      "value": "129",
      "label": "紅銅C11000"
    },
    {
      "value": "087",
      "label": "紅銅板"
    },
    {
      "value": "157",
      "label": "套金銅BC6"
    },
    {
      "value": "193",
      "label": "能登DR05C"
    },
    {
      "value": "182",
      "label": "能登DR07C"
    },
    {
      "value": "183",
      "label": "能登DR09C"
    },
    {
      "value": "184",
      "label": "能登DR11C"
    },
    {
      "value": "185",
      "label": "能登DR14C"
    },
    {
      "value": "186",
      "label": "能登DR17C"
    },
    {
      "value": "194",
      "label": "能登TR05C"
    },
    {
      "value": "188",
      "label": "能登TR09C"
    },
    {
      "value": "189",
      "label": "能登TR15C"
    },
    {
      "value": "190",
      "label": "能登TR20C"
    },
    {
      "value": "191",
      "label": "能登TR25C"
    },
    {
      "value": "192",
      "label": "能登YR28C"
    },
    {
      "value": "120",
      "label": "高拉力鈹銅C17200"
    },
    {
      "value": "096",
      "label": "無氧銅"
    },
    {
      "value": "119",
      "label": "紫銅C1100"
    },
    {
      "value": "016",
      "label": "黃銅"
    },
    {
      "value": "178",
      "label": "銅C90800"
    },
    {
      "value": "094",
      "label": "銅鎢"
    },
    {
      "value": "105",
      "label": "鉻鋯銅"
    },
    {
      "value": "103",
      "label": "彈簧鋼SK5(中鋼K5)"
    },
    {
      "value": "131",
      "label": "鋁板材"
    },
    {
      "value": "150",
      "label": "鋁青銅 95400"
    },
    {
      "value": "180",
      "label": "鋁青銅C95400"
    },
    {
      "value": "145",
      "label": "鋁青銅C95800"
    },
    {
      "value": "162",
      "label": "鋁青銅C95810"
    },
    {
      "value": "146",
      "label": "鋁圓棒"
    },
    {
      "value": "085",
      "label": "磷青銅(JIS-C-5191)"
    },
    {
      "value": "134",
      "label": "磷青銅CAC502C"
    },
    {
      "value": "160",
      "label": "磷青銅CuSn10"
    },
    {
      "value": "159",
      "label": "磷青銅CuSn6"
    },
    {
      "value": "153",
      "label": "磷青銅GC-CUSN10"
    }
  ];

  let titanizing = [{
      "value": "025",
      "label": "中日滲氮-Nitriding"
    },
    {
      "value": "001",
      "label": "TiN"
    },
    {
      "value": "002",
      "label": "TiAlN"
    },
    {
      "value": "003",
      "label": "CrN"
    },
    {
      "value": "004",
      "label": "CVD"
    },
    {
      "value": "005",
      "label": "滲氮"
    },
    {
      "value": "006",
      "label": "TIALN*2"
    },
    {
      "value": "007",
      "label": "TICN"
    },
    {
      "value": "008",
      "label": "TIALN-衡泰"
    },
    {
      "value": "009",
      "label": "TiAlN-雙面"
    },
    {
      "value": "010",
      "label": "AlCrN-衡泰"
    },
    {
      "value": "011",
      "label": "氮化處理"
    },
    {
      "value": "012",
      "label": "TiAlN(加厚)"
    },
    {
      "value": "013",
      "label": "TiN*2"
    },
    {
      "value": "014",
      "label": "CrN*2"
    },
    {
      "value": "015",
      "label": "火焰硬化"
    },
    {
      "value": "016",
      "label": "染黑處理(BLACK OXIDE)"
    },
    {
      "value": "017",
      "label": "TiAlCN-瀚承"
    },
    {
      "value": "018",
      "label": "真空氮化(Nitriding)"
    },
    {
      "value": "019",
      "label": "AlTiCrN-瀚承"
    },
    {
      "value": "020",
      "label": "TaCoN-衡泰"
    },
    {
      "value": "021",
      "label": "AlCrONa"
    },
    {
      "value": "022",
      "label": "AlCrN"
    },
    {
      "value": "023",
      "label": "ZINC"
    },
    {
      "value": "024",
      "label": "鉻"
    },
    {
      "value": "026",
      "label": "DLC"
    }
  ];
  let year = [{
      "label": 2008,
      "value": "2008"
    },
    {
      "label": 2009,
      "value": "2009"
    },
    {
      "label": 2010,
      "value": "2010"
    },
    {
      "label": 2011,
      "value": "2011"
    },
    {
      "label": 2012,
      "value": "2012"
    },
    {
      "label": 2013,
      "value": "2013"
    },
    {
      "label": 2014,
      "value": "2014"
    },
    {
      "label": 2015,
      "value": "2015"
    },
    {
      "label": 2016,
      "value": "2016"
    },
    {
      "label": 2017,
      "value": "2017"
    },
    {
      "label": 2018,
      "value": "2018"
    },
    {
      "label": 2019,
      "value": "2019"
    },
    {
      "label": 2020,
      "value": "2020"
    },
    {
      "label": 2021,
      "value": "2021"
    }
  ]
  let hardness = [{
      "label": ".",
      "value": "."
    },
    {
      "label": "001",
      "value": "001"
    },
    {
      "label": "002",
      "value": "002"
    },
    {
      "label": "017",
      "value": "017"
    },
    {
      "label": "018",
      "value": "018"
    },
    {
      "label": 1,
      "value": 1
    },
    {
      "label": "15-20",
      "value": "15-20"
    },
    {
      "label": "20-25",
      "value": "20-25"
    },
    {
      "label": "20-30",
      "value": "20-30"
    },
    {
      "label": "24-32",
      "value": "24-32"
    },
    {
      "label": "25-27",
      "value": "25-27"
    },
    {
      "label": "25-30",
      "value": "25-30"
    },
    {
      "label": "25-32",
      "value": "25-32"
    },
    {
      "label": "25-35",
      "value": "25-35"
    },
    {
      "label": "26-32",
      "value": "26-32"
    },
    {
      "label": "27-30",
      "value": "27-30"
    },
    {
      "label": "27-31",
      "value": "27-31"
    },
    {
      "label": "27-32",
      "value": "27-32"
    },
    {
      "label": "27-37",
      "value": "27-37"
    },
    {
      "label": "28-30",
      "value": "28-30"
    },
    {
      "label": "28-32",
      "value": "28-32"
    },
    {
      "label": "28-34",
      "value": "28-34"
    },
    {
      "label": "28-35",
      "value": "28-35"
    },
    {
      "label": "28-35(局部",
      "value": "28-35(局部"
    },
    {
      "label": "3-4340-5",
      "value": "3-4340-5"
    },
    {
      "label": "30-35",
      "value": "30-35"
    },
    {
      "label": "30-35榮剛",
      "value": "30-35榮剛"
    },
    {
      "label": "30-40",
      "value": "30-40"
    },
    {
      "label": 32,
      "value": 32
    },
    {
      "label": "32-35",
      "value": "32-35"
    },
    {
      "label": "32-36",
      "value": "32-36"
    },
    {
      "label": "32-38",
      "value": "32-38"
    },
    {
      "label": "32-40",
      "value": "32-40"
    },
    {
      "label": "33-37",
      "value": "33-37"
    },
    {
      "label": "34-44",
      "value": "34-44"
    },
    {
      "label": "35-37",
      "value": "35-37"
    },
    {
      "label": "35-38",
      "value": "35-38"
    },
    {
      "label": "35-40",
      "value": "35-40"
    },
    {
      "label": "35-43",
      "value": "35-43"
    },
    {
      "label": "35-45",
      "value": "35-45"
    },
    {
      "label": "36-40",
      "value": "36-40"
    },
    {
      "label": "36-42",
      "value": "36-42"
    },
    {
      "label": "36-44",
      "value": "36-44"
    },
    {
      "label": "37-39",
      "value": "37-39"
    },
    {
      "label": "38-40",
      "value": "38-40"
    },
    {
      "label": "38-42",
      "value": "38-42"
    },
    {
      "label": "38-43",
      "value": "38-43"
    },
    {
      "label": "38-45",
      "value": "38-45"
    },
    {
      "label": "38-48",
      "value": "38-48"
    },
    {
      "label": "39-41",
      "value": "39-41"
    },
    {
      "label": "40-42",
      "value": "40-42"
    },
    {
      "label": "40-44",
      "value": "40-44"
    },
    {
      "label": "40-45",
      "value": "40-45"
    },
    {
      "label": "40-46",
      "value": "40-46"
    },
    {
      "label": "40-50",
      "value": "40-50"
    },
    {
      "label": "41-45",
      "value": "41-45"
    },
    {
      "label": "42-44",
      "value": "42-44"
    },
    {
      "label": "42-45",
      "value": "42-45"
    },
    {
      "label": "42-46",
      "value": "42-46"
    },
    {
      "label": "42-48",
      "value": "42-48"
    },
    {
      "label": "42-50",
      "value": "42-50"
    },
    {
      "label": "43-45",
      "value": "43-45"
    },
    {
      "label": "43-46",
      "value": "43-46"
    },
    {
      "label": "43-47",
      "value": "43-47"
    },
    {
      "label": "43-48",
      "value": "43-48"
    },
    {
      "label": "44-46",
      "value": "44-46"
    },
    {
      "label": "44-47",
      "value": "44-47"
    },
    {
      "label": "44-48",
      "value": "44-48"
    },
    {
      "label": "44-48標",
      "value": "44-48標"
    },
    {
      "label": "45-47",
      "value": "45-47"
    },
    {
      "label": "45-48",
      "value": "45-48"
    },
    {
      "label": "45-49",
      "value": "45-49"
    },
    {
      "label": "45-50",
      "value": "45-50"
    },
    {
      "label": "45-50標",
      "value": "45-50標"
    },
    {
      "label": "46-48",
      "value": "46-48"
    },
    {
      "label": "46-49",
      "value": "46-49"
    },
    {
      "label": "46-50",
      "value": "46-50"
    },
    {
      "label": "46-52",
      "value": "46-52"
    },
    {
      "label": "46-56",
      "value": "46-56"
    },
    {
      "label": "47-49",
      "value": "47-49"
    },
    {
      "label": "47-49標",
      "value": "47-49標"
    },
    {
      "label": "47-50",
      "value": "47-50"
    },
    {
      "label": "47-51",
      "value": "47-51"
    },
    {
      "label": "48-50",
      "value": "48-50"
    },
    {
      "label": "48-51",
      "value": "48-51"
    },
    {
      "label": "48-52",
      "value": "48-52"
    },
    {
      "label": "48-54",
      "value": "48-54"
    },
    {
      "label": "48-55",
      "value": "48-55"
    },
    {
      "label": "48-56",
      "value": "48-56"
    },
    {
      "label": "49-50",
      "value": "49-50"
    },
    {
      "label": "49-51",
      "value": "49-51"
    },
    {
      "label": "49-52",
      "value": "49-52"
    },
    {
      "label": "50-51",
      "value": "50-51"
    },
    {
      "label": "50-52",
      "value": "50-52"
    },
    {
      "label": "50-53",
      "value": "50-53"
    },
    {
      "label": "50-53標",
      "value": "50-53標"
    },
    {
      "label": "50-54",
      "value": "50-54"
    },
    {
      "label": "50-55",
      "value": "50-55"
    },
    {
      "label": "50-55標",
      "value": "50-55標"
    },
    {
      "label": "50-56",
      "value": "50-56"
    },
    {
      "label": "50-57",
      "value": "50-57"
    },
    {
      "label": "50-60",
      "value": "50-60"
    },
    {
      "label": 5051,
      "value": 5051
    },
    {
      "label": "50~52",
      "value": "50~52"
    },
    {
      "label": "51-52",
      "value": "51-52"
    },
    {
      "label": "51-53",
      "value": "51-53"
    },
    {
      "label": "52-53",
      "value": "52-53"
    },
    {
      "label": "52-54",
      "value": "52-54"
    },
    {
      "label": "52-55",
      "value": "52-55"
    },
    {
      "label": "52-55標",
      "value": "52-55標"
    },
    {
      "label": "52-56",
      "value": "52-56"
    },
    {
      "label": "52-58",
      "value": "52-58"
    },
    {
      "label": "52-64",
      "value": "52-64"
    },
    {
      "label": "53-54",
      "value": "53-54"
    },
    {
      "label": "53-55",
      "value": "53-55"
    },
    {
      "label": "53-55標",
      "value": "53-55標"
    },
    {
      "label": "53-56",
      "value": "53-56"
    },
    {
      "label": "53-57",
      "value": "53-57"
    },
    {
      "label": "54-55",
      "value": "54-55"
    },
    {
      "label": "54-56",
      "value": "54-56"
    },
    {
      "label": "54-56標",
      "value": "54-56標"
    },
    {
      "label": "54-57",
      "value": "54-57"
    },
    {
      "label": "54-58",
      "value": "54-58"
    },
    {
      "label": "54-60",
      "value": "54-60"
    },
    {
      "label": "54-62",
      "value": "54-62"
    },
    {
      "label": "54-62標",
      "value": "54-62標"
    },
    {
      "label": 55,
      "value": 55
    },
    {
      "label": "55-57",
      "value": "55-57"
    },
    {
      "label": "55-57標",
      "value": "55-57標"
    },
    {
      "label": "55-58",
      "value": "55-58"
    },
    {
      "label": "55-59",
      "value": "55-59"
    },
    {
      "label": "55-60",
      "value": "55-60"
    },
    {
      "label": "56-57",
      "value": "56-57"
    },
    {
      "label": "56-58",
      "value": "56-58"
    },
    {
      "label": "56-58 裂",
      "value": "56-58 裂"
    },
    {
      "label": "56-58標",
      "value": "56-58標"
    },
    {
      "label": "56-59",
      "value": "56-59"
    },
    {
      "label": "56-60",
      "value": "56-60"
    },
    {
      "label": "56-61",
      "value": "56-61"
    },
    {
      "label": "56-62",
      "value": "56-62"
    },
    {
      "label": "56-64",
      "value": "56-64"
    },
    {
      "label": "57-29",
      "value": "57-29"
    },
    {
      "label": "57-58",
      "value": "57-58"
    },
    {
      "label": "57-59",
      "value": "57-59"
    },
    {
      "label": "57-59標",
      "value": "57-59標"
    },
    {
      "label": "57-59標準",
      "value": "57-59標準"
    },
    {
      "label": "57-60",
      "value": "57-60"
    },
    {
      "label": "57-61",
      "value": "57-61"
    },
    {
      "label": "57-62",
      "value": "57-62"
    },
    {
      "label": "58-59",
      "value": "58-59"
    },
    {
      "label": "58-59標",
      "value": "58-59標"
    },
    {
      "label": "58-60",
      "value": "58-60"
    },
    {
      "label": "58-60 標準",
      "value": "58-60 標準"
    },
    {
      "label": "58-60 舊",
      "value": "58-60 舊"
    },
    {
      "label": "58-60標",
      "value": "58-60標"
    },
    {
      "label": "58-60舊",
      "value": "58-60舊"
    },
    {
      "label": "58-61",
      "value": "58-61"
    },
    {
      "label": "58-61舊",
      "value": "58-61舊"
    },
    {
      "label": "58-62",
      "value": "58-62"
    },
    {
      "label": "58-62標",
      "value": "58-62標"
    },
    {
      "label": "58-63",
      "value": "58-63"
    },
    {
      "label": "58-64",
      "value": "58-64"
    },
    {
      "label": "58~60",
      "value": "58~60"
    },
    {
      "label": "59-51",
      "value": "59-51"
    },
    {
      "label": "59-60",
      "value": "59-60"
    },
    {
      "label": "59-61",
      "value": "59-61"
    },
    {
      "label": "59-61 舊",
      "value": "59-61 舊"
    },
    {
      "label": "59-61舊",
      "value": "59-61舊"
    },
    {
      "label": "59-62",
      "value": "59-62"
    },
    {
      "label": "59-63",
      "value": "59-63"
    },
    {
      "label": "60-61",
      "value": "60-61"
    },
    {
      "label": "60-62",
      "value": "60-62"
    },
    {
      "label": "60-62標",
      "value": "60-62標"
    },
    {
      "label": "60-62舊",
      "value": "60-62舊"
    },
    {
      "label": "60-63",
      "value": "60-63"
    },
    {
      "label": "60-64",
      "value": "60-64"
    },
    {
      "label": 6062,
      "value": 6062
    },
    {
      "label": "61-62",
      "value": "61-62"
    },
    {
      "label": "61-62.5",
      "value": "61-62.5"
    },
    {
      "label": "61-63",
      "value": "61-63"
    },
    {
      "label": "61-63 舊",
      "value": "61-63 舊"
    },
    {
      "label": "61-63.5",
      "value": "61-63.5"
    },
    {
      "label": "61-63舊",
      "value": "61-63舊"
    },
    {
      "label": "61-64",
      "value": "61-64"
    },
    {
      "label": "61-65",
      "value": "61-65"
    },
    {
      "label": "61-66",
      "value": "61-66"
    },
    {
      "label": "62-61",
      "value": "62-61"
    },
    {
      "label": "62-63",
      "value": "62-63"
    },
    {
      "label": "62-63.5",
      "value": "62-63.5"
    },
    {
      "label": "62-64",
      "value": "62-64"
    },
    {
      "label": "62-64 舊",
      "value": "62-64 舊"
    },
    {
      "label": "62-64舊",
      "value": "62-64舊"
    },
    {
      "label": "62-65",
      "value": "62-65"
    },
    {
      "label": "62-65舊",
      "value": "62-65舊"
    },
    {
      "label": "62-66",
      "value": "62-66"
    },
    {
      "label": "63-64",
      "value": "63-64"
    },
    {
      "label": "63-64裂",
      "value": "63-64裂"
    },
    {
      "label": "63-64舊",
      "value": "63-64舊"
    },
    {
      "label": "63-65",
      "value": "63-65"
    },
    {
      "label": "63-65 舊",
      "value": "63-65 舊"
    },
    {
      "label": "63-65舊",
      "value": "63-65舊"
    },
    {
      "label": "63-66",
      "value": "63-66"
    },
    {
      "label": "63-67",
      "value": "63-67"
    },
    {
      "label": "64-48",
      "value": "64-48"
    },
    {
      "label": "64-65",
      "value": "64-65"
    },
    {
      "label": "64-65舊",
      "value": "64-65舊"
    },
    {
      "label": "64-66",
      "value": "64-66"
    },
    {
      "label": "64-66油淬",
      "value": "64-66油淬"
    },
    {
      "label": "64-66舊",
      "value": "64-66舊"
    },
    {
      "label": "64-67",
      "value": "64-67"
    },
    {
      "label": "64-67舊",
      "value": "64-67舊"
    },
    {
      "label": "64.5-65.5",
      "value": "64.5-65.5"
    },
    {
      "label": "65-57",
      "value": "65-57"
    },
    {
      "label": "65-66",
      "value": "65-66"
    },
    {
      "label": "65-66舊",
      "value": "65-66舊"
    },
    {
      "label": "65-67",
      "value": "65-67"
    },
    {
      "label": "65-67 磨",
      "value": "65-67 磨"
    },
    {
      "label": "65-67舊",
      "value": "65-67舊"
    },
    {
      "label": "65-68 舊",
      "value": "65-68 舊"
    },
    {
      "label": "66-67",
      "value": "66-67"
    },
    {
      "label": "66-67.5",
      "value": "66-67.5"
    },
    {
      "label": "66-67油淬",
      "value": "66-67油淬"
    },
    {
      "label": "66-68",
      "value": "66-68"
    },
    {
      "label": "66-68油淬",
      "value": "66-68油淬"
    },
    {
      "label": "66-68舊",
      "value": "66-68舊"
    },
    {
      "label": 67,
      "value": 67
    },
    {
      "label": "67-68",
      "value": "67-68"
    },
    {
      "label": "67-69",
      "value": "67-69"
    },
    {
      "label": "67-69舊",
      "value": "67-69舊"
    },
    {
      "label": "68-69",
      "value": "68-69"
    },
    {
      "label": "68-69 磨",
      "value": "68-69 磨"
    },
    {
      "label": "68-69舊",
      "value": "68-69舊"
    },
    {
      "label": "68-70",
      "value": "68-70"
    },
    {
      "label": "69-70",
      "value": "69-70"
    },
    {
      "label": "82-84",
      "value": "82-84"
    },
    {
      "label": "82-85",
      "value": "82-85"
    },
    {
      "label": "83-85",
      "value": "83-85"
    },
    {
      "label": "84-86",
      "value": "84-86"
    },
    {
      "label": "85-87",
      "value": "85-87"
    },
    {
      "label": "86-88",
      "value": "86-88"
    },
    {
      "label": "88-90",
      "value": "88-90"
    },
    {
      "label": "89-91",
      "value": "89-91"
    },
    {
      "label": "90-92",
      "value": "90-92"
    },
    {
      "label": "91-92.5",
      "value": "91-92.5"
    },
    {
      "label": 99999,
      "value": 99999
    },
    {
      "label": "C-1100",
      "value": "C-1100"
    },
    {
      "label": "C3604",
      "value": "C3604"
    },
    {
      "label": "G20",
      "value": "G20"
    },
    {
      "label": "G7",
      "value": "G7"
    },
    {
      "label": "H11C(燒結)",
      "value": "H11C(燒結)"
    },
    {
      "label": "H40",
      "value": "H40"
    },
    {
      "label": "HRA83-85",
      "value": "HRA83-85"
    },
    {
      "label": "HRA86-88",
      "value": "HRA86-88"
    },
    {
      "label": "HT",
      "value": "HT"
    },
    {
      "label": "KE-10",
      "value": "KE-10"
    },
    {
      "label": "KG-5",
      "value": "KG-5"
    },
    {
      "label": "KG3",
      "value": "KG3"
    },
    {
      "label": "KG5",
      "value": "KG5"
    },
    {
      "label": "KG5/H11C/K",
      "value": "KG5/H11C/K"
    },
    {
      "label": "KG5/KG7",
      "value": "KG5/KG7"
    },
    {
      "label": "KG5/KG7/H1",
      "value": "KG5/KG7/H1"
    },
    {
      "label": "KG7",
      "value": "KG7"
    },
    {
      "label": "RC15-20",
      "value": "RC15-20"
    },
    {
      "label": "RX-10",
      "value": "RX-10"
    },
    {
      "label": "RX10",
      "value": "RX10"
    },
    {
      "label": "RX15",
      "value": "RX15"
    },
    {
      "label": "ST-6",
      "value": "ST-6"
    },
    {
      "label": "ST6",
      "value": "ST6"
    },
    {
      "label": "ST7",
      "value": "ST7"
    },
    {
      "label": "VA70",
      "value": "VA70"
    },
    {
      "label": "VA80",
      "value": "VA80"
    },
    {
      "label": "VA90",
      "value": "VA90"
    },
    {
      "label": "VA95",
      "value": "VA95"
    },
    {
      "label": "WC 8%",
      "value": "WC 8%"
    },
    {
      "label": "WF-20",
      "value": "WF-20"
    },
    {
      "label": "WF-25",
      "value": "WF-25"
    },
    {
      "label": "WF25",
      "value": "WF25"
    },
    {
      "label": "一森G20",
      "value": "一森G20"
    },
    {
      "label": "一森G40",
      "value": "一森G40"
    },
    {
      "label": "一森G50",
      "value": "一森G50"
    },
    {
      "label": "不必熱處理",
      "value": "不必熱處理"
    },
    {
      "label": "不用熱處理",
      "value": "不用熱處理"
    },
    {
      "label": "不作熱處理",
      "value": "不作熱處理"
    },
    {
      "label": "不做熱處理",
      "value": "不做熱處理"
    },
    {
      "label": "不硬化",
      "value": "不硬化"
    },
    {
      "label": "不熱處理",
      "value": "不熱處理"
    },
    {
      "label": "夾持部硬化",
      "value": "夾持部硬化"
    },
    {
      "label": "兩段熱處理",
      "value": "兩段熱處理"
    },
    {
      "label": "春保KG-5",
      "value": "春保KG-5"
    },
    {
      "label": "春保KG-7",
      "value": "春保KG-7"
    },
    {
      "label": "春保KG3",
      "value": "春保KG3"
    },
    {
      "label": "春保KG5",
      "value": "春保KG5"
    },
    {
      "label": "春保KG6",
      "value": "春保KG6"
    },
    {
      "label": "春保KG7",
      "value": "春保KG7"
    },
    {
      "label": "春保KR20",
      "value": "春保KR20"
    },
    {
      "label": "春保ST-6",
      "value": "春保ST-6"
    },
    {
      "label": "春保ST6",
      "value": "春保ST6"
    },
    {
      "label": "春保ST7",
      "value": "春保ST7"
    },
    {
      "label": "春保VA70",
      "value": "春保VA70"
    },
    {
      "label": "春保VA80",
      "value": "春保VA80"
    },
    {
      "label": "春保VA90",
      "value": "春保VA90"
    },
    {
      "label": "春保VA95",
      "value": "春保VA95"
    },
    {
      "label": "春保WF15",
      "value": "春保WF15"
    },
    {
      "label": "春保WF20",
      "value": "春保WF20"
    },
    {
      "label": "春保WF25",
      "value": "春保WF25"
    },
    {
      "label": "春保WF30",
      "value": "春保WF30"
    },
    {
      "label": "泰登H11C",
      "value": "泰登H11C"
    },
    {
      "label": "泰登HN",
      "value": "泰登HN"
    },
    {
      "label": "泰登HT",
      "value": "泰登HT"
    },
    {
      "label": "泰登HV",
      "value": "泰登HV"
    },
    {
      "label": "泰登HY",
      "value": "泰登HY"
    },
    {
      "label": "泰登WC",
      "value": "泰登WC"
    },
    {
      "label": "能登DR05C",
      "value": "能登DR05C"
    },
    {
      "label": "能登DR09C",
      "value": "能登DR09C"
    },
    {
      "label": "能登DR11C",
      "value": "能登DR11C"
    },
    {
      "label": "能登DR14C",
      "value": "能登DR14C"
    },
    {
      "label": "能登DR17C",
      "value": "能登DR17C"
    },
    {
      "label": "能登SR10C",
      "value": "能登SR10C"
    },
    {
      "label": "能登SR13C",
      "value": "能登SR13C"
    },
    {
      "label": "能登SR16C",
      "value": "能登SR16C"
    },
    {
      "label": "能登SR22C",
      "value": "能登SR22C"
    },
    {
      "label": "能登TR05C",
      "value": "能登TR05C"
    },
    {
      "label": "能登TR09C",
      "value": "能登TR09C"
    },
    {
      "label": "能登TR15C",
      "value": "能登TR15C"
    },
    {
      "label": "能登TR20C",
      "value": "能登TR20C"
    },
    {
      "label": "能登TR25C",
      "value": "能登TR25C"
    },
    {
      "label": "能登UR10C",
      "value": "能登UR10C"
    },
    {
      "label": "能登UR13C",
      "value": "能登UR13C"
    },
    {
      "label": "能登YR10C",
      "value": "能登YR10C"
    },
    {
      "label": "能登YR28C",
      "value": "能登YR28C"
    },
    {
      "label": "源登CD337",
      "value": "源登CD337"
    },
    {
      "label": "源登RX-10",
      "value": "源登RX-10"
    },
    {
      "label": "源登RX-15",
      "value": "源登RX-15"
    },
    {
      "label": "源登RX10",
      "value": "源登RX10"
    },
    {
      "label": "源登RX15",
      "value": "源登RX15"
    },
    {
      "label": "預硬",
      "value": "預硬"
    },
    {
      "label": "標30-35",
      "value": "標30-35"
    },
    {
      "label": "標40-45",
      "value": "標40-45"
    },
    {
      "label": "整支硬化",
      "value": "整支硬化"
    },
    {
      "label": "頭部40-50",
      "value": "頭部40-50"
    },
    {
      "label": "雙層WC",
      "value": "雙層WC"
    }
  ];
  let stuff = [{
      "label": "#1 DIE",
      "value": "#1 DIE"
    },
    {
      "label": "#1 DIE CORE",
      "value": "#1 DIE CORE"
    },
    {
      "label": "#1 DIE INSERT",
      "value": "#1 DIE INSERT"
    },
    {
      "label": "#1 PUNCH ASSEMBLY",
      "value": "#1 PUNCH ASSEMBLY"
    },
    {
      "label": "#2 DIE",
      "value": "#2 DIE"
    },
    {
      "label": "#2 DIE CORE",
      "value": "#2 DIE CORE"
    },
    {
      "label": "#2 DIE INSERT-TRAP STATION",
      "value": "#2 DIE INSERT-TRAP STATION"
    },
    {
      "label": "#2 UNDERCUT DIE INSERT",
      "value": "#2 UNDERCUT DIE INSERT"
    },
    {
      "label": "#3 DIE",
      "value": "#3 DIE"
    },
    {
      "label": "#3 DIE ASSEMBLY",
      "value": "#3 DIE ASSEMBLY"
    },
    {
      "label": "#3 DIE CORE",
      "value": "#3 DIE CORE"
    },
    {
      "label": "#3 KNURL DIE INSERT",
      "value": "#3 KNURL DIE INSERT"
    },
    {
      "label": "#3 PUNCH ASSEMBLY",
      "value": "#3 PUNCH ASSEMBLY"
    },
    {
      "label": "#30 HARTFORD ROLLERS",
      "value": "#30 HARTFORD ROLLERS"
    },
    {
      "label": "#304 不銹鋼",
      "value": "#304 不銹鋼"
    },
    {
      "label": "#4 DIE",
      "value": "#4 DIE"
    },
    {
      "label": "#4 DIE ASSEMBLY",
      "value": "#4 DIE ASSEMBLY"
    },
    {
      "label": "#4 DIE CORE",
      "value": "#4 DIE CORE"
    },
    {
      "label": "#4 HEX PIN",
      "value": "#4 HEX PIN"
    },
    {
      "label": "#4,#5 DIE測PIN",
      "value": "#4,#5 DIE測PIN"
    },
    {
      "label": "#40 6 LOBE PUNCH",
      "value": "#40 6 LOBE PUNCH"
    },
    {
      "label": "#5 DIE CORE",
      "value": "#5 DIE CORE"
    },
    {
      "label": "#5 DIE INSERT",
      "value": "#5 DIE INSERT"
    },
    {
      "label": "#6 WIRE STOP BODY",
      "value": "#6 WIRE STOP BODY"
    },
    {
      "label": ".395 Draw Die",
      "value": ".395 Draw Die"
    },
    {
      "label": "1. Stat. Matrize",
      "value": "1. Stat. Matrize"
    },
    {
      "label": "1.STAT MATRIZE",
      "value": "1.STAT MATRIZE"
    },
    {
      "label": "1.STAT.MATRIZE",
      "value": "1.STAT.MATRIZE"
    },
    {
      "label": "1.Stat. Matrize",
      "value": "1.Stat. Matrize"
    },
    {
      "label": "1.Stat.Matrize",
      "value": "1.Stat.Matrize"
    },
    {
      "label": "1/4-14#2PT CARBIDE POINT DIE",
      "value": "1/4-14#2PT CARBIDE POINT DIE"
    },
    {
      "label": "12 ANGLE PUNCH",
      "value": "12 ANGLE PUNCH"
    },
    {
      "label": "12-14#2PT CARBIDE POINTING DIE",
      "value": "12-14#2PT CARBIDE POINTING DIE"
    },
    {
      "label": "12-14#3PT CARBIDE DIE",
      "value": "12-14#3PT CARBIDE DIE"
    },
    {
      "label": "12-34-51",
      "value": "12-34-51"
    },
    {
      "label": "12ANG PUNCH",
      "value": "12ANG PUNCH"
    },
    {
      "label": "12ANG PUNCH E33A9",
      "value": "12ANG PUNCH E33A9"
    },
    {
      "label": "12KT STEMPEL",
      "value": "12KT STEMPEL"
    },
    {
      "label": "12KT. STEMPEL",
      "value": "12KT. STEMPEL"
    },
    {
      "label": "12片模治具",
      "value": "12片模治具"
    },
    {
      "label": "1ND DIE CORE",
      "value": "1ND DIE CORE"
    },
    {
      "label": "1ST DIE",
      "value": "1ST DIE"
    },
    {
      "label": "1ST DIE ASSEMBLY",
      "value": "1ST DIE ASSEMBLY"
    },
    {
      "label": "1ST DIE CORE",
      "value": "1ST DIE CORE"
    },
    {
      "label": "1ST DIE DORE",
      "value": "1ST DIE DORE"
    },
    {
      "label": "1ST DIE FRONT INSERT",
      "value": "1ST DIE FRONT INSERT"
    },
    {
      "label": "1ST DIE INSERT",
      "value": "1ST DIE INSERT"
    },
    {
      "label": "1ST DIE INSERT FRONT",
      "value": "1ST DIE INSERT FRONT"
    },
    {
      "label": "1ST DIE INSERT REAR",
      "value": "1ST DIE INSERT REAR"
    },
    {
      "label": "1ST DIE POINT INSERT",
      "value": "1ST DIE POINT INSERT"
    },
    {
      "label": "1ST DIE PUNCH SIDE",
      "value": "1ST DIE PUNCH SIDE"
    },
    {
      "label": "1ST DIE REAR INSERT",
      "value": "1ST DIE REAR INSERT"
    },
    {
      "label": "1ST MIDDLE DIE INSERT",
      "value": "1ST MIDDLE DIE INSERT"
    },
    {
      "label": "1ST OP DIE",
      "value": "1ST OP DIE"
    },
    {
      "label": "1ST PUNCH INSERT",
      "value": "1ST PUNCH INSERT"
    },
    {
      "label": "1ST PUNCH INSERT 2",
      "value": "1ST PUNCH INSERT 2"
    },
    {
      "label": "1ST STA.DIE",
      "value": "1ST STA.DIE"
    },
    {
      "label": "1st DIE",
      "value": "1st DIE"
    },
    {
      "label": "2. STAT. MATRIZE",
      "value": "2. STAT. MATRIZE"
    },
    {
      "label": "2.STAT. STEMPEL",
      "value": "2.STAT. STEMPEL"
    },
    {
      "label": "2.STAT.MATRIZE",
      "value": "2.STAT.MATRIZE"
    },
    {
      "label": "2.STAT.MATRIZENEINSATZ",
      "value": "2.STAT.MATRIZENEINSATZ"
    },
    {
      "label": "2.Stat. Matrize",
      "value": "2.Stat. Matrize"
    },
    {
      "label": "2ND BOTTOM DIE INSERT",
      "value": "2ND BOTTOM DIE INSERT"
    },
    {
      "label": "2ND DIE",
      "value": "2ND DIE"
    },
    {
      "label": "2ND DIE ASSEMBLY",
      "value": "2ND DIE ASSEMBLY"
    },
    {
      "label": "2ND DIE CARBIDE",
      "value": "2ND DIE CARBIDE"
    },
    {
      "label": "2ND DIE CORE",
      "value": "2ND DIE CORE"
    },
    {
      "label": "2ND DIE DORE",
      "value": "2ND DIE DORE"
    },
    {
      "label": "2ND DIE FRONT INSERT",
      "value": "2ND DIE FRONT INSERT"
    },
    {
      "label": "2ND DIE INSERT",
      "value": "2ND DIE INSERT"
    },
    {
      "label": "2ND DIE POINT INSERT",
      "value": "2ND DIE POINT INSERT"
    },
    {
      "label": "2ND DIE REAR",
      "value": "2ND DIE REAR"
    },
    {
      "label": "2ND DIE REAR INSERT",
      "value": "2ND DIE REAR INSERT"
    },
    {
      "label": "2ND DIE SECOND INSERT",
      "value": "2ND DIE SECOND INSERT"
    },
    {
      "label": "2ND INSERT",
      "value": "2ND INSERT"
    },
    {
      "label": "2ND OPERATION DIECORE ASSEMBLY",
      "value": "2ND OPERATION DIECORE ASSEMBLY"
    },
    {
      "label": "2ND PUNCH ASSEMBLY",
      "value": "2ND PUNCH ASSEMBLY"
    },
    {
      "label": "2ND PUNCH DIE",
      "value": "2ND PUNCH DIE"
    },
    {
      "label": "2ND PUNCH HOLDER",
      "value": "2ND PUNCH HOLDER"
    },
    {
      "label": "2ND PUNCH INSERT",
      "value": "2ND PUNCH INSERT"
    },
    {
      "label": "2ND STA. DIE INSERT",
      "value": "2ND STA. DIE INSERT"
    },
    {
      "label": "2ND STA. DIE IST INSERT",
      "value": "2ND STA. DIE IST INSERT"
    },
    {
      "label": "2ND STAT. DIE ASSY'",
      "value": "2ND STAT. DIE ASSY'"
    },
    {
      "label": "2ND STATION HEX PUNCH PIN",
      "value": "2ND STATION HEX PUNCH PIN"
    },
    {
      "label": "2ND TOP DIE",
      "value": "2ND TOP DIE"
    },
    {
      "label": "2nd DIE",
      "value": "2nd DIE"
    },
    {
      "label": "2nd Hex Ext Pin",
      "value": "2nd Hex Ext Pin"
    },
    {
      "label": "3 STAT MATRIZE",
      "value": "3 STAT MATRIZE"
    },
    {
      "label": "3 STAT. TRANSPORTFINGER",
      "value": "3 STAT. TRANSPORTFINGER"
    },
    {
      "label": "3-4&4-5 FINGERS",
      "value": "3-4&4-5 FINGERS"
    },
    {
      "label": "3. STAT. MATRIZE",
      "value": "3. STAT. MATRIZE"
    },
    {
      "label": "3. Stat. Matrize",
      "value": "3. Stat. Matrize"
    },
    {
      "label": "3.STAT.MATRIZE",
      "value": "3.STAT.MATRIZE"
    },
    {
      "label": "3.Stat. Matrize",
      "value": "3.Stat. Matrize"
    },
    {
      "label": "3.Stat.Matrize",
      "value": "3.Stat.Matrize"
    },
    {
      "label": "3/8 SHC P3 PUNCH",
      "value": "3/8 SHC P3 PUNCH"
    },
    {
      "label": "3/8 SHC P3 PUNCHES",
      "value": "3/8 SHC P3 PUNCHES"
    },
    {
      "label": "32-MIHON180前熱處理",
      "value": "32-MIHON180前熱處理"
    },
    {
      "label": "32-MIHON180後熱處理",
      "value": "32-MIHON180後熱處理"
    },
    {
      "label": "32-MIHON180熱處理",
      "value": "32-MIHON180熱處理"
    },
    {
      "label": "32-MIHON1811熱處理",
      "value": "32-MIHON1811熱處理"
    },
    {
      "label": "32-MIHON1812熱處理",
      "value": "32-MIHON1812熱處理"
    },
    {
      "label": "32-MIHON207A熱處理",
      "value": "32-MIHON207A熱處理"
    },
    {
      "label": "32-MIHON208A熱處理",
      "value": "32-MIHON208A熱處理"
    },
    {
      "label": "32-MIHON209A熱處理",
      "value": "32-MIHON209A熱處理"
    },
    {
      "label": "32-MIHON2171A熱處理",
      "value": "32-MIHON2171A熱處理"
    },
    {
      "label": "32-MIHON2172A熱處理",
      "value": "32-MIHON2172A熱處理"
    },
    {
      "label": "32-MIHON2173A熱處理",
      "value": "32-MIHON2173A熱處理"
    },
    {
      "label": "32-MIHON2581熱處理",
      "value": "32-MIHON2581熱處理"
    },
    {
      "label": "32-MIHON2582熱處理",
      "value": "32-MIHON2582熱處理"
    },
    {
      "label": "32-MIHON286A電刷片熱處理",
      "value": "32-MIHON286A電刷片熱處理"
    },
    {
      "label": "32-MIHON287A電刷片熱處理",
      "value": "32-MIHON287A電刷片熱處理"
    },
    {
      "label": "35-00200-012+35-70102-110+35-7",
      "value": "35-00200-012+35-70102-110+35-7"
    },
    {
      "label": "35-00200-012+35-70102-200+35-7",
      "value": "35-00200-012+35-70102-200+35-7"
    },
    {
      "label": "35-00200-012+35-70102-300+35-7",
      "value": "35-00200-012+35-70102-300+35-7"
    },
    {
      "label": "3KT PUNCH",
      "value": "3KT PUNCH"
    },
    {
      "label": "3RD DIE",
      "value": "3RD DIE"
    },
    {
      "label": "3RD DIE ASSEMBLY",
      "value": "3RD DIE ASSEMBLY"
    },
    {
      "label": "3RD DIE ASSY",
      "value": "3RD DIE ASSY"
    },
    {
      "label": "3RD DIE CORE",
      "value": "3RD DIE CORE"
    },
    {
      "label": "3RD DIE CORE ASSEMBLY",
      "value": "3RD DIE CORE ASSEMBLY"
    },
    {
      "label": "3RD DIE FRONT INSERT",
      "value": "3RD DIE FRONT INSERT"
    },
    {
      "label": "3RD DIE INSERT",
      "value": "3RD DIE INSERT"
    },
    {
      "label": "3RD DIE OUTER HEX INS",
      "value": "3RD DIE OUTER HEX INS"
    },
    {
      "label": "3RD DIE OUTER HEX INS EXP",
      "value": "3RD DIE OUTER HEX INS EXP"
    },
    {
      "label": "3RD DIE SECOND INSERT",
      "value": "3RD DIE SECOND INSERT"
    },
    {
      "label": "3RD EXTRUDE DIE",
      "value": "3RD EXTRUDE DIE"
    },
    {
      "label": "3RD EXTRUSTION DIE",
      "value": "3RD EXTRUSTION DIE"
    },
    {
      "label": "3RD M16 DIE CORE",
      "value": "3RD M16 DIE CORE"
    },
    {
      "label": "3RD OPERATION DIE CORE ASSEMBL",
      "value": "3RD OPERATION DIE CORE ASSEMBL"
    },
    {
      "label": "3RD POS.",
      "value": "3RD POS."
    },
    {
      "label": "3RD POS.RAM",
      "value": "3RD POS.RAM"
    },
    {
      "label": "3RD POSITION",
      "value": "3RD POSITION"
    },
    {
      "label": "3RD POSITION PUNCH",
      "value": "3RD POSITION PUNCH"
    },
    {
      "label": "3RD PUNCH HEX PIN",
      "value": "3RD PUNCH HEX PIN"
    },
    {
      "label": "3RD PUNCH PIN",
      "value": "3RD PUNCH PIN"
    },
    {
      "label": "3RD STA HEX EXT PIN",
      "value": "3RD STA HEX EXT PIN"
    },
    {
      "label": "3RD STA. DIE",
      "value": "3RD STA. DIE"
    },
    {
      "label": "3RD STA.DIE",
      "value": "3RD STA.DIE"
    },
    {
      "label": "3RD STA.HEX EXT.PIN",
      "value": "3RD STA.HEX EXT.PIN"
    },
    {
      "label": "3RD STATION PUNCH PIN",
      "value": "3RD STATION PUNCH PIN"
    },
    {
      "label": "3RD STATION SPECIAL PUNCH PIN",
      "value": "3RD STATION SPECIAL PUNCH PIN"
    },
    {
      "label": "3RD. STATION DIE",
      "value": "3RD. STATION DIE"
    },
    {
      "label": "3TH CARBIDE SEGMENTED DIE CORE",
      "value": "3TH CARBIDE SEGMENTED DIE CORE"
    },
    {
      "label": "3rd DIE",
      "value": "3rd DIE"
    },
    {
      "label": "3rd Ext Pin",
      "value": "3rd Ext Pin"
    },
    {
      "label": "3rd Matrize",
      "value": "3rd Matrize"
    },
    {
      "label": "3rd PUNCH HEX PIN FOR 20MM KCS",
      "value": "3rd PUNCH HEX PIN FOR 20MM KCS"
    },
    {
      "label": "3rd Punch 1/4 HEX PIN 5/16 KCS",
      "value": "3rd Punch 1/4 HEX PIN 5/16 KCS"
    },
    {
      "label": "3rd Punch HEX PIN 5/16",
      "value": "3rd Punch HEX PIN 5/16"
    },
    {
      "label": "3rd Punch Hex Pin 5/32\" HEX #10",
      "value": "3rd Punch Hex Pin 5/32\" HEX #10"
    },
    {
      "label": "3rd Punch PIN 1/2",
      "value": "3rd Punch PIN 1/2"
    },
    {
      "label": "3rd Punch Pin HEX",
      "value": "3rd Punch Pin HEX"
    },
    {
      "label": "3rd outer insert",
      "value": "3rd outer insert"
    },
    {
      "label": "4 ST MATRIZE-VORDERKERN",
      "value": "4 ST MATRIZE-VORDERKERN"
    },
    {
      "label": "4 STA. PUNCH PIN",
      "value": "4 STA. PUNCH PIN"
    },
    {
      "label": "4 STAT MATRIZE",
      "value": "4 STAT MATRIZE"
    },
    {
      "label": "4 STAT. MATRIZE",
      "value": "4 STAT. MATRIZE"
    },
    {
      "label": "4. STAT. MATRIZE",
      "value": "4. STAT. MATRIZE"
    },
    {
      "label": "4. Stat. Matrize",
      "value": "4. Stat. Matrize"
    },
    {
      "label": "4.STAT MATRIZE",
      "value": "4.STAT MATRIZE"
    },
    {
      "label": "4.STAT. AUSWERFER",
      "value": "4.STAT. AUSWERFER"
    },
    {
      "label": "4.STAT. BUCHSE IN MATRIZE",
      "value": "4.STAT. BUCHSE IN MATRIZE"
    },
    {
      "label": "4.STAT. BUCHSE IN MATRIZENEINS",
      "value": "4.STAT. BUCHSE IN MATRIZENEINS"
    },
    {
      "label": "4.STAT. MATRIZE",
      "value": "4.STAT. MATRIZE"
    },
    {
      "label": "4.STAT. STEMPEL",
      "value": "4.STAT. STEMPEL"
    },
    {
      "label": "4.STAT. STEMPEL IN HULSE",
      "value": "4.STAT. STEMPEL IN HULSE"
    },
    {
      "label": "4.STAT.MATRIZE",
      "value": "4.STAT.MATRIZE"
    },
    {
      "label": "4.STAT.MATRIZENEINSATZ",
      "value": "4.STAT.MATRIZENEINSATZ"
    },
    {
      "label": "4.STAT.STEMPEL",
      "value": "4.STAT.STEMPEL"
    },
    {
      "label": "4.Stat. Auswerfer M8x1 Spindelmutter /M8S2",
      "value": "4.Stat. Auswerfer M8x1 Spindelmutter /M8S2"
    },
    {
      "label": "4.Stat. Matrize",
      "value": "4.Stat. Matrize"
    },
    {
      "label": "4.Stat.Matrize",
      "value": "4.Stat.Matrize"
    },
    {
      "label": "4ST STA. DIE",
      "value": "4ST STA. DIE"
    },
    {
      "label": "4TH ASSEMBLY",
      "value": "4TH ASSEMBLY"
    },
    {
      "label": "4TH DIE",
      "value": "4TH DIE"
    },
    {
      "label": "4TH DIE ASSEMBLY",
      "value": "4TH DIE ASSEMBLY"
    },
    {
      "label": "4TH DIE CARBIDE",
      "value": "4TH DIE CARBIDE"
    },
    {
      "label": "4TH DIE CORE",
      "value": "4TH DIE CORE"
    },
    {
      "label": "4TH DIE EXTRUDE PIN",
      "value": "4TH DIE EXTRUDE PIN"
    },
    {
      "label": "4TH DIE HEX INSERT",
      "value": "4TH DIE HEX INSERT"
    },
    {
      "label": "4TH DIE INSERT",
      "value": "4TH DIE INSERT"
    },
    {
      "label": "4TH DIE PIN",
      "value": "4TH DIE PIN"
    },
    {
      "label": "4TH DIE REAR",
      "value": "4TH DIE REAR"
    },
    {
      "label": "4TH INSERT",
      "value": "4TH INSERT"
    },
    {
      "label": "4TH INSERT ASSEMBLY",
      "value": "4TH INSERT ASSEMBLY"
    },
    {
      "label": "4TH M16 DIE CORE",
      "value": "4TH M16 DIE CORE"
    },
    {
      "label": "4TH OPERATION DIE CORE ASSEMBL",
      "value": "4TH OPERATION DIE CORE ASSEMBL"
    },
    {
      "label": "4TH POS DIE",
      "value": "4TH POS DIE"
    },
    {
      "label": "4TH PUNCH ASSEMBLY",
      "value": "4TH PUNCH ASSEMBLY"
    },
    {
      "label": "4TH PUNCH EXTRUSION",
      "value": "4TH PUNCH EXTRUSION"
    },
    {
      "label": "4TH Punch",
      "value": "4TH Punch"
    },
    {
      "label": "4TH STA. 3RD DIE INSERT",
      "value": "4TH STA. 3RD DIE INSERT"
    },
    {
      "label": "4TH STA. DIE",
      "value": "4TH STA. DIE"
    },
    {
      "label": "4TH STATION HEX EXTRUSION PIN",
      "value": "4TH STATION HEX EXTRUSION PIN"
    },
    {
      "label": "4TH STATION PUNCH",
      "value": "4TH STATION PUNCH"
    },
    {
      "label": "4TH STH PUNCH INSERT",
      "value": "4TH STH PUNCH INSERT"
    },
    {
      "label": "4th DIE",
      "value": "4th DIE"
    },
    {
      "label": "4th Hex Punch",
      "value": "4th Hex Punch"
    },
    {
      "label": "4th STA. DIE",
      "value": "4th STA. DIE"
    },
    {
      "label": "4th Sta. Punch",
      "value": "4th Sta. Punch"
    },
    {
      "label": "4th sta. Hex Punch",
      "value": "4th sta. Hex Punch"
    },
    {
      "label": "5 STATION PUNCH",
      "value": "5 STATION PUNCH"
    },
    {
      "label": "5 mm 5TH STA. HEX Punch",
      "value": "5 mm 5TH STA. HEX Punch"
    },
    {
      "label": "5.ST.HALTER",
      "value": "5.ST.HALTER"
    },
    {
      "label": "5.ST.MATRIZENKERN",
      "value": "5.ST.MATRIZENKERN"
    },
    {
      "label": "5.STAT.MATRIZE",
      "value": "5.STAT.MATRIZE"
    },
    {
      "label": "5.Stat. Matrize",
      "value": "5.Stat. Matrize"
    },
    {
      "label": "5HT DIE",
      "value": "5HT DIE"
    },
    {
      "label": "5TH DIE",
      "value": "5TH DIE"
    },
    {
      "label": "5TH DIE ASSEMBLY",
      "value": "5TH DIE ASSEMBLY"
    },
    {
      "label": "5TH DIE CORE",
      "value": "5TH DIE CORE"
    },
    {
      "label": "5TH DIE DORE",
      "value": "5TH DIE DORE"
    },
    {
      "label": "5TH DIE INSERT",
      "value": "5TH DIE INSERT"
    },
    {
      "label": "5TH PUNCH INSERT",
      "value": "5TH PUNCH INSERT"
    },
    {
      "label": "5TH SQUA. Punch",
      "value": "5TH SQUA. Punch"
    },
    {
      "label": "5TH STA. HEX PUCNH 7/16",
      "value": "5TH STA. HEX PUCNH 7/16"
    },
    {
      "label": "5TH STATION HEX PUNCH",
      "value": "5TH STATION HEX PUNCH"
    },
    {
      "label": "5TH STATION HEX. PUNCH",
      "value": "5TH STATION HEX. PUNCH"
    },
    {
      "label": "5TH STATION PUNCH",
      "value": "5TH STATION PUNCH"
    },
    {
      "label": "5TH. STA. HEX PUNCH(IHI)",
      "value": "5TH. STA. HEX PUNCH(IHI)"
    },
    {
      "label": "5th DIE",
      "value": "5th DIE"
    },
    {
      "label": "5th HEX Punch",
      "value": "5th HEX Punch"
    },
    {
      "label": "5th Hex Punch",
      "value": "5th Hex Punch"
    },
    {
      "label": "5th Hex punch",
      "value": "5th Hex punch"
    },
    {
      "label": "5th Sta. HEX Punch",
      "value": "5th Sta. HEX Punch"
    },
    {
      "label": "5th Stat. Hex Punch",
      "value": "5th Stat. Hex Punch"
    },
    {
      "label": "5th hex punch",
      "value": "5th hex punch"
    },
    {
      "label": "6 KT EINSATZ",
      "value": "6 KT EINSATZ"
    },
    {
      "label": "6 KT STEMPEL",
      "value": "6 KT STEMPEL"
    },
    {
      "label": "6 KT-NADEL",
      "value": "6 KT-NADEL"
    },
    {
      "label": "6 KT. STEMPEL",
      "value": "6 KT. STEMPEL"
    },
    {
      "label": "6 KT.STEMPEL",
      "value": "6 KT.STEMPEL"
    },
    {
      "label": "6 Kt. Stempel",
      "value": "6 Kt. Stempel"
    },
    {
      "label": "6 LOBE PUNCH",
      "value": "6 LOBE PUNCH"
    },
    {
      "label": "6-KT-NADEL",
      "value": "6-KT-NADEL"
    },
    {
      "label": "6-KT-STEMPEL",
      "value": "6-KT-STEMPEL"
    },
    {
      "label": "6-KT. STAMPEL",
      "value": "6-KT. STAMPEL"
    },
    {
      "label": "6-KT.STEMPEL",
      "value": "6-KT.STEMPEL"
    },
    {
      "label": "6-LOBE SPEZIALSTEMPEL",
      "value": "6-LOBE SPEZIALSTEMPEL"
    },
    {
      "label": "6-RD-STEMPEL",
      "value": "6-RD-STEMPEL"
    },
    {
      "label": "6-kt-Nadel M6x43,95",
      "value": "6-kt-Nadel M6x43,95"
    },
    {
      "label": "6.STAT. MATRIZE",
      "value": "6.STAT. MATRIZE"
    },
    {
      "label": "6KT PUNCH",
      "value": "6KT PUNCH"
    },
    {
      "label": "6KT SEGMENTE",
      "value": "6KT SEGMENTE"
    },
    {
      "label": "6KT STEMPEL",
      "value": "6KT STEMPEL"
    },
    {
      "label": "6KT-STEMPEL SW13",
      "value": "6KT-STEMPEL SW13"
    },
    {
      "label": "6KT-STIFT",
      "value": "6KT-STIFT"
    },
    {
      "label": "6KT.STEMPEL",
      "value": "6KT.STEMPEL"
    },
    {
      "label": "6TH DIE",
      "value": "6TH DIE"
    },
    {
      "label": "6TH HEX EXT PIN",
      "value": "6TH HEX EXT PIN"
    },
    {
      "label": "6kt-Stift",
      "value": "6kt-Stift"
    },
    {
      "label": "6kt.-Stempel SW8.08",
      "value": "6kt.-Stempel SW8.08"
    },
    {
      "label": "6th DIE",
      "value": "6th DIE"
    },
    {
      "label": "8-18 CARBIDE POINTING DIE",
      "value": "8-18 CARBIDE POINTING DIE"
    },
    {
      "label": "91-B35熱處理",
      "value": "91-B35熱處理"
    },
    {
      "label": "91-MIHON217熱處理",
      "value": "91-MIHON217熱處理"
    },
    {
      "label": "ABSCHNITTMESSER",
      "value": "ABSCHNITTMESSER"
    },
    {
      "label": "ADJUSTABLE RAIL TIP",
      "value": "ADJUSTABLE RAIL TIP"
    },
    {
      "label": "ALLOY SHCS HEX PUNCH",
      "value": "ALLOY SHCS HEX PUNCH"
    },
    {
      "label": "AUSWERFER MIT VIELZAHNPROFIL N",
      "value": "AUSWERFER MIT VIELZAHNPROFIL N"
    },
    {
      "label": "Angratmatrize",
      "value": "Angratmatrize"
    },
    {
      "label": "Auswerfer",
      "value": "Auswerfer"
    },
    {
      "label": "Auswerfer T30",
      "value": "Auswerfer T30"
    },
    {
      "label": "BOLT SHELF",
      "value": "BOLT SHELF"
    },
    {
      "label": "BOTTOM INSERT 1ST",
      "value": "BOTTOM INSERT 1ST"
    },
    {
      "label": "BROACH",
      "value": "BROACH"
    },
    {
      "label": "BROACH PIN",
      "value": "BROACH PIN"
    },
    {
      "label": "BUSH",
      "value": "BUSH"
    },
    {
      "label": "BUTEROLA",
      "value": "BUTEROLA"
    },
    {
      "label": "CARBIDE",
      "value": "CARBIDE"
    },
    {
      "label": "CARBIDE POINT DIE",
      "value": "CARBIDE POINT DIE"
    },
    {
      "label": "CARBIDE POINTER DIE",
      "value": "CARBIDE POINTER DIE"
    },
    {
      "label": "CARBIDE POINTING DIE",
      "value": "CARBIDE POINTING DIE"
    },
    {
      "label": "CARBIDE SEGMENT DIE",
      "value": "CARBIDE SEGMENT DIE"
    },
    {
      "label": "CASSETTE BASE PLATE",
      "value": "CASSETTE BASE PLATE"
    },
    {
      "label": "CB HX DIE 4TH",
      "value": "CB HX DIE 4TH"
    },
    {
      "label": "CLAMP WEDGE",
      "value": "CLAMP WEDGE"
    },
    {
      "label": "COATED PUNCH",
      "value": "COATED PUNCH"
    },
    {
      "label": "CONJUNTO MATRIZ 30P",
      "value": "CONJUNTO MATRIZ 30P"
    },
    {
      "label": "CONJUNTO MATRIZ 5OP",
      "value": "CONJUNTO MATRIZ 5OP"
    },
    {
      "label": "CRIMPER",
      "value": "CRIMPER"
    },
    {
      "label": "CUT OFF QUILL",
      "value": "CUT OFF QUILL"
    },
    {
      "label": "CUTOFF KNIFE",
      "value": "CUTOFF KNIFE"
    },
    {
      "label": "CUTOFF KNIFF",
      "value": "CUTOFF KNIFF"
    },
    {
      "label": "CUTTER",
      "value": "CUTTER"
    },
    {
      "label": "DESIGNACAO",
      "value": "DESIGNACAO"
    },
    {
      "label": "DIE",
      "value": "DIE"
    },
    {
      "label": "DIE (外徑龍畿製做)",
      "value": "DIE (外徑龍畿製做)"
    },
    {
      "label": "DIE 4TH STATION WITH UNDERCUT",
      "value": "DIE 4TH STATION WITH UNDERCUT"
    },
    {
      "label": "DIE ASSEMBLY",
      "value": "DIE ASSEMBLY"
    },
    {
      "label": "DIE ASSEMBLY 模組",
      "value": "DIE ASSEMBLY 模組"
    },
    {
      "label": "DIE ASSSEMBLY",
      "value": "DIE ASSSEMBLY"
    },
    {
      "label": "DIE ASSY",
      "value": "DIE ASSY"
    },
    {
      "label": "DIE ASSY (STA.3)",
      "value": "DIE ASSY (STA.3)"
    },
    {
      "label": "DIE ASSY STA.4",
      "value": "DIE ASSY STA.4"
    },
    {
      "label": "DIE ASSY' STA.4",
      "value": "DIE ASSY' STA.4"
    },
    {
      "label": "DIE ASSY' SYA.1",
      "value": "DIE ASSY' SYA.1"
    },
    {
      "label": "DIE CASE",
      "value": "DIE CASE"
    },
    {
      "label": "DIE CORE",
      "value": "DIE CORE"
    },
    {
      "label": "DIE CORE E630A",
      "value": "DIE CORE E630A"
    },
    {
      "label": "DIE EXT. INSERT",
      "value": "DIE EXT. INSERT"
    },
    {
      "label": "DIE EXTRUSION INSERT",
      "value": "DIE EXTRUSION INSERT"
    },
    {
      "label": "DIE FIRST STATION",
      "value": "DIE FIRST STATION"
    },
    {
      "label": "DIE FOR HOT FORGING",
      "value": "DIE FOR HOT FORGING"
    },
    {
      "label": "DIE HES INSERT",
      "value": "DIE HES INSERT"
    },
    {
      "label": "DIE HES INSERT(TOP)",
      "value": "DIE HES INSERT(TOP)"
    },
    {
      "label": "DIE HEX INSERT",
      "value": "DIE HEX INSERT"
    },
    {
      "label": "DIE HEX INSERT (TOP)",
      "value": "DIE HEX INSERT (TOP)"
    },
    {
      "label": "DIE HEX ISNERT",
      "value": "DIE HEX ISNERT"
    },
    {
      "label": "DIE INSERT",
      "value": "DIE INSERT"
    },
    {
      "label": "DIE INSERT FRONT",
      "value": "DIE INSERT FRONT"
    },
    {
      "label": "DIE INSERT(放電文字龍畿做)",
      "value": "DIE INSERT(放電文字龍畿做)"
    },
    {
      "label": "DIE POINT INSERT",
      "value": "DIE POINT INSERT"
    },
    {
      "label": "DIE SECOND STATION",
      "value": "DIE SECOND STATION"
    },
    {
      "label": "DIE SPECIAL INSERT",
      "value": "DIE SPECIAL INSERT"
    },
    {
      "label": "DIE STRAIGHT I.D. INSERT (TOP)",
      "value": "DIE STRAIGHT I.D. INSERT (TOP)"
    },
    {
      "label": "DIE SUB-ASSEMBLY",
      "value": "DIE SUB-ASSEMBLY"
    },
    {
      "label": "DIE THIRD STATION",
      "value": "DIE THIRD STATION"
    },
    {
      "label": "DIE WAFER",
      "value": "DIE WAFER"
    },
    {
      "label": "DIE-CORE",
      "value": "DIE-CORE"
    },
    {
      "label": "DIE-PUNCH",
      "value": "DIE-PUNCH"
    },
    {
      "label": "DORN",
      "value": "DORN"
    },
    {
      "label": "DRAGHYLSA",
      "value": "DRAGHYLSA"
    },
    {
      "label": "DRAGHYLSA M14 FLANSSKRUA",
      "value": "DRAGHYLSA M14 FLANSSKRUA"
    },
    {
      "label": "DRAW DIE",
      "value": "DRAW DIE"
    },
    {
      "label": "DRILL POINT DIES",
      "value": "DRILL POINT DIES"
    },
    {
      "label": "Die Point Insert",
      "value": "Die Point Insert"
    },
    {
      "label": "Dopper Einsatz",
      "value": "Dopper Einsatz"
    },
    {
      "label": "Dopper-Einsatz",
      "value": "Dopper-Einsatz"
    },
    {
      "label": "Doppereinsatz",
      "value": "Doppereinsatz"
    },
    {
      "label": "EINSATZ",
      "value": "EINSATZ"
    },
    {
      "label": "EINSATZ MATRIZE",
      "value": "EINSATZ MATRIZE"
    },
    {
      "label": "EINSATZ PREBMATRIZE SW18.6",
      "value": "EINSATZ PREBMATRIZE SW18.6"
    },
    {
      "label": "EXT PUNCH",
      "value": "EXT PUNCH"
    },
    {
      "label": "EXTRUDE DIE",
      "value": "EXTRUDE DIE"
    },
    {
      "label": "EXTRUDE PIN",
      "value": "EXTRUDE PIN"
    },
    {
      "label": "EXTRUDING DIE",
      "value": "EXTRUDING DIE"
    },
    {
      "label": "EXTRUSION DIE",
      "value": "EXTRUSION DIE"
    },
    {
      "label": "EXTRUSION INSERT",
      "value": "EXTRUSION INSERT"
    },
    {
      "label": "EXTRUSION PIN",
      "value": "EXTRUSION PIN"
    },
    {
      "label": "EXTRUSION PUNCH",
      "value": "EXTRUSION PUNCH"
    },
    {
      "label": "FASHYLSA",
      "value": "FASHYLSA"
    },
    {
      "label": "FEED FINGER",
      "value": "FEED FINGER"
    },
    {
      "label": "FERBGSTAUCHER",
      "value": "FERBGSTAUCHER"
    },
    {
      "label": "FERTIGSTAUCHER",
      "value": "FERTIGSTAUCHER"
    },
    {
      "label": "FERTIGSTAUCHER SW12.82",
      "value": "FERTIGSTAUCHER SW12.82"
    },
    {
      "label": "FILEBPREBDORN",
      "value": "FILEBPREBDORN"
    },
    {
      "label": "FILEBPREBMATRIZE",
      "value": "FILEBPREBMATRIZE"
    },
    {
      "label": "FINGER",
      "value": "FINGER"
    },
    {
      "label": "FINGER ARM",
      "value": "FINGER ARM"
    },
    {
      "label": "FINGER BLANK",
      "value": "FINGER BLANK"
    },
    {
      "label": "FINGER ψ10.80 H=40",
      "value": "FINGER ψ10.80 H=40"
    },
    {
      "label": "FINGER ψ11.80 H=40",
      "value": "FINGER ψ11.80 H=40"
    },
    {
      "label": "FINGER ψ12.60 H=40",
      "value": "FINGER ψ12.60 H=40"
    },
    {
      "label": "FINGER ψ12.60 H=9",
      "value": "FINGER ψ12.60 H=9"
    },
    {
      "label": "FINGER ψ13.80 H=40",
      "value": "FINGER ψ13.80 H=40"
    },
    {
      "label": "FINGER ψ14.60 H=40",
      "value": "FINGER ψ14.60 H=40"
    },
    {
      "label": "FINGER ψ15.80 H=40",
      "value": "FINGER ψ15.80 H=40"
    },
    {
      "label": "FINGER ψ8.90 H=40",
      "value": "FINGER ψ8.90 H=40"
    },
    {
      "label": "FINGER ψ8.90 H=9",
      "value": "FINGER ψ8.90 H=9"
    },
    {
      "label": "FINGER ψ9.80 H=40",
      "value": "FINGER ψ9.80 H=40"
    },
    {
      "label": "FINGERS",
      "value": "FINGERS"
    },
    {
      "label": "FINISH PUNCH ASSEMBLY",
      "value": "FINISH PUNCH ASSEMBLY"
    },
    {
      "label": "FINISH PUNCH CASE ASSEMBLY",
      "value": "FINISH PUNCH CASE ASSEMBLY"
    },
    {
      "label": "FIRST DIE CORE",
      "value": "FIRST DIE CORE"
    },
    {
      "label": "FIRST DIE STATION",
      "value": "FIRST DIE STATION"
    },
    {
      "label": "FLIEBPREBDORN",
      "value": "FLIEBPREBDORN"
    },
    {
      "label": "FLIEBPREBMATIRIZE",
      "value": "FLIEBPREBMATIRIZE"
    },
    {
      "label": "FLIEBPREBMATRIZE",
      "value": "FLIEBPREBMATRIZE"
    },
    {
      "label": "FOURTH PUNCH TORX T55",
      "value": "FOURTH PUNCH TORX T55"
    },
    {
      "label": "FOURTH STATION HEX PIN",
      "value": "FOURTH STATION HEX PIN"
    },
    {
      "label": "FRONT PUNCH INSERT",
      "value": "FRONT PUNCH INSERT"
    },
    {
      "label": "FUHRUNGSLEISTE",
      "value": "FUHRUNGSLEISTE"
    },
    {
      "label": "Fertigstaucher",
      "value": "Fertigstaucher"
    },
    {
      "label": "FileBpreBmatrize",
      "value": "FileBpreBmatrize"
    },
    {
      "label": "Final Die Assy.",
      "value": "Final Die Assy."
    },
    {
      "label": "Finish Punch",
      "value": "Finish Punch"
    },
    {
      "label": "FlieBpreBmatrize",
      "value": "FlieBpreBmatrize"
    },
    {
      "label": "Fuhrungsmatrize",
      "value": "Fuhrungsmatrize"
    },
    {
      "label": "GREIFER",
      "value": "GREIFER"
    },
    {
      "label": "GREIFER DREHBAR RE+LI",
      "value": "GREIFER DREHBAR RE+LI"
    },
    {
      "label": "GREIFER LINKS",
      "value": "GREIFER LINKS"
    },
    {
      "label": "GREIFER RECHTS",
      "value": "GREIFER RECHTS"
    },
    {
      "label": "GREIFER ψ14.65",
      "value": "GREIFER ψ14.65"
    },
    {
      "label": "GREIFERFINGER",
      "value": "GREIFERFINGER"
    },
    {
      "label": "GREIFERFINGER (2L)",
      "value": "GREIFERFINGER (2L)"
    },
    {
      "label": "GREIFERFINGER (2R)",
      "value": "GREIFERFINGER (2R)"
    },
    {
      "label": "GREIFERFINGER (3L)",
      "value": "GREIFERFINGER (3L)"
    },
    {
      "label": "GREIFERFINGER (3L+4L)",
      "value": "GREIFERFINGER (3L+4L)"
    },
    {
      "label": "GREIFERFINGER (3R)",
      "value": "GREIFERFINGER (3R)"
    },
    {
      "label": "GREIFERFINGER (3R+4R)",
      "value": "GREIFERFINGER (3R+4R)"
    },
    {
      "label": "GREIFERFINGER (4+5R+L)",
      "value": "GREIFERFINGER (4+5R+L)"
    },
    {
      "label": "GREIFERFINGER (5+6L)",
      "value": "GREIFERFINGER (5+6L)"
    },
    {
      "label": "GREIFERFINGER (5+6R)",
      "value": "GREIFERFINGER (5+6R)"
    },
    {
      "label": "GREIFERFINGER (L)",
      "value": "GREIFERFINGER (L)"
    },
    {
      "label": "GREIFERFINGER (R)",
      "value": "GREIFERFINGER (R)"
    },
    {
      "label": "GREIFERFINGER (R+L)",
      "value": "GREIFERFINGER (R+L)"
    },
    {
      "label": "GREIFERSCHENKEL",
      "value": "GREIFERSCHENKEL"
    },
    {
      "label": "GRIP-STATIONARY,POINTER",
      "value": "GRIP-STATIONARY,POINTER"
    },
    {
      "label": "GUIDE TUBE SPRING",
      "value": "GUIDE TUBE SPRING"
    },
    {
      "label": "HEAD PUNCH INSERT",
      "value": "HEAD PUNCH INSERT"
    },
    {
      "label": "HEAD SUPPORT GUIDE",
      "value": "HEAD SUPPORT GUIDE"
    },
    {
      "label": "HEADING PUNCH INSERTS",
      "value": "HEADING PUNCH INSERTS"
    },
    {
      "label": "HES PUNCH",
      "value": "HES PUNCH"
    },
    {
      "label": "HEX BROACH",
      "value": "HEX BROACH"
    },
    {
      "label": "HEX BROACH PART",
      "value": "HEX BROACH PART"
    },
    {
      "label": "HEX BROACH PIN",
      "value": "HEX BROACH PIN"
    },
    {
      "label": "HEX BROACH PUNCH",
      "value": "HEX BROACH PUNCH"
    },
    {
      "label": "HEX EXT. PIN",
      "value": "HEX EXT. PIN"
    },
    {
      "label": "HEX EXTRUDE PIN",
      "value": "HEX EXTRUDE PIN"
    },
    {
      "label": "HEX EXTRUDE PIN 5TH STA",
      "value": "HEX EXTRUDE PIN 5TH STA"
    },
    {
      "label": "HEX EXTRUSION DIE",
      "value": "HEX EXTRUSION DIE"
    },
    {
      "label": "HEX EXTRUSION PUNCH",
      "value": "HEX EXTRUSION PUNCH"
    },
    {
      "label": "HEX EXTRUSTION PIN",
      "value": "HEX EXTRUSTION PIN"
    },
    {
      "label": "HEX HOLD PIN",
      "value": "HEX HOLD PIN"
    },
    {
      "label": "HEX INSERT",
      "value": "HEX INSERT"
    },
    {
      "label": "HEX PIN",
      "value": "HEX PIN"
    },
    {
      "label": "HEX PIN M16",
      "value": "HEX PIN M16"
    },
    {
      "label": "HEX PINS",
      "value": "HEX PINS"
    },
    {
      "label": "HEX PUNCH",
      "value": "HEX PUNCH"
    },
    {
      "label": "HEX PUNCH (ALUMINUM)",
      "value": "HEX PUNCH (ALUMINUM)"
    },
    {
      "label": "HEX PUNCH (W/O MARKING)",
      "value": "HEX PUNCH (W/O MARKING)"
    },
    {
      "label": "HEX PUNCH 3/8",
      "value": "HEX PUNCH 3/8"
    },
    {
      "label": "HEX PUNCH E23A8",
      "value": "HEX PUNCH E23A8"
    },
    {
      "label": "HEX PUNCH M12",
      "value": "HEX PUNCH M12"
    },
    {
      "label": "HEX PUNCH PIN",
      "value": "HEX PUNCH PIN"
    },
    {
      "label": "HEX PUNCH PIN FOR",
      "value": "HEX PUNCH PIN FOR"
    },
    {
      "label": "HEX PUNCH(T55)",
      "value": "HEX PUNCH(T55)"
    },
    {
      "label": "HEX PUNCHE",
      "value": "HEX PUNCHE"
    },
    {
      "label": "HEX PUNCN",
      "value": "HEX PUNCN"
    },
    {
      "label": "HEX Punch",
      "value": "HEX Punch"
    },
    {
      "label": "HEX RECESS PIN",
      "value": "HEX RECESS PIN"
    },
    {
      "label": "HEX SOCKET PIN",
      "value": "HEX SOCKET PIN"
    },
    {
      "label": "HEX WASHER INSERT",
      "value": "HEX WASHER INSERT"
    },
    {
      "label": "HEXAGON PUNCH",
      "value": "HEXAGON PUNCH"
    },
    {
      "label": "HEXAGON PUNCHES",
      "value": "HEXAGON PUNCHES"
    },
    {
      "label": "HEXAGONAL PUNCH",
      "value": "HEXAGONAL PUNCH"
    },
    {
      "label": "HEXRICH",
      "value": "HEXRICH"
    },
    {
      "label": "HOLD PIN",
      "value": "HOLD PIN"
    },
    {
      "label": "HOLE PUNCH",
      "value": "HOLE PUNCH"
    },
    {
      "label": "HOLE PUNCH WITH EJECTOR PIN",
      "value": "HOLE PUNCH WITH EJECTOR PIN"
    },
    {
      "label": "HSS DIN PUNCH",
      "value": "HSS DIN PUNCH"
    },
    {
      "label": "HSS PUNCH",
      "value": "HSS PUNCH"
    },
    {
      "label": "HYLSA",
      "value": "HYLSA"
    },
    {
      "label": "HYLSA INSEX HELG",
      "value": "HYLSA INSEX HELG"
    },
    {
      "label": "Hex Punch",
      "value": "Hex Punch"
    },
    {
      "label": "Hex punch",
      "value": "Hex punch"
    },
    {
      "label": "I-6-KT-STEMPEL",
      "value": "I-6-KT-STEMPEL"
    },
    {
      "label": "I-6-RD-STIFT",
      "value": "I-6-RD-STIFT"
    },
    {
      "label": "I.VIEIZAHNSTEMPEL N12",
      "value": "I.VIEIZAHNSTEMPEL N12"
    },
    {
      "label": "INNENSECHSKANT",
      "value": "INNENSECHSKANT"
    },
    {
      "label": "INNENSECHSKANTSTEMPEL",
      "value": "INNENSECHSKANTSTEMPEL"
    },
    {
      "label": "INNENSECHSRUND-STIFT",
      "value": "INNENSECHSRUND-STIFT"
    },
    {
      "label": "INSATSHYLSA",
      "value": "INSATSHYLSA"
    },
    {
      "label": "INSERT",
      "value": "INSERT"
    },
    {
      "label": "INSERT (INNER)",
      "value": "INSERT (INNER)"
    },
    {
      "label": "INSERT FINGER",
      "value": "INSERT FINGER"
    },
    {
      "label": "INSERTO",
      "value": "INSERTO"
    },
    {
      "label": "INTERNAL HEX PIN",
      "value": "INTERNAL HEX PIN"
    },
    {
      "label": "INTERNAL KARUND PIN",
      "value": "INTERNAL KARUND PIN"
    },
    {
      "label": "INTRODUCTION PINS",
      "value": "INTRODUCTION PINS"
    },
    {
      "label": "ISK-STEMPEL",
      "value": "ISK-STEMPEL"
    },
    {
      "label": "IST OP DIE",
      "value": "IST OP DIE"
    },
    {
      "label": "Innensechskantstempel",
      "value": "Innensechskantstempel"
    },
    {
      "label": "Innensechskantstemple",
      "value": "Innensechskantstemple"
    },
    {
      "label": "Innensechsrundstift",
      "value": "Innensechsrundstift"
    },
    {
      "label": "Innensechsrundstift-sonder",
      "value": "Innensechsrundstift-sonder"
    },
    {
      "label": "Internal 12 Point Pin",
      "value": "Internal 12 Point Pin"
    },
    {
      "label": "Internal 6-Lobe Pin 15V",
      "value": "Internal 6-Lobe Pin 15V"
    },
    {
      "label": "Internal Hex Pin",
      "value": "Internal Hex Pin"
    },
    {
      "label": "Internal Hex Pin Napfdorn 6-kant",
      "value": "Internal Hex Pin Napfdorn 6-kant"
    },
    {
      "label": "Internal K. Pin",
      "value": "Internal K. Pin"
    },
    {
      "label": "Internal Karund Pin",
      "value": "Internal Karund Pin"
    },
    {
      "label": "KEEPER FINGER",
      "value": "KEEPER FINGER"
    },
    {
      "label": "KEEPER TOE",
      "value": "KEEPER TOE"
    },
    {
      "label": "KERN",
      "value": "KERN"
    },
    {
      "label": "KERN HLNTEN",
      "value": "KERN HLNTEN"
    },
    {
      "label": "KEY FOR CLUTCH ASSEMBLY",
      "value": "KEY FOR CLUTCH ASSEMBLY"
    },
    {
      "label": "KEYF FOR CLUTCH ASSEMBLY",
      "value": "KEYF FOR CLUTCH ASSEMBLY"
    },
    {
      "label": "KLAPPE",
      "value": "KLAPPE"
    },
    {
      "label": "KNIFE HOLDER",
      "value": "KNIFE HOLDER"
    },
    {
      "label": "KOPFRING",
      "value": "KOPFRING"
    },
    {
      "label": "KOPFSCHEIBE",
      "value": "KOPFSCHEIBE"
    },
    {
      "label": "KOPFSCHELBE",
      "value": "KOPFSCHELBE"
    },
    {
      "label": "KOPFSTEMPEL",
      "value": "KOPFSTEMPEL"
    },
    {
      "label": "KT123前沖棒",
      "value": "KT123前沖棒"
    },
    {
      "label": "KUPPMATRIZE",
      "value": "KUPPMATRIZE"
    },
    {
      "label": "Karund Pin",
      "value": "Karund Pin"
    },
    {
      "label": "Kern",
      "value": "Kern"
    },
    {
      "label": "Kuppmatrize",
      "value": "Kuppmatrize"
    },
    {
      "label": "L.H. FINGER",
      "value": "L.H. FINGER"
    },
    {
      "label": "L.H. FINGER HOLDER",
      "value": "L.H. FINGER HOLDER"
    },
    {
      "label": "L.H. TRANSFER FINGER",
      "value": "L.H. TRANSFER FINGER"
    },
    {
      "label": "L.H.TRANSFER FINGER",
      "value": "L.H.TRANSFER FINGER"
    },
    {
      "label": "LARGE HEX DIE INSERT",
      "value": "LARGE HEX DIE INSERT"
    },
    {
      "label": "LEFT FINGER",
      "value": "LEFT FINGER"
    },
    {
      "label": "LEFT HAND FINGER",
      "value": "LEFT HAND FINGER"
    },
    {
      "label": "LEFT HAND TRANS FINGER",
      "value": "LEFT HAND TRANS FINGER"
    },
    {
      "label": "LEFT MON-OPENING FINGER M6 KEPS",
      "value": "LEFT MON-OPENING FINGER M6 KEPS"
    },
    {
      "label": "LEFT TRANSFER FINGER",
      "value": "LEFT TRANSFER FINGER"
    },
    {
      "label": "LI GREIFER",
      "value": "LI GREIFER"
    },
    {
      "label": "LI GRIFER",
      "value": "LI GRIFER"
    },
    {
      "label": "LINKER GREIFER",
      "value": "LINKER GREIFER"
    },
    {
      "label": "LINKER TRANSPORTFINGER",
      "value": "LINKER TRANSPORTFINGER"
    },
    {
      "label": "LONG TIP 12mm",
      "value": "LONG TIP 12mm"
    },
    {
      "label": "LOWER WIRE GRIP",
      "value": "LOWER WIRE GRIP"
    },
    {
      "label": "Leuka Karkikone",
      "value": "Leuka Karkikone"
    },
    {
      "label": "M16 HEX BROACH",
      "value": "M16 HEX BROACH"
    },
    {
      "label": "M16 HEX PUNCH",
      "value": "M16 HEX PUNCH"
    },
    {
      "label": "M1F H 882 1190",
      "value": "M1F H 882 1190"
    },
    {
      "label": "M24 HEX PUNCH",
      "value": "M24 HEX PUNCH"
    },
    {
      "label": "M2F H 890/135",
      "value": "M2F H 890/135"
    },
    {
      "label": "M6 HEX BROZCH",
      "value": "M6 HEX BROZCH"
    },
    {
      "label": "M6 HEX. BROACH",
      "value": "M6 HEX. BROACH"
    },
    {
      "label": "M6 SHC HEX PUNCH",
      "value": "M6 SHC HEX PUNCH"
    },
    {
      "label": "M8 HEX BROACH",
      "value": "M8 HEX BROACH"
    },
    {
      "label": "MARKING PUNCH",
      "value": "MARKING PUNCH"
    },
    {
      "label": "MARTELO 2ND OP",
      "value": "MARTELO 2ND OP"
    },
    {
      "label": "MARTRIZE (外徑龍畿製作)",
      "value": "MARTRIZE (外徑龍畿製作)"
    },
    {
      "label": "MATRICE",
      "value": "MATRICE"
    },
    {
      "label": "MATRICE POSTE 2",
      "value": "MATRICE POSTE 2"
    },
    {
      "label": "MATRIZE",
      "value": "MATRIZE"
    },
    {
      "label": "MATRIZE     外徑由MIL自行加工",
      "value": "MATRIZE     外徑由MIL自行加工"
    },
    {
      "label": "MATRIZE (外徑龍畿製作)",
      "value": "MATRIZE (外徑龍畿製作)"
    },
    {
      "label": "MATRIZE (外徑龍畿製做)",
      "value": "MATRIZE (外徑龍畿製做)"
    },
    {
      "label": "MATRIZE(MIL自行研磨外徑)",
      "value": "MATRIZE(MIL自行研磨外徑)"
    },
    {
      "label": "MATRIZE(外徑錐度MIL製作)",
      "value": "MATRIZE(外徑錐度MIL製作)"
    },
    {
      "label": "MATRIZE(外徑龍畿製作)",
      "value": "MATRIZE(外徑龍畿製作)"
    },
    {
      "label": "MATRIZENEINSATZ",
      "value": "MATRIZENEINSATZ"
    },
    {
      "label": "MATRIZENEINSATZ STAT 3",
      "value": "MATRIZENEINSATZ STAT 3"
    },
    {
      "label": "MERKZEUGEINHEIT",
      "value": "MERKZEUGEINHEIT"
    },
    {
      "label": "MES 875 1170",
      "value": "MES 875 1170"
    },
    {
      "label": "MID DIE 1ST",
      "value": "MID DIE 1ST"
    },
    {
      "label": "MIDDLE DIE INSERT",
      "value": "MIDDLE DIE INSERT"
    },
    {
      "label": "MK5 CARBIDE",
      "value": "MK5 CARBIDE"
    },
    {
      "label": "MKL H 888 1290",
      "value": "MKL H 888 1290"
    },
    {
      "label": "MM16 CUTOF  KINFE AND HOLDER",
      "value": "MM16 CUTOF  KINFE AND HOLDER"
    },
    {
      "label": "MM16 CUTTER AND HOLDER",
      "value": "MM16 CUTTER AND HOLDER"
    },
    {
      "label": "MM16 TRANSFER POST LEFT",
      "value": "MM16 TRANSFER POST LEFT"
    },
    {
      "label": "MM16 TRANSFER POST RIGHT",
      "value": "MM16 TRANSFER POST RIGHT"
    },
    {
      "label": "MM8 CUTTER AND HOLDER",
      "value": "MM8 CUTTER AND HOLDER"
    },
    {
      "label": "MM8 FINGERS",
      "value": "MM8 FINGERS"
    },
    {
      "label": "MOVABLE RAIL END",
      "value": "MOVABLE RAIL END"
    },
    {
      "label": "MPE H 892",
      "value": "MPE H 892"
    },
    {
      "label": "MTR ESTR",
      "value": "MTR ESTR"
    },
    {
      "label": "MTR TC",
      "value": "MTR TC"
    },
    {
      "label": "Matirzeneinsatz",
      "value": "Matirzeneinsatz"
    },
    {
      "label": "Matrice",
      "value": "Matrice"
    },
    {
      "label": "Matrice Poste 2",
      "value": "Matrice Poste 2"
    },
    {
      "label": "Matrice superieur",
      "value": "Matrice superieur"
    },
    {
      "label": "Matriza",
      "value": "Matriza"
    },
    {
      "label": "Matrize",
      "value": "Matrize"
    },
    {
      "label": "Matrize-Vorderkern",
      "value": "Matrize-Vorderkern"
    },
    {
      "label": "Messer",
      "value": "Messer"
    },
    {
      "label": "NAPFDORN",
      "value": "NAPFDORN"
    },
    {
      "label": "NAPFDORN 6-KT",
      "value": "NAPFDORN 6-KT"
    },
    {
      "label": "NAPFSTEMPEL",
      "value": "NAPFSTEMPEL"
    },
    {
      "label": "NAPFSTEMPEL TYPL",
      "value": "NAPFSTEMPEL TYPL"
    },
    {
      "label": "NOYEAU SUPERIEUR",
      "value": "NOYEAU SUPERIEUR"
    },
    {
      "label": "Napfdorn",
      "value": "Napfdorn"
    },
    {
      "label": "Napfdorn Karund",
      "value": "Napfdorn Karund"
    },
    {
      "label": "Napfdorn-Karund",
      "value": "Napfdorn-Karund"
    },
    {
      "label": "Napfstempel L=116.5",
      "value": "Napfstempel L=116.5"
    },
    {
      "label": "OFFSET FINGERS",
      "value": "OFFSET FINGERS"
    },
    {
      "label": "OPERATION DIE CORE ASSEMBLY",
      "value": "OPERATION DIE CORE ASSEMBLY"
    },
    {
      "label": "Oil Hydraulic Press",
      "value": "Oil Hydraulic Press"
    },
    {
      "label": "PART OF KNIFE",
      "value": "PART OF KNIFE"
    },
    {
      "label": "PIM",
      "value": "PIM"
    },
    {
      "label": "PIN",
      "value": "PIN"
    },
    {
      "label": "PIN-單件",
      "value": "PIN-單件"
    },
    {
      "label": "PINCE DROITE",
      "value": "PINCE DROITE"
    },
    {
      "label": "PINCE GAUCHE POSTE 0-1",
      "value": "PINCE GAUCHE POSTE 0-1"
    },
    {
      "label": "PINCE POSTE 0 VERS 1",
      "value": "PINCE POSTE 0 VERS 1"
    },
    {
      "label": "PINCE POSTE 1 VERS 2",
      "value": "PINCE POSTE 1 VERS 2"
    },
    {
      "label": "PINCE POSTE 2 VERS 3",
      "value": "PINCE POSTE 2 VERS 3"
    },
    {
      "label": "PINCE POSTE 3 VERS 4",
      "value": "PINCE POSTE 3 VERS 4"
    },
    {
      "label": "PINCES GAUCHE",
      "value": "PINCES GAUCHE"
    },
    {
      "label": "PLATE",
      "value": "PLATE"
    },
    {
      "label": "POINCON",
      "value": "POINCON"
    },
    {
      "label": "POINCON 6 PANS",
      "value": "POINCON 6 PANS"
    },
    {
      "label": "POINCON D'EXTRUSION",
      "value": "POINCON D'EXTRUSION"
    },
    {
      "label": "POINCON TORX T55",
      "value": "POINCON TORX T55"
    },
    {
      "label": "POINT DIAMETER GAGE",
      "value": "POINT DIAMETER GAGE"
    },
    {
      "label": "POINT DIE",
      "value": "POINT DIE"
    },
    {
      "label": "POINT DIE INSERT",
      "value": "POINT DIE INSERT"
    },
    {
      "label": "POINTER FILLER PLATE",
      "value": "POINTER FILLER PLATE"
    },
    {
      "label": "POINTING DIE",
      "value": "POINTING DIE"
    },
    {
      "label": "POINTING DIES STYLE III",
      "value": "POINTING DIES STYLE III"
    },
    {
      "label": "PRESSMATRIZE",
      "value": "PRESSMATRIZE"
    },
    {
      "label": "PRESSMATRIZE MAT POINT",
      "value": "PRESSMATRIZE MAT POINT"
    },
    {
      "label": "PUNCAO",
      "value": "PUNCAO"
    },
    {
      "label": "PUNCAO HEXAGONAL",
      "value": "PUNCAO HEXAGONAL"
    },
    {
      "label": "PUNCH",
      "value": "PUNCH"
    },
    {
      "label": "PUNCH CASE SECOND STATION",
      "value": "PUNCH CASE SECOND STATION"
    },
    {
      "label": "PUNCH DIE",
      "value": "PUNCH DIE"
    },
    {
      "label": "PUNCH EXTRUSION INSERT",
      "value": "PUNCH EXTRUSION INSERT"
    },
    {
      "label": "PUNCH HEX",
      "value": "PUNCH HEX"
    },
    {
      "label": "PUNCH HEX PIN",
      "value": "PUNCH HEX PIN"
    },
    {
      "label": "PUNCH INSERT",
      "value": "PUNCH INSERT"
    },
    {
      "label": "PUNCH PIN",
      "value": "PUNCH PIN"
    },
    {
      "label": "Puncao",
      "value": "Puncao"
    },
    {
      "label": "Puncao Torx",
      "value": "Puncao Torx"
    },
    {
      "label": "Puncao sextavado",
      "value": "Puncao sextavado"
    },
    {
      "label": "Punch Shroud",
      "value": "Punch Shroud"
    },
    {
      "label": "Punch Straight I.D. Insert",
      "value": "Punch Straight I.D. Insert"
    },
    {
      "label": "QUILL INSERT",
      "value": "QUILL INSERT"
    },
    {
      "label": "QUILL PLATE",
      "value": "QUILL PLATE"
    },
    {
      "label": "R,H.TRANSFER FINGER 2ND OP",
      "value": "R,H.TRANSFER FINGER 2ND OP"
    },
    {
      "label": "R.H. TRANSFER FINGER",
      "value": "R.H. TRANSFER FINGER"
    },
    {
      "label": "R.H.TFANSFER FINGER 3RD OP",
      "value": "R.H.TFANSFER FINGER 3RD OP"
    },
    {
      "label": "R.H.TRANSFER FINGER",
      "value": "R.H.TRANSFER FINGER"
    },
    {
      "label": "R.H.TRANSFER FINGER 1ST OP",
      "value": "R.H.TRANSFER FINGER 1ST OP"
    },
    {
      "label": "R.H.TRANSFER FINGER 2ND OP",
      "value": "R.H.TRANSFER FINGER 2ND OP"
    },
    {
      "label": "RAIL LEDGE",
      "value": "RAIL LEDGE"
    },
    {
      "label": "RE GREIFER",
      "value": "RE GREIFER"
    },
    {
      "label": "REAR DIE INNER INSERT",
      "value": "REAR DIE INNER INSERT"
    },
    {
      "label": "REAR DIE INSERT",
      "value": "REAR DIE INSERT"
    },
    {
      "label": "REAR PUNCH INSERT",
      "value": "REAR PUNCH INSERT"
    },
    {
      "label": "RECHTER GREIFER",
      "value": "RECHTER GREIFER"
    },
    {
      "label": "RECHTER TRANSPORTFINGER",
      "value": "RECHTER TRANSPORTFINGER"
    },
    {
      "label": "RED-MATRIZE",
      "value": "RED-MATRIZE"
    },
    {
      "label": "RED. MATRIZE (外徑龍畿製作)",
      "value": "RED. MATRIZE (外徑龍畿製作)"
    },
    {
      "label": "RED.-MATRIZE",
      "value": "RED.-MATRIZE"
    },
    {
      "label": "RED.-MATRIZE (外徑龍畿製作)",
      "value": "RED.-MATRIZE (外徑龍畿製作)"
    },
    {
      "label": "RED.-MATRIZE(模殼外徑龍畿做)",
      "value": "RED.-MATRIZE(模殼外徑龍畿做)"
    },
    {
      "label": "RED.EINSATZ",
      "value": "RED.EINSATZ"
    },
    {
      "label": "RED.MATRIZE",
      "value": "RED.MATRIZE"
    },
    {
      "label": "RED.MATRIZE (外徑龍畿製作)",
      "value": "RED.MATRIZE (外徑龍畿製作)"
    },
    {
      "label": "RED.MATRIZE(外徑龍畿製作)",
      "value": "RED.MATRIZE(外徑龍畿製作)"
    },
    {
      "label": "REDUZIERMATRIZE",
      "value": "REDUZIERMATRIZE"
    },
    {
      "label": "RH RAIL END TOP PLATE",
      "value": "RH RAIL END TOP PLATE"
    },
    {
      "label": "RIGHT FINGER",
      "value": "RIGHT FINGER"
    },
    {
      "label": "RIGHT HAND TRANS FINGER",
      "value": "RIGHT HAND TRANS FINGER"
    },
    {
      "label": "RIGHT RAIL END TOP PLATE",
      "value": "RIGHT RAIL END TOP PLATE"
    },
    {
      "label": "RIGHT TRANSFER FINGER",
      "value": "RIGHT TRANSFER FINGER"
    },
    {
      "label": "ROLLER INNER TRACK",
      "value": "ROLLER INNER TRACK"
    },
    {
      "label": "ROLLER OUTER TRACK",
      "value": "ROLLER OUTER TRACK"
    },
    {
      "label": "ROLLER PUSH BLADE",
      "value": "ROLLER PUSH BLADE"
    },
    {
      "label": "Red.-Einsatz",
      "value": "Red.-Einsatz"
    },
    {
      "label": "Red.Einsatz",
      "value": "Red.Einsatz"
    },
    {
      "label": "S-PLATE 退磁熱處理",
      "value": "S-PLATE 退磁熱處理"
    },
    {
      "label": "S.M CARBURE",
      "value": "S.M CARBURE"
    },
    {
      "label": "SCHIEBER",
      "value": "SCHIEBER"
    },
    {
      "label": "SECHSKANTDORN",
      "value": "SECHSKANTDORN"
    },
    {
      "label": "SECHSKANTSTEMPEL",
      "value": "SECHSKANTSTEMPEL"
    },
    {
      "label": "SECHSKANTSTEMPEL (HEX)",
      "value": "SECHSKANTSTEMPEL (HEX)"
    },
    {
      "label": "SECHSKANTSTIFT",
      "value": "SECHSKANTSTIFT"
    },
    {
      "label": "SECOND DIE CORE",
      "value": "SECOND DIE CORE"
    },
    {
      "label": "SECOND DIE STATION",
      "value": "SECOND DIE STATION"
    },
    {
      "label": "SEG HEX INSERT",
      "value": "SEG HEX INSERT"
    },
    {
      "label": "SEGMENTED CARBDE HEX WAS",
      "value": "SEGMENTED CARBDE HEX WAS"
    },
    {
      "label": "SEGMENTED CARBIDE",
      "value": "SEGMENTED CARBIDE"
    },
    {
      "label": "SEGMENTED CARBIDE HEX WASHER",
      "value": "SEGMENTED CARBIDE HEX WASHER"
    },
    {
      "label": "SEGMENTED CARBIDE HEX WASHER INSERT",
      "value": "SEGMENTED CARBIDE HEX WASHER INSERT"
    },
    {
      "label": "SEGMENTED CARBIDE INSERT",
      "value": "SEGMENTED CARBIDE INSERT"
    },
    {
      "label": "SEGMENTED DIE INSERT",
      "value": "SEGMENTED DIE INSERT"
    },
    {
      "label": "SEGMENTED HEX DIE",
      "value": "SEGMENTED HEX DIE"
    },
    {
      "label": "SEGMENTED HEX INSERT",
      "value": "SEGMENTED HEX INSERT"
    },
    {
      "label": "SEGMENTED HEX WASHER INSERT",
      "value": "SEGMENTED HEX WASHER INSERT"
    },
    {
      "label": "SEGMENTED HM DIE CORE",
      "value": "SEGMENTED HM DIE CORE"
    },
    {
      "label": "SHANK DIE ASSY",
      "value": "SHANK DIE ASSY"
    },
    {
      "label": "SHANK DIE ASSY (STA.2)",
      "value": "SHANK DIE ASSY (STA.2)"
    },
    {
      "label": "SHEAR QUILL",
      "value": "SHEAR QUILL"
    },
    {
      "label": "SIGNISERSTEMPEL",
      "value": "SIGNISERSTEMPEL"
    },
    {
      "label": "SKD-11熱處理",
      "value": "SKD-11熱處理"
    },
    {
      "label": "SKD-61熱處理",
      "value": "SKD-61熱處理"
    },
    {
      "label": "SKD11熱處理",
      "value": "SKD11熱處理"
    },
    {
      "label": "SKH-9熱處理",
      "value": "SKH-9熱處理"
    },
    {
      "label": "SKT Presskern",
      "value": "SKT Presskern"
    },
    {
      "label": "SKT.-STEMPEL",
      "value": "SKT.-STEMPEL"
    },
    {
      "label": "SLEEVED DIE INSERT",
      "value": "SLEEVED DIE INSERT"
    },
    {
      "label": "SLIDING DIE",
      "value": "SLIDING DIE"
    },
    {
      "label": "SLOT INSERT",
      "value": "SLOT INSERT"
    },
    {
      "label": "SOLID CARBIDE",
      "value": "SOLID CARBIDE"
    },
    {
      "label": "SPACER",
      "value": "SPACER"
    },
    {
      "label": "SPANNTEIL",
      "value": "SPANNTEIL"
    },
    {
      "label": "SPANNTEIL FEST",
      "value": "SPANNTEIL FEST"
    },
    {
      "label": "STA 5 FLOATIN PUNCH",
      "value": "STA 5 FLOATIN PUNCH"
    },
    {
      "label": "STA. 5 FLOATING PUNCH",
      "value": "STA. 5 FLOATING PUNCH"
    },
    {
      "label": "STA. 5 PUNCH",
      "value": "STA. 5 PUNCH"
    },
    {
      "label": "STA.5 FLOATING PUNCH",
      "value": "STA.5 FLOATING PUNCH"
    },
    {
      "label": "STA.5 FLOATING PUNCH(NWESTYLE)",
      "value": "STA.5 FLOATING PUNCH(NWESTYLE)"
    },
    {
      "label": "STATION 5 PUNCH",
      "value": "STATION 5 PUNCH"
    },
    {
      "label": "STATIONARY RAIL END",
      "value": "STATIONARY RAIL END"
    },
    {
      "label": "STEMPEL",
      "value": "STEMPEL"
    },
    {
      "label": "STEMPEL 12KT",
      "value": "STEMPEL 12KT"
    },
    {
      "label": "STEMPEL T45",
      "value": "STEMPEL T45"
    },
    {
      "label": "STEMPEL T50",
      "value": "STEMPEL T50"
    },
    {
      "label": "STOPPER PLATE",
      "value": "STOPPER PLATE"
    },
    {
      "label": "STOPPER PLATES",
      "value": "STOPPER PLATES"
    },
    {
      "label": "STRAIGHT I.D. INSERT",
      "value": "STRAIGHT I.D. INSERT"
    },
    {
      "label": "Schieber",
      "value": "Schieber"
    },
    {
      "label": "Skt.-Stempel",
      "value": "Skt.-Stempel"
    },
    {
      "label": "Stempel",
      "value": "Stempel"
    },
    {
      "label": "Stempel ASR 45",
      "value": "Stempel ASR 45"
    },
    {
      "label": "Stempel L=115.9",
      "value": "Stempel L=115.9"
    },
    {
      "label": "Stempel T 40",
      "value": "Stempel T 40"
    },
    {
      "label": "Stempleinsatz",
      "value": "Stempleinsatz"
    },
    {
      "label": "Support de pinces",
      "value": "Support de pinces"
    },
    {
      "label": "T03000438+T03000440+T03000441+",
      "value": "T03000438+T03000440+T03000441+"
    },
    {
      "label": "TAP GUIDE TUBE",
      "value": "TAP GUIDE TUBE"
    },
    {
      "label": "TAPERED DIE INSERT",
      "value": "TAPERED DIE INSERT"
    },
    {
      "label": "TENSILE TEST BLOCK",
      "value": "TENSILE TEST BLOCK"
    },
    {
      "label": "THIRD DIE CORE",
      "value": "THIRD DIE CORE"
    },
    {
      "label": "THREADER",
      "value": "THREADER"
    },
    {
      "label": "TIP INSERT",
      "value": "TIP INSERT"
    },
    {
      "label": "TIP POINTER FEED CHUTE",
      "value": "TIP POINTER FEED CHUTE"
    },
    {
      "label": "TOOL INSERT",
      "value": "TOOL INSERT"
    },
    {
      "label": "TOOLING",
      "value": "TOOLING"
    },
    {
      "label": "TOP DIE INNER INSERT",
      "value": "TOP DIE INNER INSERT"
    },
    {
      "label": "TOP DIE INSERT",
      "value": "TOP DIE INSERT"
    },
    {
      "label": "TOP FRAME OF INNER RAIL HOLDER",
      "value": "TOP FRAME OF INNER RAIL HOLDER"
    },
    {
      "label": "TOP INSERT",
      "value": "TOP INSERT"
    },
    {
      "label": "TOP INSERT,4TH",
      "value": "TOP INSERT,4TH"
    },
    {
      "label": "TORX ESPECIAL T55",
      "value": "TORX ESPECIAL T55"
    },
    {
      "label": "TORX PUNCH",
      "value": "TORX PUNCH"
    },
    {
      "label": "TORX-STEMPEL",
      "value": "TORX-STEMPEL"
    },
    {
      "label": "TORX-STEMPLE T55",
      "value": "TORX-STEMPLE T55"
    },
    {
      "label": "TORXSTEMPEL",
      "value": "TORXSTEMPEL"
    },
    {
      "label": "TP31-001 3RD BLOW",
      "value": "TP31-001 3RD BLOW"
    },
    {
      "label": "TP31-001 3RD PUNCH .625",
      "value": "TP31-001 3RD PUNCH .625"
    },
    {
      "label": "TRANSFER FIGNER",
      "value": "TRANSFER FIGNER"
    },
    {
      "label": "TRANSFER FINGER",
      "value": "TRANSFER FINGER"
    },
    {
      "label": "TRANSFER FINGER BLOCK",
      "value": "TRANSFER FINGER BLOCK"
    },
    {
      "label": "TRANSFER FINGER(L)",
      "value": "TRANSFER FINGER(L)"
    },
    {
      "label": "TRANSFER FINGER(R)",
      "value": "TRANSFER FINGER(R)"
    },
    {
      "label": "TRANSFER FINGER-R.H",
      "value": "TRANSFER FINGER-R.H"
    },
    {
      "label": "TRANSFER FINGER-R.H.",
      "value": "TRANSFER FINGER-R.H."
    },
    {
      "label": "TRANSFER FINGERS",
      "value": "TRANSFER FINGERS"
    },
    {
      "label": "TRANSFER SET DOWN BLOCKS",
      "value": "TRANSFER SET DOWN BLOCKS"
    },
    {
      "label": "TRANSFERFINGER",
      "value": "TRANSFERFINGER"
    },
    {
      "label": "TRANSPORT FINGER",
      "value": "TRANSPORT FINGER"
    },
    {
      "label": "TRANSPORT GRAB DUO TT",
      "value": "TRANSPORT GRAB DUO TT"
    },
    {
      "label": "TRANSPORTFIGNER LI.",
      "value": "TRANSPORTFIGNER LI."
    },
    {
      "label": "TRANSPORTFINGER",
      "value": "TRANSPORTFINGER"
    },
    {
      "label": "TRANSPORTFINGER LI.",
      "value": "TRANSPORTFINGER LI."
    },
    {
      "label": "TRANSPORTFINGER-LINKS",
      "value": "TRANSPORTFINGER-LINKS"
    },
    {
      "label": "TRANSPORTFINGER-RECHTS",
      "value": "TRANSPORTFINGER-RECHTS"
    },
    {
      "label": "TRANSPORTGREIFER",
      "value": "TRANSPORTGREIFER"
    },
    {
      "label": "TROX PUNCH",
      "value": "TROX PUNCH"
    },
    {
      "label": "Torx T25",
      "value": "Torx T25"
    },
    {
      "label": "Transportfinger",
      "value": "Transportfinger"
    },
    {
      "label": "T板 DR11C",
      "value": "T板 DR11C"
    },
    {
      "label": "T棒 DR05C",
      "value": "T棒 DR05C"
    },
    {
      "label": "T棒 DR07C",
      "value": "T棒 DR07C"
    },
    {
      "label": "T棒 DR09C",
      "value": "T棒 DR09C"
    },
    {
      "label": "T棒 DR11C",
      "value": "T棒 DR11C"
    },
    {
      "label": "T棒 DR14C",
      "value": "T棒 DR14C"
    },
    {
      "label": "T棒 DR17C",
      "value": "T棒 DR17C"
    },
    {
      "label": "T棒 G20",
      "value": "T棒 G20"
    },
    {
      "label": "T棒 G30",
      "value": "T棒 G30"
    },
    {
      "label": "T棒 KG5",
      "value": "T棒 KG5"
    },
    {
      "label": "T棒 KG7",
      "value": "T棒 KG7"
    },
    {
      "label": "T棒 SR13C",
      "value": "T棒 SR13C"
    },
    {
      "label": "T棒 ST6",
      "value": "T棒 ST6"
    },
    {
      "label": "T棒 TR05C",
      "value": "T棒 TR05C"
    },
    {
      "label": "T棒 TR09C",
      "value": "T棒 TR09C"
    },
    {
      "label": "T棒 TR15C",
      "value": "T棒 TR15C"
    },
    {
      "label": "T棒 TR20C",
      "value": "T棒 TR20C"
    },
    {
      "label": "T棒 UR10C",
      "value": "T棒 UR10C"
    },
    {
      "label": "T棒 UR13C",
      "value": "T棒 UR13C"
    },
    {
      "label": "T棒 YR10C",
      "value": "T棒 YR10C"
    },
    {
      "label": "T棒 YR20C",
      "value": "T棒 YR20C"
    },
    {
      "label": "UMFORMSTEMPEL",
      "value": "UMFORMSTEMPEL"
    },
    {
      "label": "UPPER WIRE GRIP",
      "value": "UPPER WIRE GRIP"
    },
    {
      "label": "VERSTELLBLOCK",
      "value": "VERSTELLBLOCK"
    },
    {
      "label": "Vorstaucherstift",
      "value": "Vorstaucherstift"
    },
    {
      "label": "WASHER RAIL BASE",
      "value": "WASHER RAIL BASE"
    },
    {
      "label": "WERKZEUGEINHEIT",
      "value": "WERKZEUGEINHEIT"
    },
    {
      "label": "WIRE NOZZLE",
      "value": "WIRE NOZZLE"
    },
    {
      "label": "ZANGE",
      "value": "ZANGE"
    },
    {
      "label": "ZANGENFINGER",
      "value": "ZANGENFINGER"
    },
    {
      "label": "ZANGENFINGER LI",
      "value": "ZANGENFINGER LI"
    },
    {
      "label": "ZANGENFINGER LI.",
      "value": "ZANGENFINGER LI."
    },
    {
      "label": "ZANGENFINGER RE.",
      "value": "ZANGENFINGER RE."
    },
    {
      "label": "ZANGENHALTER",
      "value": "ZANGENHALTER"
    },
    {
      "label": "ZANGENHALTER LINKS",
      "value": "ZANGENHALTER LINKS"
    },
    {
      "label": "ZIEHKERN",
      "value": "ZIEHKERN"
    },
    {
      "label": "ZUFUHRUNG",
      "value": "ZUFUHRUNG"
    },
    {
      "label": "Zange Links f.Spreiztransport",
      "value": "Zange Links f.Spreiztransport"
    },
    {
      "label": "off set fingers",
      "value": "off set fingers"
    },
    {
      "label": "poincon d'extrusion",
      "value": "poincon d'extrusion"
    },
    {
      "label": "socket hex punch",
      "value": "socket hex punch"
    },
    {
      "label": "φ.675 Draw Die",
      "value": "φ.675 Draw Die"
    },
    {
      "label": "三片模",
      "value": "三片模"
    },
    {
      "label": "三片模-治具",
      "value": "三片模-治具"
    },
    {
      "label": "三片模仁",
      "value": "三片模仁"
    },
    {
      "label": "三片模治具",
      "value": "三片模治具"
    },
    {
      "label": "下仁電極",
      "value": "下仁電極"
    },
    {
      "label": "下切刀",
      "value": "下切刀"
    },
    {
      "label": "下墊塊",
      "value": "下墊塊"
    },
    {
      "label": "下模",
      "value": "下模"
    },
    {
      "label": "下模仁",
      "value": "下模仁"
    },
    {
      "label": "下模仁 (FU24-W6101911BDE1)",
      "value": "下模仁 (FU24-W6101911BDE1)"
    },
    {
      "label": "下模仁-套圈",
      "value": "下模仁-套圈"
    },
    {
      "label": "下模仁-電極",
      "value": "下模仁-電極"
    },
    {
      "label": "下模仁-模仁",
      "value": "下模仁-模仁"
    },
    {
      "label": "下模仁組",
      "value": "下模仁組"
    },
    {
      "label": "下模引申模仁",
      "value": "下模引申模仁"
    },
    {
      "label": "上切刀",
      "value": "上切刀"
    },
    {
      "label": "上件",
      "value": "上件"
    },
    {
      "label": "上座",
      "value": "上座"
    },
    {
      "label": "上墊塊",
      "value": "上墊塊"
    },
    {
      "label": "上模",
      "value": "上模"
    },
    {
      "label": "上模仁",
      "value": "上模仁"
    },
    {
      "label": "上模仁 (FU24-W6101911TDE1)",
      "value": "上模仁 (FU24-W6101911TDE1)"
    },
    {
      "label": "上模仁 (六片模)",
      "value": "上模仁 (六片模)"
    },
    {
      "label": "上模仁(12片)",
      "value": "上模仁(12片)"
    },
    {
      "label": "上模仁(12片模)",
      "value": "上模仁(12片模)"
    },
    {
      "label": "上模仁(八片模)",
      "value": "上模仁(八片模)"
    },
    {
      "label": "上模仁(六片)",
      "value": "上模仁(六片)"
    },
    {
      "label": "上模仁(六片模)",
      "value": "上模仁(六片模)"
    },
    {
      "label": "上模仁-套圈",
      "value": "上模仁-套圈"
    },
    {
      "label": "上模仁-電極",
      "value": "上模仁-電極"
    },
    {
      "label": "上模仁-模仁",
      "value": "上模仁-模仁"
    },
    {
      "label": "上模仁組",
      "value": "上模仁組"
    },
    {
      "label": "上模電極",
      "value": "上模電極"
    },
    {
      "label": "上擠型沖",
      "value": "上擠型沖"
    },
    {
      "label": "大墊塊",
      "value": "大墊塊"
    },
    {
      "label": "子母沖",
      "value": "子母沖"
    },
    {
      "label": "小墊塊",
      "value": "小墊塊"
    },
    {
      "label": "不良品",
      "value": "不良品"
    },
    {
      "label": "不銹鋼本體真空銲接-BODY",
      "value": "不銹鋼本體真空銲接-BODY"
    },
    {
      "label": "不銹鋼組合主體真空焊接",
      "value": "不銹鋼組合主體真空焊接"
    },
    {
      "label": "不銹鋼組合主體真空銲接",
      "value": "不銹鋼組合主體真空銲接"
    },
    {
      "label": "不銹鋼量酒杯真空銲接",
      "value": "不銹鋼量酒杯真空銲接"
    },
    {
      "label": "不銹鋼管(無縫)",
      "value": "不銹鋼管(無縫)"
    },
    {
      "label": "不銹鋼應力消除退火",
      "value": "不銹鋼應力消除退火"
    },
    {
      "label": "中套圈",
      "value": "中套圈"
    },
    {
      "label": "中模仁",
      "value": "中模仁"
    },
    {
      "label": "內六角沉頭螺絲",
      "value": "內六角沉頭螺絲"
    },
    {
      "label": "內六角螺釘",
      "value": "內六角螺釘"
    },
    {
      "label": "內六角螺絲",
      "value": "內六角螺絲"
    },
    {
      "label": "內穴塞",
      "value": "內穴塞"
    },
    {
      "label": "內套筒",
      "value": "內套筒"
    },
    {
      "label": "內套管",
      "value": "內套管"
    },
    {
      "label": "內筒",
      "value": "內筒"
    },
    {
      "label": "內筒(二)",
      "value": "內筒(二)"
    },
    {
      "label": "內筒(測試)",
      "value": "內筒(測試)"
    },
    {
      "label": "內模仁",
      "value": "內模仁"
    },
    {
      "label": "六片模",
      "value": "六片模"
    },
    {
      "label": "六片模仁",
      "value": "六片模仁"
    },
    {
      "label": "六角沖棒",
      "value": "六角沖棒"
    },
    {
      "label": "公牙",
      "value": "公牙"
    },
    {
      "label": "切刀",
      "value": "切刀"
    },
    {
      "label": "切刀(單)",
      "value": "切刀(單)"
    },
    {
      "label": "切刀(碳化鎢KE10+VA80)",
      "value": "切刀(碳化鎢KE10+VA80)"
    },
    {
      "label": "切刀(碳化鎢ST6+HV)",
      "value": "切刀(碳化鎢ST6+HV)"
    },
    {
      "label": "切刀(碳化鎢ST6+VA80)",
      "value": "切刀(碳化鎢ST6+VA80)"
    },
    {
      "label": "切刀-單件",
      "value": "切刀-單件"
    },
    {
      "label": "切刀-碳化鎢",
      "value": "切刀-碳化鎢"
    },
    {
      "label": "切刀一",
      "value": "切刀一"
    },
    {
      "label": "切刀二",
      "value": "切刀二"
    },
    {
      "label": "切刀退鍍(TiN)",
      "value": "切刀退鍍(TiN)"
    },
    {
      "label": "切刀鍍鈦(TiN)",
      "value": "切刀鍍鈦(TiN)"
    },
    {
      "label": "引伸入塊",
      "value": "引伸入塊"
    },
    {
      "label": "方板 DR11C",
      "value": "方板 DR11C"
    },
    {
      "label": "方板 DR14C",
      "value": "方板 DR14C"
    },
    {
      "label": "方板 TR20C",
      "value": "方板 TR20C"
    },
    {
      "label": "日揚機件銲接",
      "value": "日揚機件銲接"
    },
    {
      "label": "代客電極加工製作",
      "value": "代客電極加工製作"
    },
    {
      "label": "代客製作電極",
      "value": "代客製作電極"
    },
    {
      "label": "代客製作電極加工",
      "value": "代客製作電極加工"
    },
    {
      "label": "代客製作電極加工(車單邊)",
      "value": "代客製作電極加工(車單邊)"
    },
    {
      "label": "代客製作電極加工(車雙頭)",
      "value": "代客製作電極加工(車雙頭)"
    },
    {
      "label": "代客製作電擊加工(車單邊)",
      "value": "代客製作電擊加工(車單邊)"
    },
    {
      "label": "半成品",
      "value": "半成品"
    },
    {
      "label": "半成品 DC53",
      "value": "半成品 DC53"
    },
    {
      "label": "半成品 SKD61",
      "value": "半成品 SKD61"
    },
    {
      "label": "半成品 SKH55",
      "value": "半成品 SKH55"
    },
    {
      "label": "半成品 SKH9",
      "value": "半成品 SKH9"
    },
    {
      "label": "半成品 實際10.3",
      "value": "半成品 實際10.3"
    },
    {
      "label": "半成品-SKH9",
      "value": "半成品-SKH9"
    },
    {
      "label": "半成品-零件",
      "value": "半成品-零件"
    },
    {
      "label": "右件",
      "value": "右件"
    },
    {
      "label": "右夾子",
      "value": "右夾子"
    },
    {
      "label": "右零件",
      "value": "右零件"
    },
    {
      "label": "四片模",
      "value": "四片模"
    },
    {
      "label": "四片模-治具",
      "value": "四片模-治具"
    },
    {
      "label": "四片模仁",
      "value": "四片模仁"
    },
    {
      "label": "四片模治具",
      "value": "四片模治具"
    },
    {
      "label": "四角外殼",
      "value": "四角外殼"
    },
    {
      "label": "四角板 青銅 (JIS CAC406C)",
      "value": "四角板 青銅 (JIS CAC406C)"
    },
    {
      "label": "四角板 青銅C95500",
      "value": "四角板 青銅C95500"
    },
    {
      "label": "四角板 紅銅",
      "value": "四角板 紅銅"
    },
    {
      "label": "四角板 紅銅 C1100",
      "value": "四角板 紅銅 C1100"
    },
    {
      "label": "四角板 無氧銅",
      "value": "四角板 無氧銅"
    },
    {
      "label": "四角板 銅C90800",
      "value": "四角板 銅C90800"
    },
    {
      "label": "四角鐵 4140",
      "value": "四角鐵 4140"
    },
    {
      "label": "四角鐵 ASP23",
      "value": "四角鐵 ASP23"
    },
    {
      "label": "四角鐵 DC53",
      "value": "四角鐵 DC53"
    },
    {
      "label": "四角鐵 DC53 庫20",
      "value": "四角鐵 DC53 庫20"
    },
    {
      "label": "四角鐵 DC53 庫22",
      "value": "四角鐵 DC53 庫22"
    },
    {
      "label": "四角鐵 DC53 庫24",
      "value": "四角鐵 DC53 庫24"
    },
    {
      "label": "四角鐵 DC53 庫9",
      "value": "四角鐵 DC53 庫9"
    },
    {
      "label": "四角鐵 R3",
      "value": "四角鐵 R3"
    },
    {
      "label": "四角鐵 S45C",
      "value": "四角鐵 S45C"
    },
    {
      "label": "四角鐵 SK5H預硬",
      "value": "四角鐵 SK5H預硬"
    },
    {
      "label": "四角鐵 SKD11",
      "value": "四角鐵 SKD11"
    },
    {
      "label": "四角鐵 SKD11(日立)",
      "value": "四角鐵 SKD11(日立)"
    },
    {
      "label": "四角鐵 SKD11(榮剛)",
      "value": "四角鐵 SKD11(榮剛)"
    },
    {
      "label": "四角鐵 SKD61",
      "value": "四角鐵 SKD61"
    },
    {
      "label": "四角鐵 SKD61 (DAC)",
      "value": "四角鐵 SKD61 (DAC)"
    },
    {
      "label": "四角鐵 SKD61 (榮鋼)",
      "value": "四角鐵 SKD61 (榮鋼)"
    },
    {
      "label": "四角鐵 SKD61(DAC)",
      "value": "四角鐵 SKD61(DAC)"
    },
    {
      "label": "四角鐵 SKD61(日立)",
      "value": "四角鐵 SKD61(日立)"
    },
    {
      "label": "四角鐵 SKH55",
      "value": "四角鐵 SKH55"
    },
    {
      "label": "四角鐵 SKH9",
      "value": "四角鐵 SKH9"
    },
    {
      "label": "四角鐵 SKH9 (日立)",
      "value": "四角鐵 SKH9 (日立)"
    },
    {
      "label": "四角鐵 SKH9(日立)",
      "value": "四角鐵 SKH9(日立)"
    },
    {
      "label": "四角鐵 SLD",
      "value": "四角鐵 SLD"
    },
    {
      "label": "四角鐵 SLD(日立)",
      "value": "四角鐵 SLD(日立)"
    },
    {
      "label": "四角鐵 SUS304",
      "value": "四角鐵 SUS304"
    },
    {
      "label": "四角鐵 青銅 (SAE64)",
      "value": "四角鐵 青銅 (SAE64)"
    },
    {
      "label": "四角鐵 紅銅",
      "value": "四角鐵 紅銅"
    },
    {
      "label": "四角鐵 無氧銅",
      "value": "四角鐵 無氧銅"
    },
    {
      "label": "四角鐵 無氧銅(鍛造黑皮99.99)",
      "value": "四角鐵 無氧銅(鍛造黑皮99.99)"
    },
    {
      "label": "四角鐵 黃銅C3604",
      "value": "四角鐵 黃銅C3604"
    },
    {
      "label": "四角鐵 彈簧鋼(中鋼SK5)",
      "value": "四角鐵 彈簧鋼(中鋼SK5)"
    },
    {
      "label": "外穴塞",
      "value": "外穴塞"
    },
    {
      "label": "外注共用件",
      "value": "外注共用件"
    },
    {
      "label": "外套圈",
      "value": "外套圈"
    },
    {
      "label": "外套筒",
      "value": "外套筒"
    },
    {
      "label": "外套管",
      "value": "外套管"
    },
    {
      "label": "外套管真空銲接",
      "value": "外套管真空銲接"
    },
    {
      "label": "外筒",
      "value": "外筒"
    },
    {
      "label": "外筒(二)",
      "value": "外筒(二)"
    },
    {
      "label": "外筒(測試)",
      "value": "外筒(測試)"
    },
    {
      "label": "外模仁",
      "value": "外模仁"
    },
    {
      "label": "左件",
      "value": "左件"
    },
    {
      "label": "左夾子",
      "value": "左夾子"
    },
    {
      "label": "左零件",
      "value": "左零件"
    },
    {
      "label": "平腳油杯蓋",
      "value": "平腳油杯蓋"
    },
    {
      "label": "平鍵",
      "value": "平鍵"
    },
    {
      "label": "白鐵 SUS440c",
      "value": "白鐵 SUS440c"
    },
    {
      "label": "白鐵#304",
      "value": "白鐵#304"
    },
    {
      "label": "穴塞",
      "value": "穴塞"
    },
    {
      "label": "仿殼",
      "value": "仿殼"
    },
    {
      "label": "仿殼1",
      "value": "仿殼1"
    },
    {
      "label": "仿殼2",
      "value": "仿殼2"
    },
    {
      "label": "仿殼組1",
      "value": "仿殼組1"
    },
    {
      "label": "仿殼組2",
      "value": "仿殼組2"
    },
    {
      "label": "全陞只做內孔及外徑不含頭型",
      "value": "全陞只做內孔及外徑不含頭型"
    },
    {
      "label": "全鎢鋼沖棒",
      "value": "全鎢鋼沖棒"
    },
    {
      "label": "字具1",
      "value": "字具1"
    },
    {
      "label": "字具2",
      "value": "字具2"
    },
    {
      "label": "字具3",
      "value": "字具3"
    },
    {
      "label": "字模",
      "value": "字模"
    },
    {
      "label": "字模1",
      "value": "字模1"
    },
    {
      "label": "字模2",
      "value": "字模2"
    },
    {
      "label": "有頭內六角螺絲",
      "value": "有頭內六角螺絲"
    },
    {
      "label": "有頭內六角螺絲(SCM435)",
      "value": "有頭內六角螺絲(SCM435)"
    },
    {
      "label": "有頭內六角螺絲(強化螺絲)",
      "value": "有頭內六角螺絲(強化螺絲)"
    },
    {
      "label": "夾子",
      "value": "夾子"
    },
    {
      "label": "夾子-碳化鎢",
      "value": "夾子-碳化鎢"
    },
    {
      "label": "夾子半成品",
      "value": "夾子半成品"
    },
    {
      "label": "攻牙",
      "value": "攻牙"
    },
    {
      "label": "束套",
      "value": "束套"
    },
    {
      "label": "束套(物件四)",
      "value": "束套(物件四)"
    },
    {
      "label": "束圈",
      "value": "束圈"
    },
    {
      "label": "沉頭螺絲",
      "value": "沉頭螺絲"
    },
    {
      "label": "沖棒",
      "value": "沖棒"
    },
    {
      "label": "沖棒固定塊",
      "value": "沖棒固定塊"
    },
    {
      "label": "沖棒鍍鈦(TiAlN)",
      "value": "沖棒鍍鈦(TiAlN)"
    },
    {
      "label": "沖棒鍍鈦(TiN)",
      "value": "沖棒鍍鈦(TiN)"
    },
    {
      "label": "沖管",
      "value": "沖管"
    },
    {
      "label": "沖模固定塊",
      "value": "沖模固定塊"
    },
    {
      "label": "其他",
      "value": "其他"
    },
    {
      "label": "其他-碳化鎢",
      "value": "其他-碳化鎢"
    },
    {
      "label": "其它",
      "value": "其它"
    },
    {
      "label": "固定塊",
      "value": "固定塊"
    },
    {
      "label": "底板+鏡座真空銲接",
      "value": "底板+鏡座真空銲接"
    },
    {
      "label": "底板+鏡座熱處理",
      "value": "底板+鏡座熱處理"
    },
    {
      "label": "底座",
      "value": "底座"
    },
    {
      "label": "底模",
      "value": "底模"
    },
    {
      "label": "底膜",
      "value": "底膜"
    },
    {
      "label": "杯蓋真空銲接",
      "value": "杯蓋真空銲接"
    },
    {
      "label": "油杯真空銲接",
      "value": "油杯真空銲接"
    },
    {
      "label": "油杯蓋真空銲接",
      "value": "油杯蓋真空銲接"
    },
    {
      "label": "治具",
      "value": "治具"
    },
    {
      "label": "治具(四片模)",
      "value": "治具(四片模)"
    },
    {
      "label": "治具(每次製作)",
      "value": "治具(每次製作)"
    },
    {
      "label": "治具-12片模",
      "value": "治具-12片模"
    },
    {
      "label": "治具-PIN",
      "value": "治具-PIN"
    },
    {
      "label": "治具-三片模",
      "value": "治具-三片模"
    },
    {
      "label": "治具-上模仁",
      "value": "治具-上模仁"
    },
    {
      "label": "治具-六片模",
      "value": "治具-六片模"
    },
    {
      "label": "治具-四片模",
      "value": "治具-四片模"
    },
    {
      "label": "治具-四片模仁",
      "value": "治具-四片模仁"
    },
    {
      "label": "治具1",
      "value": "治具1"
    },
    {
      "label": "治具1-四片模",
      "value": "治具1-四片模"
    },
    {
      "label": "治具2",
      "value": "治具2"
    },
    {
      "label": "治具2-四片模",
      "value": "治具2-四片模"
    },
    {
      "label": "治具用共用品號",
      "value": "治具用共用品號"
    },
    {
      "label": "物件1",
      "value": "物件1"
    },
    {
      "label": "物件2",
      "value": "物件2"
    },
    {
      "label": "物件3",
      "value": "物件3"
    },
    {
      "label": "物件一",
      "value": "物件一"
    },
    {
      "label": "物件一 (01-1108-04)",
      "value": "物件一 (01-1108-04)"
    },
    {
      "label": "物件一 (0350-2001-0011)",
      "value": "物件一 (0350-2001-0011)"
    },
    {
      "label": "物件一 (1050-01011C)",
      "value": "物件一 (1050-01011C)"
    },
    {
      "label": "物件一 (1050-01011D)",
      "value": "物件一 (1050-01011D)"
    },
    {
      "label": "物件一 (14025)",
      "value": "物件一 (14025)"
    },
    {
      "label": "物件一 (1A-370)",
      "value": "物件一 (1A-370)"
    },
    {
      "label": "物件一 (1B-223)",
      "value": "物件一 (1B-223)"
    },
    {
      "label": "物件一 (4-N10-MD6-400A)",
      "value": "物件一 (4-N10-MD6-400A)"
    },
    {
      "label": "物件一 (524F05524.62600)",
      "value": "物件一 (524F05524.62600)"
    },
    {
      "label": "物件一 (60-19-12)",
      "value": "物件一 (60-19-12)"
    },
    {
      "label": "物件一 (61555)",
      "value": "物件一 (61555)"
    },
    {
      "label": "物件一 (61580)",
      "value": "物件一 (61580)"
    },
    {
      "label": "物件一 (FU24-DC-001)",
      "value": "物件一 (FU24-DC-001)"
    },
    {
      "label": "物件一 (FU24-DC-008)",
      "value": "物件一 (FU24-DC-008)"
    },
    {
      "label": "物件一 (FU24-W6101911TDE2)",
      "value": "物件一 (FU24-W6101911TDE2)"
    },
    {
      "label": "物件一 (FU24-W6101911TDE3)",
      "value": "物件一 (FU24-W6101911TDE3)"
    },
    {
      "label": "物件一 (FU24-WR6510312TDE2)",
      "value": "物件一 (FU24-WR6510312TDE2)"
    },
    {
      "label": "物件一 (T0300438)",
      "value": "物件一 (T0300438)"
    },
    {
      "label": "物件一 (TOC154819)",
      "value": "物件一 (TOC154819)"
    },
    {
      "label": "物件一 (TiAlN)",
      "value": "物件一 (TiAlN)"
    },
    {
      "label": "物件一 (TiAlN) (60-43-45B)",
      "value": "物件一 (TiAlN) (60-43-45B)"
    },
    {
      "label": "物件一(11905)",
      "value": "物件一(11905)"
    },
    {
      "label": "物件一(11962)",
      "value": "物件一(11962)"
    },
    {
      "label": "物件一(12片模)",
      "value": "物件一(12片模)"
    },
    {
      "label": "物件一(下模仁)",
      "value": "物件一(下模仁)"
    },
    {
      "label": "物件一(上模仁)",
      "value": "物件一(上模仁)"
    },
    {
      "label": "物件一(六片模)",
      "value": "物件一(六片模)"
    },
    {
      "label": "物件一(六片模仁)",
      "value": "物件一(六片模仁)"
    },
    {
      "label": "物件一(套圈)",
      "value": "物件一(套圈)"
    },
    {
      "label": "物件一(嵌入件)",
      "value": "物件一(嵌入件)"
    },
    {
      "label": "物件一(殼)",
      "value": "物件一(殼)"
    },
    {
      "label": "物件一(殼)(TOC141606)",
      "value": "物件一(殼)(TOC141606)"
    },
    {
      "label": "物件一(模仁)",
      "value": "物件一(模仁)"
    },
    {
      "label": "物件一(模殼)",
      "value": "物件一(模殼)"
    },
    {
      "label": "物件一-下模仁",
      "value": "物件一-下模仁"
    },
    {
      "label": "物件一-上模仁",
      "value": "物件一-上模仁"
    },
    {
      "label": "物件一-六片模",
      "value": "物件一-六片模"
    },
    {
      "label": "物件一-模殼",
      "value": "物件一-模殼"
    },
    {
      "label": "物件一模組",
      "value": "物件一模組"
    },
    {
      "label": "物件七",
      "value": "物件七"
    },
    {
      "label": "物件七 (60-71-04)",
      "value": "物件七 (60-71-04)"
    },
    {
      "label": "物件九",
      "value": "物件九"
    },
    {
      "label": "物件九 (TiN)",
      "value": "物件九 (TiN)"
    },
    {
      "label": "物件九 (TiN) (60-67-26)",
      "value": "物件九 (TiN) (60-67-26)"
    },
    {
      "label": "物件二",
      "value": "物件二"
    },
    {
      "label": "物件二 (0350-2029-0012)",
      "value": "物件二 (0350-2029-0012)"
    },
    {
      "label": "物件二 (1049-01003)",
      "value": "物件二 (1049-01003)"
    },
    {
      "label": "物件二 (1050-01006)",
      "value": "物件二 (1050-01006)"
    },
    {
      "label": "物件二 (13478-04)",
      "value": "物件二 (13478-04)"
    },
    {
      "label": "物件二 (4-N10-MD6-400B)",
      "value": "物件二 (4-N10-MD6-400B)"
    },
    {
      "label": "物件二 (524F05524.38400)",
      "value": "物件二 (524F05524.38400)"
    },
    {
      "label": "物件二 (61556)",
      "value": "物件二 (61556)"
    },
    {
      "label": "物件二 (FU24-STNE71620TP1)",
      "value": "物件二 (FU24-STNE71620TP1)"
    },
    {
      "label": "物件二 (FU24-W6101911BDE2)",
      "value": "物件二 (FU24-W6101911BDE2)"
    },
    {
      "label": "物件二 (FU24-W6101911BDE3)",
      "value": "物件二 (FU24-W6101911BDE3)"
    },
    {
      "label": "物件二 (FU24-W6101911TDE1)",
      "value": "物件二 (FU24-W6101911TDE1)"
    },
    {
      "label": "物件二 (FU24-W6101911TDE2)",
      "value": "物件二 (FU24-W6101911TDE2)"
    },
    {
      "label": "物件二 (FU24-W6101911TDE3)",
      "value": "物件二 (FU24-W6101911TDE3)"
    },
    {
      "label": "物件二 (FU24-W6101911TDE4)",
      "value": "物件二 (FU24-W6101911TDE4)"
    },
    {
      "label": "物件二 (FU24-WR6510312BDE2)",
      "value": "物件二 (FU24-WR6510312BDE2)"
    },
    {
      "label": "物件二 (T03000439)",
      "value": "物件二 (T03000439)"
    },
    {
      "label": "物件二 (TOC154794)",
      "value": "物件二 (TOC154794)"
    },
    {
      "label": "物件二 (TiAlN)",
      "value": "物件二 (TiAlN)"
    },
    {
      "label": "物件二 (TiAlN) (60-43-38C)",
      "value": "物件二 (TiAlN) (60-43-38C)"
    },
    {
      "label": "物件二 (TiAlN) (60-43-48B)",
      "value": "物件二 (TiAlN) (60-43-48B)"
    },
    {
      "label": "物件二((下模仁)",
      "value": "物件二((下模仁)"
    },
    {
      "label": "物件二((上模仁)",
      "value": "物件二((上模仁)"
    },
    {
      "label": "物件二(11907)",
      "value": "物件二(11907)"
    },
    {
      "label": "物件二(三片)",
      "value": "物件二(三片)"
    },
    {
      "label": "物件二(下模仁)",
      "value": "物件二(下模仁)"
    },
    {
      "label": "物件二(下模仁)(TOC141626)",
      "value": "物件二(下模仁)(TOC141626)"
    },
    {
      "label": "物件二(上模仁)",
      "value": "物件二(上模仁)"
    },
    {
      "label": "物件二(上模仁)(TOC141626)",
      "value": "物件二(上模仁)(TOC141626)"
    },
    {
      "label": "物件二(六片模)",
      "value": "物件二(六片模)"
    },
    {
      "label": "物件二(四片模)",
      "value": "物件二(四片模)"
    },
    {
      "label": "物件二(套圈)",
      "value": "物件二(套圈)"
    },
    {
      "label": "物件二(套圈)(TOC141626)",
      "value": "物件二(套圈)(TOC141626)"
    },
    {
      "label": "物件二(模仁)",
      "value": "物件二(模仁)"
    },
    {
      "label": "物件二-下模仁",
      "value": "物件二-下模仁"
    },
    {
      "label": "物件二-上模仁",
      "value": "物件二-上模仁"
    },
    {
      "label": "物件二-四片模仁",
      "value": "物件二-四片模仁"
    },
    {
      "label": "物件二-套圈",
      "value": "物件二-套圈"
    },
    {
      "label": "物件二-電極",
      "value": "物件二-電極"
    },
    {
      "label": "物件二-模仁組",
      "value": "物件二-模仁組"
    },
    {
      "label": "物件八",
      "value": "物件八"
    },
    {
      "label": "物件八 (60-68-06)",
      "value": "物件八 (60-68-06)"
    },
    {
      "label": "物件三",
      "value": "物件三"
    },
    {
      "label": "物件三 (0350-2028-0043)",
      "value": "物件三 (0350-2028-0043)"
    },
    {
      "label": "物件三 (1050-01007)",
      "value": "物件三 (1050-01007)"
    },
    {
      "label": "物件三 (4-N5-MD6-412)",
      "value": "物件三 (4-N5-MD6-412)"
    },
    {
      "label": "物件三 (524F05524.62500)",
      "value": "物件三 (524F05524.62500)"
    },
    {
      "label": "物件三 (60-47-13)",
      "value": "物件三 (60-47-13)"
    },
    {
      "label": "物件三 (FU24-LN-001)",
      "value": "物件三 (FU24-LN-001)"
    },
    {
      "label": "物件三 (FU24-W6101911BDE1)",
      "value": "物件三 (FU24-W6101911BDE1)"
    },
    {
      "label": "物件三 (FU24-W6101911BDE2)",
      "value": "物件三 (FU24-W6101911BDE2)"
    },
    {
      "label": "物件三 (FU24-W6101911BDE3)",
      "value": "物件三 (FU24-W6101911BDE3)"
    },
    {
      "label": "物件三 (FU24-W6101911BDE4)",
      "value": "物件三 (FU24-W6101911BDE4)"
    },
    {
      "label": "物件三 (FU24-WR6510312TP2)",
      "value": "物件三 (FU24-WR6510312TP2)"
    },
    {
      "label": "物件三 (T03000443)",
      "value": "物件三 (T03000443)"
    },
    {
      "label": "物件三 (TOC141452)",
      "value": "物件三 (TOC141452)"
    },
    {
      "label": "物件三 (TiAlN)",
      "value": "物件三 (TiAlN)"
    },
    {
      "label": "物件三 (TiAlN) (60-43-39C)",
      "value": "物件三 (TiAlN) (60-43-39C)"
    },
    {
      "label": "物件三(11906)",
      "value": "物件三(11906)"
    },
    {
      "label": "物件三(B)",
      "value": "物件三(B)"
    },
    {
      "label": "物件三(八片模)",
      "value": "物件三(八片模)"
    },
    {
      "label": "物件三(下模仁)",
      "value": "物件三(下模仁)"
    },
    {
      "label": "物件三(中模仁)",
      "value": "物件三(中模仁)"
    },
    {
      "label": "物件三(套圈)",
      "value": "物件三(套圈)"
    },
    {
      "label": "物件三(模殼)",
      "value": "物件三(模殼)"
    },
    {
      "label": "物件三(銷)",
      "value": "物件三(銷)"
    },
    {
      "label": "物件三-1",
      "value": "物件三-1"
    },
    {
      "label": "物件三-1(PIN)",
      "value": "物件三-1(PIN)"
    },
    {
      "label": "物件三-2",
      "value": "物件三-2"
    },
    {
      "label": "物件三-3",
      "value": "物件三-3"
    },
    {
      "label": "物件三-4",
      "value": "物件三-4"
    },
    {
      "label": "物件三-下模仁",
      "value": "物件三-下模仁"
    },
    {
      "label": "物件三-中模仁",
      "value": "物件三-中模仁"
    },
    {
      "label": "物件三-電極",
      "value": "物件三-電極"
    },
    {
      "label": "物件三-墊片",
      "value": "物件三-墊片"
    },
    {
      "label": "物件五",
      "value": "物件五"
    },
    {
      "label": "物件五 (60-17-68)",
      "value": "物件五 (60-17-68)"
    },
    {
      "label": "物件五 (C-6257-4007-1)",
      "value": "物件五 (C-6257-4007-1)"
    },
    {
      "label": "物件五 (FU24-LN-001)",
      "value": "物件五 (FU24-LN-001)"
    },
    {
      "label": "物件五 (FU24-STNE71620BDE2)",
      "value": "物件五 (FU24-STNE71620BDE2)"
    },
    {
      "label": "物件五 (T03023051)",
      "value": "物件五 (T03023051)"
    },
    {
      "label": "物件五 (TiAlN)",
      "value": "物件五 (TiAlN)"
    },
    {
      "label": "物件五 (TiAlN) (60-43-49B)",
      "value": "物件五 (TiAlN) (60-43-49B)"
    },
    {
      "label": "物件五(公牙)",
      "value": "物件五(公牙)"
    },
    {
      "label": "物件五(公牙) (TOC141608)",
      "value": "物件五(公牙) (TOC141608)"
    },
    {
      "label": "物件五-後沖棒",
      "value": "物件五-後沖棒"
    },
    {
      "label": "物件六",
      "value": "物件六"
    },
    {
      "label": "物件六 (60-11-05)",
      "value": "物件六 (60-11-05)"
    },
    {
      "label": "物件六 (60-68-08)",
      "value": "物件六 (60-68-08)"
    },
    {
      "label": "物件六 (T03023051)",
      "value": "物件六 (T03023051)"
    },
    {
      "label": "物件六-PIN",
      "value": "物件六-PIN"
    },
    {
      "label": "物件四",
      "value": "物件四"
    },
    {
      "label": "物件四 (0350-2028-0044)",
      "value": "物件四 (0350-2028-0044)"
    },
    {
      "label": "物件四 (1047-01005A)",
      "value": "物件四 (1047-01005A)"
    },
    {
      "label": "物件四 (FU24-DC-002)",
      "value": "物件四 (FU24-DC-002)"
    },
    {
      "label": "物件四 (FU24-STNE71620TDE2)",
      "value": "物件四 (FU24-STNE71620TDE2)"
    },
    {
      "label": "物件四 (FU24-W6101911TP1)",
      "value": "物件四 (FU24-W6101911TP1)"
    },
    {
      "label": "物件四 (FU24-W6101911TP2)",
      "value": "物件四 (FU24-W6101911TP2)"
    },
    {
      "label": "物件四 (FU24-W6101911TP3)",
      "value": "物件四 (FU24-W6101911TP3)"
    },
    {
      "label": "物件四 (FU24-W6101911TP4)",
      "value": "物件四 (FU24-W6101911TP4)"
    },
    {
      "label": "物件四 (T03011527)",
      "value": "物件四 (T03011527)"
    },
    {
      "label": "物件四 (TOC1414638)",
      "value": "物件四 (TOC1414638)"
    },
    {
      "label": "物件四 (TiN)",
      "value": "物件四 (TiN)"
    },
    {
      "label": "物件四 (TiN) (60-67-24)",
      "value": "物件四 (TiN) (60-67-24)"
    },
    {
      "label": "物件四 (TiN) (60-67-25)",
      "value": "物件四 (TiN) (60-67-25)"
    },
    {
      "label": "物件四(下模仁)",
      "value": "物件四(下模仁)"
    },
    {
      "label": "物件四(套圈+六片模)",
      "value": "物件四(套圈+六片模)"
    },
    {
      "label": "物件四(模殼)",
      "value": "物件四(模殼)"
    },
    {
      "label": "物件四-下模仁",
      "value": "物件四-下模仁"
    },
    {
      "label": "物件四-六片模",
      "value": "物件四-六片模"
    },
    {
      "label": "物件四-後沖棒",
      "value": "物件四-後沖棒"
    },
    {
      "label": "物件四-套圈",
      "value": "物件四-套圈"
    },
    {
      "label": "長圓棒 CD636",
      "value": "長圓棒 CD636"
    },
    {
      "label": "長圓棒 G20",
      "value": "長圓棒 G20"
    },
    {
      "label": "長圓棒 G50",
      "value": "長圓棒 G50"
    },
    {
      "label": "長圓棒 H10F",
      "value": "長圓棒 H10F"
    },
    {
      "label": "長圓棒 H11C",
      "value": "長圓棒 H11C"
    },
    {
      "label": "長圓棒 KG7",
      "value": "長圓棒 KG7"
    },
    {
      "label": "長圓棒 RX10",
      "value": "長圓棒 RX10"
    },
    {
      "label": "長圓棒 RX15",
      "value": "長圓棒 RX15"
    },
    {
      "label": "長圓棒 ST6",
      "value": "長圓棒 ST6"
    },
    {
      "label": "長圓棒 TR09C",
      "value": "長圓棒 TR09C"
    },
    {
      "label": "長圓棒 VA80",
      "value": "長圓棒 VA80"
    },
    {
      "label": "長圓棒 WF25",
      "value": "長圓棒 WF25"
    },
    {
      "label": "長圓棒 WF25(白皮)",
      "value": "長圓棒 WF25(白皮)"
    },
    {
      "label": "長圓棒 WF25(黑皮)",
      "value": "長圓棒 WF25(黑皮)"
    },
    {
      "label": "長圓棒 WF30",
      "value": "長圓棒 WF30"
    },
    {
      "label": "前沖棒",
      "value": "前沖棒"
    },
    {
      "label": "前沖棒 (不可發外注)",
      "value": "前沖棒 (不可發外注)"
    },
    {
      "label": "前沖棒-碳化鎢",
      "value": "前沖棒-碳化鎢"
    },
    {
      "label": "前治具(套圈)",
      "value": "前治具(套圈)"
    },
    {
      "label": "前模仁",
      "value": "前模仁"
    },
    {
      "label": "前擋塊",
      "value": "前擋塊"
    },
    {
      "label": "客戶提供(O-Ring)",
      "value": "客戶提供(O-Ring)"
    },
    {
      "label": "客戶提供品",
      "value": "客戶提供品"
    },
    {
      "label": "後沖棒",
      "value": "後沖棒"
    },
    {
      "label": "後沖棒(半成品)",
      "value": "後沖棒(半成品)"
    },
    {
      "label": "後沖棒-1",
      "value": "後沖棒-1"
    },
    {
      "label": "後沖棒-2",
      "value": "後沖棒-2"
    },
    {
      "label": "後沖棒-單件",
      "value": "後沖棒-單件"
    },
    {
      "label": "後沖棒-碳化鎢",
      "value": "後沖棒-碳化鎢"
    },
    {
      "label": "後治具(套圈)",
      "value": "後治具(套圈)"
    },
    {
      "label": "後模仁",
      "value": "後模仁"
    },
    {
      "label": "後擋塊",
      "value": "後擋塊"
    },
    {
      "label": "柑鍋座真空銲接",
      "value": "柑鍋座真空銲接"
    },
    {
      "label": "美規鉛青銅 C93200",
      "value": "美規鉛青銅 C93200"
    },
    {
      "label": "美規鉛青銅 C93210",
      "value": "美規鉛青銅 C93210"
    },
    {
      "label": "美規鉛青銅(C93210)",
      "value": "美規鉛青銅(C93210)"
    },
    {
      "label": "美規鉛青銅C93210",
      "value": "美規鉛青銅C93210"
    },
    {
      "label": "修改用套管",
      "value": "修改用套管"
    },
    {
      "label": "套圈",
      "value": "套圈"
    },
    {
      "label": "套圈#304",
      "value": "套圈#304"
    },
    {
      "label": "套圈&外徑治具-(四片模用)",
      "value": "套圈&外徑治具-(四片模用)"
    },
    {
      "label": "套圈(每次製作)",
      "value": "套圈(每次製作)"
    },
    {
      "label": "套圈(物件三)",
      "value": "套圈(物件三)"
    },
    {
      "label": "套圈(物件五)",
      "value": "套圈(物件五)"
    },
    {
      "label": "套圈+模仁",
      "value": "套圈+模仁"
    },
    {
      "label": "套圈-(四片模用)",
      "value": "套圈-(四片模用)"
    },
    {
      "label": "套圈6",
      "value": "套圈6"
    },
    {
      "label": "套筒",
      "value": "套筒"
    },
    {
      "label": "套筒(內筒)",
      "value": "套筒(內筒)"
    },
    {
      "label": "套筒(外筒)",
      "value": "套筒(外筒)"
    },
    {
      "label": "套筒(單筒)",
      "value": "套筒(單筒)"
    },
    {
      "label": "套管",
      "value": "套管"
    },
    {
      "label": "套管 半成品 R3",
      "value": "套管 半成品 R3"
    },
    {
      "label": "套管(一)",
      "value": "套管(一)"
    },
    {
      "label": "套管(二)",
      "value": "套管(二)"
    },
    {
      "label": "套管(內筒)",
      "value": "套管(內筒)"
    },
    {
      "label": "套管(半成品)",
      "value": "套管(半成品)"
    },
    {
      "label": "套管(外筒)",
      "value": "套管(外筒)"
    },
    {
      "label": "套管(單筒)",
      "value": "套管(單筒)"
    },
    {
      "label": "套管-仿殼",
      "value": "套管-仿殼"
    },
    {
      "label": "套管-治具",
      "value": "套管-治具"
    },
    {
      "label": "套管-單件",
      "value": "套管-單件"
    },
    {
      "label": "套管-碳化鎢",
      "value": "套管-碳化鎢"
    },
    {
      "label": "崁入件",
      "value": "崁入件"
    },
    {
      "label": "真空時效硬化熱處理",
      "value": "真空時效硬化熱處理"
    },
    {
      "label": "真空熱處理",
      "value": "真空熱處理"
    },
    {
      "label": "退火熱處理",
      "value": "退火熱處理"
    },
    {
      "label": "退鍍加工費",
      "value": "退鍍加工費"
    },
    {
      "label": "強力磁鐵",
      "value": "強力磁鐵"
    },
    {
      "label": "梅花沖棒",
      "value": "梅花沖棒"
    },
    {
      "label": "組合模",
      "value": "組合模"
    },
    {
      "label": "組前電極",
      "value": "組前電極"
    },
    {
      "label": "組後電極",
      "value": "組後電極"
    },
    {
      "label": "組後電極-六片模",
      "value": "組後電極-六片模"
    },
    {
      "label": "組後電極-墊塊",
      "value": "組後電極-墊塊"
    },
    {
      "label": "組後電極2-墊塊",
      "value": "組後電極2-墊塊"
    },
    {
      "label": "組後電極3",
      "value": "組後電極3"
    },
    {
      "label": "通孔沖-碳化鎢",
      "value": "通孔沖-碳化鎢"
    },
    {
      "label": "通孔沖棒",
      "value": "通孔沖棒"
    },
    {
      "label": "通孔沖棒 不複使用",
      "value": "通孔沖棒 不複使用"
    },
    {
      "label": "通孔沖棒 半成品",
      "value": "通孔沖棒 半成品"
    },
    {
      "label": "通孔沖棒 雙層",
      "value": "通孔沖棒 雙層"
    },
    {
      "label": "通孔沖棒(雙層)",
      "value": "通孔沖棒(雙層)"
    },
    {
      "label": "通孔沖棒-半成品",
      "value": "通孔沖棒-半成品"
    },
    {
      "label": "通孔沖棒-碳化鎢",
      "value": "通孔沖棒-碳化鎢"
    },
    {
      "label": "通孔管",
      "value": "通孔管"
    },
    {
      "label": "通孔管 不複使用",
      "value": "通孔管 不複使用"
    },
    {
      "label": "嵌入件",
      "value": "嵌入件"
    },
    {
      "label": "嵌入件 (四片模)",
      "value": "嵌入件 (四片模)"
    },
    {
      "label": "嵌入件(物件一)",
      "value": "嵌入件(物件一)"
    },
    {
      "label": "替代料件",
      "value": "替代料件"
    },
    {
      "label": "減壓閥真空銲接",
      "value": "減壓閥真空銲接"
    },
    {
      "label": "無氧銅真空銲接",
      "value": "無氧銅真空銲接"
    },
    {
      "label": "無氧銅銲接",
      "value": "無氧銅銲接"
    },
    {
      "label": "無頭內六角螺絲",
      "value": "無頭內六角螺絲"
    },
    {
      "label": "無頭內六角螺絲 (全牙 固定)",
      "value": "無頭內六角螺絲 (全牙 固定)"
    },
    {
      "label": "無縫鋼管",
      "value": "無縫鋼管"
    },
    {
      "label": "筒夾",
      "value": "筒夾"
    },
    {
      "label": "華司頭",
      "value": "華司頭"
    },
    {
      "label": "圓形切刀片真空銲接",
      "value": "圓形切刀片真空銲接"
    },
    {
      "label": "圓形切刀真空銲接",
      "value": "圓形切刀真空銲接"
    },
    {
      "label": "圓棒",
      "value": "圓棒"
    },
    {
      "label": "圓棒  SKH9",
      "value": "圓棒  SKH9"
    },
    {
      "label": "圓棒 #303不銹鋼(韓國)",
      "value": "圓棒 #303不銹鋼(韓國)"
    },
    {
      "label": "圓棒 #304不銹鋼",
      "value": "圓棒 #304不銹鋼"
    },
    {
      "label": "圓棒 1.2365(道)",
      "value": "圓棒 1.2365(道)"
    },
    {
      "label": "圓棒 1.2367(道)",
      "value": "圓棒 1.2367(道)"
    },
    {
      "label": "圓棒 1.2379道(SKD11)",
      "value": "圓棒 1.2379道(SKD11)"
    },
    {
      "label": "圓棒 1.2767道",
      "value": "圓棒 1.2767道"
    },
    {
      "label": "圓棒 1.3243道(SKH55)",
      "value": "圓棒 1.3243道(SKH55)"
    },
    {
      "label": "圓棒 1.3247道(M42)",
      "value": "圓棒 1.3247道(M42)"
    },
    {
      "label": "圓棒 1.3292 PMD60(道)(ASP60)",
      "value": "圓棒 1.3292 PMD60(道)(ASP60)"
    },
    {
      "label": "圓棒 1.3343(道)SKH9",
      "value": "圓棒 1.3343(道)SKH9"
    },
    {
      "label": "圓棒 1.3343道(SKH9)",
      "value": "圓棒 1.3343道(SKH9)"
    },
    {
      "label": "圓棒 1.3395道(ASP23)",
      "value": "圓棒 1.3395道(ASP23)"
    },
    {
      "label": "圓棒 4140",
      "value": "圓棒 4140"
    },
    {
      "label": "圓棒 4140(SCM440)",
      "value": "圓棒 4140(SCM440)"
    },
    {
      "label": "圓棒 420J2",
      "value": "圓棒 420J2"
    },
    {
      "label": "圓棒 AISI 8620(SncM220)",
      "value": "圓棒 AISI 8620(SncM220)"
    },
    {
      "label": "圓棒 ALBC3C/鋁青銅圓條(連鑄) C958/C955",
      "value": "圓棒 ALBC3C/鋁青銅圓條(連鑄) C958/C955"
    },
    {
      "label": "圓棒 ASP2060(ASP60)",
      "value": "圓棒 ASP2060(ASP60)"
    },
    {
      "label": "圓棒 ASP23",
      "value": "圓棒 ASP23"
    },
    {
      "label": "圓棒 ASP30(道)",
      "value": "圓棒 ASP30(道)"
    },
    {
      "label": "圓棒 ASP60",
      "value": "圓棒 ASP60"
    },
    {
      "label": "圓棒 CPM 10V",
      "value": "圓棒 CPM 10V"
    },
    {
      "label": "圓棒 CPM-3V",
      "value": "圓棒 CPM-3V"
    },
    {
      "label": "圓棒 CPM-M4",
      "value": "圓棒 CPM-M4"
    },
    {
      "label": "圓棒 CPOH道(DC53)",
      "value": "圓棒 CPOH道(DC53)"
    },
    {
      "label": "圓棒 DC53",
      "value": "圓棒 DC53"
    },
    {
      "label": "圓棒 DC53 庫4",
      "value": "圓棒 DC53 庫4"
    },
    {
      "label": "圓棒 DC53 庫7",
      "value": "圓棒 DC53 庫7"
    },
    {
      "label": "圓棒 DC53(道)",
      "value": "圓棒 DC53(道)"
    },
    {
      "label": "圓棒 DC53道",
      "value": "圓棒 DC53道"
    },
    {
      "label": "圓棒 DR11C",
      "value": "圓棒 DR11C"
    },
    {
      "label": "圓棒 DR14C",
      "value": "圓棒 DR14C"
    },
    {
      "label": "圓棒 DR17C",
      "value": "圓棒 DR17C"
    },
    {
      "label": "圓棒 G20",
      "value": "圓棒 G20"
    },
    {
      "label": "圓棒 HAP10",
      "value": "圓棒 HAP10"
    },
    {
      "label": "圓棒 K340",
      "value": "圓棒 K340"
    },
    {
      "label": "圓棒 K890",
      "value": "圓棒 K890"
    },
    {
      "label": "圓棒 KG5",
      "value": "圓棒 KG5"
    },
    {
      "label": "圓棒 M42",
      "value": "圓棒 M42"
    },
    {
      "label": "圓棒 M42 庫3",
      "value": "圓棒 M42 庫3"
    },
    {
      "label": "圓棒 R3",
      "value": "圓棒 R3"
    },
    {
      "label": "圓棒 R4",
      "value": "圓棒 R4"
    },
    {
      "label": "圓棒 RX15",
      "value": "圓棒 RX15"
    },
    {
      "label": "圓棒 S25C",
      "value": "圓棒 S25C"
    },
    {
      "label": "圓棒 S390",
      "value": "圓棒 S390"
    },
    {
      "label": "圓棒 S45C",
      "value": "圓棒 S45C"
    },
    {
      "label": "圓棒 S45C (光面)",
      "value": "圓棒 S45C (光面)"
    },
    {
      "label": "圓棒 S45C(光面)",
      "value": "圓棒 S45C(光面)"
    },
    {
      "label": "圓棒 S590",
      "value": "圓棒 S590"
    },
    {
      "label": "圓棒 S600百樂(SKH9)",
      "value": "圓棒 S600百樂(SKH9)"
    },
    {
      "label": "圓棒 SAE841油銅",
      "value": "圓棒 SAE841油銅"
    },
    {
      "label": "圓棒 SCM415",
      "value": "圓棒 SCM415"
    },
    {
      "label": "圓棒 SKD11",
      "value": "圓棒 SKD11"
    },
    {
      "label": "圓棒 SKD11 (日立)",
      "value": "圓棒 SKD11 (日立)"
    },
    {
      "label": "圓棒 SKD11 (榮剛)",
      "value": "圓棒 SKD11 (榮剛)"
    },
    {
      "label": "圓棒 SKD11 (榮鋼)",
      "value": "圓棒 SKD11 (榮鋼)"
    },
    {
      "label": "圓棒 SKD11(榮剛)",
      "value": "圓棒 SKD11(榮剛)"
    },
    {
      "label": "圓棒 SKD11(榮鋼)",
      "value": "圓棒 SKD11(榮鋼)"
    },
    {
      "label": "圓棒 SKD61",
      "value": "圓棒 SKD61"
    },
    {
      "label": "圓棒 SKD61 (DAC)",
      "value": "圓棒 SKD61 (DAC)"
    },
    {
      "label": "圓棒 SKD61 (榮剛)",
      "value": "圓棒 SKD61 (榮剛)"
    },
    {
      "label": "圓棒 SKD61 (榮鋼)",
      "value": "圓棒 SKD61 (榮鋼)"
    },
    {
      "label": "圓棒 SKD61 榮剛",
      "value": "圓棒 SKD61 榮剛"
    },
    {
      "label": "圓棒 SKD61 榮鋼",
      "value": "圓棒 SKD61 榮鋼"
    },
    {
      "label": "圓棒 SKD61(DAC)",
      "value": "圓棒 SKD61(DAC)"
    },
    {
      "label": "圓棒 SKD61(DAC) 榮剛",
      "value": "圓棒 SKD61(DAC) 榮剛"
    },
    {
      "label": "圓棒 SKD61(榮剛)",
      "value": "圓棒 SKD61(榮剛)"
    },
    {
      "label": "圓棒 SKD61(榮鋼)",
      "value": "圓棒 SKD61(榮鋼)"
    },
    {
      "label": "圓棒 SKD61-榮剛",
      "value": "圓棒 SKD61-榮剛"
    },
    {
      "label": "圓棒 SKH55",
      "value": "圓棒 SKH55"
    },
    {
      "label": "圓棒 SKH55 (日立)",
      "value": "圓棒 SKH55 (日立)"
    },
    {
      "label": "圓棒 SKH55 (有鑽孔32D)",
      "value": "圓棒 SKH55 (有鑽孔32D)"
    },
    {
      "label": "圓棒 SKH55(日立)",
      "value": "圓棒 SKH55(日立)"
    },
    {
      "label": "圓棒 SKH9",
      "value": "圓棒 SKH9"
    },
    {
      "label": "圓棒 SKH9  庫9",
      "value": "圓棒 SKH9  庫9"
    },
    {
      "label": "圓棒 SKH9 (日立)",
      "value": "圓棒 SKH9 (日立)"
    },
    {
      "label": "圓棒 SKH9 庫1",
      "value": "圓棒 SKH9 庫1"
    },
    {
      "label": "圓棒 SKH9 庫10",
      "value": "圓棒 SKH9 庫10"
    },
    {
      "label": "圓棒 SKH9 庫11",
      "value": "圓棒 SKH9 庫11"
    },
    {
      "label": "圓棒 SKH9 庫16",
      "value": "圓棒 SKH9 庫16"
    },
    {
      "label": "圓棒 SKH9 庫20",
      "value": "圓棒 SKH9 庫20"
    },
    {
      "label": "圓棒 SKH9 庫22",
      "value": "圓棒 SKH9 庫22"
    },
    {
      "label": "圓棒 SKH9 庫28",
      "value": "圓棒 SKH9 庫28"
    },
    {
      "label": "圓棒 SKH9 庫28支",
      "value": "圓棒 SKH9 庫28支"
    },
    {
      "label": "圓棒 SKH9 庫35",
      "value": "圓棒 SKH9 庫35"
    },
    {
      "label": "圓棒 SKH9 庫36",
      "value": "圓棒 SKH9 庫36"
    },
    {
      "label": "圓棒 SKH9 庫36支",
      "value": "圓棒 SKH9 庫36支"
    },
    {
      "label": "圓棒 SKH9 庫4",
      "value": "圓棒 SKH9 庫4"
    },
    {
      "label": "圓棒 SKH9 庫40",
      "value": "圓棒 SKH9 庫40"
    },
    {
      "label": "圓棒 SKH9 庫42支",
      "value": "圓棒 SKH9 庫42支"
    },
    {
      "label": "圓棒 SKH9 庫43",
      "value": "圓棒 SKH9 庫43"
    },
    {
      "label": "圓棒 SKH9 庫48",
      "value": "圓棒 SKH9 庫48"
    },
    {
      "label": "圓棒 SKH9(日立)",
      "value": "圓棒 SKH9(日立)"
    },
    {
      "label": "圓棒 SLD",
      "value": "圓棒 SLD"
    },
    {
      "label": "圓棒 SLD (榮剛)",
      "value": "圓棒 SLD (榮剛)"
    },
    {
      "label": "圓棒 SLD(SKD11)",
      "value": "圓棒 SLD(SKD11)"
    },
    {
      "label": "圓棒 SLD(榮鋼)",
      "value": "圓棒 SLD(榮鋼)"
    },
    {
      "label": "圓棒 SNCM220",
      "value": "圓棒 SNCM220"
    },
    {
      "label": "圓棒 SNCM8 (4340)",
      "value": "圓棒 SNCM8 (4340)"
    },
    {
      "label": "圓棒 SR10C",
      "value": "圓棒 SR10C"
    },
    {
      "label": "圓棒 SUJ2(軸承鋼)",
      "value": "圓棒 SUJ2(軸承鋼)"
    },
    {
      "label": "圓棒 SUS304",
      "value": "圓棒 SUS304"
    },
    {
      "label": "圓棒 SUS440C",
      "value": "圓棒 SUS440C"
    },
    {
      "label": "圓棒 SUS440C(日製)",
      "value": "圓棒 SUS440C(日製)"
    },
    {
      "label": "圓棒 SUS440C(韓國)",
      "value": "圓棒 SUS440C(韓國)"
    },
    {
      "label": "圓棒 SUS440c",
      "value": "圓棒 SUS440c"
    },
    {
      "label": "圓棒 SUS440c(日本)",
      "value": "圓棒 SUS440c(日本)"
    },
    {
      "label": "圓棒 SUS440c(日製)",
      "value": "圓棒 SUS440c(日製)"
    },
    {
      "label": "圓棒 SUS440c(韓製)",
      "value": "圓棒 SUS440c(韓製)"
    },
    {
      "label": "圓棒 TR15C",
      "value": "圓棒 TR15C"
    },
    {
      "label": "圓棒 TR20C",
      "value": "圓棒 TR20C"
    },
    {
      "label": "圓棒 TR25C",
      "value": "圓棒 TR25C"
    },
    {
      "label": "圓棒 UR10C",
      "value": "圓棒 UR10C"
    },
    {
      "label": "圓棒 UR13C",
      "value": "圓棒 UR13C"
    },
    {
      "label": "圓棒 V4 (Vanadis V4E)",
      "value": "圓棒 V4 (Vanadis V4E)"
    },
    {
      "label": "圓棒 W360",
      "value": "圓棒 W360"
    },
    {
      "label": "圓棒 YR10C",
      "value": "圓棒 YR10C"
    },
    {
      "label": "圓棒 YR20C",
      "value": "圓棒 YR20C"
    },
    {
      "label": "圓棒 YXR33",
      "value": "圓棒 YXR33"
    },
    {
      "label": "圓棒 ZR10C",
      "value": "圓棒 ZR10C"
    },
    {
      "label": "圓棒 不鏽鋼316L(美規)",
      "value": "圓棒 不鏽鋼316L(美規)"
    },
    {
      "label": "圓棒 青銅 (JIS CAC406C)",
      "value": "圓棒 青銅 (JIS CAC406C)"
    },
    {
      "label": "圓棒 青銅 (JIS-CAC406C)",
      "value": "圓棒 青銅 (JIS-CAC406C)"
    },
    {
      "label": "圓棒 青銅 (SAE660)",
      "value": "圓棒 青銅 (SAE660)"
    },
    {
      "label": "圓棒 青銅(BC6C)",
      "value": "圓棒 青銅(BC6C)"
    },
    {
      "label": "圓棒 青銅JIS CAC406C",
      "value": "圓棒 青銅JIS CAC406C"
    },
    {
      "label": "圓棒 套金銅BC6",
      "value": "圓棒 套金銅BC6"
    },
    {
      "label": "圓棒 高拉力鈹銅 (C17200)",
      "value": "圓棒 高拉力鈹銅 (C17200)"
    },
    {
      "label": "圓棒 無氧銅",
      "value": "圓棒 無氧銅"
    },
    {
      "label": "圓棒 紫銅 (C1100)",
      "value": "圓棒 紫銅 (C1100)"
    },
    {
      "label": "圓棒 紫銅圓條(C1100)",
      "value": "圓棒 紫銅圓條(C1100)"
    },
    {
      "label": "圓棒 黃銅",
      "value": "圓棒 黃銅"
    },
    {
      "label": "圓棒 黃銅C3604",
      "value": "圓棒 黃銅C3604"
    },
    {
      "label": "圓棒 銅鎢",
      "value": "圓棒 銅鎢"
    },
    {
      "label": "圓棒 鉻鋯銅",
      "value": "圓棒 鉻鋯銅"
    },
    {
      "label": "圓棒 鉻鋯鋼",
      "value": "圓棒 鉻鋯鋼"
    },
    {
      "label": "圓棒 鋁青銅 (C95800)",
      "value": "圓棒 鋁青銅 (C95800)"
    },
    {
      "label": "圓棒 鋁青銅 (C95810)",
      "value": "圓棒 鋁青銅 (C95810)"
    },
    {
      "label": "圓棒 鋁青銅C95400",
      "value": "圓棒 鋁青銅C95400"
    },
    {
      "label": "圓棒 磷青銅 (CAC502C)",
      "value": "圓棒 磷青銅 (CAC502C)"
    },
    {
      "label": "圓棒 磷青銅 (CuSn10)",
      "value": "圓棒 磷青銅 (CuSn10)"
    },
    {
      "label": "圓棒 磷青銅 (GC-CUSN10)",
      "value": "圓棒 磷青銅 (GC-CUSN10)"
    },
    {
      "label": "圓棒 磷青銅 (JIS-C-5191)",
      "value": "圓棒 磷青銅 (JIS-C-5191)"
    },
    {
      "label": "圓棒 磷青銅 GC-CUSN10",
      "value": "圓棒 磷青銅 GC-CUSN10"
    },
    {
      "label": "圓棒SLD",
      "value": "圓棒SLD"
    },
    {
      "label": "圓管 SUJ2(軸承鋼管)",
      "value": "圓管 SUJ2(軸承鋼管)"
    },
    {
      "label": "圓管 青銅 C90700",
      "value": "圓管 青銅 C90700"
    },
    {
      "label": "圓管 青銅 C90700 日本製",
      "value": "圓管 青銅 C90700 日本製"
    },
    {
      "label": "圓管 青銅 C90700/PBC2C磷青銅",
      "value": "圓管 青銅 C90700/PBC2C磷青銅"
    },
    {
      "label": "圓管 青銅C90700",
      "value": "圓管 青銅C90700"
    },
    {
      "label": "圓管 鋁青銅 C95500",
      "value": "圓管 鋁青銅 C95500"
    },
    {
      "label": "圓管 磷青銅 GCC-usn10",
      "value": "圓管 磷青銅 GCC-usn10"
    },
    {
      "label": "塑膠噴嘴真空銲接",
      "value": "塑膠噴嘴真空銲接"
    },
    {
      "label": "鉛青銅管(C93200)",
      "value": "鉛青銅管(C93200)"
    },
    {
      "label": "電刷片熱處理",
      "value": "電刷片熱處理"
    },
    {
      "label": "電刷片熱處理加工費",
      "value": "電刷片熱處理加工費"
    },
    {
      "label": "電極",
      "value": "電極"
    },
    {
      "label": "電極 (物件三+四)",
      "value": "電極 (物件三+四)"
    },
    {
      "label": "電極(下模仁)",
      "value": "電極(下模仁)"
    },
    {
      "label": "電極(上.下模仁)",
      "value": "電極(上.下模仁)"
    },
    {
      "label": "電極(上模仁)",
      "value": "電極(上模仁)"
    },
    {
      "label": "電極(物件一)",
      "value": "電極(物件一)"
    },
    {
      "label": "電極(物件二)",
      "value": "電極(物件二)"
    },
    {
      "label": "電極(組前)",
      "value": "電極(組前)"
    },
    {
      "label": "電極(組後放電)(上下模仁)",
      "value": "電極(組後放電)(上下模仁)"
    },
    {
      "label": "電極(鼎勝後放電加工)",
      "value": "電極(鼎勝後放電加工)"
    },
    {
      "label": "電極-(物件三+物件四)",
      "value": "電極-(物件三+物件四)"
    },
    {
      "label": "電極-60-43-39C",
      "value": "電極-60-43-39C"
    },
    {
      "label": "電極-60-43-39C+60-43-38C",
      "value": "電極-60-43-39C+60-43-38C"
    },
    {
      "label": "電極-60-43-49B+60-43-38C",
      "value": "電極-60-43-49B+60-43-38C"
    },
    {
      "label": "電極-C銑",
      "value": "電極-C銑"
    },
    {
      "label": "電極-下仁",
      "value": "電極-下仁"
    },
    {
      "label": "電極-下模仁",
      "value": "電極-下模仁"
    },
    {
      "label": "電極-下模仁 (FU24-W6101911BDE1)",
      "value": "電極-下模仁 (FU24-W6101911BDE1)"
    },
    {
      "label": "電極-上仁",
      "value": "電極-上仁"
    },
    {
      "label": "電極-上仁+下仁",
      "value": "電極-上仁+下仁"
    },
    {
      "label": "電極-上模仁",
      "value": "電極-上模仁"
    },
    {
      "label": "電極-上模仁 (FU24-W6101911TDE1)",
      "value": "電極-上模仁 (FU24-W6101911TDE1)"
    },
    {
      "label": "電極-仁",
      "value": "電極-仁"
    },
    {
      "label": "電極-六片模",
      "value": "電極-六片模"
    },
    {
      "label": "電極-六片模仁",
      "value": "電極-六片模仁"
    },
    {
      "label": "電極-沖棒",
      "value": "電極-沖棒"
    },
    {
      "label": "電極-沖棒1",
      "value": "電極-沖棒1"
    },
    {
      "label": "電極-沖棒2",
      "value": "電極-沖棒2"
    },
    {
      "label": "電極-物件一",
      "value": "電極-物件一"
    },
    {
      "label": "電極-物件一 (FU24-DC-001)",
      "value": "電極-物件一 (FU24-DC-001)"
    },
    {
      "label": "電極-物件一 (FU24-W6101911TDE2)",
      "value": "電極-物件一 (FU24-W6101911TDE2)"
    },
    {
      "label": "電極-物件一 (FU24-W6101911TDE3)",
      "value": "電極-物件一 (FU24-W6101911TDE3)"
    },
    {
      "label": "電極-物件二",
      "value": "電極-物件二"
    },
    {
      "label": "電極-物件二 (FU24-W6101911BDE2)",
      "value": "電極-物件二 (FU24-W6101911BDE2)"
    },
    {
      "label": "電極-物件二 (FU24-W6101911BDE3)",
      "value": "電極-物件二 (FU24-W6101911BDE3)"
    },
    {
      "label": "電極-物件二 (FU24-W6101911TDE2)",
      "value": "電極-物件二 (FU24-W6101911TDE2)"
    },
    {
      "label": "電極-物件二 (FU24-W6101911TDE3)",
      "value": "電極-物件二 (FU24-W6101911TDE3)"
    },
    {
      "label": "電極-物件二 (FU24-W6101911TDE4)",
      "value": "電極-物件二 (FU24-W6101911TDE4)"
    },
    {
      "label": "電極-物件二 (FU24-WR6510312BDE2)",
      "value": "電極-物件二 (FU24-WR6510312BDE2)"
    },
    {
      "label": "電極-物件三",
      "value": "電極-物件三"
    },
    {
      "label": "電極-物件三 (FU24-W6101911BDE2)",
      "value": "電極-物件三 (FU24-W6101911BDE2)"
    },
    {
      "label": "電極-物件三 (FU24-W6101911BDE4)",
      "value": "電極-物件三 (FU24-W6101911BDE4)"
    },
    {
      "label": "電極-物件五",
      "value": "電極-物件五"
    },
    {
      "label": "電極-物件四",
      "value": "電極-物件四"
    },
    {
      "label": "電極-修改用",
      "value": "電極-修改用"
    },
    {
      "label": "電極-套圈",
      "value": "電極-套圈"
    },
    {
      "label": "電極-套管",
      "value": "電極-套管"
    },
    {
      "label": "電極-組前",
      "value": "電極-組前"
    },
    {
      "label": "電極-組前電極",
      "value": "電極-組前電極"
    },
    {
      "label": "電極-組後",
      "value": "電極-組後"
    },
    {
      "label": "電極-組後放電(上下模仁)",
      "value": "電極-組後放電(上下模仁)"
    },
    {
      "label": "電極-組後電極",
      "value": "電極-組後電極"
    },
    {
      "label": "電極-殼",
      "value": "電極-殼"
    },
    {
      "label": "電極-傳銑",
      "value": "電極-傳銑"
    },
    {
      "label": "電極-模仁",
      "value": "電極-模仁"
    },
    {
      "label": "電極-模組",
      "value": "電極-模組"
    },
    {
      "label": "電極-模組(上模仁)",
      "value": "電極-模組(上模仁)"
    },
    {
      "label": "電極-模殼",
      "value": "電極-模殼"
    },
    {
      "label": "電極-模殼組",
      "value": "電極-模殼組"
    },
    {
      "label": "電極-總組",
      "value": "電極-總組"
    },
    {
      "label": "電極-總組電極",
      "value": "電極-總組電極"
    },
    {
      "label": "電極1",
      "value": "電極1"
    },
    {
      "label": "電極1 (仁)",
      "value": "電極1 (仁)"
    },
    {
      "label": "電極1 (組後)",
      "value": "電極1 (組後)"
    },
    {
      "label": "電極1(下模仁)",
      "value": "電極1(下模仁)"
    },
    {
      "label": "電極1(物件一)",
      "value": "電極1(物件一)"
    },
    {
      "label": "電極1-(物件二+三)",
      "value": "電極1-(物件二+三)"
    },
    {
      "label": "電極1-下模仁",
      "value": "電極1-下模仁"
    },
    {
      "label": "電極1-六片模",
      "value": "電極1-六片模"
    },
    {
      "label": "電極1-六片模(組前)",
      "value": "電極1-六片模(組前)"
    },
    {
      "label": "電極1-物件一",
      "value": "電極1-物件一"
    },
    {
      "label": "電極1-物件一(12片模)",
      "value": "電極1-物件一(12片模)"
    },
    {
      "label": "電極1-物件二",
      "value": "電極1-物件二"
    },
    {
      "label": "電極1-物件二+三",
      "value": "電極1-物件二+三"
    },
    {
      "label": "電極1-物件五",
      "value": "電極1-物件五"
    },
    {
      "label": "電極1-物件四",
      "value": "電極1-物件四"
    },
    {
      "label": "電極1-組前",
      "value": "電極1-組前"
    },
    {
      "label": "電極1-模組",
      "value": "電極1-模組"
    },
    {
      "label": "電極1-總組",
      "value": "電極1-總組"
    },
    {
      "label": "電極2",
      "value": "電極2"
    },
    {
      "label": "電極2 (組後)",
      "value": "電極2 (組後)"
    },
    {
      "label": "電極2 (殼)",
      "value": "電極2 (殼)"
    },
    {
      "label": "電極2(物件二)",
      "value": "電極2(物件二)"
    },
    {
      "label": "電極2(組後)",
      "value": "電極2(組後)"
    },
    {
      "label": "電極2(模組)",
      "value": "電極2(模組)"
    },
    {
      "label": "電極2-(物件三)",
      "value": "電極2-(物件三)"
    },
    {
      "label": "電極2-下模仁",
      "value": "電極2-下模仁"
    },
    {
      "label": "電極2-物件一",
      "value": "電極2-物件一"
    },
    {
      "label": "電極2-物件一(12片模)",
      "value": "電極2-物件一(12片模)"
    },
    {
      "label": "電極2-物件二",
      "value": "電極2-物件二"
    },
    {
      "label": "電極2-物件二+三",
      "value": "電極2-物件二+三"
    },
    {
      "label": "電極2-物件三",
      "value": "電極2-物件三"
    },
    {
      "label": "電極2-組後",
      "value": "電極2-組後"
    },
    {
      "label": "電極2-組後放電",
      "value": "電極2-組後放電"
    },
    {
      "label": "電極2-模組",
      "value": "電極2-模組"
    },
    {
      "label": "電極3",
      "value": "電極3"
    },
    {
      "label": "電極3 (組前)",
      "value": "電極3 (組前)"
    },
    {
      "label": "電極3(組後)",
      "value": "電極3(組後)"
    },
    {
      "label": "電極3-(物件二+五)",
      "value": "電極3-(物件二+五)"
    },
    {
      "label": "電極3-下模仁",
      "value": "電極3-下模仁"
    },
    {
      "label": "電極3-物件一",
      "value": "電極3-物件一"
    },
    {
      "label": "電極3-物件一(12片模)",
      "value": "電極3-物件一(12片模)"
    },
    {
      "label": "電極3-物件一+物件二",
      "value": "電極3-物件一+物件二"
    },
    {
      "label": "電極3-物件二",
      "value": "電極3-物件二"
    },
    {
      "label": "電極3-物件二+五",
      "value": "電極3-物件二+五"
    },
    {
      "label": "電極3-物件三",
      "value": "電極3-物件三"
    },
    {
      "label": "電極3-組後",
      "value": "電極3-組後"
    },
    {
      "label": "電極3-模組",
      "value": "電極3-模組"
    },
    {
      "label": "電極3-銲接前",
      "value": "電極3-銲接前"
    },
    {
      "label": "電極4-下模仁",
      "value": "電極4-下模仁"
    },
    {
      "label": "電極4-物件一(12片模)",
      "value": "電極4-物件一(12片模)"
    },
    {
      "label": "電極4-物件二",
      "value": "電極4-物件二"
    },
    {
      "label": "電極4-組後",
      "value": "電極4-組後"
    },
    {
      "label": "電極5-物件二",
      "value": "電極5-物件二"
    },
    {
      "label": "電極60-43-49B+60-43-38C",
      "value": "電極60-43-49B+60-43-38C"
    },
    {
      "label": "電電",
      "value": "電電"
    },
    {
      "label": "零件",
      "value": "零件"
    },
    {
      "label": "零件 (四片模)",
      "value": "零件 (四片模)"
    },
    {
      "label": "零件(四片模)",
      "value": "零件(四片模)"
    },
    {
      "label": "零件(四片模)-治具",
      "value": "零件(四片模)-治具"
    },
    {
      "label": "零件-右",
      "value": "零件-右"
    },
    {
      "label": "零件-右件",
      "value": "零件-右件"
    },
    {
      "label": "零件-右物件",
      "value": "零件-右物件"
    },
    {
      "label": "零件-左",
      "value": "零件-左"
    },
    {
      "label": "零件-左件",
      "value": "零件-左件"
    },
    {
      "label": "零件-左物件",
      "value": "零件-左物件"
    },
    {
      "label": "零件-底座",
      "value": "零件-底座"
    },
    {
      "label": "零件-單片",
      "value": "零件-單片"
    },
    {
      "label": "零件-單件",
      "value": "零件-單件"
    },
    {
      "label": "零件-碳化鎢",
      "value": "零件-碳化鎢"
    },
    {
      "label": "零件1",
      "value": "零件1"
    },
    {
      "label": "零件2",
      "value": "零件2"
    },
    {
      "label": "零件一",
      "value": "零件一"
    },
    {
      "label": "零件二",
      "value": "零件二"
    },
    {
      "label": "零件三",
      "value": "零件三"
    },
    {
      "label": "零件四",
      "value": "零件四"
    },
    {
      "label": "墊片",
      "value": "墊片"
    },
    {
      "label": "墊圈",
      "value": "墊圈"
    },
    {
      "label": "墊塊",
      "value": "墊塊"
    },
    {
      "label": "墊塊(物件四)",
      "value": "墊塊(物件四)"
    },
    {
      "label": "墊塊(組入模殼先放電)",
      "value": "墊塊(組入模殼先放電)"
    },
    {
      "label": "墊塊-單件",
      "value": "墊塊-單件"
    },
    {
      "label": "墊塊-鐵氟龍",
      "value": "墊塊-鐵氟龍"
    },
    {
      "label": "墊塊5",
      "value": "墊塊5"
    },
    {
      "label": "墊塊6",
      "value": "墊塊6"
    },
    {
      "label": "墊塊7",
      "value": "墊塊7"
    },
    {
      "label": "碳化碳沖棒 DR14C",
      "value": "碳化碳沖棒 DR14C"
    },
    {
      "label": "碳化鎢",
      "value": "碳化鎢"
    },
    {
      "label": "碳化鎢 DH30",
      "value": "碳化鎢 DH30"
    },
    {
      "label": "碳化鎢 DH40",
      "value": "碳化鎢 DH40"
    },
    {
      "label": "碳化鎢 DR05C",
      "value": "碳化鎢 DR05C"
    },
    {
      "label": "碳化鎢 DR11C",
      "value": "碳化鎢 DR11C"
    },
    {
      "label": "碳化鎢 DR14C",
      "value": "碳化鎢 DR14C"
    },
    {
      "label": "碳化鎢 DR17C",
      "value": "碳化鎢 DR17C"
    },
    {
      "label": "碳化鎢 EA65",
      "value": "碳化鎢 EA65"
    },
    {
      "label": "碳化鎢 FA14C",
      "value": "碳化鎢 FA14C"
    },
    {
      "label": "碳化鎢 G20",
      "value": "碳化鎢 G20"
    },
    {
      "label": "碳化鎢 G3",
      "value": "碳化鎢 G3"
    },
    {
      "label": "碳化鎢 G30",
      "value": "碳化鎢 G30"
    },
    {
      "label": "碳化鎢 G35",
      "value": "碳化鎢 G35"
    },
    {
      "label": "碳化鎢 G4",
      "value": "碳化鎢 G4"
    },
    {
      "label": "碳化鎢 G40",
      "value": "碳化鎢 G40"
    },
    {
      "label": "碳化鎢 G45",
      "value": "碳化鎢 G45"
    },
    {
      "label": "碳化鎢 G50",
      "value": "碳化鎢 G50"
    },
    {
      "label": "碳化鎢 H11C",
      "value": "碳化鎢 H11C"
    },
    {
      "label": "碳化鎢 H15F",
      "value": "碳化鎢 H15F"
    },
    {
      "label": "碳化鎢 HN",
      "value": "碳化鎢 HN"
    },
    {
      "label": "碳化鎢 HR",
      "value": "碳化鎢 HR"
    },
    {
      "label": "碳化鎢 HT",
      "value": "碳化鎢 HT"
    },
    {
      "label": "碳化鎢 HV",
      "value": "碳化鎢 HV"
    },
    {
      "label": "碳化鎢 HY",
      "value": "碳化鎢 HY"
    },
    {
      "label": "碳化鎢 KE10",
      "value": "碳化鎢 KE10"
    },
    {
      "label": "碳化鎢 KE9",
      "value": "碳化鎢 KE9"
    },
    {
      "label": "碳化鎢 KG",
      "value": "碳化鎢 KG"
    },
    {
      "label": "碳化鎢 KG1",
      "value": "碳化鎢 KG1"
    },
    {
      "label": "碳化鎢 KG4",
      "value": "碳化鎢 KG4"
    },
    {
      "label": "碳化鎢 KG5",
      "value": "碳化鎢 KG5"
    },
    {
      "label": "碳化鎢 KG6",
      "value": "碳化鎢 KG6"
    },
    {
      "label": "碳化鎢 KG7",
      "value": "碳化鎢 KG7"
    },
    {
      "label": "碳化鎢 LR20C",
      "value": "碳化鎢 LR20C"
    },
    {
      "label": "碳化鎢 NO",
      "value": "碳化鎢 NO"
    },
    {
      "label": "碳化鎢 RO",
      "value": "碳化鎢 RO"
    },
    {
      "label": "碳化鎢 SR10C",
      "value": "碳化鎢 SR10C"
    },
    {
      "label": "碳化鎢 SR13C",
      "value": "碳化鎢 SR13C"
    },
    {
      "label": "碳化鎢 SR16C",
      "value": "碳化鎢 SR16C"
    },
    {
      "label": "碳化鎢 SR19C",
      "value": "碳化鎢 SR19C"
    },
    {
      "label": "碳化鎢 SR22C",
      "value": "碳化鎢 SR22C"
    },
    {
      "label": "碳化鎢 ST6",
      "value": "碳化鎢 ST6"
    },
    {
      "label": "碳化鎢 ST6 4/25暫停使用",
      "value": "碳化鎢 ST6 4/25暫停使用"
    },
    {
      "label": "碳化鎢 ST6(實吋12.2D)",
      "value": "碳化鎢 ST6(實吋12.2D)"
    },
    {
      "label": "碳化鎢 ST7",
      "value": "碳化鎢 ST7"
    },
    {
      "label": "碳化鎢 TR05C",
      "value": "碳化鎢 TR05C"
    },
    {
      "label": "碳化鎢 TR09C",
      "value": "碳化鎢 TR09C"
    },
    {
      "label": "碳化鎢 TR13M",
      "value": "碳化鎢 TR13M"
    },
    {
      "label": "碳化鎢 TR15C",
      "value": "碳化鎢 TR15C"
    },
    {
      "label": "碳化鎢 TR20C",
      "value": "碳化鎢 TR20C"
    },
    {
      "label": "碳化鎢 TR20M",
      "value": "碳化鎢 TR20M"
    },
    {
      "label": "碳化鎢 TR25C",
      "value": "碳化鎢 TR25C"
    },
    {
      "label": "碳化鎢 UR10C",
      "value": "碳化鎢 UR10C"
    },
    {
      "label": "碳化鎢 VA70",
      "value": "碳化鎢 VA70"
    },
    {
      "label": "碳化鎢 VA70 不複使用",
      "value": "碳化鎢 VA70 不複使用"
    },
    {
      "label": "碳化鎢 VA70 批號990716雷仔孔小",
      "value": "碳化鎢 VA70 批號990716雷仔孔小"
    },
    {
      "label": "碳化鎢 VA70(內孔不良氣壓時注意",
      "value": "碳化鎢 VA70(內孔不良氣壓時注意"
    },
    {
      "label": "碳化鎢 VA80",
      "value": "碳化鎢 VA80"
    },
    {
      "label": "碳化鎢 VA80 8度",
      "value": "碳化鎢 VA80 8度"
    },
    {
      "label": "碳化鎢 VA90",
      "value": "碳化鎢 VA90"
    },
    {
      "label": "碳化鎢 VA90 (平)",
      "value": "碳化鎢 VA90 (平)"
    },
    {
      "label": "碳化鎢 VA95",
      "value": "碳化鎢 VA95"
    },
    {
      "label": "碳化鎢 WF20",
      "value": "碳化鎢 WF20"
    },
    {
      "label": "碳化鎢 WF30",
      "value": "碳化鎢 WF30"
    },
    {
      "label": "碳化鎢 YR20C",
      "value": "碳化鎢 YR20C"
    },
    {
      "label": "碳化鎢 YR24C",
      "value": "碳化鎢 YR24C"
    },
    {
      "label": "碳化鎢 YR28C",
      "value": "碳化鎢 YR28C"
    },
    {
      "label": "碳化鎢 ZR15C",
      "value": "碳化鎢 ZR15C"
    },
    {
      "label": "碳化鎢 ZR18C",
      "value": "碳化鎢 ZR18C"
    },
    {
      "label": "碳化鎢 方板 TR20C",
      "value": "碳化鎢 方板 TR20C"
    },
    {
      "label": "碳化鎢方板 DR05C",
      "value": "碳化鎢方板 DR05C"
    },
    {
      "label": "碳化鎢方板 DR14C",
      "value": "碳化鎢方板 DR14C"
    },
    {
      "label": "碳化鎢凸形圓盲孔 H11C",
      "value": "碳化鎢凸形圓盲孔 H11C"
    },
    {
      "label": "碳化鎢沖棒 DR05C",
      "value": "碳化鎢沖棒 DR05C"
    },
    {
      "label": "碳化鎢沖棒 DR07C",
      "value": "碳化鎢沖棒 DR07C"
    },
    {
      "label": "碳化鎢沖棒 DR09C",
      "value": "碳化鎢沖棒 DR09C"
    },
    {
      "label": "碳化鎢沖棒 DR11C",
      "value": "碳化鎢沖棒 DR11C"
    },
    {
      "label": "碳化鎢沖棒 DR14C",
      "value": "碳化鎢沖棒 DR14C"
    },
    {
      "label": "碳化鎢沖棒 DR17C",
      "value": "碳化鎢沖棒 DR17C"
    },
    {
      "label": "碳化鎢沖棒 RX10",
      "value": "碳化鎢沖棒 RX10"
    },
    {
      "label": "碳化鎢沖棒 RX15",
      "value": "碳化鎢沖棒 RX15"
    },
    {
      "label": "碳化鎢沖棒 SR10C",
      "value": "碳化鎢沖棒 SR10C"
    },
    {
      "label": "碳化鎢沖棒 TR09C",
      "value": "碳化鎢沖棒 TR09C"
    },
    {
      "label": "碳化鎢沖棒 TR15C",
      "value": "碳化鎢沖棒 TR15C"
    },
    {
      "label": "碳化鎢沖棒 TR20C",
      "value": "碳化鎢沖棒 TR20C"
    },
    {
      "label": "碳化鎢沖棒 TR25C",
      "value": "碳化鎢沖棒 TR25C"
    },
    {
      "label": "碳化鎢沖棒 UR10C",
      "value": "碳化鎢沖棒 UR10C"
    },
    {
      "label": "碳化鎢沖棒 UR13C",
      "value": "碳化鎢沖棒 UR13C"
    },
    {
      "label": "碳化鎢沖棒 YR10C",
      "value": "碳化鎢沖棒 YR10C"
    },
    {
      "label": "碳化鎢沖棒 YR20C",
      "value": "碳化鎢沖棒 YR20C"
    },
    {
      "label": "碳化鎢沖棒 YR28C",
      "value": "碳化鎢沖棒 YR28C"
    },
    {
      "label": "碳化鎢沖棒 ZR10C",
      "value": "碳化鎢沖棒 ZR10C"
    },
    {
      "label": "碳化鎢沖棒棒 DR11C",
      "value": "碳化鎢沖棒棒 DR11C"
    },
    {
      "label": "碳化鎢沖棒棒 DR14C",
      "value": "碳化鎢沖棒棒 DR14C"
    },
    {
      "label": "碳化鎢針",
      "value": "碳化鎢針"
    },
    {
      "label": "碳化鎢通孔 VA70",
      "value": "碳化鎢通孔 VA70"
    },
    {
      "label": "碳化鎢圓形內孔 ST6",
      "value": "碳化鎢圓形內孔 ST6"
    },
    {
      "label": "碳化鎢圓形通孔 HT",
      "value": "碳化鎢圓形通孔 HT"
    },
    {
      "label": "碳化鎢圓棒 DR11C",
      "value": "碳化鎢圓棒 DR11C"
    },
    {
      "label": "碳化鎢圓棒 DR14C",
      "value": "碳化鎢圓棒 DR14C"
    },
    {
      "label": "碳化鎢圓棒 RX15",
      "value": "碳化鎢圓棒 RX15"
    },
    {
      "label": "碳化鎢圓棒 SR10C",
      "value": "碳化鎢圓棒 SR10C"
    },
    {
      "label": "碳化鎢圓棒 TR09C",
      "value": "碳化鎢圓棒 TR09C"
    },
    {
      "label": "碳化鎢圓棒 TR15C",
      "value": "碳化鎢圓棒 TR15C"
    },
    {
      "label": "碳化鎢圓棒 TR20C",
      "value": "碳化鎢圓棒 TR20C"
    },
    {
      "label": "碳化鎢圓棒 TR25C",
      "value": "碳化鎢圓棒 TR25C"
    },
    {
      "label": "碳化鎢圓棒 ZR18C",
      "value": "碳化鎢圓棒 ZR18C"
    },
    {
      "label": "碳化鎢錐度內孔 ST6",
      "value": "碳化鎢錐度內孔 ST6"
    },
    {
      "label": "管套",
      "value": "管套"
    },
    {
      "label": "製作電極加工費",
      "value": "製作電極加工費"
    },
    {
      "label": "銅板 青銅C83600",
      "value": "銅板 青銅C83600"
    },
    {
      "label": "銅板 黃銅C3604",
      "value": "銅板 黃銅C3604"
    },
    {
      "label": "銅料",
      "value": "銅料"
    },
    {
      "label": "銑刀片",
      "value": "銑刀片"
    },
    {
      "label": "彈簧",
      "value": "彈簧"
    },
    {
      "label": "彈簧銷 ISO 13337",
      "value": "彈簧銷 ISO 13337"
    },
    {
      "label": "彈簧鋼 SK5",
      "value": "彈簧鋼 SK5"
    },
    {
      "label": "模仁",
      "value": "模仁"
    },
    {
      "label": "模仁  (三片模)",
      "value": "模仁  (三片模)"
    },
    {
      "label": "模仁 (14027)",
      "value": "模仁 (14027)"
    },
    {
      "label": "模仁 (八片模)",
      "value": "模仁 (八片模)"
    },
    {
      "label": "模仁 (三片模)",
      "value": "模仁 (三片模)"
    },
    {
      "label": "模仁 (三片模組)",
      "value": "模仁 (三片模組)"
    },
    {
      "label": "模仁 (六片模)",
      "value": "模仁 (六片模)"
    },
    {
      "label": "模仁 (四片模)",
      "value": "模仁 (四片模)"
    },
    {
      "label": "模仁 (四片模)-治具",
      "value": "模仁 (四片模)-治具"
    },
    {
      "label": "模仁 ST6",
      "value": "模仁 ST6"
    },
    {
      "label": "模仁 半成品",
      "value": "模仁 半成品"
    },
    {
      "label": "模仁(HS)",
      "value": "模仁(HS)"
    },
    {
      "label": "模仁(WC)",
      "value": "模仁(WC)"
    },
    {
      "label": "模仁(三片模)",
      "value": "模仁(三片模)"
    },
    {
      "label": "模仁(中)",
      "value": "模仁(中)"
    },
    {
      "label": "模仁(內)",
      "value": "模仁(內)"
    },
    {
      "label": "模仁(六片)",
      "value": "模仁(六片)"
    },
    {
      "label": "模仁(六片)-治具",
      "value": "模仁(六片)-治具"
    },
    {
      "label": "模仁(六片模)",
      "value": "模仁(六片模)"
    },
    {
      "label": "模仁(六片模仁)",
      "value": "模仁(六片模仁)"
    },
    {
      "label": "模仁(四片)",
      "value": "模仁(四片)"
    },
    {
      "label": "模仁(四片)-治具",
      "value": "模仁(四片)-治具"
    },
    {
      "label": "模仁(四片模)",
      "value": "模仁(四片模)"
    },
    {
      "label": "模仁(四片模)-治具",
      "value": "模仁(四片模)-治具"
    },
    {
      "label": "模仁(物件一)",
      "value": "模仁(物件一)"
    },
    {
      "label": "模仁(物件二)",
      "value": "模仁(物件二)"
    },
    {
      "label": "模仁(物件三)",
      "value": "模仁(物件三)"
    },
    {
      "label": "模仁+套圈",
      "value": "模仁+套圈"
    },
    {
      "label": "模仁-仿殼",
      "value": "模仁-仿殼"
    },
    {
      "label": "模仁-有組流程",
      "value": "模仁-有組流程"
    },
    {
      "label": "模仁-組前",
      "value": "模仁-組前"
    },
    {
      "label": "模仁-單件",
      "value": "模仁-單件"
    },
    {
      "label": "模仁-電極",
      "value": "模仁-電極"
    },
    {
      "label": "模仁-電極1",
      "value": "模仁-電極1"
    },
    {
      "label": "模仁-電極2",
      "value": "模仁-電極2"
    },
    {
      "label": "模仁-碳化鎢",
      "value": "模仁-碳化鎢"
    },
    {
      "label": "模仁1",
      "value": "模仁1"
    },
    {
      "label": "模仁2",
      "value": "模仁2"
    },
    {
      "label": "模仁3",
      "value": "模仁3"
    },
    {
      "label": "模仁4",
      "value": "模仁4"
    },
    {
      "label": "模仁5",
      "value": "模仁5"
    },
    {
      "label": "模仁6",
      "value": "模仁6"
    },
    {
      "label": "模仁8",
      "value": "模仁8"
    },
    {
      "label": "模仁組",
      "value": "模仁組"
    },
    {
      "label": "模具銲接(大)",
      "value": "模具銲接(大)"
    },
    {
      "label": "模具銲接(小)",
      "value": "模具銲接(小)"
    },
    {
      "label": "模具鍍鈦(TiAlN)",
      "value": "模具鍍鈦(TiAlN)"
    },
    {
      "label": "模具鍍鈦(TiN)",
      "value": "模具鍍鈦(TiN)"
    },
    {
      "label": "模底",
      "value": "模底"
    },
    {
      "label": "模圈",
      "value": "模圈"
    },
    {
      "label": "模組",
      "value": "模組"
    },
    {
      "label": "模組 (四片模)",
      "value": "模組 (四片模)"
    },
    {
      "label": "模組-碳化鎢",
      "value": "模組-碳化鎢"
    },
    {
      "label": "模組一(物件一)",
      "value": "模組一(物件一)"
    },
    {
      "label": "模組二(物件二)",
      "value": "模組二(物件二)"
    },
    {
      "label": "模殼",
      "value": "模殼"
    },
    {
      "label": "模殼 (13560-01)",
      "value": "模殼 (13560-01)"
    },
    {
      "label": "模殼 不複使用",
      "value": "模殼 不複使用"
    },
    {
      "label": "模殼(物件五)",
      "value": "模殼(物件五)"
    },
    {
      "label": "模殼(物件六)",
      "value": "模殼(物件六)"
    },
    {
      "label": "模殼(單)",
      "value": "模殼(單)"
    },
    {
      "label": "模殼+上模仁",
      "value": "模殼+上模仁"
    },
    {
      "label": "模殼+碳化鎢",
      "value": "模殼+碳化鎢"
    },
    {
      "label": "模殼-有組裝",
      "value": "模殼-有組裝"
    },
    {
      "label": "模殼-單件",
      "value": "模殼-單件"
    },
    {
      "label": "模殼1",
      "value": "模殼1"
    },
    {
      "label": "模殼熱處理",
      "value": "模殼熱處理"
    },
    {
      "label": "模管",
      "value": "模管"
    },
    {
      "label": "熱處理",
      "value": "熱處理"
    },
    {
      "label": "盤片",
      "value": "盤片"
    },
    {
      "label": "銷",
      "value": "銷"
    },
    {
      "label": "鋁角材",
      "value": "鋁角材"
    },
    {
      "label": "鋁板材",
      "value": "鋁板材"
    },
    {
      "label": "鋁圓棒",
      "value": "鋁圓棒"
    },
    {
      "label": "導能管真空銲接",
      "value": "導能管真空銲接"
    },
    {
      "label": "擋塊",
      "value": "擋塊"
    },
    {
      "label": "擋塊一",
      "value": "擋塊一"
    },
    {
      "label": "擋塊二",
      "value": "擋塊二"
    },
    {
      "label": "磨仁",
      "value": "磨仁"
    },
    {
      "label": "磨殼",
      "value": "磨殼"
    },
    {
      "label": "鋼料",
      "value": "鋼料"
    },
    {
      "label": "鋼料 1.3243道(SKH55)",
      "value": "鋼料 1.3243道(SKH55)"
    },
    {
      "label": "鋼料 1.3247道(M42)",
      "value": "鋼料 1.3247道(M42)"
    },
    {
      "label": "鋼料 1.3343道(SKH9)",
      "value": "鋼料 1.3343道(SKH9)"
    },
    {
      "label": "鋼料 1.3343道(SKH9)龍畿預硬",
      "value": "鋼料 1.3343道(SKH9)龍畿預硬"
    },
    {
      "label": "鋼料 1.3395道(ASP23)",
      "value": "鋼料 1.3395道(ASP23)"
    },
    {
      "label": "鋼料 4140",
      "value": "鋼料 4140"
    },
    {
      "label": "鋼料 ASP23",
      "value": "鋼料 ASP23"
    },
    {
      "label": "鋼料 ASP60",
      "value": "鋼料 ASP60"
    },
    {
      "label": "鋼料 CPM-M4",
      "value": "鋼料 CPM-M4"
    },
    {
      "label": "鋼料 DC53",
      "value": "鋼料 DC53"
    },
    {
      "label": "鋼料 K340",
      "value": "鋼料 K340"
    },
    {
      "label": "鋼料 M42",
      "value": "鋼料 M42"
    },
    {
      "label": "鋼料 M42(日立)",
      "value": "鋼料 M42(日立)"
    },
    {
      "label": "鋼料 M42龍畿預硬",
      "value": "鋼料 M42龍畿預硬"
    },
    {
      "label": "鋼料 M7(美國)",
      "value": "鋼料 M7(美國)"
    },
    {
      "label": "鋼料 MCR1",
      "value": "鋼料 MCR1"
    },
    {
      "label": "鋼料 R3",
      "value": "鋼料 R3"
    },
    {
      "label": "鋼料 R4",
      "value": "鋼料 R4"
    },
    {
      "label": "鋼料 S45C",
      "value": "鋼料 S45C"
    },
    {
      "label": "鋼料 S45C 光面",
      "value": "鋼料 S45C 光面"
    },
    {
      "label": "鋼料 S590",
      "value": "鋼料 S590"
    },
    {
      "label": "鋼料 SKD61",
      "value": "鋼料 SKD61"
    },
    {
      "label": "鋼料 SKD61(DAC)",
      "value": "鋼料 SKD61(DAC)"
    },
    {
      "label": "鋼料 SKH55",
      "value": "鋼料 SKH55"
    },
    {
      "label": "鋼料 SKH55 龍畿預硬",
      "value": "鋼料 SKH55 龍畿預硬"
    },
    {
      "label": "鋼料 SKH55龍畿預硬",
      "value": "鋼料 SKH55龍畿預硬"
    },
    {
      "label": "鋼料 SKH9",
      "value": "鋼料 SKH9"
    },
    {
      "label": "鋼料 SKH9 (日立)",
      "value": "鋼料 SKH9 (日立)"
    },
    {
      "label": "鋼料 SKH9 缺料",
      "value": "鋼料 SKH9 缺料"
    },
    {
      "label": "鋼料 SKH9 預硬",
      "value": "鋼料 SKH9 預硬"
    },
    {
      "label": "鋼料 SLD",
      "value": "鋼料 SLD"
    },
    {
      "label": "鋼料 SUJ2(軸承鋼)",
      "value": "鋼料 SUJ2(軸承鋼)"
    },
    {
      "label": "鋼料 SUS420J2",
      "value": "鋼料 SUS420J2"
    },
    {
      "label": "鋼料 中鋼S45C",
      "value": "鋼料 中鋼S45C"
    },
    {
      "label": "鋼料 日立SGT(SKS3)",
      "value": "鋼料 日立SGT(SKS3)"
    },
    {
      "label": "鋼料DC53",
      "value": "鋼料DC53"
    },
    {
      "label": "鋼模",
      "value": "鋼模"
    },
    {
      "label": "壓扁模鎢鋼真空銲接",
      "value": "壓扁模鎢鋼真空銲接"
    },
    {
      "label": "翼形杯蓋真空銲接",
      "value": "翼形杯蓋真空銲接"
    },
    {
      "label": "翼形油杯蓋真空銲接",
      "value": "翼形油杯蓋真空銲接"
    },
    {
      "label": "螺紋護套",
      "value": "螺紋護套"
    },
    {
      "label": "螺帽",
      "value": "螺帽"
    },
    {
      "label": "螺絲",
      "value": "螺絲"
    },
    {
      "label": "螺絲內六角孔",
      "value": "螺絲內六角孔"
    },
    {
      "label": "螺絲內六角孔皿頭",
      "value": "螺絲內六角孔皿頭"
    },
    {
      "label": "螺絲內六角孔皿頭(平頭)",
      "value": "螺絲內六角孔皿頭(平頭)"
    },
    {
      "label": "轉子",
      "value": "轉子"
    },
    {
      "label": "轉子(日)",
      "value": "轉子(日)"
    },
    {
      "label": "轉子(測試)",
      "value": "轉子(測試)"
    },
    {
      "label": "轉子(韓)",
      "value": "轉子(韓)"
    },
    {
      "label": "轉子-半成品",
      "value": "轉子-半成品"
    },
    {
      "label": "鎢棒 純鎢Folfran",
      "value": "鎢棒 純鎢Folfran"
    },
    {
      "label": "鎢鋼沖棒",
      "value": "鎢鋼沖棒"
    },
    {
      "label": "鎢鋼墊塊",
      "value": "鎢鋼墊塊"
    },
    {
      "label": "鎢鋼墊塊 ST6",
      "value": "鎢鋼墊塊 ST6"
    },
    {
      "label": "鐵氟龍",
      "value": "鐵氟龍"
    },
    {
      "label": "襯套(套圈)",
      "value": "襯套(套圈)"
    }
  ];
  let customer = [{
      "value": "5010330",
      "label": "5010330 (株)WATANABE"
    },
    {
      "value": "2080010",
      "label": "2080010 20210323"
    },
    {
      "value": "2080590",
      "label": "2080590 A+E Keller"
    },
    {
      "value": "2080180",
      "label": "2080180 A. FRIEDBERG"
    },
    {
      "value": "2030030",
      "label": "2030030 A.AGRATI S.P.A."
    },
    {
      "value": "2080400",
      "label": "2080400 A.I.M. ALL IN METAL GmbH"
    },
    {
      "value": "2010030",
      "label": "2010030 A.M.C.(U.K.) FASTENERS LIMITED"
    },
    {
      "value": "2030100",
      "label": "2030100 A.M.E.A. srl"
    },
    {
      "value": "2080410",
      "label": "2080410 ABC UMFORMTECHNIK"
    },
    {
      "value": "1010370",
      "label": "1010370 ACME SCREW CO."
    },
    {
      "value": "2090030",
      "label": "2090030 ACUMENT AMIENS SAS"
    },
    {
      "value": "1030050",
      "label": "1030050 ACUMENT BRASIL SISTEMAS DE FIXA??O S.A."
    },
    {
      "value": "1030050",
      "label": "1030050 ACUMENT GLOBAL TECHNOLOGIES"
    },
    {
      "value": "1031021",
      "label": "1031021 ACUMENT GLOBAL TECHNOLOGIES"
    },
    {
      "value": "1030010",
      "label": "1030010 ACUMENT GLOBAL TECHNOLOGIES SOUTH AMERICA"
    },
    {
      "value": "2080040",
      "label": "2080040 ACUMENT GLOBAL TECHNOLOGLES ACUMENT GMBH & CO.OHG"
    },
    {
      "value": "2080030",
      "label": "2080030 ACUMENT GMBH & CO. OHG"
    },
    {
      "value": "2080340",
      "label": "2080340 ACUMENT GMBH & CO., OHG DURBHEIM"
    },
    {
      "value": "1040010",
      "label": "1040010 ACUMENT/ CAMCAR DE MEXICO"
    },
    {
      "value": "2080110",
      "label": "2080110 ACUMENT/NEUWIED"
    },
    {
      "value": "2080290",
      "label": "2080290 ADOLF MENSCHEL VERBINDUNGSTECHNIK"
    },
    {
      "value": "1031014",
      "label": "1031014 AESA"
    },
    {
      "value": "1010090",
      "label": "1010090 AGRATI-Medina, LLC"
    },
    {
      "value": "1010210",
      "label": "1010210 AGRATI-TIFFIN,LLC"
    },
    {
      "value": "1010690",
      "label": "1010690 AIR INDUSTRIES COMPANY (A PCC COMPANY)"
    },
    {
      "value": "1030020",
      "label": "1030020 AJK COMERCIO IMPORTADORA E EXPORTADORA LTDA"
    },
    {
      "value": "2010110",
      "label": "2010110 ALCOA FASTEINING SYSTEMS"
    },
    {
      "value": "2090010",
      "label": "2090010 ALCOA FIXATIONS SIMMONDS SAS."
    },
    {
      "value": "2010111",
      "label": "2010111 ALCOA-AEROSPACE DIVISION"
    },
    {
      "value": "1031012",
      "label": "1031012 ANCORA"
    },
    {
      "value": "5040040",
      "label": "5040040 ANKIT FASTENERS PVT. LTD."
    },
    {
      "value": "2030010",
      "label": "2030010 ANNALISA BETTINI"
    },
    {
      "value": "2010110",
      "label": "2010110 ARCONIC FASTEINING SYSTEMS & RINGS"
    },
    {
      "value": "2090010",
      "label": "2090010 ARCONIC FIXATIONS SIMMONDS  SAS"
    },
    {
      "value": "1010660",
      "label": "1010660 ARCONIC Fastening Systems and Rings - Industrial Products Division"
    },
    {
      "value": "1010640",
      "label": "1010640 ARNOLD FASTENING SYSTEMS"
    },
    {
      "value": "1010640",
      "label": "1010640 ARNOLD FASTENING SYSTEMS,INC."
    },
    {
      "value": "2080420",
      "label": "2080420 ARNOLD UMFORMTECHNIK GMBH"
    },
    {
      "value": "1010230",
      "label": "1010230 ATF, Inc--(舊稱HEADER)"
    },
    {
      "value": "1010400",
      "label": "1010400 ATG PRECISION PRODUCTS"
    },
    {
      "value": "1010400",
      "label": "1010400 ATG PRECISION PRODUCTSA 0501"
    },
    {
      "value": "1010700",
      "label": "1010700 AVDEL LLC"
    },
    {
      "value": "2010190",
      "label": "2010190 AVDEL UK LTD."
    },
    {
      "value": "1010660",
      "label": "1010660 Alcoa Fastening Systems and Rings - Industrial Products Division"
    },
    {
      "value": "1010770",
      "label": "1010770 Anderson Manufacturing Co.,INC."
    },
    {
      "value": "6080020",
      "label": "6080020 Arnold Fasteners (Shenyang) Co., Ltd."
    },
    {
      "value": "2010050",
      "label": "2010050 BARTON COLD-FORM LTD."
    },
    {
      "value": "2010050",
      "label": "2010050 BARTON COLD-FORM(UK) LTD."
    },
    {
      "value": "1030080",
      "label": "1030080 BELENUS"
    },
    {
      "value": "2080000",
      "label": "2080000 BERGKVIST.,& CO., GMBH"
    },
    {
      "value": "5040050",
      "label": "5040050 BHAVANI INDUSTRIES"
    },
    {
      "value": "2010210",
      "label": "2010210 BLAKEACRE LTD."
    },
    {
      "value": "2010260",
      "label": "2010260 BLANC AERO INDUSTRIES UK LTD"
    },
    {
      "value": "2090100",
      "label": "2090100 BOLLHOFF FRANCE"
    },
    {
      "value": "1030030",
      "label": "1030030 BOLLHOFF INDUSTRIAL"
    },
    {
      "value": "1010290",
      "label": "1010290 BOLLHOFF RIVNUT INC."
    },
    {
      "value": "2030060",
      "label": "2030060 BOLLHOFF UNIFAST S.R.L."
    },
    {
      "value": "6060020",
      "label": "6060020 BOLLHOFF WUXI"
    },
    {
      "value": "2010230",
      "label": "2010230 BOLT KING"
    },
    {
      "value": "2030130",
      "label": "2030130 BONTEMPI VIBO"
    },
    {
      "value": "5050041",
      "label": "5050041 BPS PATLAYICI SANAYI AS."
    },
    {
      "value": "1010250",
      "label": "1010250 BRUNNER DRILLING & MANUFACTURING,INC."
    },
    {
      "value": "1010250",
      "label": "1010250 BRUNNER MFG.CO.INC."
    },
    {
      "value": "2080380",
      "label": "2080380 BULTEN GMBH"
    },
    {
      "value": "2050020",
      "label": "2050020 BULTEN-ASHAMMAR"
    },
    {
      "value": "2050010",
      "label": "2050010 BULTEN-SVARTA"
    },
    {
      "value": "2050020",
      "label": "2050020 BUMAX A BUFAB COMPANY"
    },
    {
      "value": "2010070",
      "label": "2010070 BURCAS LTD."
    },
    {
      "value": "2010180",
      "label": "2010180 Baker&Finnemore Ltd. (TITGEMEYER Group)"
    },
    {
      "value": "1040010",
      "label": "1040010 CAMCAR DE MAXICO S.A. DE C.V."
    },
    {
      "value": "1010121",
      "label": "1010121 CAMCAR LLC SPENCER OPERATIONS"
    },
    {
      "value": "2010160",
      "label": "2010160 CAPARO ATLAS FASTENINGS"
    },
    {
      "value": "1020030",
      "label": "1020030 CAPTITAL METAL IND. CORP."
    },
    {
      "value": "2070000",
      "label": "2070000 CARBIDE TOOLSNORDEN OY AB-TENALA"
    },
    {
      "value": "2010240",
      "label": "2010240 CHARTER AUTOMOTIVE"
    },
    {
      "value": "1015010",
      "label": "1015010 CHARTER AUTOMOTIVE"
    },
    {
      "value": "6060050",
      "label": "6060050 CHARTER AUTOMOTIVE LLC.-CHANGZHOU"
    },
    {
      "value": "1010620",
      "label": "1010620 CHARTER AUTOMOTIVE, LLC."
    },
    {
      "value": "1030090",
      "label": "1030090 CHUN ZU DO BRASIL"
    },
    {
      "value": "2100010",
      "label": "2100010 CIE ZDANICE S.R.O."
    },
    {
      "value": "5010080",
      "label": "5010080 CK TECHNO"
    },
    {
      "value": "1031010",
      "label": "1031010 COLAR PARAFUSOS-REBITES"
    },
    {
      "value": "1010180",
      "label": "1010180 COLD HEADING - FREMONT HD"
    },
    {
      "value": "1010190",
      "label": "1010190 COLD HEADING-CLEVELAND"
    },
    {
      "value": "1010180",
      "label": "1010180 COLD HEADING-FREMONT HD"
    },
    {
      "value": "1010330",
      "label": "1010330 Celo USA-TRIDENT FASTENERS INC."
    },
    {
      "value": "1010780",
      "label": "1010780 Chicago Rivet & Machine Co."
    },
    {
      "value": "2010210",
      "label": "2010210 Clevtec"
    },
    {
      "value": "1010540",
      "label": "1010540 Coldforming Group LLC"
    },
    {
      "value": "1010010",
      "label": "1010010 DECKER MANUFACTURING CORP."
    },
    {
      "value": "2020010",
      "label": "2020010 DEEPAK FASTENERS (SHANNON) LTD. SHANNON INDUSTRIAL ESTATE"
    },
    {
      "value": "2030016",
      "label": "2030016 DEFREMM S.P.A LECCO-ITALY"
    },
    {
      "value": "5010280",
      "label": "5010280 DENSO"
    },
    {
      "value": "1010360",
      "label": "1010360 DEXTECH FASTENER TECHNOLOGIES."
    },
    {
      "value": "1010401",
      "label": "1010401 DIRKSEN SCREW PRODUCTS CO."
    },
    {
      "value": "1010401",
      "label": "1010401 DIRKSEN SCREW PRODUCTS CO.,"
    },
    {
      "value": "2190010",
      "label": "2190010 DIV Group"
    },
    {
      "value": "1010530",
      "label": "1010530 DOKKA FASTENERS"
    },
    {
      "value": "1031019",
      "label": "1031019 DOUBLE FASTENER"
    },
    {
      "value": "2020010",
      "label": "2020010 Deepak Fasteners (Shannon) Ltd."
    },
    {
      "value": "2190010",
      "label": "2190010 Div d.o.o. tvornica vijaka"
    },
    {
      "value": "2080250",
      "label": "2080250 EICHSFELDER SCHRAUBENWERK (ESW)"
    },
    {
      "value": "6020060",
      "label": "6020060 EJOT"
    },
    {
      "value": "2085020",
      "label": "2085020 EJOT GmbH & Co.KG"
    },
    {
      "value": "5050050",
      "label": "5050050 EJOT TEZMAK BAGLANTI ELEMANLARI"
    },
    {
      "value": "1010170",
      "label": "1010170 ELGIN  FASTENERS BEREA PLANT"
    },
    {
      "value": "1010030",
      "label": "1010030 EMHART TEKNOLOGIES INC.(MONTPELIER)"
    },
    {
      "value": "1010031",
      "label": "1010031 EMHART TEKNOLOGIES LLC"
    },
    {
      "value": "1010032",
      "label": "1010032 EMHART TEKNOLOGIES-CHESTERFIELD"
    },
    {
      "value": "1010031",
      "label": "1010031 EMHART-HOPKINSVILLE"
    },
    {
      "value": "1010740",
      "label": "1010740 ENGINEERED PARTS SOURCING"
    },
    {
      "value": "1010740",
      "label": "1010740 ENGINEERED PARTS SOURCING."
    },
    {
      "value": "2080190",
      "label": "2080190 ESKA CHEMNITZ"
    },
    {
      "value": "1031015",
      "label": "1031015 ESSEBI"
    },
    {
      "value": "1010270",
      "label": "1010270 FABRISTEEL TAYLOR MANUFACTURING"
    },
    {
      "value": "1010240",
      "label": "1010240 FASTCO INDUSTRIES INC."
    },
    {
      "value": "2080060",
      "label": "2080060 FASTENRATH BEFESTIGUNGSTECHNIK GMBH"
    },
    {
      "value": "2030120",
      "label": "2030120 FCF FONTANAFREDDO COLD FORGING S.R.L."
    },
    {
      "value": "2010040",
      "label": "2010040 FEDERAL MOGUL BRADFORD LTD."
    },
    {
      "value": "1010021",
      "label": "1010021 FEDERAL SCREW WORKS (TRAVERSE CITY)"
    },
    {
      "value": "1010020",
      "label": "1010020 FEDERAL SCREW WORKS ROMULUS DIVISION"
    },
    {
      "value": "2080460",
      "label": "2080460 FEDERAL-MOGUL VALVETRAIN GmbH"
    },
    {
      "value": "2040030",
      "label": "2040030 FERRIERE DI STABIO"
    },
    {
      "value": "2080390",
      "label": "2080390 FISCHER REINACH"
    },
    {
      "value": "2080440",
      "label": "2080440 FIVES FCB"
    },
    {
      "value": "2080280",
      "label": "2080280 FLAIG & HOMMEL"
    },
    {
      "value": "2080280",
      "label": "2080280 FLAIG & HOMMEL GMBH"
    },
    {
      "value": "1031017",
      "label": "1031017 FLECHA INDUSTRIA E COMERCIO LTDA."
    },
    {
      "value": "1020040",
      "label": "1020040 FORMNET INC."
    },
    {
      "value": "2030070",
      "label": "2030070 FRIULSIDER S.P.A."
    },
    {
      "value": "7100132",
      "label": "7100132 FRONTEC"
    },
    {
      "value": "7109111",
      "label": "7109111 FRONTEC"
    },
    {
      "value": "2080430",
      "label": "2080430 FUCHS SCHRANBENWERKE"
    },
    {
      "value": "5010161",
      "label": "5010161 FUSERASI(三重)"
    },
    {
      "value": "5010162",
      "label": "5010162 FUSERASI(群馬)"
    },
    {
      "value": "2080510",
      "label": "2080510 Fa. HEIN"
    },
    {
      "value": "2040031",
      "label": "2040031 Ferriere di Stabio"
    },
    {
      "value": "1010810",
      "label": "1010810 Ferry Cap & Set Screw"
    },
    {
      "value": "2010160",
      "label": "2010160 GAPARO ATLAS FASTENINGS"
    },
    {
      "value": "2080220",
      "label": "2080220 GEBR KUNZE GMBH"
    },
    {
      "value": "2080450",
      "label": "2080450 GESIPA"
    },
    {
      "value": "2010080",
      "label": "2010080 GESIPA BLIND RIVETING SYSTEMS LTD."
    },
    {
      "value": "2090140",
      "label": "2090140 GEVELOT EXTRUSION LAVAL"
    },
    {
      "value": "2090141",
      "label": "2090141 GEVELOT-OFFRANVILLE PLANT"
    },
    {
      "value": "2030080",
      "label": "2030080 GI.DI MECCANICA SPA."
    },
    {
      "value": "2080560",
      "label": "2080560 GMAK KALTFORMTEILE"
    },
    {
      "value": "5040080",
      "label": "5040080 GRACE INFRRSTRUCTURE PRIVATE LIMITED."
    },
    {
      "value": "1010630",
      "label": "1010630 GULF FASTENER, INC."
    },
    {
      "value": "2170010",
      "label": "2170010 GYURO TECHNIK KFT"
    },
    {
      "value": "1010730",
      "label": "1010730 General Plug & Manufacturing CO."
    },
    {
      "value": "1010420",
      "label": "1010420 H & L TOOL COMPANY, INC."
    },
    {
      "value": "2200010",
      "label": "2200010 HAFELE BERLIN"
    },
    {
      "value": "1030100",
      "label": "1030100 HASSMANN"
    },
    {
      "value": "1010550",
      "label": "1010550 HEAD SET SOCKETS"
    },
    {
      "value": "1010230",
      "label": "1010230 HEADER PRODUCTS,INC"
    },
    {
      "value": "1031013",
      "label": "1031013 HERAL S/A INDUSTRIA METALURGICA"
    },
    {
      "value": "1010320",
      "label": "1010320 HERITAGE TOOLING SERVICES LTD."
    },
    {
      "value": "2080010",
      "label": "2080010 HERMANN WINKER GMBH & CO.KG."
    },
    {
      "value": "7110620",
      "label": "7110620 HEWI(台灣)"
    },
    {
      "value": "5010040",
      "label": "5010040 HI TEN"
    },
    {
      "value": "5010400",
      "label": "5010400 HI-DIE工業"
    },
    {
      "value": "5010401",
      "label": "5010401 HI-DIE工業-阪村產業(阪東)"
    },
    {
      "value": "1010660",
      "label": "1010660 HOWMET FASTENING SYSTEMS - Industrial Products Division"
    },
    {
      "value": "1010550",
      "label": "1010550 Head Set Sockets,Inc."
    },
    {
      "value": "2180010",
      "label": "2180010 Hilti Aktiengesellschaft"
    },
    {
      "value": "2010110",
      "label": "2010110 Howmet Fastening Systems"
    },
    {
      "value": "2080220",
      "label": "2080220 IBEX COMPONENTS GMBH"
    },
    {
      "value": "1010450",
      "label": "1010450 IDEAL-TRIDON"
    },
    {
      "value": "2080500",
      "label": "2080500 IFU"
    },
    {
      "value": "2010020",
      "label": "2010020 IMI YOURKSHIRE FITTINGS LIMITED."
    },
    {
      "value": "6010000",
      "label": "6010000 INDUCON"
    },
    {
      "value": "1010800",
      "label": "1010800 INDUSTRIAL MACHINE DOCTOR"
    },
    {
      "value": "1020020",
      "label": "1020020 INFASCO TORONTO (INFASCO NUT DIV IVACO INC.)"
    },
    {
      "value": "1010040",
      "label": "1010040 INFASTECH DECORAH LLC"
    },
    {
      "value": "4010020",
      "label": "4010020 INTERFIT IMPORT-EXPORT"
    },
    {
      "value": "1010131",
      "label": "1010131 ITW LANCASTER"
    },
    {
      "value": "1010130",
      "label": "1010130 ITW SHAKEPROOF (DARLINGTON)"
    },
    {
      "value": "1010132",
      "label": "1010132 ITW-ELGIN"
    },
    {
      "value": "1010133",
      "label": "1010133 ITW-WATERTOWN"
    },
    {
      "value": "1010710",
      "label": "1010710 Internation Welding Technologies Inc."
    },
    {
      "value": "1020060",
      "label": "1020060 J-TECH DESIGN, LTD."
    },
    {
      "value": "2080070",
      "label": "2080070 J. VOM CLEFF A.SOHN"
    },
    {
      "value": "1010090",
      "label": "1010090 JACOBSON MFG LLC"
    },
    {
      "value": "1010210",
      "label": "1010210 JACOBSON MFG-TIFFIN,LLC"
    },
    {
      "value": "5010220",
      "label": "5010220 KAJIUME工業"
    },
    {
      "value": "6060060",
      "label": "6060060 KAMAX AUTOMOTIVE FASTENERS(CHINA) CO.LTD."
    },
    {
      "value": "1010610",
      "label": "1010610 KAMAX L.P."
    },
    {
      "value": "1040040",
      "label": "1040040 KAMAX MEXICO"
    },
    {
      "value": "2080170",
      "label": "2080170 KAMAX TOOLS & EQUIPMENT GMBH & CO.KG"
    },
    {
      "value": "2080170",
      "label": "2080170 KAMAX WERKE"
    },
    {
      "value": "2080170",
      "label": "2080170 KAMAX WERKL"
    },
    {
      "value": "2150010",
      "label": "2150010 KAMCO"
    },
    {
      "value": "2080240",
      "label": "2080240 KERB KONUS"
    },
    {
      "value": "1010670",
      "label": "1010670 KERR LAKES INC."
    },
    {
      "value": "1010670",
      "label": "1010670 KERR LAKESIDE INC."
    },
    {
      "value": "1010140",
      "label": "1010140 KEY FASTENERS CORPORATION."
    },
    {
      "value": "5010030",
      "label": "5010030 KOHWA 機工"
    },
    {
      "value": "2080020",
      "label": "2080020 KOLB-WUPPERTAL"
    },
    {
      "value": "5010310",
      "label": "5010310 KONDO"
    },
    {
      "value": "2030040",
      "label": "2030040 LA RO SRL"
    },
    {
      "value": "2080110",
      "label": "2080110 LAMISTAHL-NEUWIED"
    },
    {
      "value": "1020050",
      "label": "1020050 LELAND INDUSTRIES, INC."
    },
    {
      "value": "1031016",
      "label": "1031016 LICUV"
    },
    {
      "value": "2080470",
      "label": "2080470 LINAMAR VALVETRAIN GmbH"
    },
    {
      "value": "6020030",
      "label": "6020030 LISI AUTOMOTIVE (SHANGHAI) CO., LTD."
    },
    {
      "value": "2090120",
      "label": "2090120 LISI AUTOMOTIVE DELLE"
    },
    {
      "value": "2090130",
      "label": "2090130 LISI AUTOMOTIVE FORMER"
    },
    {
      "value": "2090042",
      "label": "2090042 LISI AUTOMOTIVE FORMER D.O. LURE"
    },
    {
      "value": "2090120",
      "label": "2090120 LISI AUTOMOTIVE FORMER DELLE"
    },
    {
      "value": "2090090",
      "label": "2090090 LISI AUTOMOTIVE FORMER MELISEY"
    },
    {
      "value": "2090060",
      "label": "2090060 LISI AUTOMOTIVE FORMER SAS"
    },
    {
      "value": "2090050",
      "label": "2090050 LISI AUTOMOTIVE FORMER USINE DE DASLE"
    },
    {
      "value": "5050020",
      "label": "5050020 LISI FASTENER TECHNOLOGY BESTAS"
    },
    {
      "value": "2080260",
      "label": "2080260 LISI GERMANY MOHR&FRIEDRICH"
    },
    {
      "value": "1020090",
      "label": "1020090 LISI HI-SHEAR CORPORATION"
    },
    {
      "value": "2080600",
      "label": "2080600 LISI KIERSPE"
    },
    {
      "value": "2080110",
      "label": "2080110 LS BOESNER GMBH"
    },
    {
      "value": "2080110",
      "label": "2080110 Lamistahl Boesner"
    },
    {
      "value": "2090020",
      "label": "2090020 Lisi Nomel"
    },
    {
      "value": "5040070",
      "label": "5040070 MAADHAV AUTOMOTIVE FASTENERS (P) LTD."
    },
    {
      "value": "1010080",
      "label": "1010080 MACLEAN FASTENERS-RICHMOND"
    },
    {
      "value": "1010080",
      "label": "1010080 MACLEAN FOGG  RICHMOND"
    },
    {
      "value": "1010110",
      "label": "1010110 MACLEAN SAEGERTOWN, LLC"
    },
    {
      "value": "1010050",
      "label": "1010050 MACLEAN-ESNA"
    },
    {
      "value": "2080540",
      "label": "2080540 MACLEAN-FOGG"
    },
    {
      "value": "1010070",
      "label": "1010070 MACLEAN-FOGG COMPANY"
    },
    {
      "value": "1010570",
      "label": "1010570 MACLEAN-SAEGRTOWN, LLC"
    },
    {
      "value": "1010000",
      "label": "1010000 MAJOR INDSTRIES NORTH AMERICA LTD."
    },
    {
      "value": "1010560",
      "label": "1010560 MARISA MANUFACTURING ., INC (BAE GROUP)"
    },
    {
      "value": "5010120",
      "label": "5010120 MARUYASU長野"
    },
    {
      "value": "1031018",
      "label": "1031018 MAX DEL"
    },
    {
      "value": "2090150",
      "label": "2090150 MECA-FORGING"
    },
    {
      "value": "1010520",
      "label": "1010520 METAL FLOW"
    },
    {
      "value": "6060030",
      "label": "6060030 METAL FLOW"
    },
    {
      "value": "1031011",
      "label": "1031011 METALTORK"
    },
    {
      "value": "1031009",
      "label": "1031009 METALURGICA FEY S.A."
    },
    {
      "value": "1030040",
      "label": "1030040 METALURGICA TUPA"
    },
    {
      "value": "1010160",
      "label": "1010160 MICHIGAN WIRE DIE COMPANY (MNP)"
    },
    {
      "value": "5010001",
      "label": "5010001 MIL JAPAN LTD."
    },
    {
      "value": "5010060",
      "label": "5010060 MIL TECH"
    },
    {
      "value": "5010270",
      "label": "5010270 MINAMIDA"
    },
    {
      "value": "5070010",
      "label": "5070010 MINAMIDA (THAILAND)"
    },
    {
      "value": "5010271",
      "label": "5010271 MINAMIDA(大分)"
    },
    {
      "value": "5010270",
      "label": "5010270 MINAMIDA(本社)"
    },
    {
      "value": "2080230",
      "label": "2080230 MN KALTFORMTEILE"
    },
    {
      "value": "1010160",
      "label": "1010160 MNP CORPORATION"
    },
    {
      "value": "1010161",
      "label": "1010161 MNP PRECISION PARTS, LLC"
    },
    {
      "value": "2080200",
      "label": "2080200 MOHLING"
    },
    {
      "value": "2080200",
      "label": "2080200 MOHLING GMBH & CO."
    },
    {
      "value": "1010271",
      "label": "1010271 MULTIFASTENER TOOLING DIVISION."
    },
    {
      "value": "1010060",
      "label": "1010060 MVS-ROYAL OAK"
    },
    {
      "value": "2080520",
      "label": "2080520 Mahle Motorenkomponenten GmbH"
    },
    {
      "value": "2030014",
      "label": "2030014 MdM CHATILLON"
    },
    {
      "value": "1010520",
      "label": "1010520 Metal Flow Corporation"
    },
    {
      "value": "2130010",
      "label": "2130010 NEDSCHROEF HELMOND B.V."
    },
    {
      "value": "2080370",
      "label": "2080370 NEDSCHROEF HERENTALS GmBH"
    },
    {
      "value": "2080370",
      "label": "2080370 NEDSCHROEF PLETTENBERG GmBH"
    },
    {
      "value": "2080050",
      "label": "2080050 NEDSCHROEF SCHROZBERG VERBINDUNGSTECHNIK"
    },
    {
      "value": "2130010",
      "label": "2130010 NEDSCHROEF, NETHERLANDS"
    },
    {
      "value": "2080040",
      "label": "2080040 NEDSCHROEF-BECKINGEN GMBH & CO.OHG"
    },
    {
      "value": "1010150",
      "label": "1010150 NET SHAPED SOLUTIONS"
    },
    {
      "value": "5010290",
      "label": "5010290 NICHIWA"
    },
    {
      "value": "6020040",
      "label": "6020040 NICHIWA (佛山日和汽車零件有限公司)"
    },
    {
      "value": "2010200",
      "label": "2010200 NON-STANDARD SOCKET SCREW LTD."
    },
    {
      "value": "5050030",
      "label": "5050030 NORM SOMUN SAN. VE TIC. A.S."
    },
    {
      "value": "2150030",
      "label": "2150030 NORMAL MET"
    },
    {
      "value": "1010150",
      "label": "1010150 NSS TECHNOLOGIES (PLYMOUTH)"
    },
    {
      "value": "1010100",
      "label": "1010100 NUCOR FASTENER"
    },
    {
      "value": "2080100",
      "label": "2080100 NUTAP SCHUHL & CO., LTD."
    },
    {
      "value": "7109310",
      "label": "7109310 NormCivata土耳其"
    },
    {
      "value": "1010790",
      "label": "1010790 North MS Tool and Die LLC"
    },
    {
      "value": "1010273",
      "label": "1010273 OAKLAND UNIVERSITY"
    },
    {
      "value": "2030015",
      "label": "2030015 OMEGA-IFS s.r.l."
    },
    {
      "value": "2160010",
      "label": "2160010 ORNIT BLIND RIVETS"
    },
    {
      "value": "2030110",
      "label": "2030110 OTERACCORDI S.P.A."
    },
    {
      "value": "1010460",
      "label": "1010460 Oconomowoc Manufacturing Corp"
    },
    {
      "value": "1010460",
      "label": "1010460 Oconomowoe Manufacturing Corp"
    },
    {
      "value": "2030050",
      "label": "2030050 PAOLO ASTORI SRL"
    },
    {
      "value": "2150020",
      "label": "2150020 PAPADELTA LIMITED"
    },
    {
      "value": "1030070",
      "label": "1030070 PARASMO"
    },
    {
      "value": "1010040",
      "label": "1010040 PARKER-KALON"
    },
    {
      "value": "2110010",
      "label": "2110010 PECOL 2-COMPONENTES I INDUSTRIASI,LDA."
    },
    {
      "value": "2110010",
      "label": "2110010 PECOL AUTOMOTIVE, S. A."
    },
    {
      "value": "6060010",
      "label": "6060010 PENNENGINEERING AUTOMOTIVE"
    },
    {
      "value": "2010250",
      "label": "2010250 PENNENGINEERING FASTENING TECHNOLOGIES(EUROPE)LTD."
    },
    {
      "value": "2010010",
      "label": "2010010 PHILIDAS LIMITED"
    },
    {
      "value": "5010560",
      "label": "5010560 PLAZWIRE CO., LTD."
    },
    {
      "value": "6030030",
      "label": "6030030 PPM (富泰和) MANUFACTURING CO. LTD."
    },
    {
      "value": "1010280",
      "label": "1010280 PRECISION FITTINGS INC."
    },
    {
      "value": "2080141",
      "label": "2080141 PROFIL GmbH & Co. KG"
    },
    {
      "value": "2010270",
      "label": "2010270 PSM International Fasteners Ltd."
    },
    {
      "value": "2080220",
      "label": "2080220 PWK IBEX GMBH"
    },
    {
      "value": "1010271",
      "label": "1010271 Penn AuTOMOTIVE Waterford"
    },
    {
      "value": "1010271",
      "label": "1010271 Penn Automotive Waterford"
    },
    {
      "value": "1010270",
      "label": "1010270 Penn Automotive Waterford"
    },
    {
      "value": "1010440",
      "label": "1010440 Penn Engineering Danboro"
    },
    {
      "value": "2080570",
      "label": "2080570 Prepart"
    },
    {
      "value": "1010500",
      "label": "1010500 QUALITY FORM TOOLS"
    },
    {
      "value": "1010580",
      "label": "1010580 RAMCO SPECIALTIES"
    },
    {
      "value": "1010580",
      "label": "1010580 RAMCO SPECIALTIES INC."
    },
    {
      "value": "5040060",
      "label": "5040060 RAMSAYS CORPORATION"
    },
    {
      "value": "1020010",
      "label": "1020010 RB&W OF CANADA LTD."
    },
    {
      "value": "1020070",
      "label": "1020070 READY RIVET & FASTENER LTD."
    },
    {
      "value": "2100010",
      "label": "2100010 RECYDE ZDANICE SRO"
    },
    {
      "value": "5010070",
      "label": "5010070 REES-WORKS"
    },
    {
      "value": "2080150",
      "label": "2080150 REISSER SCHRAUBENTECHNIK"
    },
    {
      "value": "2080160",
      "label": "2080160 RIBE (RICHARD BERGNER GMBH&CO.KG)"
    },
    {
      "value": "1010120",
      "label": "1010120 RING SCREW LLC"
    },
    {
      "value": "1010122",
      "label": "1010122 RING SCREW LLC. STERLING HEIGHTS OPERATIONS."
    },
    {
      "value": "2080550",
      "label": "2080550 ROTO FRANK AG"
    },
    {
      "value": "1010720",
      "label": "1010720 Right Way Fastener"
    },
    {
      "value": "5010450",
      "label": "5010450 S.N TECH"
    },
    {
      "value": "1010200",
      "label": "1010200 SAFETY SOCKET SCREW CORP. (BLUE DEVEL)"
    },
    {
      "value": "5010260",
      "label": "5010260 SAGA STANPING"
    },
    {
      "value": "5010020",
      "label": "5010020 SAKAMURA TECH"
    },
    {
      "value": "2090160",
      "label": "2090160 SAMAT"
    },
    {
      "value": "5010000",
      "label": "5010000 SAN ALLOY INDUSTRY CO., LTD.(本社)"
    },
    {
      "value": "5050000",
      "label": "5050000 SANTECH INDUSTRIAL TECHNOLOGIES"
    },
    {
      "value": "5050040",
      "label": "5050040 SARSILMAZ PATLAYICI SANAYI"
    },
    {
      "value": "2080130",
      "label": "2080130 SCHURMANN & HILLEKE, NEUENRADE"
    },
    {
      "value": "1010370",
      "label": "1010370 SCME SCREW CO."
    },
    {
      "value": "2080530",
      "label": "2080530 SEISSENSCHMIDT AG"
    },
    {
      "value": "2010080",
      "label": "2010080 SFS Group Fastening Technology Ltd."
    },
    {
      "value": "5010470",
      "label": "5010470 SIGMA"
    },
    {
      "value": "6060040",
      "label": "6060040 SIGMA PRECISE MACHINERY(JIANGSU) CO., LTD."
    },
    {
      "value": "1020080",
      "label": "1020080 SIGMA TOOL & MACHINE LTD."
    },
    {
      "value": "6020071",
      "label": "6020071 SINGU KELLER"
    },
    {
      "value": "2050040",
      "label": "2050040 SKF"
    },
    {
      "value": "2150040",
      "label": "2150040 SKF-POLAND"
    },
    {
      "value": "2130010",
      "label": "2130010 SMF TOOLS, NETHERLANDS"
    },
    {
      "value": "1010470",
      "label": "1010470 SMW MANUFACTURING"
    },
    {
      "value": "1010470",
      "label": "1010470 SMW Manufacturing,INC."
    },
    {
      "value": "1010490",
      "label": "1010490 SNAP-ON"
    },
    {
      "value": "1010470",
      "label": "1010470 SPARTAN INDUSTRIAL SUPPLY CO."
    },
    {
      "value": "1010190",
      "label": "1010190 SPS TECHNOLOGIES CLEVELAND"
    },
    {
      "value": "1010820",
      "label": "1010820 STABIO NORTH AMERICA"
    },
    {
      "value": "2080140",
      "label": "2080140 STABO (PROFIL)"
    },
    {
      "value": "2150020",
      "label": "2150020 STALMAX JEZ STANISLAW"
    },
    {
      "value": "1010033",
      "label": "1010033 STANLEY BLACK & DECKER (HELI-COIL)"
    },
    {
      "value": "1010030",
      "label": "1010030 STANLEY ENGINEERED FASTENING"
    },
    {
      "value": "1010032",
      "label": "1010032 STANLEY ENGINEERED FASTENING-CHESTERFIELD"
    },
    {
      "value": "1010031",
      "label": "1010031 STANLEY ENGINEERED FASTENING-HOPKINSVILLE"
    },
    {
      "value": "1010030",
      "label": "1010030 STANLEY ENGINEERED FASTENING-MONTPELIER"
    },
    {
      "value": "2080050",
      "label": "2080050 SUKOSIM-TEXTRON VERBINDUNGSTECHNIK"
    },
    {
      "value": "5040030",
      "label": "5040030 SUPER AUTO FORGE"
    },
    {
      "value": "5040010",
      "label": "5040010 SWADESH ENGINEERING IND."
    },
    {
      "value": "1031002",
      "label": "1031002 SWT-BOLLHOFF"
    },
    {
      "value": "1031005",
      "label": "1031005 SWT-FEX INDUSTRIAL REX LTDA."
    },
    {
      "value": "1031006",
      "label": "1031006 SWT-FIXAR"
    },
    {
      "value": "1031007",
      "label": "1031007 SWT-INGEPAL"
    },
    {
      "value": "1031000",
      "label": "1031000 SWT-Industria e Comercio de Ferramentas Ltda."
    },
    {
      "value": "1031008",
      "label": "1031008 SWT-MAPRI (TEXTRON)"
    },
    {
      "value": "1031003",
      "label": "1031003 SWT-METALAC"
    },
    {
      "value": "1031001",
      "label": "1031001 SWT-NORFLEX"
    },
    {
      "value": "1010750",
      "label": "1010750 Seaway Bolt & Specials Corp."
    },
    {
      "value": "1010600",
      "label": "1010600 Shively Bros., Inc."
    },
    {
      "value": "1010600",
      "label": "1010600 Shively Brothers, Inc."
    },
    {
      "value": "2010120",
      "label": "2010120 T.J.BROOKS LTD. (SPS TECHNOLOGIES)"
    },
    {
      "value": "5010050",
      "label": "5010050 TAKEDA TOOLS"
    },
    {
      "value": "1010460",
      "label": "1010460 TALMA FASTENER, DIV. OF OCONOMOWOC MFG."
    },
    {
      "value": "1010260",
      "label": "1010260 TCR ENGINEERED COMPONENTS."
    },
    {
      "value": "2030000",
      "label": "2030000 TDE SRL"
    },
    {
      "value": "1010170",
      "label": "1010170 TELEFAST INDUSTRIES INC."
    },
    {
      "value": "2090040",
      "label": "2090040 TEXTRON FASTENING SYSTEMS SITE DE VIEUX CONDE"
    },
    {
      "value": "2090020",
      "label": "2090020 TEXTRON FASTENING SYSTEMS-SITE D'AMIENS"
    },
    {
      "value": "2080110",
      "label": "2080110 TEXTRON-NEUWIED"
    },
    {
      "value": "1020100",
      "label": "1020100 TFI"
    },
    {
      "value": "1010180",
      "label": "1010180 THE COLD HEADING CO-STG DIVISION"
    },
    {
      "value": "2010000",
      "label": "2010000 THOR ELECTRICAL"
    },
    {
      "value": "2085010",
      "label": "2085010 TIGGES GmbH & Co.KG"
    },
    {
      "value": "2010180",
      "label": "2010180 TITGEMEYER (UK) LTD."
    },
    {
      "value": "2010220",
      "label": "2010220 TOOLING INTERNATIONAL LIMITED"
    },
    {
      "value": "1010121",
      "label": "1010121 TOWNSEND ENGINEERED PRODUCTS, CAMCAR TEXTRON SPENCER, TN"
    },
    {
      "value": "1010330",
      "label": "1010330 TRIDENT FASTENERS INC."
    },
    {
      "value": "2080460",
      "label": "2080460 TRW AUTOMOTIVE GMBH"
    },
    {
      "value": "5010550",
      "label": "5010550 TSUBAKIMOTO CHAIN CO."
    },
    {
      "value": "2010060",
      "label": "2010060 TUCKER FASTENERS LTD."
    },
    {
      "value": "2080270",
      "label": "2080270 TUCKER GMBH (EMHARDT GERMANY)"
    },
    {
      "value": "2040010",
      "label": "2040010 UMICORE STRUB AG."
    },
    {
      "value": "3010010",
      "label": "3010010 UNBRAKO PTY LTD."
    },
    {
      "value": "1030060",
      "label": "1030060 UNIFAP"
    },
    {
      "value": "2030060",
      "label": "2030060 UNIFAST"
    },
    {
      "value": "2030013",
      "label": "2030013 URSUS VIA A.MANZAAI"
    },
    {
      "value": "1010650",
      "label": "1010650 VAMP COMPANY"
    },
    {
      "value": "1010300",
      "label": "1010300 VICO PRODUCTS COMPANY"
    },
    {
      "value": "2030011",
      "label": "2030011 VITOP SRL"
    },
    {
      "value": "2080480",
      "label": "2080480 WALTER SCHNEIDER GmbH"
    },
    {
      "value": "1010380",
      "label": "1010380 WC AVALON WC Mfg."
    },
    {
      "value": "2080040",
      "label": "2080040 WHITESELL GLOBAL TECHNOLOGLES ACUMENT GMBH & CO.OHG"
    },
    {
      "value": "2080030",
      "label": "2080030 WHITESELL GMBH & CO. OHG"
    },
    {
      "value": "1010274",
      "label": "1010274 WHITESELL PRECISION COMPONENTS INC."
    },
    {
      "value": "2080050",
      "label": "2080050 WHITESELL SCHROZBERG VERBINDUNGSTECHNIK"
    },
    {
      "value": "2080120",
      "label": "2080120 WILLI HAHN"
    },
    {
      "value": "2080320",
      "label": "2080320 WSH-SCREWS"
    },
    {
      "value": "2090140",
      "label": "2090140 Walor Laval-Gevelot Extrusion"
    },
    {
      "value": "1010380",
      "label": "1010380 Whitesell Corporation"
    },
    {
      "value": "5070030",
      "label": "5070030 YAHATA FASTENER THAI CO., LTD."
    },
    {
      "value": "5010150",
      "label": "5010150 YAMANAKA"
    },
    {
      "value": "2030012",
      "label": "2030012 Z.M.C"
    },
    {
      "value": "2080260",
      "label": "2080260 ZB MOHR&FRIEDRICH GMBH"
    },
    {
      "value": "5050040",
      "label": "5050040 ZSR Patlayici Sanayi A.S."
    },
    {
      "value": "2050080",
      "label": "2050080 iPm Tool ab"
    },
    {
      "value": "5010170",
      "label": "5010170 九州FUSERASI"
    },
    {
      "value": "5010580",
      "label": "5010580 九菱NUT"
    },
    {
      "value": "5010410",
      "label": "5010410 三協商事"
    },
    {
      "value": "6020070",
      "label": "6020070 上海宏勵金屬成型科技有限公司"
    },
    {
      "value": "7110160",
      "label": "7110160 上滿國際有限公司"
    },
    {
      "value": "5010240",
      "label": "5010240 丸榮宮崎"
    },
    {
      "value": "5010520",
      "label": "5010520 山口NUT"
    },
    {
      "value": "6080010",
      "label": "6080010 山口制作(大連)鍛壓部件有限公司 YAMAGUCHI SEISAKU FORGE PARTS CO.,LTD."
    },
    {
      "value": "5010510",
      "label": "5010510 山口製作所"
    },
    {
      "value": "5010230",
      "label": "5010230 中國精螺"
    },
    {
      "value": "5070020",
      "label": "5070020 中國精螺(Thailand)"
    },
    {
      "value": "6060070",
      "label": "6060070 內德史羅夫緊固件(昆山)有限公司"
    },
    {
      "value": "7050010",
      "label": "7050010 天源義記機械股份有限公司"
    },
    {
      "value": "7020050",
      "label": "7020050 台灣椿本股份有限公司"
    },
    {
      "value": "5010460",
      "label": "5010460 永山電子工業(株)"
    },
    {
      "value": "7109115",
      "label": "7109115 永信金屬"
    },
    {
      "value": "5010180",
      "label": "5010180 光洋產業舍"
    },
    {
      "value": "7010070",
      "label": "7010070 光輝合金工業股份有限公司"
    },
    {
      "value": "7110560",
      "label": "7110560 全景工業有限公司"
    },
    {
      "value": "5010350",
      "label": "5010350 共立精機"
    },
    {
      "value": "7110640",
      "label": "7110640 至盈實業股份有限公司"
    },
    {
      "value": "5010253",
      "label": "5010253 佐賀鐵大町"
    },
    {
      "value": "5010254",
      "label": "5010254 佐賀鐵工"
    },
    {
      "value": "5010250",
      "label": "5010250 佐賀鐵工(本社)"
    },
    {
      "value": "5010252",
      "label": "5010252 佐賀鐵工(多久)"
    },
    {
      "value": "6020070",
      "label": "6020070 宏勵技術"
    },
    {
      "value": "5010010",
      "label": "5010010 阪村產業"
    },
    {
      "value": "5010011",
      "label": "5010011 阪村產業"
    },
    {
      "value": "7110530",
      "label": "7110530 易登盛實業有限公司"
    },
    {
      "value": "5010192",
      "label": "5010192 松本重工業(吳)"
    },
    {
      "value": "5010191",
      "label": "5010191 松本重工業(音戶)"
    },
    {
      "value": "5010590",
      "label": "5010590 松尾"
    },
    {
      "value": "5010570",
      "label": "5010570 長野製作所"
    },
    {
      "value": "5010490",
      "label": "5010490 昭和金屬工業(株)"
    },
    {
      "value": "5010202",
      "label": "5010202 音戶工作所(八本松)"
    },
    {
      "value": "5010201",
      "label": "5010201 音戶工作所(柳井)"
    },
    {
      "value": "5010203",
      "label": "5010203 音戶工作所(雙見)"
    },
    {
      "value": "5010204",
      "label": "5010204 音戶神商精工(南通)"
    },
    {
      "value": "7020060",
      "label": "7020060 倍騰國際股份有限公司"
    },
    {
      "value": "7110600",
      "label": "7110600 峻峰企業社"
    },
    {
      "value": "7110321",
      "label": "7110321 晉緯螺絲股份有限公司"
    },
    {
      "value": "5010003",
      "label": "5010003 株式會社 MIL"
    },
    {
      "value": "5010202",
      "label": "5010202 株式會社ONDO(八本松)"
    },
    {
      "value": "5010201",
      "label": "5010201 株式會社ONDO(柳井)"
    },
    {
      "value": "7010080",
      "label": "7010080 純裕工業有限公司"
    },
    {
      "value": "5010610",
      "label": "5010610 能登株式會社"
    },
    {
      "value": "6030010",
      "label": "6030010 高科技螺絲"
    },
    {
      "value": "7109110",
      "label": "7109110 崇曜國際實業有限公司"
    },
    {
      "value": "6030020",
      "label": "6030020 深圳威賽科技 (VEXCELL)"
    },
    {
      "value": "7010010",
      "label": "7010010 琛元企業有限公司"
    },
    {
      "value": "7100134",
      "label": "7100134 進合"
    },
    {
      "value": "7110580",
      "label": "7110580 新倡發工業股份有限公司"
    },
    {
      "value": "6040030",
      "label": "6040030 寧波宏業金屬"
    },
    {
      "value": "6040020",
      "label": "6040020 寧波固遠管健有限公司"
    },
    {
      "value": "7110610",
      "label": "7110610 綱利興有限公司"
    },
    {
      "value": "7110080",
      "label": "7110080 豪茂螺帽工廠股份有限公司"
    },
    {
      "value": "7110590",
      "label": "7110590 德商殷士諦股份有限公司(台灣分公司)"
    },
    {
      "value": "7110500",
      "label": "7110500 盧森堡商司普斯國際有限公司台灣分公司"
    },
    {
      "value": "7110090",
      "label": "7110090 諾雅科技有限公司"
    },
    {
      "value": "7050030",
      "label": "7050030 豐德興業股份有限公司"
    },
    {
      "value": "7110280",
      "label": "7110280 鑫達精密科技有限公司"
    }
  ]
  var outsourcerTags = [

  ];
  var url = new URL(window.location.href);
  var id = url.searchParams.get("id");
  // var file_id_dest = url.searchParams.get("file_id_dest");
  var file_id_dest = url.searchParams.get("id");
  var module_id;
  var module_name = '業務';
  var allState = [];
  let RD_cost = 0;
  let PM_outsources = 0;

  $(function() {
    $('#WeightConverter').attr('file_id',id);
    import("/static/weightConverter/js/main.a12e174d.js");
    getModule();
    getCheckQuotation();
    getFinishInformation();
    getappraisalSummary();
    getDiscriptOther();
    getOutsourcer();
    getAllOutsourcer();
    getAllcomment()
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
      getComment();

      getFilter()
    }
    getFileOutsourcer();


    $(document).on('change', '[name="inputpdfemail"][data-type="material"],[name="inputpdfemail"][data-type="titanizing"]', function(e) {
      e.preventDefault();
      inUpdateCustomer();
    })

    $(document).on('submit', '#formQuotation', function(e) {
      e.preventDefault();
      inUpdateQuotation();
    })

    $(document).on('submit', '#formOutsourcer', function(e) {
      e.preventDefault();
      inUpdateOutsourcer();
      getFileOutsourcerCount();
    })





  });


  function inUpdateCustomer() {
    $.ajax({
      url: `/file/customer/send`,
      type: 'patch',
      data: {
        titanizing: $('[name="inputpdfemail"][data-type="titanizing"]').val(),
        material: $('[name="inputpdfemail"][data-type="material"]').val(),
        file_id: id,

        // deadline:$('#inputDeadline').val().replace('T',' ')
      },
      dataType: 'json',
      success: function(response) {}
    });
  }

  function getappraisalSummary() {
    window.sharedVariable = {
      file_id: id,
      file_id_dest: file_id_dest,
      module_name: '業務'
    };
    $("#appraisalSummary").load(`/discript/appraisalSummary`);
  }

  function getDiscriptOther() {
    window.sharedVariable = {
      file_id: id,
      module_name: '業務',
      module_id: module_id
    };
    $("#discriptOther").load(`/discript/newother`);
  }



  function getAllcomment() {
    let allcomment = '';
    let countallcomment = 0;
    $.ajax({
      url: `/processes/crop/${id}`,
      type: 'get',
      success: function(response) {
        processArr = response.process
        $.each(processArr, function(key, value) {
          process_id = processArr[key];
          setTimeout(getcomment(process_id), 3000)

        })

        function getcomment(process_id) {
          console.log('getcomment' + process_id)
          $.ajax({
            url: `/components/Match/${process_id}`,
            type: 'get',
            data: {
              threshold: 0,
              amount: 10,
              module_name: '業務'
            },
            success: function(response) {
              $(response.result).each(function(index) {
                if (this.comment != null && this.comment != '') {
                  allcomment += `${this.comment}、`
                  countallcomment += 1;
                }
              });
              if (countallcomment > 0) {
                allcomment = allcomment.slice(0, -1)
              }
              countallcomment = 0;
              $('#allcomment').html(allcomment == '' ? '尚未有任何註記' : allcomment)

            }
          });
        }
      }
    });
  }

  function getCheckQuotation() {
    window.sharedVariable = {
      file_id: id,
      file_id_dest: file_id_dest,
      module_name: '業務',
      type: 'finish'
    };
    $("#checkQuotaton").load(`/quotation/check`);
  }


  $('#exampleModal').on('show.bs.modal', function(e) {

    $('#exampleModal .modal-footer').html(`<button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>`);
    $("#exampleModal .modal-dialog ").attr("class", "modal-dialog modal-xl");

    // $('#exampleModal .modal-footer').html(basicModalFooter);
    var type = $(e.relatedTarget).data('type');
    // console.log(type);

    if (type == 'generateQuotation') {
      generateQuotationModal(type);
    }


  });

  $('#emailForm').on('submit', function(e) {
    e.preventDefault();
    $('#exampleModal .modal-footer').html(`<button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>`);
    $("#exampleModal .modal-dialog ").attr("class", "modal-dialog modal-xl");
    $("#exampleModal").modal('show')
    generateQuotationModal($(this).data('type'))
  })

  function generateQuotationModal(type) {
    arr_radioItemNO = []
    $('#exampleModal .modal-title').html(`產生報價單`);
    $('#exampleModal .modal-body').html(`
    <table class="table table-borderless" id="generatedataTable" width=100%>
      <thead>
        <tr>
          <th>#</th>
          <th>開單日期</th>
          <th>客戶圖號</th>
          <th>客戶名稱</th>
        </tr>
      </thead>
    </table>
    `);
    let setting_business = JSON.parse(JSON.stringify(setting));
    $('#generatedataTable').DataTable(setting_business).destroy();

    setting_business['ajax'] = {
      url: `/files/sameCustomer`,
      type: 'get',
      "data": function(d) {
        d.id = id,
          d.module_id = 1,
          d.starttime = '',
          d.endtime = ''
      }
    };
    setting_business['processing'] = true;
    setting_business['serverSide'] = true;
    setting_business['createdRow'] = function(row, data, dataIndex) {
      // $(row).attr('onclick', `inLoad(${data['id']})`);
      $(row).attr('style', `cursor:pointer`);
    };

    setting_business['columns'] = [{
        "data": null,
        render: function(data, type, row, meta) {
          return `<input type="checkbox" value=${data['id']} ${arr_radioItemNO.includes(data['id']+"")?'checked':''} name="checkboxmakepdf" aria-label="Checkbox for following text input"> `;
        }
      }, {
        "data": "upload_time"
      },
      {
        "data": "order_name"
      }, {
        "data": "customer_code",
      },
    ];
    $('#generatedataTable').DataTable(setting_business);
    if (type == 'generateQuotation') {
      $('#exampleModal .modal-footer').append(`<button type="button" class="btn btn-primary" onclick=" makepdf()">下一步</button>`);

    } else {
      $('#exampleModal .modal-footer').append(`<button type="button" class="btn btn-primary" onclick="sendpdf()">下一步</button>`);
    }
    // generateQuotation()

  }

  function sendpdf() {
    let param = arr_radioItemNO;
    // $('[name="checkboxmakepdf"]:checked').each(function(){
    //   param.push($(this).val())
    //   // param+=`id[]=${$(this).val()}&`;
    // });
    $.ajax({
      url: `/quotation/pdf/email`,
      type: 'post',
      data: {
        email: $('[name="inputpdfemail"][data-type="email"]').val(),
        content: '',
        message: '',
        id: param,
        file_id: id
        // deadline:$('#inputDeadline').val().replace('T',' ')
      },
      dataType: 'json',
      success: function(response) {
        $('#exampleModal .modal-title').html('')
        $('#exampleModal .modal-body').html('寄信完成！')
        $('#exampleModal .modal-footer').html(`
          <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
            <button type="button" class="btn btn-primary"  data-dismiss="modal">確認</button>`)
        $('#exampleModal').modal('show')
      }
    })
    // param = param.slice(0, -1)
    // param = JSON.stringify(param);
    // setTimeout(function(){
    //       window.location.href=`/quotation/pdf/${id}?id=${param}`;
    // },3000);
  }

  function makepdf() {
    let param = arr_radioItemNO;
    // $('[name="checkboxmakepdf"]:checked').each(function(){
    // param.push($(this).val())
    // param+=`id[]=${$(this).val()}&`;
    // });
    // param = param.slice(0, -1)
    param = JSON.stringify(param);
    setTimeout(function() {
      window.location.href = `/quotation/pdf/${id}?id=${param}&file_id=${id}&type=${$('[data-type="discription"]:checked').val()}`;
    }, 3000);



  }

  function generateQuotation() {
    $.ajax({
      url: `/quotation/pdf/${id}`,
      type: 'get',
      data: {
        file_id: id
      },
      dataType: 'json',
      success: function(response) {

      }
    });
  }



  $(document).on('change', '[name="temparyOutsourcer"]', function() {
    getFileOutsourcerCount();
  })
  $(document).on('change', '[name="historyOutsourcer"]', function() {
    getHistoryOutsourcerChart();
  })

  function getHistoryOutsourcerChart() {
    var start = $('[name="historyOutsourcer"][data-type="start"]').val().replace("T", " ")
    var end = $('[name="historyOutsourcer"][data-type="end"]').val().replace("T", " ")

    console.log(start, end)
    $.ajax({
      url: `/file/outsourcer/limittime`,
      type: 'get',
      data: {
        start: start,
        end: end
      },
      dataType: 'json',
      success: function(response) {
        // outsourcerChart.data.datasets.forEach((dataset) => {
        // dataset.data.pop();
        // });
        // outsourcerChart.update();

        outsourcerChart.destroy();

        outsourcerchartdata = {
          labels: labels,
          datasets: []
        }
        outsourcerchartconfig = {
          type: 'bar',
          data: outsourcerchartdata,
          options: {
            "responsive": true,
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
                  maxTicksLimit: 7,
                  display: false //this will remove only the label
                }
              }],
              yAxes: [{
                display: true,
                ticks: {
                  beginAtZero: true
                }
              }]
            }
          },
        };
        outsourcerChart = new Chart(
          document.getElementById('BarChart3'),
          outsourcerchartconfig
        );
        // console.log(outsourcerchartdata)


        $.each(response, function() {
          console.log(this)
          var newDataset = {
            label: this.name,
            data: [this.count],
            backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
            borderWidth: 1,
            datalabels: {
              anchor: 'end',
              align: 'start',
            }
          }
          outsourcerchartdata.datasets.push(newDataset);
          outsourcerChart.update();
        });


      }
    });

  }

  function getAllOutsourcer() {
    $.ajax({
      url: `/file/outsourcer/history`,
      type: 'get',
      data: {},
      dataType: 'json',
      success: function(response) {
        // outsourcerchartdata = {};

        $.each(response, function() {
          console.log(this)
          var newDataset = {
            label: this.name,
            data: [this.count],
            backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
            borderWidth: 1,
            datalabels: {
              anchor: 'end',
              align: 'start',
            }
          }
          outsourcerchartdata.datasets.push(newDataset);
          outsourcerChart.update();
        });


      }
    });
  }

  function getFileOutsourcer() {
    $.ajax({
      url: `/file/outsourcer`,
      type: 'get',
      data: {
        file_id: id,

      },
      dataType: 'json',
      success: function(response) {
        $.each(response, function() {
          $('#inputOutsourcer').val(this.name);
          $('#inputOutsourcerAmount').val(this.outsourcer_amount);
        });

        getFileOutsourcerCount();


      }
    })
  }

  function getFileOutsourcerCount() {
    var start = $('[name="temparyOutsourcer"][data-type="start"]').val().replace("T", " ")
    var end = $('[name="temparyOutsourcer"][data-type="end"]').val().replace("T", " ")
    $.ajax({
      url: `/file/outsourcer/count`,
      type: 'get',
      data: {
        outsourcer: $('#inputOutsourcer').val(),
        start: start,
        end: end

      },
      dataType: 'json',
      success: function(response) {
        temparyChart.destroy();

        temparydata = {
          labels: labels,
          datasets: []
        }
        temparyconfig = {
          type: 'bar',
          data: temparydata,
          options: {
            "responsive": true,
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
                  maxTicksLimit: 7,
                  display: false //this will remove only the label
                }
              }],
              yAxes: [{
                display: true,
                ticks: {
                  beginAtZero: true
                }
              }]
            }
          },
        };
        temparyChart = new Chart(
          document.getElementById('BarChart2'),
          temparyconfig
        );
        // console.log(outsourcerchartdata)


        $.each(response, function() {
          console.log(this)
          var newDataset = {
            label: this.name,
            data: [this.count],
            backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
            borderWidth: 1,
            datalabels: {
              anchor: 'end',
              align: 'start',
            }
          }
          temparydata.datasets.push(newDataset);
          temparyChart.update();
        });


      }
    })
  }


  function inUpdateOutsourcer() {
    let outsourcerName = $('#inputOutsourcer').val();
    let outsourcerAmount = $('#inputOutsourcerAmount').val();
    $.ajax({
      url: `/file/outsourcer`,
      type: 'post',
      data: {
        file_id: id,
        name: outsourcerName,
        amount: outsourcerAmount
      },
      dataType: 'json',
      success: function(response) {
        // getOutsourcer()
        if (!outsourcerTags.includes(outsourcerName)) {
          outsourcerTags.push(outsourcerName)
          $("#inputOutsourcer").autocomplete({
            source: outsourcerTags
          });
        }
        console.log(outsourcerChart)


      }
    });

  }

  function getOutsourcer() {
    $.ajax({
      url: `/setting/outsourcer`,
      type: 'get',
      data: {},
      dataType: 'json',
      success: function(response) {
        $.each(response, function() {
          outsourcerTags.push(this.name);
        })
        $("#inputOutsourcer").autocomplete({
          source: outsourcerTags
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
          }

        })
        console.log(module_id)
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



  function inUpdateQuotation() {
    // let last_quotation = $('[name="inputQuotation"][data-type="quotation"]').val();
    // let description = $('[name="inputQuotation"][data-type="description"]').val();
    // if (description == undefined){
    //   description=''
    // }

    var tmpArr = new Object();
    $('[name="inputQuotation"]').each(function() {
      if ($(this).val() == undefined) {
        tmpArr[$(this).data('type')] = '';
      } else {
        tmpArr[$(this).data('type')] = $(this).val();
      }

    });
    tmpArr['file_id'] = id;
    console.log(tmpArr)

    // console.log( $('[name="inputQuotation"][data-type="quotation"]'))
    // console.log(last_quotation,description)
    $.ajax({
      url: `/quotation`,
      type: 'post',
      dataType: 'json',
      data: tmpArr,
      success: function(response) {
        buttonPass()
      }
    });
  }

  function sendemail(modules) {
    let content = `報價編號${id} ${module_name}部門已完成填寫`;
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
        $('#basicModal').modal('hide');
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
        if (allState.includes('已上傳圖檔') && allState.includes('已查詢歷史訂單') && allState.includes('已全圖比對') && moduleArr.length > 0) {
          sendemail(moduleArr)
        } else {
          $('#basicModal').modal('hide');

        }
      }
    })
  }


  function getFilter() {

    var instanceYear = new SelectPure('#divYear', {
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
      }

    });

    var instanceMaterial = new SelectPure('#divMaterial', {
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
      }
    });

    var instanceTitanizing = new SelectPure('#divTitanizing', {
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
      }
    });
    var instanceHardness = new SelectPure('#divHardness', {
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
      }
    });
    var instanceStuff = new SelectPure('#divStuff', {
      options: stuff,
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
      }
    });
    var instanceCustomer = new SelectPure('#divCustomer', {
      options: customer,
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
      }
    });
    // $(year).each(function(){
    //   $('#divYear').append(`
    //     <div class="form-group form-check col-auto form-inline">
    //       <input type="checkbox" class="form-check-input">
    //       <label class="form-check-label">${this.label}</label>
    //     </div>
    //   `);
    // })
    // $(material).each(function(){
    //   $('#divMaterial').append(`
    //     <div class="form-group form-check col-auto form-inline">
    //       <input type="checkbox" class="form-check-input">
    //       <label class="form-check-label">${this.label}</label>
    //     </div>
    //   `);
    // })
    // $(titanizing).each(function(){
    //   $('#divTitanizing').append(`
    //     <div class="form-group form-check col-auto form-inline">
    //       <input type="checkbox" class="form-check-input">
    //       <label class="form-check-label">${this.label}</label>
    //     </div>
    //   `);
    // })

  }

  function getFinishSuggestion() {
    $.ajax({
      url: `/finish/suggestion`,
      type: 'get',
      data: {
        file_id: id,
      },
      success: function(response) {
        $.each(response.title, function() {
          tmpTh = this
          let table;
          if (this.type == 'rd') {
            table = $('#dataTable_develop');
          } else if (this.type == 'valuation') {
            table = $('#dataTable_deeplearning');

          }
          $(table).find('thead tr').each(function() {
            $(this).append(`
            <th>
            <div class="input-group mb-3">
                
                <input data-suggest_id="${tmpTh.id}" value="${tmpTh.title==null?'':tmpTh.title}" type="text" data-type="${tmpTh.type}" name="thSuggestion" placeholder="請輸入項目名稱" class="form-control" />
                <div class="input-group-append">
                  <button data-suggest_id="${tmpTh.id}" type="button" class="close" aria-label="Close" onclick="deleteSuggestion('${tmpTh.id}')">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
              </div>
              </th>
            `);
            // $(this).append($(element).closest('th'));
          })
          $(table).find('tbody tr').each(function() {
            $(this).append(`
              <td style="min-width:200px"><input data-suggest_id="${tmpTh.id}" name="valSuggestion" type="text" placeholder="請輸入項目成本" class="form-control" /></td>
            `);
          })
        })

        $.each(response.val, function() {
          console.log(this)
          $(`tr[data-process_mapping_id="${this.process_mapping_id}"]`).find(`[name="valSuggestion"][data-suggest_id="${this.suggest_id}"]`).val(this.val)
        });

        $('#dataTable_develop').find('thead tr').append(`<th><button class="btn btn-primary" onclick="itemAdd(this,'rd')">新增項目</button></th>`)
        $('#dataTable_deeplearning').find('thead tr').append(`<th><button class="btn btn-primary" onclick="itemAdd(this,'valuation')">新增項目</button></th>`)
      }
    });

  }
  $(document).on('submit', '#formQuotation', function() {
    $.ajax({
      url: '/file/progress',
      type: 'post',
      data: {
        url: window.location.href,
        id: id
      },
      dataType: 'json',
      success: function(response) {
        // $(response).each(function() {
        //   window.location.href = `${this.url}?id=${file_id}&file_id_dest=${file_id_dest}`
        // })
      }
    })
  })

  $(document).on('change', '[name="selectThreeshold"],[name="selectLimit"]', function() {
    getFinishInformation();
  })
  $(document).on('change', '[name="valSuggestion"]', function() {
    saveSuggestionVal($(this).data('suggest_id'), $(this).closest('tr').data('process_mapping_id'), $(this).val())
  })

  $(document).on('change', '[name="thSuggestion"]', function() {
    saveSuggestion($(this).data('suggest_id'), $(this).val())
  })

  function saveSuggestionVal(suggest_id, process_mapping_id, tmpVal) {
    console.log(suggest_id, process_mapping_id, tmpVal)
    $.ajax({
      url: `/finish/suggestionVal`,
      type: 'post',
      data: {
        suggest_id: suggest_id,
        process_mapping_id: process_mapping_id,
        val: tmpVal
      },
      success: function(response) {}
    });
  }

  function deleteSuggestion(suggest_id) {
    $(`[name="thSuggestion"][data-suggest_id="${suggest_id}"]`).closest('th').remove();
    $(`[name="valSuggestion"][data-suggest_id="${suggest_id}"]`).each(function() {
      $(this).closest('td').remove()
    });
    $.ajax({
      url: `/finish/suggestion`,
      type: 'delete',
      data: {
        id: suggest_id
      },
      success: function(response) {

      }
    });


  }

  function saveSuggestion(suggest_id, tmpVal) {


    $.ajax({
      url: `/finish/suggestion`,
      type: 'patch',
      data: {
        id: suggest_id,
        title: tmpVal,
      },
      success: function(response) {

      }
    });
  }

  function itemAdd(element, type) {
    $.ajax({
      url: `/finish/suggestion`,
      type: 'post',
      data: {
        file_id: id,
        type: type
      },
      success: function(response) {
        let table = $(element).closest('table');
        $(table).find('thead tr').each(function() {
          $(this).append(`
              <th class="text-nowrap">
                <div class="input-group mb-3">
                
                  <input data-suggest_id="${response.suggest_id}" type="text" data-type="${type}" name="thSuggestion" placeholder="請輸入項目名稱" class="form-control" />
                  <div class="input-group-append">
                    <button data-suggest_id="${response.suggest_id}" type="button" class="close" aria-label="Close" onclick="deleteSuggestion('${response.suggest_id}')">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                </div>
              </th>
            `);
          $(this).append($(element).closest('th'));
        })
        $(table).find('tbody tr').each(function() {
          $(this).append(`
              <td><input data-suggest_id="${response.suggest_id}" name="valSuggestion" type="text" placeholder="請輸入項目成本" class="form-control" /></td>
            `);
        })
      }
    });

  }

  // let fileInformation = [];

  function getFinishInformation() {
    return;
    // console.log('ingetFinishInformation')

    $.ajax({
      url: `/finish/information`,
      type: 'get',
      data: {
        process_mapping: {
          file_id: id,
          file_id_dest: file_id_dest,
          threshold: $('#selectThreeshold_develop').val(),
          limit: $('#selectLimit_develop').val(),
        },
        process_result: {
          file_id: id,
          file_id_dest: file_id_dest,
          threshold: $('#selectThreeshold_deeplearning').val(),
          limit: $('#selectLimit_deeplearning').val(),
        },
        modify_process: {
          file_id: id,
        }
      },
      success: function(response) {

        let sumtech = 0;
        let summanage = 0;
        $(fileInformation.modify_process).each(function(index) {
          sumtech += parseInt(this.cost || '0')
          summanage += parseInt(this.outsourcer_cost || '0')
        })

        fileInformation = response;
        $('#dataTable_develop tbody').empty();
        $('#dataTable_deeplearning tbody').empty();
        $(fileInformation.process_mapping).each(function(index) {
          let row = this;
          let component_dest = $('<td></td>');
          let total_all = new Object();
          let process_mapping_id = this.process_mapping_id;
          let tmpcurrency = null;
          $(this.order_dest_name).each(function() {
            $(this.詳細內容).each(function() {
              let date = null;
              $.each(this.日期, function() {
                date = new Date(this);
              })
              $.each(this.幣別, function() {
                tmpcurrency = this;
              })
              let 報價金額 = this.報價金額;
              $.each(this.報價金額, function() {
                報價金額 = this;
              })
              if (!total_all.hasOwnProperty(date))
                total_all[date] = [];
              total_all[date].push(parseFloat(報價金額));
            })
          })
          total_all = Object.keys(total_all).sort().reduce(
            (obj, key) => {
              obj[key] = total_all[key];
              return obj;
            }, {}
          );
          let init = 0;
          let randexp = 0;
          $.each(total_all, function(i, row) {
            let now = row.reduce((a, b) => a + b, 0) / row.length || 0;
            if (init == -1) {
              init = now;
              return;
            }
            randexp += now - init;
            init = now;
          })
          let price = (init + randomExponential(1 / randexp)).toFixed(2) || 0;
          let material = ` + <span class="col-auto text-nowrap">追加成本 ${row.material}</span>`
          if (row.material == null || row.material == '') material = '';
          // let process = ` + <span class="col-auto text-nowrap">追加成本 ${row.process}</span>`
          let process = ` + <span class="col-auto text-nowrap">追加成本 ${sumtech}</span>`
          // if (row.process == null || row.process == '') process = '';
          let outsourcer = `  <span class="col-auto text-nowrap">追加成本 ${summanage}</span>`
          // let outsourcer = `  <span class="col-auto text-nowrap">追加成本 ${row.outsourcer}</span>`
          // if (row.outsourcer == null || row.outsourcer == '') outsourcer = '';
          // while(price < 0){
          //   price = (init + randomExponential(1/randexp)).toFixed(2)
          // }
          let other = `  <span class="col-auto text-nowrap">加工成本 ${row.other}</span>`
          if (row.other == null || row.other == '') other = '';
          $('#dataTable_develop tbody').append(`
            <tr data-process_mapping_id="${process_mapping_id}" onclick="getDetail('process_mapping',${index},this)">
              <td>${index+1}</td>
              <td>${price+' '+tmpcurrency}</td>
              <td><span class="col-auto text-nowrap"></span>${material+RD_cost!=0?'追加成本'+ RD_cost:''}</td>
              <td><span class="col-auto text-nowrap"></span>${other+process}</td>
              <td><span class="col-auto text-nowrap"></span>${outsourcer}</td>
            </tr>
          `);
        })
        $(fileInformation.process_result).each(function(index) {
          let component_dest = $('<td></td>');
          let total_all = new Object();
          let tmpcurrency = null;
          $(this.order_dest_name).each(function() {
            $(this.詳細內容).each(function() {
              $.each(this.幣別, function() {
                tmpcurrency = this;
              })
              if (!total_all.hasOwnProperty(this.日期))
                total_all[this.日期] = [];
              total_all[this.日期].push(parseFloat(this.報價金額));
            })
          })
          total_all = Object.keys(total_all).sort().reduce(
            (obj, key) => {
              obj[key] = total_all[key];
              return obj;
            }, {}
          );
          let init = -1;
          let randexp = 0;
          $.each(total_all, function(i, row) {
            let now = row.reduce((a, b) => a + b, 0) / row.length || 0;
            if (init == -1) {
              init = now;
              return;
            }
            randexp += now - init;
            init = now;
          })
          let price = (init + randomExponential(1 / randexp)).toFixed(2) || 0;
          // while(price < 0){
          //   price = (init + randomExponential(1/randexp)).toFixed(2)
          // }
          $('#dataTable_deeplearning tbody').append(`
            <tr onclick="getDetail('process_result',${index})">
              <td>${index+1}</td>
              <td>${price +' '+ tmpcurrency}</td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          `);
        })
        $('#labelCount_develop').text($('#selectLimit_develop').val());
        $('#labelTotal_develop').text(fileInformation.total.process_mapping);
        $('#labelCount_deeplearning').text($('#selectLimit_deeplearning').val());
        $('#labelTotal_deeplearning').text(fileInformation.total.process_result);
        getFinishSuggestion()
      }
    })
  }

  function getDetail(type, index, tmpelement) {
    $('#dataTable_detail tbody').empty();
    $(fileInformation[type][index]).each(function() {
      $(this.order_dest_name).each(function(dest_index) {
        let detail = $(`<tr></tr>`);
        $(this.詳細內容).each(function() {
          let date = null;
          $.each(this.日期, function() {
            date = new Date(this);
          })
          let 報價金額 = this.報價金額;
          $.each(this.報價金額, function() {
            報價金額 = this;
          })
          let confidence = 0;
          $.each(this.confidence, function() {
            confidence = this;
          })
          $(detail).append(`
            <td>${confidence}</td>
            <td>${date.getFullYear()}</td>
            <td>${報價金額}</td>
          `);
          return false;
        });
        if (this.詳細內容 != null) {
          $('#dataTable_detail tbody').append(`
            <tr>
              <td>${dest_index+1}</td>
              <td>${this.零件名稱||''}</td>
              ${detail.html()}
            </tr>
          `);
        }
      })
      console.log(this.order_dest_name)
    })
    console.log((fileInformation[type][index]))
    $.ajax({
      url: `/trend/cost`,
      type: 'get',
      data: {
        process_mapping_id: $(tmpelement).data('process_mapping_id')
      },
      dataType: 'json',
      success: function(response) {
        getChart(type, index, response);
      }
    });

  }

  function modifyorder_name(tmporder_name) {
    $('#spanFileId').html(`
      <form id="formorder_name">
          <div class="row">
            <input type="text" class="form-control col-8" value="${tmporder_name}" id="inputorder_name" placeholder="" required>
            <button type="submit" class="btn btn-primary col-auto">送出</button>
          </div>
      </form>
      `)

    $('#formorder_name').on('submit', function(e) {
      e.preventDefault();
      console.log('test')
      $.ajax({
        url: `/file/order_name`,
        type: 'patch',
        data: {
          file_id: file_id,
          order_name: $('#inputorder_name').val()
        },
        dataType: 'json',
        success: function(response) {}
      });

    })
  }

  function getListState(file_id) {
    $.ajax({
      url: `/file/state/${file_id}`,
      type: 'get',
      data: {
        module_name: '業務'
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
          $('#spanFileId').append(`<button type="button" class="btn btn-primary" onclick="modifyorder_name('${this.order_name}')">修改</button>`)

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

        $(response.station).each(function() {
          if (this.name == '研發') {
            $(this.station).each(function(index) {
              RD_cost += this.追加材料成本 != "" ? parseInt(this.追加材料成本) : 0
              RD_cost += this.追加材質成本 != "" ? parseInt(this.追加材質成本) : 0
            })
          }
        });


      }
    })
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
  /* 
    <li class="list-group-item flex-fill w-100 active">業務-上傳圖檔</li>
    <li class="list-group-item flex-fill w-100">業務-查詢歷史訂單</li>
    <li class="list-group-item flex-fill w-100">業務-全圖比對</li>
    <li class="list-group-item flex-fill w-100">製圖-零件分類</li>
    <li class="list-group-item flex-fill w-100">製圖-零件比對</li>
    <li class="list-group-item flex-fill w-100">製圖-刻度圈選</li>
    <li class="list-group-item flex-fill w-100">製圖-刻度修改</li>
  */
  var setting = {
    "lengthChange": true,
    "destroy": true,
    "info": true,
    "searching": false,
    "order": [],
    "fixedHeader": true,
    "orderCellsTop": true,
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

  $(document).on('click', '#dataTable_develop tbody tr,#dataTable_deeplearning tbody tr', function() {
    if (!$(this).hasClass('table-active')) {
      $('#dataTable_develop tbody tr,#dataTable_deeplearning tbody tr').removeClass('table-active');
      $(this).addClass('table-active')
    }
  })

  function inspin() {
    $('#exampleModal .modal-title').html('讀取中')
    $('#exampleModal .modal-footer').html('')
    $('#exampleModal .modal-body').html(`<div class="spinner-border text-primary" role="status">
      <span class="sr-only">Loading...</span>
    </div>`);
    $('#exampleModal').modal('show');
  }
</script>
<script>
  var myLineChart = null;

  function getChart(type, index, tmpresponse) {
    if (myLineChart != null)
      myLineChart.destroy();

    let trendcostchartdata = {
      labels: labels,
      datasets: []
    }
    let trendcostchartconfig = {
      type: 'bar',
      data: trendcostchartdata,
      options: {
        "responsive": true,
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
              maxTicksLimit: 7,
              display: false //this will remove only the label
            }
          }],
          yAxes: [{
            display: true,
            ticks: {
              beginAtZero: true
            }
          }]
        }
      },
    };
    myLineChart = new Chart(
      document.getElementById('myAreaChart'),
      trendcostchartconfig
    );
    // console.log(outsourcerchartdata)

    if (tmpresponse == null) {

      return false;
    }
    $.each(tmpresponse, function() {
      console.log(this)
      let newDataset = {
        label: this.訂單日期,
        data: [parseInt(this.材料成本)],
        backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
        borderWidth: 1,
        datalabels: {
          anchor: 'end',
          align: 'start',
        }
      }
      trendcostchartdata.datasets.push(newDataset);
      myLineChart.update();
    });



    // let data = new Object();
    // let datas = new Object();
    // $(fileInformation[type][index]).each(function() {
    //   $(this.order_dest_name).each(function(dest_index) {
    //     $(this.詳細內容).each(function() {
    //       let date = null;
    //       $.each(this.日期, function() {
    //         date = new Date(this);
    //       })
    //       let 報價金額 = this.報價金額;
    //       $.each(this.報價金額, function() {
    //         報價金額 = this;
    //       })
    //       let confidence = 0;
    //       $.each(this.confidence, function() {
    //         confidence = this;
    //       })
    //       let 零件名稱 = this.零件名稱;
    //       $.each(this.零件名稱, function() {
    //         零件名稱 = this;
    //       })
    //       if (!data.hasOwnProperty(this.日期)) {
    //         data[date] = new Object();
    //       }
    //       if (!data[date].hasOwnProperty(零件名稱)) {
    //         data[date][零件名稱] = [];
    //       }
    //       if (!datas.hasOwnProperty(零件名稱)) {
    //         datas[零件名稱] = [];
    //       }
    //       data[date][零件名稱].push({
    //         price: 報價金額,
    //         confidence: confidence
    //       });
    //     });
    //   })
    // })
    // data = Object.keys(data).sort().reduce(
    //   (obj, key) => {
    //     obj[key] = data[key];
    //     return obj;
    //   }, {}
    // );
    // console.log(data)
    // let label = Object.keys(data);
    // $('#divDetailYear').html(`
    // <label class="col-form-label col-sm-auto">年份：</label>
    // `);
    // $(label).each(function() {
    //   let date = new Date(this);
    //   if ($(`#divDetailYear`).find(`input[value="${date.getFullYear()}]`).length == 0) {
    //     $('#divDetailYear').append(`
    //     <div class="col-sm form-check form-inline">
    //       <input type="checkbox" class="form-check-input" value="${date.getFullYear()}">
    //       <label class="form-check-label">${date.getFullYear()}</label>
    //     </div>
    //   `);
    //   }
    // })
    // let avg = [];
    // $(label).each(function() {
    //   let row = this;
    //   let avg_price = 0;
    //   let avg_confidence = 0;
    //   $(Object.keys(datas)).each(function() {
    //     let price = 0;
    //     let total_confidence = 0;
    //     $(data[row][this]).each(function() {
    //       price += this.price * this.confidence;
    //       total_confidence += this.confidence;

    //       avg_price += this.price * this.confidence;
    //       avg_confidence += this.confidence;
    //     })
    //     datas[this].push(price / total_confidence || null);
    //   })
    //   avg.push((avg_price / avg_confidence).toFixed(2))
    // });
    // let datasets = [];
    // label=[]
    // $.each(tmpresponse, function(key, value) {
    //   console.log( parseInt(value['材料成本']))
    //   let tmpNum = [];
    //   tmpNum.push(parseInt(value['材料成本']))
    //   let r = Math.floor(Math.random() * 255),
    //     g = Math.floor(Math.random() * 255),
    //     b = Math.floor(Math.random() * 255);
    //   datasets.push({
    //     type: 'bar',
    //     label: key,
    //     lineTension:0.3,
    //     backgroundColor: `rgba(${r}, ${g}, ${b}, 1)`,
    //     borderColor: `rgba(${r}, ${g}, ${b}, 1)`,
    //     data:tmpNum,
    //   });
    //   label.push(value['訂單日期'])
    // })
    // // {
    // //   let r = Math.floor(Math.random() * 255),
    // //     g = Math.floor(Math.random() * 255),
    // //     b = Math.floor(Math.random() * 255);
    // //   datasets.push({
    // //     type: 'line',
    // //     label: '平均價格',
    // //     lineTension: 0.3,
    // //     backgroundColor: `rgba(${r}, ${g}, ${b}, 0.05)`,
    // //     borderColor: `rgba(${r}, ${g}, ${b}, 1)`,
    // //     data: avg,
    // //   });
    // // }

    // // Set new default font family and font color to mimic Bootstrap's default styling
    // Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    // Chart.defaults.global.defaultFontColor = '#858796';

    // function number_format(number, decimals, dec_point, thousands_sep) {
    //   // *     example: number_format(1234.56, 2, ',', ' ');
    //   // *     return: '1 234,56'
    //   number = (number + '').replace(',', '').replace(' ', '');
    //   var n = !isFinite(+number) ? 0 : +number,
    //     prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    //     sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    //     dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    //     s = '',
    //     toFixedFix = function(n, prec) {
    //       var k = Math.pow(10, prec);
    //       return '' + Math.round(n * k) / k;
    //     };
    //   // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    //   s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    //   if (s[0].length > 3) {
    //     s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    //   }
    //   if ((s[1] || '').length < prec) {
    //     s[1] = s[1] || '';
    //     s[1] += new Array(prec - s[1].length + 1).join('0');
    //   }
    //   return s.join(dec);
    // }

    // // Area Chart Example
    // console.log(label)
    // var ctx = document.getElementById("myAreaChart");
    // if (myLineChart != null)
    //   myLineChart.destroy();
    // myLineChart = new Chart(ctx, {
    //   data: {
    //     labels: label,
    //     display: false,
    //     datasets: datasets,
    //   },
    //   // options: {
    //   //   "responsive": true,
    //   //   scales: {
    //   //     xAxes: [{
    //   //       time: {
    //   //         unit: 'date'
    //   //       },
    //   //       gridLines: {
    //   //         display: false,
    //   //         drawBorder: false
    //   //       },
    //   //       ticks: {
    //   //         maxTicksLimit: 7,
    //   //         display: false //this will remove only the label
    //   //       }
    //   //     }],
    //   //     yAxes: [{
    //   //       display: true,
    //   //       ticks: {
    //   //         beginAtZero: true
    //   //       }
    //   //     }]
    //   //   }
    //   // },
    //   options: {
    //     plugins: {
    //       // Change options for ALL labels of THIS CHART
    //       datalabels: {
    //         color: '#000000',
    //       }
    //     },
    //     maintainAspectRatio: false,
    //     layout: {
    //       padding: {
    //         left: 10,
    //         right: 25,
    //         top: 25,
    //         bottom: 0
    //       }
    //     },
    //     scales: {
    //       xAxes: [{
    //         time: {
    //           unit: 'date'
    //         },
    //         gridLines: {
    //           display: false,
    //           drawBorder: false
    //         },
    //         ticks: {
    //           maxTicksLimit: 7,
    //           display: false //this will remove only the label
    //         }
    //       }],
    //       yAxes: [{
    //         ticks: {
    //           maxTicksLimit: 5,
    //           padding: 10,
    //           // Include a dollar sign in the ticks
    //           callback: function(value, index, values) {
    //             return '$' + number_format(value);
    //           }
    //         },
    //         gridLines: {
    //           color: "rgb(234, 236, 244)",
    //           zeroLineColor: "rgb(234, 236, 244)",
    //           drawBorder: false,
    //           borderDash: [2],
    //           zeroLineBorderDash: [2]
    //         }
    //       }],
    //     },
    //     legend: {
    //       display: true,
    //       fontSize: 20
    //     },
    //     tooltips: {
    //       backgroundColor: "rgb(255,255,255)",
    //       bodyFontColor: "#858796",
    //       bodyFontSize: 24,
    //       titleMarginBottom: 10,
    //       titleFontColor: '#6e707e',
    //       titleFontSize: 28,
    //       borderColor: '#dddfeb',
    //       borderWidth: 1,
    //       xPadding: 15,
    //       yPadding: 15,
    //       displayColors: false,
    //       intersect: false,
    //       mode: 'index',
    //       caretPadding: 10,
    //       // callbacks: {
    //       //   label: function(tooltipItem, chart) {
    //       //     var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
    //       //     return datasetLabel + ': $' + number_format(tooltipItem.yLabel);
    //       //   }
    //       // }
    //     }
    //   }
    // });
  }

  function randomExponential(rate, randomUniform) {
    // http://en.wikipedia.org/wiki/Exponential_distribution#Generating_exponential_variates
    rate = rate || 1;

    // Allow to pass a random uniform value or function
    // Default to Math.random()
    var U = randomUniform;
    if (typeof randomUniform === 'function') U = randomUniform();
    if (!U) U = Math.random();

    return -Math.log(U) / rate;
  }
</script>
<script>
  let labels = new Array('永力昇', '吉兵', '奇鼎');
  // "星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"
  let temparydata = {
    labels: labels,
    datasets: [
      //   {
      //   label: ["數量"],
      //   data: [65, 59, 80],
      //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
      //   borderWidth: 1,
      //   datalabels: {
      //     anchor: 'end',
      //     align: 'start',
      //   },
      //   type: 'bar',
      //   yAxisID: 'y',
      // }, {
      //   label: '報價金額',
      //   data: [120, 100, 200],
      //   borderWidth: 1,
      //   borderColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 1)`,
      //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0)`,
      //   yAxisID: 'y2',
      // }, {
      //   label: '相似度',
      //   data: [70, 80, 90],
      //   borderWidth: 1,
      //   borderColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 1)`,
      //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0)`,
      //   yAxisID: 'y',
      // }
    ]
  };
  let temparyconfig = {
    type: 'line',
    data: temparydata,
    options: {
      "responsive": true,
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
            maxTicksLimit: 7,
            display: false //this will remove only the label
          }
        }],
        yAxes: [{
            id: 'y',
            type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
            position: 'left',
            display: true,
            ticks: {
              color: `rgba(255,255,255,0)`,
              beginAtZero: true
            },
          },
          {
            id: 'y2',
            type: 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
            position: 'right',
            display: true,
            ticks: {
              color: `rgba(255,255,255,0)`,
              beginAtZero: true
            },
            grid: {
              drawOnChartArea: false, // only want the grid lines for one axis to show up
            },
          }
        ],
      }
    }
  };
  var temparyChart = new Chart(
    document.getElementById('BarChart2'),
    temparyconfig
  );
</script>
<script>
  labels = new Array("數量");
  // "星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"
  var outsourcerchartdata = {
    labels: labels,
    datasets: [
      // {
      //   label: "永力昇",
      //   data: [35],
      //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
      //   borderWidth: 1,
      //   datalabels: {
      //     anchor: 'end',
      //     align: 'start',
      //   }
      // },{
      //   label: "吉兵",
      //   data: [40],
      //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
      //   borderWidth: 1,
      //   datalabels: {
      //     anchor: 'end',
      //     align: 'start',
      //   }
      // },{
      //   label: "奇鼎",
      //   data: [81],
      //   backgroundColor: `rgba(${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, ${Math.floor(Math.random()*255)}, 0.2)`,
      //   borderWidth: 1,
      //   datalabels: {
      //     anchor: 'end',
      //     align: 'start',
      //   }
      // }
    ]
  };
  var outsourcerchartconfig = {
    type: 'bar',
    data: outsourcerchartdata,
    options: {
      "responsive": true,
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
            maxTicksLimit: 7,
            display: false //this will remove only the label
          }
        }],
        yAxes: [{
          display: true,
          ticks: {
            beginAtZero: true
          }
        }]
      }
    },
  };
  var outsourcerChart = new Chart(
    document.getElementById('BarChart3'),
    outsourcerchartconfig
  );

  $('#exampleModal2').on('show.bs.modal', function(event) {
    // console.log($(event.relatedTarget).attr("data-type"));
    var type = $(event.relatedTarget).attr("data-type");
    $('#exampleModal2 .modal-footer').html('<button class="btn btn-secondary" type="button" data-dismiss="modal">取消</button>');
    if (type == 'selectItemNO') {
      $("#exampleModal2 .modal-dialog ").attr("class", "modal-dialog modal-xl");
      selectItemNO();
    } else {
      $("#exampleModal2 .modal-dialog ").attr("class", "modal-dialog");
    }
  });

  function selectItemNO() {
    $('#exampleModal2 .modal-title').html('選擇品號')
    $('#exampleModal2 .modal-footer').append(`<button type="button" class="btn btn-primary" onclick="updateItemNO()">下一步</button>`)
    $('#exampleModal2 .modal-body').html(`
    <div class="form-group row">
          <label for="filteritemno" class="col-sm-auto col-form-label">客戶圖號</label>
          <input type="text" class="form-control col-sm-6" data-type="picture_num" name="filteritemno"  >
    </div>
    <div class="form-group row">
          <label for="filteritemno" class="col-sm-auto col-form-label">客戶代號</label>
          <input type="text" class="form-control col-sm-6" data-type="customer_id" name="filteritemno" >
    </div>
    <table class="table table-borderless" id="generatedataTable" width=100%>
      <thead>
        <tr>
          <th>#</th>
          <th>品號</th>
          <th>硬度</th>
          <th>客戶圖號</th>
          <th>版次</th>
          <th>材質</th>
          <th>鍍鈦</th>
        </tr>
      </thead>
    </table>
  `);
    setitemnoTable()
  }
  let arr_radioItemNO = [];

  function setitemnoTable() {
    let picture_num = $('[name="filteritemno"][data-type="picture_num"]').val()
    let customer_id = $('[name="filteritemno"][data-type="customer_id"]').val()

    let setting_business = JSON.parse(JSON.stringify(setting));
    $('#generatedataTable').DataTable(setting_business).destroy();

    setting_business['ajax'] = {
      url: `/business/itemNO`,
      type: 'get',
      "data": function(d) {
        d.picture_num = picture_num,
          d.customer_id = customer_id
      }
    };
    setting_business['processing'] = true;
    setting_business['serverSide'] = true;
    setting_business['createdRow'] = function(row, data, dataIndex) {
      // $(row).attr('onclick', `inLoad(${data['id']})`);
      $(row).attr('style', `cursor:pointer`);
    };

    setting_business['columns'] = [{
        "data": null,
        render: function(data, type, row, meta) {
          return `<input type="radio" value=${data['品號']} name="radioItemNO" aria-label="Checkbox for following text input"> `;
        }
      }, {
        "data": "品號"
      },
      {
        "data": "硬度"
      }, {
        "data": "客戶圖號",
      }, {
        "data": "版次",
      }, {
        "data": "材質",
      }, {
        "data": "鍍鈦",
      },
    ];
    $('#generatedataTable').DataTable(setting_business);
  }
  $(document).on('change', '#generatedataTable [name="checkboxmakepdf"]', function(e) {
    if (arr_radioItemNO.includes($(this).val())) {
      arr_radioItemNO.splice(arr_radioItemNO.indexOf($(this).val()), 1);
    } else {
      arr_radioItemNO.push($(this).val());
    }
    console.log(arr_radioItemNO)
  })
  let timeout_generatedataTable = null;
  $(document).on('input', '[name="filteritemno"]', function() {
    clearTimeout(timeout_generatedataTable);
    timeout_generatedataTable = setTimeout(function() {
      setitemnoTable();
    }, 1000)
  })

  function getitemno() {
    $.ajax({
      url: `/file/information`,
      type: 'get',
      data: {
        file_id: id,
      },
      dataType: 'json',
      success: function(response) {
        let itemno = '';
        let custom_material = '';
        let custom_titanizing = '';
        $.each(response, function() {
          itemno = this.itemno
          custom_material = this.custom_material
          custom_titanizing = this.custom_titanizing

        })
        $('[name=inputitemNo]').val(itemno)
        $('[name="inputpdfemail"][data-type="material"]').val(custom_material)
        $('[name="inputpdfemail"][data-type="titanizing"]').val(custom_titanizing)
      }
    });
  }

  function updateItemNO() {
    $('[name="radioItemNO"]:checked').each(function() {
      itemno = $(this).val()
    })
    $('#exampleModal2').modal('hide');
    $('[name=inputitemNo]').val(itemno)
    $.ajax({
      url: `/file/itemno`,
      type: 'patch',
      data: {
        file_id: id,
        itemno: itemno
      },
      dataType: 'json',
      success: function(response) {

      }
    });

  }

  $(function() {
    getitemno();

  })
</script>
<link href="/static/weightConverter/css/main.72d64a8f.css" rel="stylesheet" />

