<?php include(__DIR__ . '/basic/header.html'); ?>
<!-- <link href="/tour-css/bootstrap-tour.min.css" rel="stylesheet">
<link href="/tour-css/bootstrap-tour-standalone.min.css" rel="stylesheet"> -->
<style>
  #custom_rego {
    transition: height 3s, width 3s;
    z-index : 1;
}
 
  #custom_rego:hover {
  width: 600px;
  z-index : 1;

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
</style>
<script src="/dropzone/dist/dropzone.js"></script>
<link rel="stylesheet" href="/dropzone/dist/dropzone.css">
<link rel="stylesheet" href="/css/compare-norecog.css">
<link rel="stylesheet" href="/vendor/select-pure/dist/select-pure.css">
<div id="fileState"></div>
<div class="row">
  <div class="col-12 col-md-4 mb-4">
    <div class="row row-cols-1 h-100">
      <div class="col">
        <div class="card shadow h-100">
          <div class="card-header">
            檔案上傳區
            <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="上傳方式:1. 點此並擇檔案進行上傳、2. 拖曳客戶圖至此上傳，檔案格式:jpg、PDF"></i>
          </div>
          <div id="fileUpdate" class="card-body h-100">
            <div class="form-group row h-100">
              <div class="col-12 overflow-auto h-100">
                <form action="/file?mode=textrecog" class="dropzone h-100" id="uploadDropzone" method='post' style="min-width:200px"></form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow h-100" id="custom_input">
          <div class="card-header">
            客戶名稱/交貨日
            <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="選擇客戶名稱、產品品號、產品交期"></i>
            <!-- <button id="customBtn" type="button" class="btn  mx-2"><i class="fas fa-exclamation-circle"></i></button> -->
          </div>

          <div id="custom" class="card-body overflow-auto">
            <div class="form-group row">
              <label for="selectcustomerCode" class="col-sm-auto col-form-label">客戶名稱</label>
              <div class="col-sm-6 row" id="divcustomerCode">
              </div>
            </div>
            <div class="form-group row">
              <label for="input_custom_id" class="col-sm-4 col-form-label">客戶報價單號</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" data-type="custom_id" id="input_custom_id">
              </div>
            </div>
            <!-- <div class="form-group row">
              <label for="selectcustomerCode" class="col-sm-auto col-form-label">交貨日</label>
              <input type="datetime-local" class="form-control" data-type="deadline" id="inputdelivery_date" >
            </div> -->
            <div class="form-group row">
              <label for="inputdelivery_week" class="col-sm-4 col-form-label">交貨週數</label>
              <input type="number" class="form-control col-sm-6" data-type="delivery_week" id="inputdelivery_week">
            </div>
            <div class="form-group row">
              <label for="inputitemNo" class="col-sm-auto col-form-label">品號</label>
              <input type="text" class="form-control col-sm-6" data-type="itemNo" id="inputitemNo" disabled>
              <button type="button" class="col-sm-auto btn btn-primary" data-toggle="modal" data-target="#exampleModal" data-type="selectItemNO">修改</button>
            </div>
            <div class="form-group row">
              <label class="col-form-label col-sm-auto">註記</label>
              <div class="col" name="divFileComment" data-type="top">
                <textarea class="form-control" name="inputFileComment" rows="3" ></textarea>
              </div>
            </div>
            <div class="col text-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" data-type="insert">確認</button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" data-type="next">下一步</button>
              </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow h-100" id="custom_rego">
          <div class="card-header">客戶圖號辨識
            <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="選擇客戶圖號，若辨識結果有誤，於文字框中直接修改即可"></i>
            <!-- <button id="OCRResultBtn" type="button" class="btn  mx-2"><i class="fas fa-exclamation-circle"></i></button> -->
          </div>
          <div class="card-body overflow-auto" id="OCRResult">
            <div id="inspin" class=" row">

            </div>
            <ul>
              <li>請選擇圖中的客圖編號</li>
              <li>若辨識有誤，可以直接修改</li>
            </ul>
            <div class="form-group row table-responsive">
              <table class="table" id="dataTable" width=100%>
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">修正編號</th>
                    <th scope="col" class="text-nowrap" width=20%>客戶圖號選擇</th>
                    <th scope="col" class="text-nowrap" width=20%>客戶logo選擇</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <div class="col-12 col-md-8 mb-4">
    <div class="row row-cols-1 h-0">
      <!-- <div class="col">
        <div class="card shadow">
          <div class="card-header">動作
            <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="若要儲存步前進至查詢歷史訂單請點選確認，若要前進至查詢歷史訂單請點選下一步，將會自動儲存並前往下一階段"></i> -->
            <!-- <button id="actionBtn" type="button" class="btn  mx-2"><i class="fas fa-exclamation-circle"></i></button> -->
          <!-- </div>
          <div id="action" class="card-body">
            <div class="row">
              <div class="col-auto">
                <ul>
                  <li>確認：若要儲存，不前進至查詢歷史訂單</li>
                  <li>下一步：儲存並前往下一階段</li>
                </ul>
              </div>
              <div class="col text-right">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" data-type="insert">確認</button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" data-type="next">下一步</button>
              </div>
            </div>
          </div>
        </div>
      </div> -->
      <div class="col">
        <div class="card shadow h-100">
          <div class="card-header">客戶零件辨識
          <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="請選擇切割結果為零件圖的項目"></i>

            <!-- <button id="componentsBtn" type="button" class="btn  mx-2"><i class="fas fa-exclamation-circle"></i></button> -->

          </div>
          <div id="components" class="card-body overflow-auto">
            <div id="inspin" class=" row">

            </div>
            <ul>
              <li>請選擇圖中的客圖編號</li>
              <li>若辨識有誤，可以直接修改</li>
            </ul>
            <div class="form-group row table-responsive">
              <table class="table" id="cropTable" width=100%>
                <thead>
                  <!-- <tr>
                    <th scope="col">#</th>
                    <th scope="col">縮圖</th>
                    <th scope="col" class="text-nowrap" width=20%>零件選擇</th>
                  </tr> -->
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card shadow h-100" id="OCR">
          <div class="card-header">文字辨識
          <i class="fas fa-exclamation-circle" data-toggle="tooltip" data-placement="top" title="若客戶圖號無法順利辨識，請利用旋轉按鈕將客戶圖轉正"></i>
            <!-- <button id="OCRBtn" type="button" class="btn  mx-2"><i class="fas fa-exclamation-circle"></i></button> -->
          </div>

          <div class="col-auto">
            <div class="input-group">
              <div class="input-group-prepend">
                <button type="button" id="toolbar_zoom_out" title="Zoom Out" onclick="zoom_out()" class="btn btn-secondary">-</button>
                <button type="button" id="toolbar_zoom_in" onclick="zoom_in()" title="Zoom In" class="btn btn-secondary">+</button>
                <button type="button" id="toolbar_zoom_reset" onclick="reset_zoom_level()" title="Zoom Reset" class="btn btn-secondary">=</button>
                <span class="input-group-text" id="">旋轉</span>
                <button type="button" id="" title="Zoom Out" onclick="rotatepic(-10)" class="btn btn-secondary">-10°</button>
                <button type="button" id="" title="Zoom Out" onclick="rotatepic(10)" class="btn btn-secondary">+10°</button>
              </div>
              <input type="number" class="form-control" id="rotatepic" value="0">
              <div class="input-group-append">
                <button type="button" id="" title="Zoom Out" onclick="rotatepic(-90)" class="btn btn-secondary">-90°</button>
                <button type="button" id="" title="Zoom Out" onclick="rotatepic(90)" class="btn btn-secondary">+90°</button>
              </div>
            </div>
          </div></br>

          <div class="card-body overflow-auto  vh-100" onresize="_via_update_ui_components()">
            <svg hidden style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
              <defs>
                <symbol id="shape_rectangle">
                  <title>Rectangular region shape</title>
                  <rect width="20" height="12" x="6" y="10" stroke-width="2" />
                </symbol>
                <symbol id="shape_circle">
                  <title>Circular region shape</title>
                  <circle r="10" cx="16" cy="16" stroke-width="2" />
                </symbol>
                <symbol id="shape_ellipse">
                  <title>Elliptical region shape</title>
                  <ellipse rx="12" ry="8" cx="16" cy="16" stroke-width="2" />
                </symbol>
                <symbol id="shape_polygon">
                  <title>Polygon region shape</title>
                  <path d="M 15.25,2.2372 3.625,11.6122 6,29.9872 l 20.75,-9.625 2.375,-14.75 z" stroke-width="2" />
                </symbol>
                <symbol id="shape_point">
                  <title>Point region shape</title>
                  <circle r="3" cx="16" cy="16" stroke-width="2" />
                </symbol>
                <symbol id="shape_polyline">
                  <title>Polyline region shape</title>
                  <!--<path d="M 15.25,2.2372 3.625,11.6122 6,29.9872 l 20.75,-9.625" stroke-width="2"/>-->
                  <path d="M 2,12 10,24 18,12 24,18" stroke-width="2" />
                  <circle r="1" cx="2" cy="12" stroke-width="2" />
                  <circle r="1" cx="10" cy="24" stroke-width="2" />
                  <circle r="1" cx="18" cy="12" stroke-width="2" />
                  <circle r="1" cx="24" cy="18" stroke-width="2" />
                </symbol>
              </defs>
            </svg>

            <div hidden class="top_panel" id="ui_top_panel">
              <!-- Navigation menu -->
              <div class="navbar">
                <ul>
                  <li><a onclick="show_home_panel()" title="Home">Home</a></li>
                  <li class="dropdown"><a title="Image" class="drop_menu_item">Image &#9662;</a>
                    <div class="dropdown-content">
                      <a onclick="sel_local_images()" title="Load (or add) a set of images from local disk">Load or Add
                        Images</a>
                      <a onclick="toggle_img_fn_list_visibility()" title="Browse currently loaded images">List Images</a>
                    </div>
                  </li>
                  <li class="dropdown"><a title="Annotations" class="drop_menu_item">Annotation &#9662;</a>
                    <div class="dropdown-content">
                      <a onclick="show_annotation_data()" title="View annotations">View annotations</a>
                      <a onclick="download_all_region_data('csv')" title="Save image region annotations as a CSV(comma separated value) file">Save as CSV</a>
                      <a onclick="download_all_region_data('json')" title="Save image region annotations as a JSON(Javascript Object Notation) file">Save as JSON</a>
                      <a onclick="sel_local_data_file('annotations')" title="Import existing region data from CSV or JSON file">Import</a>
                    </div>
                  </li>
                  <li class="dropdown"><a title="View" class="drop_menu_item">View &#9662;</a>
                    <div class="dropdown-content">
                      <a onclick="toggle_leftsidebar()" title="Show/hide left sidebar">Show/hide left sidebar</a>
                      <a onclick="toggle_region_boundary_visibility()" title="Show or hide region boundaries">Show/hide
                        region boundaries</a>
                      <a onclick="toggle_region_id_visibility()" title="Show or hide region labels">Show/hide region
                        labels</a>
                    </div>
                  </li>
                  <li class="dropdown"><a onclick="show_about_panel()" title="Help">Help &#9662;</a>
                    <div class="dropdown-content">
                      <a onclick="show_getting_started_panel()" title="Getting started with VGG Image Annotator (VIA)">Getting Started</a>
                      <a onclick="show_license_panel()" title="VIA License">License</a>
                      <a onclick="show_about_panel()" title="About VGG Image Annotator (VIA)">About</a>
                    </div>
                  </li>
                </ul>

              </div> <!-- end of #navbar -->

              <!-- Shortcut toolbar -->
              <div class="toolbar">
                <ul>
                  <!--
              <li onclick="sel_local_images()" title="Load or Add Images">&ctdot;</li>
              <li onclick="sel_local_data_file('annotations')" title="Import Annotations">&uarr;</li>
              <li onclick="download_all_region_data('csv')" title="Save Annotations (as CSV)">&DownArrowBar;</li>
              -->

                  <li id="toolbar_prev_img" style="margin-left: 1em;" onclick="move_to_prev_image()" title="Previous Image">
                    &larr;</li>
                  <li id="toolbar_next_img" onclick="move_to_next_image()" title="Next Image">&rarr;</li>
                  <li id="toolbar_list_img" onclick="toggle_img_list()" title="List Images">&#9776;</li>

                  <li id="toolbar_zoom_out" style="margin-left: 2em;" onclick="zoom_out()" title="Zoom Out">&minus;</li>
                  <li id="toolbar_zoom_in" onclick="zoom_in()" title="Zoom In">&plus;</li>
                  <li id="toolbar_zoom_reset" onclick="reset_zoom_level()" title="Zoom Reset">&equals;</li>


                  <li id="toolbar_copy_region" style="margin-left: 2em;" onclick="copy_sel_regions()" title="Copy Region">c
                  </li>
                  <li id="toolbar_paste_region" onclick="paste_sel_regions()" title="Paste Region">v</li>
                  <li id="toolbar_sel_all_region" onclick="sel_all_regions()" title="Select All Regions">a</li>
                  <li id="toolbar_del_region" onclick="del_sel_regions()" title="Delete Region">&times;</li>
                </ul>
              </div> <!-- endof #toolbar -->
              <input type="file" id="invisible_file_input" multiple name="files[]" style="display:none">
            </div> <!-- endof #top_panel -->

            <!-- Middle Panel contains a left-sidebar and image display areas -->
            <div hidden class="middle_panel">
              <div id="leftsidebar">
                <button class="leftsidebar_accordion active">Region Shape</button>
                <div class="leftsidebar_accordion_panel show">
                  <ul class="region_shape">
                    <li id="region_shape_rect" class="selected" onclick="select_region_shape('rect')" title="Rectangle"><svg height="32" viewbox="0 0 32 32">
                        <use xlink:href="#shape_rectangle"></use>
                      </svg></li>
                    <li id="region_shape_circle" onclick="select_region_shape('circle')" title="Circle"><svg height="32" viewbox="0 0 32 32">
                        <use xlink:href="#shape_circle"></use>
                      </svg></li>
                    <li id="region_shape_ellipse" onclick="select_region_shape('ellipse')" title="Ellipse"><svg height="32" viewbox="0 0 32 32">
                        <use xlink:href="#shape_ellipse"></use>
                      </svg></li>
                    <li id="region_shape_polygon" onclick="select_region_shape('polygon')" title="Polygon"><svg height="32" viewbox="0 0 32 32">
                        <use xlink:href="#shape_polygon"></use>
                      </svg></li>
                    <li id="region_shape_point" onclick="select_region_shape('point')" title="Point"><svg height="32" viewbox="0 0 32 32">
                        <use xlink:href="#shape_point"></use>
                      </svg></li>
                    <li id="region_shape_polyline" onclick="select_region_shape('polyline')" title="Polyline"><svg height="32" viewbox="0 0 32 32">
                        <use xlink:href="#shape_polyline"></use>
                      </svg></li>
                  </ul>
                </div>

                <button class="leftsidebar_accordion active" id="loaded_img_panel_title">Loaded Images</button>
                <div class="leftsidebar_accordion_panel show" id="img_fn_list_panel">
                  <div>
                    <input type="text" placeholder="Filter using regular expression" oninput="img_fn_list_onregex()" id="img_fn_list_regex">
                  </div>
                  <div id="img_fn_list"></div>
                </div>

                <button onclick="toggle_reg_attr_panel()" class="leftsidebar_accordion" id="reg_attr_panel_button">Region
                  Attributes</button>
                <button onclick="toggle_file_attr_panel()" class="leftsidebar_accordion" id="file_attr_panel_button">File
                  Attributes</button>

                <button class="leftsidebar_accordion">Keyboard Shortcuts</button>
                <div class="leftsidebar_accordion_panel">
                  <table style="padding: 2em 0em;">
                    <tr>
                      <td style="width: 6em;">n/p (&larr;/&rarr;)</td>
                      <td>Next/Previous image</td>
                    </tr>
                    <tr>
                      <td>+&nbsp;/&nbsp;-&nbsp;/&nbsp;=</td>
                      <td>Zoom in/out/reset</td>
                    </tr>
                    <tr>
                      <td>Ctrl + c</td>
                      <td>Copy sel. regions</td>
                    </tr>
                    <tr>
                      <td>Ctrl + v</td>
                      <td>Paste sel. regions</td>
                    </tr>
                    <tr>
                      <td>Ctrl + a</td>
                      <td>Select all regions</td>
                    </tr>
                    <tr>
                      <td>Del, Bkspc</td>
                      <td>Delete image region</td>
                    </tr>
                    <tr>
                      <td>Esc</td>
                      <td>Cancel operation</td>
                    </tr>
                    <tr>
                      <td>Ctrl + s</td>
                      <td>Download annotations</td>
                    </tr>
                    <tr>
                      <td>Spacebar</td>
                      <td>Toggle image list</td>
                    </tr>
                  </table>
                </div>

              </div> <!-- end of leftsidebar -->
              <div id="leftsidebar_collapse_panel">
                <div onclick="toggle_leftsidebar()" id="leftsidebar_collapse_button" title="Show/hide left toolbar">
                  &ltrif;</div>
              </div>
            </div>
            <!-- Main display area: contains image canvas, ... -->
            <div id="display_area">
              <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups" hidden>
                <div class="btn-group mr-2" role="group" aria-label="First group">
                  <button type="button" name="btnChangeColor" class="btn btn-secondary" data-color="red" onclick="changeColor('red')">框選logo</button>
                </div>
                <div class="btn-group mr-2" role="group" aria-label="Second group">
                  <button type="button" name="btnChangeColor" class="btn btn-primary" data-color="blue" onclick="changeColor('blue')">框選零件</button>
                </div>
              </div>

              <div id="canvas_panel">
                <canvas id="image_canvas"></canvas>
                <canvas id="region_canvas" tabindex="1">Sorry, your browser does not support HTML5 Canvas functionality
                  which is required for this application.</canvas>
              </div>

              <div hidden>
                <div class="text_panel" id="via_start_info_panel">Starting VGG Image Annotator ...</div>

                <div class="text_panel" style="padding: 1em; border: 1px solid #cccccc;" id="about_panel">
                  <p style="font-size: 2em;">VGG Image Annotator</p>
                  <p style="font-size: 0.8em;">version <a href="https://gitlab.com/vgg/via/blob/via-1.x.y/CHANGELOG">1.0.6</a></p>
                  <p>
                    VGG Image Annotator (VIA) is an <a href="https://gitlab.com/vgg/via/">open source project</a>
                    developed at the <a href="http://www.robots.ox.ac.uk/~vgg/">Visual Geometry Group</a>
                    and released under the BSD-2 clause <a href="https://gitlab.com/vgg/via/blob/master/LICENSE">license</a>.
                    With this standalone application, you can define regions in an image and create a textual description of
                    those regions.
                    Such image regions and descriptions are useful for supervised training of learning algorithms.
                  </p>
                  <p>Features:</p>
                  <ul>
                    <li>based solely on HTML, CSS and Javascript (no external javascript libraries)</li>
                    <li>can be used off-line (full application in a single html file of size &lt; 200KB)</li>
                    <li>requires nothing more than a modern web browser</li>
                    <li>supported region shapes: rectangle, circle, ellipse, polygon and point</li>
                    <li>import/export of region data in csv and json file format</li>
                  </ul>
                  <p>For more details, visit <a href="http://www.robots.ox.ac.uk/~vgg/software/via/">http://www.robots.ox.ac.uk/~vgg/software/via/</a>.
                  </p>
                  <p>&nbsp;</p>
                  <p>Copyright &copy; 2016-2018, <a href="mailto:adutta@robots.ox.ac.uk">Abhishek Dutta</a> (Visual Geometry
                    Group, Oxford University)</p>
                </div>

                <div class="text_panel" id="getting_started_panel">
                  <h1>Getting Started</h1>
                  <ol>
                    <li>Click [Image > Load or Add Images] in the top menu bar to load a set of images that you wish to
                      annotate.</li>
                    <li>Press n (or p) to navigate through the loaded images. You can also use the &larr; and &rarr; icons
                      in the top panel toolbar for navigation.</li>
                    <li>Click <b>Region Attributes</b> in the left panel to reveal a panel in the bottom. Click <b>[Add
                        New]</b> tp add a new attribute. For example:
                      <pre>
                      object_name
                      object_color
                    </pre>
                      You can add more region attributes according to you needs.
                    <li>In the <b>Region Shape</b> section in the left panel, click the rectangular shape</li>
                    <li>On the image area, keep pressing the right click button as you drag the mouse cursor. This will draw
                      a rectangular region on the image.</li>
                    <li>This newly created region is automatically selected. Now you can enter the attribute value for this
                      region in the bottom panel. For example:
                      <pre>
                      object_name = dog
                      object_color = white
                    </pre>
                      You can annotate multiple regions in this image or other images and assign a value to each pre-defined
                      attribute.
                    </li>
                    <li>To download the annotated region data, click <b>[Annotation > Save as CSV]</b> in the top menu bar.
                      This will download a text file containing region shape and attribute data.</li>
                    <li>Next time, you can start from the point your left by first loading the images and then importing the
                      CSV file (downloaded in step 7) by clicking <b>[Annotation > Import]</b>.
                    </li>
                  </ol>
                </div>

                <div class="text_panel" id="license_panel">
                  <pre>
                  Copyright (c) 2016-2018, Abhishek Dutta, Visual Geometry Group, Oxford University.
                  All rights reserved.

                  Redistribution and use in source and binary forms, with or without
                  modification, are permitted provided that the following conditions are met:

                  Redistributions of source code must retain the above copyright notice, this
                  list of conditions and the following disclaimer.
                  Redistributions in binary form must reproduce the above copyright notice,
                  this list of conditions and the following disclaimer in the documentation
                  and/or other materials provided with the distribution.
                  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS &quot;AS IS&quot;
                  AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
                  IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
                  ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
                  LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
                  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
                  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
                  INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
                  CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
                  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
                  POSSIBILITY OF SUCH DAMAGE.
                </pre>
                </div>

              </div>
            </div>
            <!-- region and file attributes input panel -->
            <div id="attributes_panel" hidden>
              <div id="attributes_panel_toolbar">
                <div onclick="toggle_attributes_input_panel()" class="attributes_panel_button">&times;</div>
              </div>
              <table id="attributes_panel_table"></table>
            </div>
            <!-- to show status messages -->
            <div id="message_panel" hidden></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include(__DIR__ . '/basic/footer.html'); ?>
  <script src="/vendor/select-pure/dist/select-pure.bundle.min.js"></script>

  <script src="/js/compare-norecog.js"></script>
  <script src="/js/enlarge-element.js"></script>
  <!-- <script src="/tour-js/bootstrap-tour.min.js"></script>
  <script src="/tour-js/bootstrap-tour-standalone.min.js"></script> -->

  <script>
    var url = new URL(window.location.href);
    var id = url.searchParams.get("id");
    var file_id_dest = url.searchParams.get("file_id_dest");
    var drawColor = 'red';
    var countRed = 0;
    var countAll = 0;
    var module_id;
    var module_name = '業務';
    var allState = [];
    var customerCode = [];
    var itemno = '';
    var order_name = '';
    var rotate = 0;
    var fileuploaded = true;


    $(function() {
      getitemno()
      getModule();
      getListState(id);
      // inspin();
      getCustomerCodes();
      // getCrops(id);
      getPicture(id);
      $('#inputdelivery_week').on('change', function() {
        $.ajax({
          url: `/file/delivery_week`,
          type: 'patch',
          data: {
            file_id: id,
            delivery_week: $('#inputdelivery_week').val()
          },
          dataType: 'json',
          success: function(response) {

          }
        });
      });
      $('#inputdelivery_date').on('change', function() {
        $.ajax({
          url: `/file/delivery_date`,
          type: 'patch',
          data: {
            file_id: id,
            delivery_date: $('#inputdelivery_date').val().replace('T', ' ')
          },
          dataType: 'json',
          success: function(response) {

          }
        });
      });
    })
      // $('#custom_rego').EnlargeElement("600px");
    // $("#custom_rego").on({
    //     // $( document ).width();
    //     mouseenter: function () {
    //         //stuff to do on mouse enter
    //         $("#custom_rego").css("width","600")
    //     },
    //     mouseleave: function () {
    //         //stuff to do on mouse leave
    //         $("#custom_rego").css("width","")

    //     }
    // });

    $('#input_custom_id').on('change', function() {
      $.ajax({
        url: `/file/custom_id`,
        type: 'patch',
        data: {
          file_id: id,
          custom_id: $('#input_custom_id').val()
        },
        dataType: 'json',
        success: function(response) {

        }
      });
    });
    
    $(document).on('input', '[name="inputFileComment"]', function() {
      insaveFileComment();
    });
    let timeoutInSave = null
    function insaveFileComment(){
      clearTimeout(timeoutInSave);
      timeoutInSave = setTimeout(function() {
        $.ajax({
          url: `/file/file_comment`,
          type: 'post',
          data: {
            file_id : id,
            module_id : module_id,
            comment : $('[name="divFileComment"][data-type="top"]').find('[name="inputFileComment"]').val(),

          },
          success: function(response) {
            
          }
        });
      }, 1000);
    }
    function getcanvas(){
      $.ajax({
        url: `/file/file_comment/canvas`,
        type: 'get',
        data: {
          file_id : id,
          module_id : module_id,
        },
        success: function(response) {
        
          $(response).each(function(){
            $('[name="inputFileComment"]').val(this.comment)
          })
        }
      });
        
    }

    function rotatepic(tmpint) {
      let rotate = parseInt($('#rotatepic').val());
      rotate += tmpint;
      // if(rotate<0){
      //   rotate+=360;
      // }
      rotate %= 360;
      $('#rotatepic').val(rotate)
      $('#rotatepic').change()
    }
    jQuery.fn.rotate = function(degrees) {
      $(this).css({
        'transform': 'rotate(' + degrees + 'deg)'
      });
    };
    let TimerRotate = null;
    $(document).on('change', '#rotatepic', function() {
      let rotate = parseInt($('#rotatepic').val());
      $('#display_area').rotate(rotate);
      if (TimerRotate != null)
        clearTimeout(TimerRotate)
      TimerRotate = setTimeout(() => {
        $.ajax({
          url: `/file/rotate`,
          type: 'patch',
          data: {
            id: id,
            rotate: rotate
          },
          dataType: 'json',
          success: function(response) {
            $('#rotatepic').val(0)
            getPicture(id)
          }
        });
      }, 2000);
    });






    function getCustomerCodes() {
      $.ajax({
        url: `/business/customerCodes`,
        type: 'get',
        data: {},
        dataType: 'json',
        success: function(response) {

          $.each(response, function() {
            let tmpObj = new Object();
            tmpObj['label'] = this.客戶名稱
            tmpObj['value'] = this.客戶代號
            customerCode.push(tmpObj);
          })

          getCustomerCode();
        }
      });
    }

    function getCustomerCode() {
      $.ajax({
        url: `/business/customerCode`,
        type: 'get',
        data: {
          file_id: id
        },
        dataType: 'json',
        success: function(response) {
          let tmpArr = '';
          $.each(response, function() {
            tmpArr = this.customer
          });
          // console.log(tmpArr)
          let selectpurecustomerCode = new SelectPure('#divcustomerCode', {
            options: customerCode,
            onChange: value => {
              postcustomerCode(value)
            },
            autocomplete: true,
            icon: "fa fa-times",
            inlineIcon: false,
            value: tmpArr,
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
        }
      });
    }

    function postcustomerCode(value) {
      console.log(value)
      $.ajax({
        url: `/business/customerCode`,
        type: 'post',
        data: {
          file_id: id,
          customer: value,
        },
        dataType: 'json',
        success: function(response) {

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
          getcanvas();
        }
      });
    }

    function changeColor(tmpcolor) {
      drawColor = tmpcolor;
      if (tmpcolor == 'red') {
        $('[name="btnChangeColor"][data-color="red"]').attr('class', 'btn btn-primary')
        $('[name="btnChangeColor"][data-color="blue"]').attr('class', 'btn btn-secondary')
        // VIA_THEME_BOUNDARY_FILL_COLOR = '#ffaaaa'
      } else {
        $('[name="btnChangeColor"][data-color="blue"]').attr('class', 'btn btn-primary')
        $('[name="btnChangeColor"][data-color="red"]').attr('class', 'btn btn-secondary')
        // VIA_THEME_BOUNDARY_FILL_COLOR = '#aaeeff'

      }
    }


    function getListState(file_id) {
      window.sharedVariable = {
        file_id: file_id,
        module_name: '業務'
      };
      $("#fileState").load(`/file/state`, function() {

      });

      $.ajax({
        url: `/file/state/${file_id}`,
        type: 'get',
        data: {
          module_name: module_name
        },
        dataType: 'json',
        success: function(response) {
          $(response.state).each(function(index) {
            if (this.module_name == module_name) {
              allState.push(this.progress)
            }
          });
        }
      });

    }
    $('#uploadDropzone').attr('action', `/file?mode=textrecog&id=${id}`)
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

    $('#exampleModal').on('show.bs.modal', function(event) {
      // console.log($(event.relatedTarget).attr("data-type"));
      var type = $(event.relatedTarget).attr("data-type");
      $('#exampleModal .modal-footer').html('<button class="btn btn-secondary" type="button" data-dismiss="modal">取消</button>');
      if (type == 'selectItemNO') {

        $("#exampleModal .modal-dialog ").attr("class", "modal-dialog modal-xl");

        selectItemNO();
      } else {
        $("#exampleModal .modal-dialog ").attr("class", "modal-dialog");

      }

    });

    function getitemno() {
      $.ajax({
        url: `/file/information`,
        type: 'get',
        data: {
          file_id: id,
        },
        dataType: 'json',
        success: function(response) {
          // let delivery_date;
          let delivery_week,custom_id='';
          $.each(response, function() {
            itemno = this.itemno
            // delivery_date = this.delivery_date.replace(" ", "T");
            delivery_week = this.delivery_week;
            rotate = parseInt(this.rotate);
            order_name = this.order_name;
            custom_id = this.custom_id
          })
          rotate %= 360;
          $('#inputitemNo').val(itemno)
          $('[name=inputitemNo]').val(itemno)
          // $('#inputdelivery_date').val(delivery_date)
          $('#inputdelivery_week').val(delivery_week)
          $('#input_custom_id').val(custom_id)
          $('#rotatepic').val(rotate)

          // insertordername()
          resetTable();
        }
      });
    }

   
    function updateItemNO() {
      $('[name="radioItemNO"]:checked').each(function() {
        itemno = $(this).val()
      })
      $('#exampleModal').modal('hide');
      $('[name=inputitemNo]').val(itemno)
      $('#inputitemNo').val(itemno)
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

    function selectItemNO() {

      $('#exampleModal .modal-title').html('選擇品號')
      $('#exampleModal .modal-footer').append(`<button type="button" class="btn btn-primary" onclick="updateItemNO()">下一步</button>`)
      $('#exampleModal .modal-body').html(`
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
    let timeout = null;
    $(document).on('input', '[name="filteritemno"]', function() {
      clearTimeout(timeout);
      timeout = setTimeout(function() {
        setitemnoTable();
      }, 1000)
    })

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

    function inspin() {
      $('#inspin').html(`
      <div class="d-flex align-items-center">
        <div class="spinner-border ml-auto  text-primary" role="status" aria-hidden="true"></div>
        <strong>辨識中...可自行操作</strong>

      </div>
      
     `)
      //   $('#exampleModal .modal-title').html('讀取中')
      //   $('#exampleModal .modal-footer').html('')
      //   $('#exampleModal .modal-body').html(`<div class="spinner-border text-primary" role="status">
      //   <span class="sr-only">Loading...</span>
      // </div>`);
      //   $('#exampleModal').modal('show');
    }


    function matchCustomer(id) {
      $.ajax({
        url: '/file/match/customer',
        type: 'get',
        dataType: 'json',
        data: {
          id: id
        },
        success: function(response) {
          if($(document).find('#divcustomerCode').find('.select-pure__option--selected').length!=0 && fileuploaded ){
            return ;
          }
          if(response.value == null){
            return
          }
          fileuploaded = true;
          $('#divcustomerCode').html('')
          let tmpArr;
          // tmpArr.push( response.value)
          tmpArr = (response.value);
          postcustomerCode(response.value);
          // $.each(response, function() {
          //   // tmpArr.push(this.customer)
          //   tmpArr = this.value;
          //   postcustomerCode(this.value);
          // });
          let selectpurecustomerCode = new SelectPure('#divcustomerCode', {
            options: customerCode,
            onChange: value => {
              postcustomerCode(value)
            },
            autocomplete: true,
            icon: "fa fa-times",
            inlineIcon: false,
            // value: tmpArr,
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
          $(document).find('#divcustomerCode').find(`[data-value*="${(tmpArr)}"]`).click();
          $(document).find('#divcustomerCode').find(`[data-value*="${(tmpArr)}"]`).click();


        }
      })
    }


    function ingetCrop(file_id) {
      $.ajax({
        url: '/file/crop',
        type: 'get',
        dataType: 'json',
        data: {
          id: file_id
        },
        success: function(response) {
          let td1 = '<tr>';
          let td2 = '<tr>';
          $('#cropTable tbody').html('');
          $.each(response, function(key, value) {
            td1 += `<td  name="">
                <img src="/fileCrop/${value.id}" style="width:100px;height:100px" class="col mx-auto d-block figure-img img-thumbnail rounded" alt="..." />
              </td>`;
            td2 += `<td name=""><input class="form-control" type="checkbox" name="cropCheckbox" ${value.isPart==true?'checked':''} id="" value="${value.id}"></td>`;


          })
          td1 += '</tr>'
          td2 += '</tr>'
          $('#cropTable tbody').append(`<tr>${td1+td2}</tr>`);
        }
      });
    }



    let process = [];
    Dropzone.options.uploadDropzone = {
      dictDefaultMessage: '拖曳客戶圖至此 或 點此選擇檔案',
      addRemoveLinks: true,
      maxFiles: 1,
      acceptedFiles: '.pdf,.jpg',
      timeout: 300000,
      /*milliseconds*/
      init: function() {
        this.on("success", function(file, response) {
            fileuploaded = false;
            _via_canvas_regions = [];
            resetTable();
            inspin();
            getPicture(id)
            ingetCrop(id);
          }),
          this.on("removedfile", function(file) {
            $.ajax({
              url: '/file',
              type: 'delete',
              dataType: 'json',
              data: {
                id: id
              },
              success: function(response) {}
            })
          });
      },
      success: function(file, response) {

      }
    };

    function insertModal() {
      var order = $('[name="exampleRadios"]:checked').closest('tr').find('input[type="text"]').val();

      $('#exampleModal .modal-title').html('選擇客戶圖號');
      console.log($('[name="exampleRadios"]:checked'))
      if ($('[name="exampleRadios"]:checked').length > 0) {
        $('#exampleModal .modal-body').html(`客戶圖號為${order}`);
        $('#exampleModal .modal-footer').append(`<button class="btn btn-primary" type="button" id="btnInsert" >確定</button>`);
      } else {
        $('#exampleModal .modal-body').html(`尚未選擇客戶圖號`);
      }


      $('#btnInsert').on('click', function() {
        $.ajax({
          url: '/file',
          type: 'patch',
          dataType: 'json',
          data: {
            id: id,
            order: order
          },
          success: function(response) {
            $('#exampleModal').modal('hide');
          }
        });
      });


    }

    function nextModal() {
      // saveCrop()
      var order = $('[name="exampleRadios"]:checked').closest('tr').find('input[type="text"]').val();

      $('#exampleModal .modal-title').html('選擇客戶圖號');
      console.log($('[name="exampleRadios"]:checked'))
      if ($('[name="exampleRadios"]:checked').length > 0) {
        $('#exampleModal .modal-body').html(`客戶圖號為${order}`);
        $('#exampleModal .modal-footer').append(`<button class="btn btn-primary" type="button" id="btnInsert" >確定</button>`);
      } else {
        $('#exampleModal .modal-body').html(`尚未選擇客戶圖號`);
      }


      $('#btnInsert').on('click', function() {
        saveCrop()
      });


    }

    function updateLogo(ele, type) {
      let element = $(ele).closest('tr');
      let tmpObj = _via_canvas_regions[$(element).find('td:first-child').html()]
      let box = [];
      var widthRadio = _via_canvas_width / _via_current_image_width;
      var heightRadio = _via_canvas_height / _via_current_image_height;
      box.push(parseInt(tmpObj['shape_attributes']['x'] / widthRadio))
      box.push(parseInt(tmpObj['shape_attributes']['y'] / heightRadio))
      box.push(parseInt(tmpObj['shape_attributes']['width'] / widthRadio))
      box.push(parseInt(tmpObj['shape_attributes']['height'] / heightRadio))

      $.ajax({
        url: '/file/logo',
        type: 'post',
        dataType: 'json',
        data: {
          id: id,
          value: $(element).find('[name="inputRecog"]').val(),
          box: box,
          type: type

        },
        success: function(response) {
          $(response).each(function() {
            // window.location.href = `${this.url}?id=${id}`
          })
        }
      });
    }




    function nextpage() {
      var order = $('[name="exampleRadios"]:checked').closest('tr').find('input[type="text"]').val();

      let rotate = $('#rotatepic').val();
      $.ajax({
        url: `/file/rotate`,
        type: 'patch',
        data: {
          id: id,
          rotate: rotate
        },
        dataType: 'json',
        success: function(response) {}
      });
      $.ajax({
        url: '/file',
        type: 'patch',
        dataType: 'json',
        data: {
          id: id,
          order: order
        },
        success: function(response) {
          $(response).each(function() {
            window.location.href = `${this.url}?id=${id}`
          })
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
          nextpage()
        }
      })

    }

    function saveCrop() {
      let tmpArr = [];
      $('[name="cropCheckbox"]:not(:checked)').each(function() {
        tmpArr.push($(this).val());

      })

      $.ajax({
        url: '/file/crop',
        type: 'patch',
        dataType: 'json',
        data: {
          id: id,
          array: tmpArr
        },
        success: buttonPass
      });
    }


    function buttonPass() {
      // saveCrop();
      let file_id = id;
      $('#basicModal').find('.modal-header').text(``);
      $('#basicModal').find('.modal-body').text(`請稍等...`);
      $('#basicModal').find('.modal-footer').text(``);
      $('#basicModal').modal('show');
      console.log(module_id)
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
          if (allState.includes('已查詢歷史訂單') && allState.includes('已全圖比對') && allState.includes('已完成報價') && moduleArr.length > 0) {
            sendemail(moduleArr)
          } else {
            nextpage()
          }
        }
      })
    }

    $(function() {
      // $('[data-toggle="tooltip"]').tooltip()
      $('[data-toggle="tooltip"]').tooltip();
    })
    // var tour = new Tour({
    //   steps: [{
    //       placement: "top",
    //       element: "#fileUpdate",
    //       title: "檔案上傳區",
    //       content: `上傳方式:1. 點此並擇檔案進行上傳、2. 拖曳客戶圖至此上傳，檔案格式:jpg、PDF`,
    //       backdrop: true
    //     },
    //     {
    //       placement: "top",
    //       element: "#OCR",
    //       title: "文字辨識",
    //       content: "若客戶圖號無法順利辨識，請利用旋轉按鈕將客戶圖轉正",
    //       backdrop: true
    //     },
    //     {
    //       placement: "top",
    //       element: "#OCRResult",
    //       title: "客戶圖號辨識",
    //       content: "選擇客戶圖號，若辨識結果有誤，於文字框中直接修改即可",
    //       backdrop: true
    //     },
    //     {
    //       placement: "top",
    //       element: "#custom",
    //       title: "客戶代號/交期",
    //       content: "選擇客戶名稱、產品品號、產品交期",
    //       backdrop: true
    //     },
    //     {
    //       placement: "top",
    //       element: "#components",
    //       title: "客戶零件辨識",
    //       content: "請選擇切割結果為零件圖的項目",
    //       backdrop: true
    //     },
    //     {
    //       placement: "top",
    //       element: "#action",
    //       title: "動作",
    //       content: "若要儲存步前進至查詢歷史訂單請點選確認，若要前進至查詢歷史訂單請點選下一步，將會自動儲存並前往下一階段",
    //       backdrop: true
    //     },
    //   ],
    //   template: `<div class='popover tour'>
    //                 <div class='arrow'></div> 
    //                 <h3 class='popover-title'></h3>
    //                 <div class='popover-content'></div>
    //                 <nav class='popover-navigation'>
    //                     <div class='btn-group'>
    //                         <button class='btn btn-primary btn-sm' data-role='prev'>上一步</button>
    //                         <button class='btn btn-primary btn-sm' data-role='next'>下一步</button>
    //                     </div>
    //                     <button class='btn btn-secondary btn-sm' data-role='end'>Close</button>
    //                 </nav>
    //               </div> `,
    // });

    // Initialize the tour
    // tour.init();

    // $("#fileUpdateBtn").click(function() {
    //   tour.restart();
    // });
    // $("#OCRBtn").click(function() {
    //   tour.restart();
    //   tour.goTo(1);
    // });
    // $("#OCRResultBtn").click(function() {
    //   tour.restart();
    //   tour.goTo(2);
    // });
    // $("#customBtn").click(function() {
    //   tour.restart();
    //   tour.goTo(3);
    // });
    // $("#componentsBtn").click(function() {
    //   tour.restart();
    //   tour.goTo(4);
    // });
    // $("#actionBtn").click(function() {
    //   tour.restart();
    //   tour.goTo(5);
    // });
  </script>