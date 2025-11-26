<?php include(__DIR__ . '/basic/header.html'); ?>

<style type="text/css">
  .scroll-to-down {
    position: fixed;
    right: 1rem;
    bottom: 1rem;
    display: none;
    width: 2.75rem;
    height: 2.75rem;
    text-align: center;
    color: #fff;
    background: rgba(90, 92, 105, 0.5);
    line-height: 46px;
  }

  .scroll-to-down:focus,
  .scroll-to-down:hover {
    color: white;
  }

  .scroll-to-down:hover {
    background: #5a5c69;
  }

  .scroll-to-down i {
    font-weight: 800;
  }

  .circleBase {
    border-radius: 50%;
    behavior: url(PIE.htc);
    /* remove if you don't care about IE8 */
  }

  .type2 {
    width: 25px;
    height: 25px;
    background: #ccc;
    border: 3px solid #000;
  }
</style>
<h3 class=" text-center">訊息</h3>
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-lg-4">
        <div class="card" style="height:65vh">
          <div class="card-header">
            <div class="row">
              <div class="col-6">
                <h4>議題列表</h4>
              </div>
              <div class="col-6">
                <div class="btn-group float-right">
                  <button class="btn btn-secondary fa fa-folder" type="button" data-toggle="modal" data-target="#basicModal" data-type="addClass"></button>
                  <button class="btn btn-secondary " type="button" data-toggle="modal" data-target="#basicModal" data-type="create">+</button>
                </div>
              </div>
            </div>
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
              </div>
              <input type="text" id="searchChatroomInput" class="form-control">
            </div>
          </div>
          <div class="card-body overflow-auto" name="inbox_chat">
          </div>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="card" style="height:65vh">
          <div class="card-header">
            <nav class="navbar navbar-expand navbar-light bg-light d-flex justify-content-between">
              <a class="navbar-brand" name="navbarChatroomTitle"></a>
              <div class="btn-group">
                <button type="button" class="btn btn-light dropdown-toggle text-dark bg-light" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false" id="tool_dropdown" style="display:none;">
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <button class="dropdown-item" type="button-light" data-toggle="modal" data-target="#basicModal" data-type="member">成員列表</button>
                  <div class="dropdown-divider"></div>
                  <button class="dropdown-item" type="button" data-toggle="modal" data-target="#basicModal" data-type="insertClass">加入議題類別</button>
                  <div class="dropdown-divider"></div>
                  <button class="dropdown-item" type="button" data-toggle="modal" data-target="#basicModal" data-type="delete">離開議題</button>
                </div>
              </div>
            </nav>
          </div>
          <div class="card-body overflow-auto msg_history">
            <div class="h-100 d-flex flex-column">
              <div class="card flex-grow-1 mb-3">
                <div class="card-body" name="chatBox">
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="align-bottom">
              <div class="dropup" id="dropupTag">
                <div class="dropdown-menu show" aria-labelledby="dropdownMenuButton" id="tagPeople">
                  <a class="dropdown-item" href="#">Action</a>
                  <a class="dropdown-item" href="#">Another action</a>
                  <a class="dropdown-item" href="#">Something else here</a>
                </div>
              </div>
              <div class="d-flex">
                <a class="scroll-to-down rounded">
                  <i class="fas fa-angle-down"></i>
                </a>
                <div class="w-100">
                  <textarea class="form-control" style="word-wrap:break-word;width:100%;" placeholder="請在此輸入訊息，ENTER可以換行&#13;&#10;SHIFT+ENTER送出訊息" id="textinput"></textarea>
                </div>
                <input style="display:none;" type="file" name="inputFile">
                <div class="btn-group dropup">
                  <div class="flex-shrink-1  align-self-center ml-1">
                    <button type="button" class="btn btn-secondary btn-block far fa-smile " aria-haspopup="true" aria-expanded="false" id="btnEmoji">
                    </button>
                  </div>
                  <div class="dropdown-menu overflow-auto" aria-labelledby="dropdownMenuEmoji" id="dropdownMenuEmoji" style="display:none;height:25vh">
                    <div class="btn-group" role="group" aria-label="Basic example">
                      <button type="button" name="emojiPic" class="btn badge badge-light ml-1-">&#128512;</button>
                      <button type="button" name="emojiPic" class="btn badge badge-light ml-1-">&#128513;</button>
                      <button type="button" name="emojiPic" class="btn badge badge-light ml-1-">&#128514;</button>
                    </div>
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                  </div>
                </div>


                <div class="flex-shrink-1  align-self-center ml-1">
                  <button class="btn btn-secondary btn-block far fa-paper-plane msg_send_btn" name="ButtonMsgSend" type="button"></button>
                </div>
                <div class="flex-shrink-1  align-self-center ml-1">
                  <button class="btn btn-secondary " type="button" onclick="uploadFile(this)">+</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Basic Modal-->
<div class="modal fade" id="basicModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ready to Leave?</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-dismiss="modal">關閉</button>
      </div>
    </div>
  </div>
</div>
<!-- double Modal-->
<div class="modal fade" id="newModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
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

<?php include(__DIR__ . '/basic/footer.html'); ?>

<script type='text/javascript'>
  //window focus
  var isTabActive = true;
  var titleOrg = $('title').text();
  $(function() {
    titleOrg = $('title').text();
  });
  var notify = [];
  window.onfocus = function() {
    window.isTabActive = true;
    $('title').text(titleOrg);
    updateLastReadTime();
  };

  window.onblur = function() {
    window.isTabActive = false;
  };
  //focus end

  $('[name=btnStarNotification]').parent().show();
  $(function() {
    $('#btnEmoji').unbind().on('click', function() {
      if ($('#dropdownMenuEmoji').css('display') != 'none') {
        $('#dropdownMenuEmoji').hide();
      } else {
        $('#dropdownMenuEmoji').empty();
        var tmpEmojiNum = 128512;
        $('#dropdownMenuEmoji').append(`<div class="btn-group" role="group" aria-label="Basic example">`);
        for (var i = 0; i < 80; i++) {
          $('#dropdownMenuEmoji').append(`<button type="button" name="emojiPic" class="btn btn-sm btn-light ml-1-">&#${tmpEmojiNum}</button>`);
          tmpEmojiNum++;
        }
        //打勾
        $('#dropdownMenuEmoji').append(`<button type="button" name="emojiPic" class="btn btn-sm btn-light ml-1-">&#${10004}</button>`);
        //叉叉
        $('#dropdownMenuEmoji').append(`<button type="button" name="emojiPic" class="btn btn-sm btn-light ml-1-">&#${10005}</button>`);
        $('#dropdownMenuEmoji').append(`</div>`);
        $('#dropdownMenuEmoji').show();
        $('[name=emojiPic]').unbind().on('click', function() {
          console.log($(this).text());
          var cursorPos = $('#textinput').getCursorPosition();
          var v = $('#textinput').val();
          var textBefore = v.substring(0, cursorPos);
          var textAfter = v.substring(cursorPos, v.length);

          $('#textinput').val(textBefore + $(this).text() + textAfter);
          $('#dropdownMenuEmoji').hide();
        });
      }
    });
  });



  $(function() {
    $('[name=btnStarNotification]').unbind().on('click', function() {
      // console.log("in");
      getStarNotification();
    });
  });

  function getStarNotification() {
    $('[name=starDropdown]').empty();
    $.ajax({
      url: '/chat/star',
      type: 'get',
      dataType: 'json',
      success: function(response) {
        // console.log(response);
        $('[name=starDropdown]').append('<h6 class="dropdown-header">待辦事項</h6>');
        $(response).each(function() {
          // console.log(this.sendtime);
          $('[name=starDropdown]').append(
            '<a class="dropdown-item d-flex align-items-center" id="star' + this.id + '" style=" z-index:9999;" data-time="' + this.fullsendTime + '" onclick="starNotificationOnclick(' + this.chatID + ',\'' + encodeURIComponent(this.chatName) + '\',' + this.id + ',this);"' +
            '<div class="mr-3">' +
            '<div class="icon-circle bg-warning">' +
            '<i class="fas fa-exclamation-triangle text-white"></i>' +
            '</div>' +
            '</div>' +
            '<div>' +
            '<div class="small text-gray-500">' +
            this.sentTime +
            '</div>' +
            '<span class="font-weight-bold">' +
            this.detail +
            '</span>' +
            '</div>' +
            '</a>'
          );
          // if(this.unread == true){
          //   // console.log("unread");
          //   $("#notification"+this.id).css("background-color", "#F0F8FF");
          // }else{
          //   $("#notification"+this.id).css("background-color", "#FFFFFF");
          // }
        });
        // $('[name=starDropdown]').append('<a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>');
      }
    });
  }

  function starNotificationOnclick(chatID, chatName, id, attr) {

    tmpTagMsg = $(attr).data('time');
    getTarget(chatID, chatName);
  }


  function starOnclick(cliclTime, chatID, content, button) {
    if ($(button).find("i").css("color") == 'rgb(170, 170, 170)') {
      $(button).find("i").css("color", "#FFBB00");
      $('[name=starNotificationNum]').text(parseInt($('[name=starNotificationNum]').text()) + 1);
      $.ajax({
        url: '/chat/star',
        type: 'post',
        data: {
          data: JSON.stringify({
            chatID: chatID,
            time: cliclTime,
            content: content
          })
        },
        dataType: 'json',
        success: function(response) {
          console.log(response);
        }
      });
    } else {
      $(button).find("i").css("color", "#AAAAAA");
      $('[name=starNotificationNum]').text(parseInt($('[name=starNotificationNum]').text()) - 1);
      $.ajax({
        url: '/chat/star',
        type: 'post',
        data: {
          data: JSON.stringify({
            chatID: chatID,
            time: cliclTime,
            content: content
          }),
          _METHOD: 'DELETE'
        },
        dataType: 'json',
        success: function(response) {
          console.log(response);
        }
      });
    }
  }



  if (window.innerWidth <= 700) $('.navbar-collapse').removeClass('show');
  var basicModalFooter = '<button class="btn btn-secondary" type="button" data-dismiss="modal">關閉</button>';
  $('.msg_history').on("scroll", function() {
    if ($(this)[0].scrollHeight - $(this)[0].clientHeight > $(this).scrollTop()) {
      $(".scroll-to-down").fadeIn();
      scrollable = true;
    } else {
      $(".scroll-to-down").fadeOut()
      scrollable = false;
    }
    if ($(this).scrollTop() == 0) {
      expendLimit();
    }
  });
  $('.scroll-to-down').unbind().on('click', function() {
    scrollable = false;
    $('.msg_history').scrollTop($('.msg_history')[0].scrollHeight);
  });
  var queue = [];
  queue['chatroom'] = null;
  queue['commentreadtime'] = null;
  var scrollable = false;


  var todatDate = null;

  function init() {
    todayDate = Date.now();
    $.ajax({
      url: '/chat/init/' + todayDate,
      type: 'get',
      dataType: 'json',
      success: function(response) {
        // console.log(response)
        if (response.status == 'success') {
          $.each(response, function(key, value) {
            if (key == 'class') {
              changeClass('init', value);
            } else if (key == 'chatroom') {
              let chatIDs = new Object();
              $.each(value, function(i, data) {
                chatIDs[i] = data.chatID;
              })
              chatIDs = JSON.stringify(chatIDs);
              // console.log(chatIDs);
              socketChat(chatIDs);
              changeChatroom('init', value);
            } else if (key == 'notification') {
              changeNotification('init', response.notification);
            } else if (key == 'star') {
              changeStar('init', response.star);
            }
          });
        }
      }
    });
  }
  init();

  function socketChat(chatIDs) {
    // websocket 連線
    // const conn = new WebSocket('ws://172.24.40.106:28080');
    const conn = new WebSocket('ws://localhost:8000');
    conn.onopen = function(e) {
      console.log("Connection established!");
      conn.send(chatIDs);
    };
    conn.onmessage = function(e) {
      console.log(e.data);
      routine();
    };
  }

  var ajax = null;

  function routine() {
    if (ajax != null)
      ajax.abort();
    ajax = $.ajax({
      url: '/chat/routine/' + todayDate + '/' + chatID,
      type: 'get',
      dataType: 'json',
      success: function(response) {
        console.log(response)
        if (response.status == 'success') {
          $.each(response.result, function(key, value) {
            if (key == 'class') {
              changeClass('routine', value, response.class);
            } else if (key == 'chatroom') {
              changeChatroom('routine', response);
            } else if (key == 'chat') {
              console.log("changeHeart")
              changeChat('routine', response);
              changeStar('routine', response);
              changeComment('routine', response);
              changeHeart(response);
              changeDelete('routine', response);
            } else if (key == 'notification') {
              changeNotification('routine', response.notification);
            } else if (key == 'readCount') {
              changeReadCount('routine', response);
            }
          });
        }
        if (tmpTagMsg != "") {
          // console.log("in");
          //console.log(tmpTagMsg);
          // console.log($('.incoming_msg[data-senttime="'+tmpTagMsg+'"]')[0].scrollHeight);
          // console.log($('.sent_msg[data-senttime="'+tmpTagMsg+'"]')[0].scrollHeight);
          // 

          scrollToTag()
        }
        // routine();
      }
    });
  }

  function changeDelete(type, data) {
    if (type == 'saveChat') {
      $.each(data.delete.delete, function() {
        $(`[name=outgoingBox][data-senttime="${this.sentTime}"]`).html(`
        <div class="d-flex flex-row-reverse bd-highlight">
          <div class="p-2 bd-highlight bg-secondary text-white rounded" >
            此訊息已刪除
          </div>
        </div>
        <small>${this.showTime}</small>
      `);
      });
      $.each(data.delete.other, function() {
        $(`[name=incomingBox][data-senttime="${this.sentTime}"]`).html(`
        <div class="d-flex bd-highlight">
          <div class="p-2 bd-highlight bg-dark text-white rounded">
            此訊息已刪除
          </div>
        </div>
        
        <small>${this.showTime}</small>
      `);

      });
    } else if (type == 'routine') {
      $.each(data.result.delete.new, function() {
        $(`[name=outgoingBox][data-senttime="${this.sentTime}"]`).html(`
        <div class="d-flex flex-row-reverse bd-highlight">
          <div class="p-2 bd-highlight bg-secondary text-white rounded" >
            此訊息已刪除
          </div>
        </div>
        <small>${this.showTime}</small>
      `);
      });
      $.each(data.result.delete.newOther, function() {
        $(`[name=incomingBox][data-senttime="${this.sentTime}"]`).html(`
          <div class="d-flex bd-highlight">
            <div class="p-2 bd-highlight bg-dark text-white rounded">
              此訊息已刪除
            </div>
          </div>
          
          <small>${this.showTime}</small>
        `);

      });
    }
  }

  function changeHeart(data) {
    console.log("changeHeart")
    $.each(data.result.heartNum.new, function() {
      $(`[name=badgeLike][data-senttime="${this.sentTime}"]`).html(`
      <i class="fa fa-heart mr-1" aria-hidden="true"></i>${this.count}
      `);
    });
    $.each(data.result.heartNum.delete, function() {
      $(`[name=badgeLike][data-senttime="${this.sentTime}"]`).html(`
      <i class="fa fa-heart mr-1" aria-hidden="true"></i>0
      `);
    });
    $.each(data.result.heartClick.new, function() {
      $(`[name=badgeLike][data-senttime="${this.sentTime}"]`).css("color", "#d9534f");
      $(`[name=badgeLike][data-senttime="${this.sentTime}"]`).attr("data-isClick", 'true');
    });
    $.each(data.result.heartClick.delete, function() {
      $(`[name=badgeLike][data-senttime="${this.sentTime}"]`).css("color", "#AAAAAA");
      $(`[name=badgeLike][data-senttime="${this.sentTime}"]`).attr("data-isClick", 'false');
    });
    $.each(data.result.heartNum.change, function() {
      $(`[name=badgeLike][data-senttime="${this.sentTime}"]`).html(`<i class="fa fa-heart mr-1" aria-hidden="true"></i>${this.count}`);
    });
  }

  function changeComment(type, data) {
    $.each(data.result.comment.new, function() {
      if (this.count != 0) {
        $(`[name=iconComment][data-senttime="${this.sentTime}"]`).append(`${this.count}`);
      }

    });
    $.each(data.result.comment.change, function() {
      if (this.count != 0) {
        $(`[name=iconComment][data-senttime="${this.sentTime}"]`).html(`<i class="fa fa-reply" aria-hidden="true"></i>${this.count}`);
      }
    });
  }

  function changeStar(type, data) {
    if (type == 'init') {
      $.each(data.num, function() {
        // console.log(this.count);
        $('[name=starNotificationNum]').empty();
        $('[name=starNotificationNum]').append(this.count);
      });
    } else if (type == 'routine') {
      console.log(data.result.star);
      // if(data.result.star.change != ''){
      //   $('[name=starNotificationNum]').empty();
      //   $('[name=starNotificationNum]').append(data.change);
      // }
      $.each(data.result.star.change, function() {
        console.log(this);
        $('[name=starNotificationNum]').empty();
        $('[name=starNotificationNum]').append(this);
      });
      $.each(data.result.star.new, function() {
        // console.log(this.sentTime);
        // console.log($('[name="starBtn"]'));
        // console.log($('[name=starBtn]').find('[data-senttime="'+this.sentTime+'"]'));

        // $('button[name=starBtn]').find("i").css("color","#FFBB00");
        $('[name=starBtn]').find('[data-senttime="' + this.sentTime + '"]').css("color", "#FFBB00");
      });
      $.each(data.result.star.delete, function() {
        // console.log(this.sentTime);
        // console.log("delete");

        $('[name=starBtn]').find('[data-senttime="' + this.sentTime + '"]').css("color", "#AAAAAA");
      });


    }
  }

  function scrollToTag() {
    //console.log($('.outgoing_msg[data-senttime = "'+tmpTagMsg+'"]').length);
    if ($('.outgoing_msg[data-senttime = "' + tmpTagMsg + '"]').length > 0)
      $('.msg_history').scrollTop($('.outgoing_msg[data-senttime = "' + tmpTagMsg + '"]')[0].offsetTop - $('.msg_history')[0].offsetTop + $('.outgoing_msg[data-senttime = "' + tmpTagMsg + '"]').height());
    else if ($('.incoming_msg[data-senttime = "' + tmpTagMsg + '"]').length > 0)
      $('.msg_history').scrollTop($('.incoming_msg[data-senttime = "' + tmpTagMsg + '"]')[0].offsetTop - $('.msg_history')[0].offsetTop + $('.incoming_msg[data-senttime = "' + tmpTagMsg + '"]').height());
    if (tagType == 'comment') {
      $('[name = iconComment][data-senttime = "' + tmpTagMsg + '"]').click();
    }
    tagType = "";
    tmpTagMsg = "";
  }

  function changeReadCount(type, data) {
    if (data.result.readCount.new.length != 0 || data.result.readCount.change.length != 0 || data.result.readCount.delete.length != 0) {
      var readcountElement = data.readCount.shift();
      if (readcountElement === undefined)
        return false;
      $('a[data-type=readlist]').each(function() {
        $(this).html('<i class="fa fa-eye" aria-hidden="true"></i>' + readcountElement.sum);
        if ($(this).attr('data-senttime') == readcountElement.sentTime) {
          readcountElement = data.readCount.shift();
          if (readcountElement === undefined)
            return false;
        }
      });
    }
  }

  function changeNotification(type, data) {
    if (type == 'init') {
      // console.log('init');
      //console.log(data[0].count);
      $('[name=notificationNum]').empty();
      $('[name=notificationNum]').append(data[0].count);
    } else if (type == 'routine') {
      //console.log('routine');
      $('[name=notificationNum]').empty();
      $('[name=notificationNum]').append(data[0].count);
    }
  }
  // function schedule(){
  //   // searchChatroom();
  //   // searchChat();
  //   // clearTimeout(queue['chatroom']);
  //   // clearTimeout(queue['chat']);
  //   setTimeout(searchChatroom,1000);
  //   setTimeout(searchChat,1000);
  // }
  // schedule();
  var dd = '';

  function changeClass(type, data, oldClass) {
    function addClass(key, value) {
      // console.log(value);
      $('[name=inbox_chat]').append(
        '<div class="card" name = "class' + value.id + '">' +
        '<div class="card-header" id="headingOne">' +
        '<div class="row" >' +
        '<h2 class="mb-0">' +
        '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#class' + value.id + '" aria-expanded="true" aria-controls="class' + value.id + '">' +
        value.name +
        '</button>' +
        '<h5><span class="badge badge-primary"  data-num="0" id = "countAllUnread' + value.id + '">' + value.sum + '</span></h5>' +
        '</h2>' +
        '</div>' +
        '</div>' +
        '<div id= "class' + value.id + '" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">' +
        '</div>' +
        '</div>'
      );
      if (value.sum == 0) {
        $('#countAllUnread' + value.id).hide();
      }
    }

    function deleteClass(key, value) {
      $('[name=class' + value.id + ']').find('.chat_list').each(function() {
        $('#class0').append($(this).parent());
      });
      $('[name=class' + value.id + ']').remove();
    }
    if (type == 'init') {
      $('[name=inbox_chat]').append(
        '<div class="accordion" id="accordionExample" name="accordionExample">' +
        '</div>'
      );
      $(data).each(addClass);
      $('[name=inbox_chat]').append($('[name=class0]'));
      addClass(null,{id:0,name:"未分類議題",sum:0});
    } else if (type == 'routine') {
      $.each(data.change, function() {
        $('[name=class' + this.id + ']').find('button').text(this.name);
      });
      // $(oldClass).each(addClass);
      $.each(data.changeNum, function() {
        console.log(this.id);
        $('#countAllUnread' + this.id).html(this.sum);
        if (this.sum == 0) {
          $('#countAllUnread' + this.id).hide();
        } else {
          $('#countAllUnread' + this.id).show();
        }
      });
      $.each(oldClass, function() {
        $('[name=inbox_chat]').append($('[name=class' + this.id + ']'));
      });
      $(data.new).each(addClass);
      $('[name=inbox_chat]').append($('[name=class0]'));
      // $.each(data.class,function(){
      //   console.log(this.id);
      // });
      $(data.delete).each(deleteClass);
    }
  }

  function changeChatroom(type, data) {
    function addChatRoom(key, value) {
      // console.log(value);
      var tmpClass = (value.classID == null ? 0 : value.classID);
      var chatName = '';
      if (this.chatName == '') {
        chatName = this.staff_name;
      } else {
        chatName = this.chatName;
      }
      var haveUnread = '';
      clearTimeout(notify['Unread']);
      $('title').text(titleOrg);
      if (value.CountUnread != '0' && value.CountUnread != null) {
        haveUnread = '<span class="badge badge-primary">' + value.CountUnread + '</span> ';
        clearTimeout(notify['Unread']);
        notify['Unread'] = setTimeout(notifyUnread, 1000);
      } else {
        haveUnread = '<span class="badge badge-primary" style="display:none;">' + value.CountUnread + '</span> ';
      }
      $('#class' + tmpClass).append(
        '<div class="card" style="height:10vh" name="room' + value.chatID + '">' +
        '<div class="card-body chat_list" name="beSearchRoom" onclick="getTarget(' + value.chatID + ',\'' + encodeURIComponent(chatName) + '\');" data-name="' + value.chatID + '" data-roomName="' + chatName + '">' +
        '<div class="d-flex">' +
        '<div class="p-2 flex-shrink-1 ">' +
        '<div class="circleBase type2"></div>' +
        '</div>' +
        '<div class="p-2 flex-grow-1  text-body chatName">' +
        chatName +
        haveUnread +
        '</div>' +
        '<div class="p-2 text-body">' +
        '<small>' +
        (value.LastTime == null ? ' ' : value.LastTime) +
        '</small>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>'
      );
    }
    if (type == 'init') {
      $(data).each(addChatRoom);
    } else if (type == 'routine') {
      $.each(data.result.chatroom.new, function() {
        $(this).each(addChatRoom);
      });
      $.each(data.result.chatroom.delete, function() {
        $('[name=room' + this.chatID + ']').remove();
        // $(this).each(addChatRoom);
      });
      // $(data.result.chatroom.new).each(addChatRoom);
      clearTimeout(notify['Unread']);
      $('title').text(titleOrg);
      $.each(data.chatroom, function() {
        var room = $('[name=room' + this.chatID + ']');
        if ($('[name=room' + this.chatID + ']').length == 1) {
          if ($('#class' + this.classID).length == 0) {
            $('#class0').append($('[name=room' + this.chatID + ']'));
          } else {
            $('#class' + this.classID).append($('[name=room' + this.chatID + ']'));
          }
          var chatName = '';
          if (this.chatName == '') {
            chatName = this.staff_name;
          } else {
            chatName = this.chatName;
          }
          room.find('.chat_list').attr('onclick', 'getTarget(' + this.chatID + ',\'' + encodeURIComponent(chatName) + '\');');
          room.find('.chat_list').attr('data-name', this.chatID);
          if (this.CountUnread != '0' && this.CountUnread != null) {
            haveUnread = '<span class="badge badge-primary">' + this.CountUnread + '</span> ';
            clearTimeout(notify['Unread']);
            notify['Unread'] = setTimeout(notifyUnread, 1000);
          } else {
            haveUnread = '<span class="badge badge-primary" style="display:none;">' + this.CountUnread + '</span> ';
          }
          room.find('.chatName').html(
            chatName +
            haveUnread
          );
        }
      });
    }
  }

  function notifyUnread() {
    if ($('title').text().indexOf('您有訊息!!') > -1)
      $('title').text(titleOrg);
    else
      $('title').text('[您有訊息!!]' + titleOrg);
    notify['Unread'] = setTimeout(notifyUnread, 1000);
  }
  var staffStatus;

  function changeChat(type, data) {
    // $('[name=chatBox]').html("");
    $('[name=msgSendNow]').remove();

    if (chatID == -1) {
      return;
    }
    var newChat = [];
    if (!data.result.chat.comchatID) {
      $('[name=chatBox]').html("");
      newChat = data.chat;
    } else {
      for (var i = 0; i < parseInt(data.result.chat.count); i++) {
        newChat.unshift(data.chat[data.chat.length - (1 + i)]);
      }
    }

    if (type == 'saveChat') {
      $('[name=chatBox]').html("");
      newChat = data.tmpchat;
    }

    $(newChat).each(function() {
      var mydate = this.fullsentTime.split(' ')[0];
      if (dd != mydate) {
        $('[name=chatBox]').append(
          '<div class="alert alert-success text-center" role="alert">' +
          this.fullsentTime.split(' ')[0] +
          '</div>'
        );
      }
      dd = mydate;
      if (this.diff != 'me') {
        $('[name=chatBox]').append(
          `<div class="text-left incoming_msg" name="incomingBox" data-sentTime="${this.fullsentTime}">
          <div class=""> <span name="tooltipOnlineTime" data-id=${this.UID}  data-toggle="tooltip" data-placement="right" title="搜尋中...">${this.UID},${this.staff_name}</span></div>
          <div class="d-flex bd-highlight" >
            <div style="max-width:75%" class="p-2 bd-highlight bg-dark text-white rounded text-break;"  name = 'incomeChatbox' data-content="${encodeURIComponent(this.content)}" data-sentTime="${this.fullsentTime}">
            ${this.content.replace(/style="color:#FFFFFF;"/g,'style="color:#CCEEFF;"').replace('<a href="/chat/','<a href="#" data-toggle="modal" data-target="#basicModal" data-type="file" data-href="/chat/')}
            </div>
          </div>
          <button type="button" class="btn badge badge-light ml-1" name="starBtn" href="#" onclick=\'starOnclick(\"${this.fullsentTime}\",\"${chatID}\",\"${this.content}\",this);\'>
            <i class="fa fa-star mr-1" data-sentTime="${this.fullsentTime}" aria-hidden="true" style="color: #AAAAAA;"></i>
          </button>

          <small>${this.sentTime}</small>
          <a target="_blank" href="#" data-toggle="modal" data-target="#basicModal" data-type="readlist" data-content="${encodeURIComponent(this.content)}" data-sentTime="${this.fullsentTime}" data-UID="'+this.UID+'"><i class="fa fa-eye" aria-hidden="true"></i>${this.Read}</a>
          <a style="display" class="btn badge badge-light ml-1" href="#" data-toggle="modal" data-target="#basicModal" data-type="comments" data-likeID="'+this.likeID+'" data-content="${encodeURIComponent(this.content)} "data-sentTime="${this.fullsentTime}" data-UID="${this.UID}" data-readcount="${this.Read}" name="iconComment"><i class="fa fa-reply" aria-hidden="true"></i></a>
                 <button class="btn badge badge-light ml-1" name="badgeLike" style="color: #AAAAAA;" href="#" data-content="${encodeURIComponent(this.content)}" data-sentTime="${this.fullsentTime}" data-UID="${this.UID}" data-isClick="false" onclick=\'onclickHeart(this,\"${this.fullsentTime}\");\'><i class="fa fa-heart mr-1" aria-hidden="true"></i>0</button> 
	</div>`
        );
      } else {
        $('[name=chatBox]').append(
          '<div name="outgoingBox" class="text-right outgoing_msg" data-sentTime="' + this.fullsentTime + '">' +
          '<div class="d-flex flex-row-reverse bd-highlight">' +
          '<div style="max-width:75%" class="p-2 bd-highlight bg-secondary text-white rounded text-break" name="contentBox" ondblclick="ondblclickMessage(this)" data-content="' + encodeURIComponent(this.content) + '" data-sentTime="' + this.fullsentTime + '">' +
          this.content.replace(/style="color:#FFFFFF;"/g, 'style="color:#CCEEFF;"').replace('<a href="/chat/', '<a href="#" data-toggle="modal" data-target="#basicModal" data-type="file" data-href="/chat/') +
          '</div>' +
          '</div>' +
          '<button type="button" class="btn badge badge-light ml-1" name="starBtn" href="#" onclick=\'starOnclick(\"' + this.fullsentTime + '\",\"' + chatID + '\",\"' + this.content + '\",this);\'>' +
          '<i class="fa fa-star mr-1" data-sentTime="' + this.fullsentTime + '" aria-hidden="true" style="color: #AAAAAA;"></i>' +
          '</button>' +
          '<small>' + this.sentTime + '</small>' +
          '<a target="_blank" href="#" data-toggle="modal" data-target="#basicModal" data-type="readlist" data-content="' + encodeURIComponent(this.content) + '" data-sentTime="' + this.fullsentTime + '" data-UID="' + this.UID + '"><i class="fa fa-eye" aria-hidden="true"></i>' + this.Read + '</a>' +
          '<a style="display" class="btn badge badge-light ml-1" href="#" data-toggle="modal" data-target="#basicModal" data-type="comments" data-likeID="' + this.likeID + '" data-content="' + encodeURIComponent(this.content) + '"data-sentTime="' + this.fullsentTime + '" data-UID="' + this.UID + '" data-readcount="' + this.Read + '" name="iconComment"><i class="fa fa-reply" aria-hidden="true"></i></a>' +
          '<button class="btn badge badge-light ml-1" name="badgeLike" style="color: #AAAAAA;" href="#" data-content="' + encodeURIComponent(this.content) + '" data-sentTime="' + this.fullsentTime + '" data-UID="' + this.UID + '" data-isClick="false" onclick=\'onclickHeart(this,\"' + this.fullsentTime + '\");\'><i class="fa fa-heart mr-1"  aria-hidden="true"></i>' +
          0 +
          '</button>' +
          '</div>'
        );
      }
    });
    if (!scrollable)
      $('.msg_history').scrollTop($('.msg_history')[0].scrollHeight);
    $('[name="tooltipOnlineTime"]').tooltip();
    $("[name='tooltipOnlineTime']").on('shown.bs.tooltip', function() {
      // do something…
      // $(this).attr('data-original-title',$(this).attr("data-id"));
      var currentTooltip = this;
      console.log($(currentTooltip).data('id'));
      if (staffStatus != null) {
        staffStatus.abort();
      }
      setTimeout(function() {
        staffStatus = $.ajax({
          url: '/chat/lastOnLine/' + $(currentTooltip).data('id'),
          type: 'get',
          data: {},
          dataType: 'json',
          success: function(response) {
            //routine();
            // console.log(response.time);
            console.log($(currentTooltip).attr('aria-describedby'));
            $('div#' + $(currentTooltip).attr('aria-describedby')).find('div.tooltip-inner').text(response.time);
          }
        });



      }, 500);
      // getStatus($(this).attr("data-id"),this);

    });
    inMoreFunction();

  }

  function inMoreFunction() {
    var longpress = 1000;
    // holds the start time
    var start;
    var touchtime = 0;
    $('[name=moreFunctionDrop]').remove();
    $("[name=contentBox]").on("click", function() {
      if (touchtime == 0) {
        // set first click
        touchtime = new Date().getTime();
      } else {
        // compare first click to this click and see if they occurred within double click threshold
        if (((new Date().getTime()) - touchtime) < 800) {
          // return false; 

          // double click occurred
          // alert("double clicked");
          // console.log($(this).data('content'));
          // $(this).hide();
          $(this).prepend(
            `<div class="btn-group dropleft" name="moreFunctionDrop">
                <div class="dropdown-menu show" aria-labelledby="dropdownMenuButton" name="dropdownRightClick">
                <button class="dropdown-item" data-toggle="modal" data-target="#basicModal" data-type="reportMessage" data-senttime="${$(this).data('senttime')}" data-content="${$(this).data('content')}" name="dropdownItemReport">轉傳訊息</button>
                <button class="dropdown-item" data-toggle="modal" data-target="#basicModal" data-type="deleteMessage" data-senttime="${$(this).data('senttime')}" name="dropdownItemDelete">刪除訊息</button>
                </div>
              </div>`);
          touchtime = 0;
          $('body').mouseup(function(e) {
            if (1 == e.which && $('[name=moreFunctionDrop]').is(":visible")) {
              $('[name=moreFunctionDrop]').hide();
              setTimeout(function() {
                $('[name=moreFunctionDrop]').remove();
              }, 200);
            }
          });
        } else {
          touchtime = new Date().getTime();
        }
      }
    });
    $("[name=incomeChatbox]").on("click", function() {
      if (touchtime == 0) {
        // set first click
        touchtime = new Date().getTime();
      } else {
        if (((new Date().getTime()) - touchtime) < 800) {
          // return false; 

          // double click occurred
          // alert("double clicked");
          // console.log($(this));
          // $(this).hide();
          $(this).prepend(
            `<div class="btn-group dropright" name="moreFunctionDrop">
              <div class="dropdown-menu show" aria-labelledby="dropdownMenuButton" name="dropdownRightClick">
              <button class="dropdown-item" data-toggle="modal" data-target="#basicModal" data-type="reportMessage" data-senttime="${$(this).data('senttime')}" data-content="${$(this).data('content')}" name="dropdownItemReport">轉傳訊息</button>
              
            </div>`);
          touchtime = 0;
          $('body').mouseup(function(e) {
            if (1 == e.which && $('[name=moreFunctionDrop]').is(":visible")) {
              $('[name=moreFunctionDrop]').hide();
              setTimeout(function() {
                $('[name=moreFunctionDrop]').remove();
              }, 200);
            }
          });
        } else {
          // not a double click so set as a new first click
          touchtime = new Date().getTime();
        }
      }
    });
  }

  function ondblclickMessage(message) {}

  function reportMessage(senttime, content) {
    $('[name="moreFunctionDrop"]').remove();
    $('#basicModal .modal-title').html('轉傳訊息');

    $('#basicModal .modal-body').html(
      `<div class="card">
      <div class="card-header">
       請選擇轉傳聊天室
      </div>
      <div class="card-body">
        <h5 class="card-title">
        <div class="srch_bar">
          <div class="stylish-input-group">
            <input type="text" class="search-bar searchInput" placeholder="搜尋" id="reportSearchInput">
            <span class="input-group-addon">
              <button type="button">
                <i class="fa fa-search" aria-hidden="true"></i>
              </button>
            </span>
          </div>
        </div>
      </div>
    </div>    
    `);
    $('#basicModal .modal-footer').append(` <button type="button" class="btn btn-primary" id="reportBtn">轉傳</button>`);
    $.ajax({
      url: '/chat/chatroom',
      type: 'get',

      dataType: 'json',
      success: function(response) {
        console.log(response);
        $(response).each(function() {
          var reportChatName
          if (this.chatName == '') {
            reportChatName = this.staff_name;
          } else {
            reportChatName = this.chatName;
          }
          if (this.chatID != -1) {
            $('#basicModal .modal-body').append(
              `<div class="input-group-prepend" value="${reportChatName}" name="reportChatroom" id = "reportChatroom${this.chatID}">
              <div class="input-group-text">
                <input type="radio" class="checkItem" data-name="${reportChatName}" value="${this.chatID}" name="reportRadios">
              </div>
              <input type="text" class="form-control listID" disabled="" value="${reportChatName}">
            </div>`);
          }


        });

      }
    });

    $('#reportBtn').unbind().on('click', function() {
      if ($('[name="reportRadios"]').is(':checked') != false) {
        var tmpChatID = $('[name="reportRadios"]:checked').val();
        console.log(content);
        $('#reportBtn').hide();
        content = decodeURIComponent(content).replace(/\r?\n/g, '<br />');
        $('#basicModal .modal-body').html(`確定將<p>${decodeURIComponent(content)}</p>轉傳至 ${$('[name="reportRadios"]:checked').data('name')}?`);
        $('#basicModal .modal-footer').append(` <button type="button" class="btn btn-primary" id="lastReportBtn">確定</button>`);
        var orgCommentID;
        $('#lastReportBtn').unbind().on('click', function() {
          $('#basicModal').modal('hide');

          $.ajax({
            url: '/chat/commentID/' + chatID + '/' + encodeURIComponent(senttime),
            type: 'get',
            dataType: 'json',
            success: function(response) {
              orgCommentID = response;

              $.ajax({
                url: '/chat/message',
                type: 'post',
                data: {
                  Msg: decodeURIComponent(content),
                  chatID: tmpChatID,
                  _METHOD: 'PATCH'
                },
                dataType: 'json',
                success: function(response) {
                  console.log(response.time);

                  $.ajax({
                    url: '/chat/commentID/' + tmpChatID + '/' + encodeURIComponent(response.time),
                    type: 'get',
                    dataType: 'json',
                    success: function(response) {
                      console.log(response);
                      updateReport(orgCommentID, response);
                    }
                  });
                }
              });
            }
          });
        });
      }
    });
    $('#reportSearchInput').unbind().on('keyup', function() {
      // console.log($('#reportSearchInput').val());

      setTimeout(function() {
        $('[name=reportChatroom]').each(function() {
          // console.log($(this).find('input.form-control ').val());
          if ($(this).find('input.form-control ').val().indexOf($('#reportSearchInput').val()) > -1) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      }, 300);
    });


  }

  function updateReport(orgCommentID, newCommentID) {
    $.ajax({
      url: '/chat/report',
      type: 'post',
      data: {
        orgCommentID: orgCommentID,
        newCommentID: newCommentID,
        _METHOD: 'PATCH'
      },
      dataType: 'json',
      success: function(response) {
        console.log(response);

        // updateRepost(orgCommentID,response);
      }
    });
  }


  function deleteMessage(senttime) {
    $('[name="moreFunctionDrop"]').remove();
    $('#basicModal .modal-title').text('刪除訊息');
    $('#basicModal .modal-body').html('確認刪除此訊息?');
    $('#basicModal .modal-footer').html(`
  <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>	
  <button class="btn btn-primary" name="sureDeleteBtn" data-senttime="${senttime}" type="button">確認</button>`);
    $('[name="sureDeleteBtn"]').unbind().on('click', function() {
      console.log($(this).data('senttime'));
      $.ajax({
        url: '/chat/delete',
        type: 'post',
        data: {
          data: JSON.stringify({
            chatID: chatID,
            time: $(this).data('senttime')
          })
        },
        dataType: 'json',
        success: function(response) {
          // getTarget(chatID,chatName);

        }
      });
      $(`[name=outgoingBox][data-senttime="${$(this).data('senttime')}"]`).html(`
      <div class="d-flex flex-row-reverse bd-highlight">
        <div class="p-2 bd-highlight bg-secondary text-white rounded" >
          此訊息已刪除
        </div>
      </div>
      <small></small>
    `);
      $('#basicModal').modal('hide');
    });


  }

  function expendLimit() {}


  function onclickHeart(button, senttime) {
    var tmpHeartNum = parseInt($(button).text());
    if ($(button).attr("data-isClick") == 'false') {
      $(button).attr("data-isClick", 'true');
      $(button).html(`<i class="fa fa-heart mr-1" aria-hidden="true"></i>${tmpHeartNum+1}`);
      $(button).css("color", "#d9534f");
    } else {
      $(button).attr("data-isClick", 'false');
      $(button).html(`<i class="fa fa-heart mr-1" aria-hidden="true"></i>${tmpHeartNum-1}`);
      $(button).css("color", "#AAAAAA");
    }
    $.ajax({
      url: '/chat/heart',
      type: 'post',
      data: {
        data: JSON.stringify({
          chatID: chatID,
          time: senttime
        }),
        _METHOD: 'PATCH'
      },
      dataType: 'json',
      success: function(response) {}
    });
  }

  function updateLastReadTime() {
    if (queue['lastReadTime'] != null)
      queue['lastReadTime'].abort();
    queue['lastReadTime'] = $.ajax({
      url: '/chat/lastReadTime',
      type: 'post',
      data: {
        chatID: chatID,
        _METHOD: 'PATCH'
      },
      dataType: 'json',
      success: function(response) {
        routine();
      }
    });
  }

  $('[name=bellbtn]').parent().show();
  $(function() {
    $('[name=bellbtn]').click(getNotification);
  });

  function getNotification() {
    console.log("inget");
    $('[name=bellDropdown]').empty();
    $.ajax({
      url: '/chat/notification/',
      type: 'get',
      dataType: 'json',
      success: function(response) {
        // console.log(response);
        $('[name=bellDropdown]').append('<h6 class="dropdown-header">通知中心</h6>');
        $(response).each(function() {
          // console.log(this.sendtime);
          $('[name=bellDropdown]').append(
            '<a class="dropdown-item d-flex align-items-center" id="notification' + this.id + '" style=" z-index:9999;" data-time="' + this.fullsendTime + '" onclick="notificationOnclick(' + this.chatID + ',\'' + encodeURIComponent(this.chatName) + '\',' + this.id + ',this,\'' + this.type + '\');"' +

            `<div class="mr-3">
              <div class="icon-circle bg-primary">
                <i class="fas ${this.type == 'tag'? 'fa-file-alt': 'fa-comment-dots'} text-white"></i>
              </div>
            </div>
            <div>
              <div class="small text-gray-500">
                ${this.sendtime}
              </div>
              <span class="font-weight-bold">
                ${this.detail}
              </span>
            </div>
          </a>`

          );
          if (this.unread == true) {
            // console.log("unread");
            $("#notification" + this.id).css("background-color", "#F0F8FF");
          } else {
            $("#notification" + this.id).css("background-color", "#FFFFFF");
          }
        });
        $('[name=bellDropdown]').append('<a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>');
      }
    });
  }


  var chatID = -1;
  var chatName = '';

  $('#tool_dropdown').hide();

  function inSaveChat(chatInfo) {
    changeChat('saveChat', chatInfo);
    changeDelete('saveChat', chatInfo);
  }


  function getTarget(_chatID, _chatName) {
    // // console.log($(div).attr("data-name"));
    // // chatID=$(div).attr("data-name");
    scrollable = false;
    // last['count'] = 0;
    // $('[name=chatBox]').html("");
    if (chatID != _chatID) {
      console.log(chatID, _chatID);


      $('[name=chatBox]').html(
        '<div class="spinner-border text-primary" role="status">' +
        '<span class="sr-only">Loading...</span>' +
        '</div>'
      );
      $.ajax({
        url: '/chat/saveChat/' + _chatID,
        type: 'get',
        data: {},
        dataType: 'json',
        success: function(response) {
          console.log(response);
          inSaveChat(response);
        }
      });
    }

    chatName = decodeURIComponent(_chatName);
    $('[name=navbarChatroomTitle]').text(chatName);
    $('#tool_dropdown').show();
    // resetLimit();
    if (chatID != _chatID) {
      chatID = _chatID;
      routine();
    } else {
      scrollToTag();
    }
    updateLastReadTime();
    $(document).scrollTop(document.body.scrollHeight);
    // schedule();
    // getReadcount();

    // routine();
  }

  function expendLimit() {
    last['limit'] += 5;
  }

  function resetLimit() {
    last['limit'] = 20;
  }
  var last = new Object();
  last['limit'] = 20;
  last['count'] = 0;
  last['countchat'] = 0;
  last['clientClass'] = {};
  last['chatClientInfo'] = {};

  function searchChatroom() {
    if (queue['chatroom'] != null)
      queue['chatroom'].abort();
    queue['chatroom'] = $.ajax({
      url: '/chat/chatroom',
      type: 'get',
      data: {
        data: JSON.stringify(last)
      },
      dataType: 'json',
      success: function(response) {
        console.log(response)
        last['clientClass'] = {};
        last['chatClientInfo'] = new Object();
        // console.log(response);
        $popNum = response.pop();
        $change = response.pop();
        $allClass = response.pop();
        if ($popNum.num == 'none') {
          return;
        }
        last['countchat'] = $popNum.num;
        // console.log($popNum.num);
        // $('[name=inbox_chat]').html("");
        $('[name=inbox_chat]').append(
          '<div class="accordion" id="accordionExample" name="accordionExample">' +
          '</div>'
        );

        $($allClass.allclass).each(function() {
          last['clientClass'][this.id] = {
            "name": this.name
          };
        });

        $(response).each(function() {
          $(this.chatInfo).each(function() {
            // console.log(this.chatID);
            var newdata = {};
            newdata[this.chatID] = {
              "chatID": this.chatID,
              "classId": this.classId,
              "lastTime": this.LastTime1
            };
            $.extend(true, last['chatClientInfo'], newdata);
          });
        });

        if ($change.changetype['changetype'] == 'changeclass') {

          if ($change.changetype['type'] == "delete") {
            // console.log($change.changetype['changething']);
            $.each($change.changetype['changething'], function(key, id) {
              // console.log(this.name);
              $('[name=class' + this.name + ']').remove();
            });
          } else if ($change.changetype['type'] == "add") {
            //console.log($change.changetype['changething'].name);
            $('[name=inbox_chat]').append(
              '<div class="card" name = "class' + $change.changetype["changething"].name + '">' +
              '<div class="card-header" id="headingOne">' +
              '<h2 class="mb-0">' +
              '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#' + $change.changetype["changething"].name + '" aria-expanded="true" aria-controls="' + $change.changetype["changething"].name + '">' +
              $change.changetype["changething"].name +
              '</button>' +
              '</h2>' +
              '</div>' +
              '<div id= "' + $change.changetype["changething"].name + '"  name= "' + $change.changetype["changething"].name + '" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">' +
              '</div>' +
              '</div>'
            );
          } else if ($change.type == "modify") {

          }

        } else if ($change.changetype['changetype'] == 'changechatroom') {
          if ($change.type == "delete") {
            $.each($change.changetype['changething'], function(key, id) {
              console.log(this);
              $('[name=room' + this + ']').remove();

            });

          } else if ($change.type == "add") {

          } else if ($change.type == "changeClass") {

          }
        } else if ($change.changetype['changetype'] == 'changeLastTime') {
          haveUnread = '<span class="badge badge-primary" style="display:none;">有' + $change.changetype['changeChatroom'].CountUnread + '則新訊息</span> ';
          var tmpClassName = $change.changetype['changeChatroom'].className;
          if (tmpClassName == null) {
            tmpClassName = "未分類議題";
          }
          console.log(tmpClassName);
          var chatName = '';
          if ($change.changetype['changeChatroom'].chatToWhom == null) {
            chatName = $change.changetype['changeChatroom'].chatName;
          } else {
            chatName = $change.changetype['changeChatroom'].staff_name;
          }
          // $('[name=room'+$change.changetype['changeChatroom'].chatID+']').remove();
          $('[name="' + tmpClassName + '"]').prepend(
            '<div class="card-body" name="room' + $change.changetype['changeChatroom'].chatID + '">' +
            '<div class="chat_list" onclick="getTarget(' + $change.changetype['changeChatroom'].chatID + ',\'' + encodeURIComponent(chatName) + '\');" data-name="' + $change.changetype['changeChatroom'].chatID + '">' +
            '<div class="chat_people">' +
            '<div class="chat_img">' +
            '<div class="circleBase type2"></div>' +
            '</div>' +
            '<div class="chat_ib">' +
            '<h5>' + chatName +
            '<span class="chat_date">' + ($change.changetype['changeChatroom'].LastTime == null ? ' ' : $change.changetype['changeChatroom'].LastTime) + '</span>' +
            '</h5>' +
            '<p class="text-truncate chatContent">' + ($change.changetype['changeChatroom'].content == null ? ' ' : ($change.changetype['changeChatroom'].content.indexOf('<a ') > -1 ? '收到一個檔案' : $change.changetype['changeChatroom'].content)) + '</p>' +
            haveUnread +
            '</div>' +
            '</div>' +
            '</div>'
          );

        } else if ($change.changetype == 'none') {

        } else if ($change.changetype == 'firstime') {
          $($allClass.allclass).each(function() {
            // console.log(this);

            $('[name=inbox_chat]').append(
              '<div class="card" name = "class' + this.id + '">' +
              '<div class="card-header" id="headingOne">' +
              '<h2 class="mb-0">' +
              '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#class' + this.id + '" aria-expanded="true" aria-controls="class' + this.id + '">' +
              this.name +
              '</button>' +
              '</h2>' +
              '</div>' +
              '<div id= "class' + this.id + '" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">' +
              '</div>' +
              '</div>'
            );
          });
          $(response).each(function() {
            var tmpClass = this.classID;

            $(this.chatInfo).each(function() {
              var chatName = '';
              if (this.chatToWhom == null) {
                chatName = this.chatName;
              } else {
                chatName = this.staff_name;
              }
              var haveUnread = '';
              if (window.isTabActive) {
                $('title').text(titleOrg);
              }
              if (this.CountUnread != '0' && this.CountUnread != null) {
                haveUnread = '<span class="badge badge-primary">有' + this.CountUnread + '則新訊息</span> ';
                if (!window.isTabActive) {
                  if ($('title').text().indexOf('您有訊息!!') > -1)
                    $('title').text(titleOrg);
                  else
                    $('title').text('[您有訊息!!]' + titleOrg);
                }
              } else {
                haveUnread = '<span class="badge badge-primary" style="display:none;">有' + this.CountUnread + '則新訊息</span> ';
              }
              $('#class' + tmpClass).append(
                '<div class="" name="room' + this.chatID + '">' +
                '<div class="chat_list" onclick="getTarget(' + this.chatID + ',\'' + encodeURIComponent(chatName) + '\');" data-name="' + this.chatID + '">' +
                '<div class="chat_people">' +
                '<div class="chat_img">' +
                '<div class="circleBase type2"></div>' +
                '</div>' +
                '<div class="chat_ib">' +
                '<h5>' + chatName +
                '<span class="chat_date">' + (this.LastTime == null ? ' ' : this.LastTime) + '</span>' +
                '</h5>' +
                '<p class="text-truncate chatContent">' + (this.content == null ? ' ' : (this.content.indexOf('<a ') > -1 ? '收到一個檔案' : this.content)) + '</p>' +
                haveUnread +
                '</div>' +
                '</div>' +
                '</div>'
              );
            });
          });

        }
        //console.log("out");
        if (!scrollable)
          $('.msg_history').scrollTop($('.msg_history')[0].scrollHeight);
        setTimeout(searchChatroom, 3000);
      }
    });

  }

  function searchChat() {
    if (chatID != -1 && chatID !== undefined) {
      if (queue['chat'] != null)
        queue['chat'].abort();
      queue['chat'] = $.ajax({
        url: '/chat/content/' + chatID,
        data: {
          data: JSON.stringify(last)
        },
        type: 'get',
        dataType: 'json',
        success: function(response) {
          // $('[name=chatBox]').html("");
          if (response[response.length - 1].chatID != chatID) {
            return false;
          }
          response.pop();
          if (window.isTabActive)
            updateLastReadTime();
          $(response).each(function() {
            last['count'] = last['count'] + 1;
            if (this.diff != 'me') {
              $('[name=chatBox]').append(
                '<div class="incoming_msg">' +
                '<div class="">' + this.UID + ',' + this.staff_name + '</div>' +
                '<div class="received_msg">' +
                '<div class="received_withd_msg">' +
                '<p class="text-break">' +
                this.content.replace(/style="color:#FFFFFF;"/g, 'style="color:#646464;"') +
                '</p>' +
                '<span class="time_date"> ' + this.sentTime + '</span>' +
                '<span class="read ml-1">' +
                '<a target="_blank" href="#" data-toggle="modal" data-target="#basicModal" data-type="readlist" data-content="' + encodeURIComponent(this.content) + '" data-sentTime="' + this.fullsentTime + '" data-UID="' + this.UID + '"><i class="fa fa-eye" aria-hidden="true"></i>' + this.Read + '</a>' +
                '</span>' +
                '<a class="badge badge-light ml-1" href="#" data-toggle="modal" data-target="#basicModal" data-type="comments" data-content="' + encodeURIComponent(this.content) + '"data-sentTime="' + this.fullsentTime + '" data-UID="' + this.UID + '" data-readcount="' + this.Read + '" ><i class="fa fa-reply" aria-hidden="true"></i><span class="badge badge-secondary ml-1" href="#">6</span></a>' +
                '<a class="badge badge-danger ml-1" href="#"><i class="fa fa-heart mr-1" aria-hidden="true"></i>6</a>' +
                '</div>' +
                '</div>' +
                '</div>'
              );
            } else {
              $('[name=chatBox]').append(
                '<div class="outgoing_msg">' +
                '<div class="sent_msg">' +
                '<p class="text-break content">' +
                this.content +
                '</p>' +
                '<span class="time_date" > ' + this.sentTime + '</span>' +
                '<a href="#" class="ml-1" data-toggle="modal" data-target="#basicModal" data-type="readlist" data-content="' + encodeURIComponent(this.content) + '" data-sentTime="' + this.fullsentTime + '" data-UID="' + this.UID + '"><i class="fa fa-eye" aria-hidden="true"></i>' + this.Read + '</a>' +
                '<a class="badge badge-light ml-1" href="#" data-toggle="modal" data-target="#basicModal" data-type="comments" data-content="' + encodeURIComponent(this.content) + '"data-sentTime="' + this.fullsentTime + '" data-UID="' + this.UID + '" data-readcount="' + this.Read + '" ><i class="fa fa-reply" aria-hidden="true"></i></a>' +
                '<a class="badge badge-light ml-1" href="#"><i class="fa fa-heart" aria-hidden="true"></i></a>' +
                '</div>' +
                '</div>'
              );
            }
          });
          if (!scrollable)
            $('.msg_history').scrollTop($('.msg_history')[0].scrollHeight);
          setTimeout(searchChat, 3000);
        }
      });
    }
  }
  var Msg = "";
  $('.msg_send_btn').on('click', function() {
    if (!$.trim($("#textarea").val()) && $("#textinput").val() != "" && chatID != -1) {
      sendMsg();
      $("#textinput").val("");
    }
  });
  $("#textinput").keypress(function(e) {
    var code = e.which;
    if ((code && e.shiftKey) && code == 13) {
      e.preventDefault();
      $('.msg_send_btn').click();
    }
  });
  $("#textinput").on('input', function(e) {
    if (($("#textinput").val().charAt($("#textinput").getCursorPosition() - 1) == "＠") || ($("#textinput").val().charAt($("#textinput").getCursorPosition() - 1) == "@")) {
      if ($("#textinput").getCursorPosition() == 1 || $("#textinput").val().charAt($("#textinput").getCursorPosition() - 2) == " ") {
        getAllEmployee();
      }
    } else if (($("#textinput").val().charAt($("#textinput").getCursorPosition() - 1) == "＃") || ($("#textinput").val().charAt($("#textinput").getCursorPosition() - 1) == "#")) {
      if ($("#textinput").getCursorPosition() == 1 || $("#textinput").val().charAt($("#textinput").getCursorPosition() - 2) == " ") {
        getDepartment();
      }
    }
    // console.log("in");
    tmpSplit = $('#textinput').val().split(" ");
    $(tmpSplit).each(function() {
      if (this.indexOf("@") == 0 || this.indexOf("＠") == 0) {
        tagboolean = true;
        tmpTag = this;
        nowkey = this.substr(1);
        var choose = null;
        choose = setTimeout(function() {
          $('[name=dropdownitemTag]').each(function() {
            // console.log(tmpTag.substr(1));
            if ($(this).data('name').indexOf(tmpTag.substr(1)) > -1) {
              $(this).show();
            } else {
              $(this).hide();
            }
          });
        }, 300);
      } else if (this.indexOf("#") == 0 || this.indexOf("＃") == 0) {
        tagboolean = true;
        tmpTag = this;
        nowkey = this.substr(1);
        var choose = null;
        choose = setTimeout(function() {
          $('[name=dropdownitemTag]').each(function() {
            // console.log(tmpTag.substr(1));
            if ($(this).data('name').indexOf(tmpTag.substr(1)) > -1) {
              $(this).show();
            } else {
              $(this).hide();
            }
          });
        }, 300);
      }
    });
    if (tagboolean == false) {
      $('#dropupTag').hide();
    }
    tagboolean = false;
    var code = e.which;
    if ((code && e.shiftKey) && code == 13) {}
  });

  var tagboolean = false;
  var tmpTag;
  var nowkey;
  var tagPeople = "";
  var tagDepartment = "";
  $('#dropupTag').hide();
  (function($, undefined) {
    $.fn.getCursorPosition = function() {
      var el = $(this).get(0);
      var pos = 0;
      if ('selectionStart' in el) {
        pos = el.selectionStart;
      } else if ('selection' in document) {
        el.focus();
        var Sel = document.selection.createRange();
        var SelLength = document.selection.createRange().text.length;
        Sel.moveStart('character', -el.value.length);
        pos = Sel.text.length - SelLength;
      }
      return pos;
    }
  })(jQuery);
  var tmpTagMsg = "";
  var tagType = "";

  function notificationOnclick(chatID, chatName, id, attr, typeNotice) {

    if ($("#notification" + id).css("background-color") == "rgb(240, 248, 255)") {
      $('[name=notificationNum]').text(parseInt($('[name=notificationNum]').text()) - 1);
    }
    tmpTagMsg = $(attr).data('time');
    tagType = typeNotice;
    getTarget(chatID, chatName);
    // console.log($(attr).data('time'));

    // console.log($('.ml-1[data-senttime="'+$(attr).data('time')+'"]'));
    $.ajax({
      url: '/chat/notification/' + id,
      type: 'post',
      data: {
        _METHOD: 'PATCH'
      },
      dataType: 'json',
      success: function(response) {
        // getTarget(chatID,chatName);
      }
    });
  }

  function addTag(tagname, tagID) {
    var textAreaContent = $("#textinput").val();
    // console.log(textAreaContent);
    for (i = $("#textinput").getCursorPosition(); i > 0; i--) {
      if ($("#textinput").val().charAt(i - 1) == "@" || $("#textinput").val().charAt(i - 1) == "＠") {
        $("textarea#textinput").val($("#textinput").val().substr(0, i - 1) + "@" + tagname + " " + $("#textinput").val().substr(i));
        break;
      } else {
        $("textarea#textinput").val($("#textinput").val().substr(0, i - 1) + $("#textinput").val().substr(i));
      }
    }
    $('#dropupTag').hide();
    if (tagPeople.indexOf(tagID) == -1)
      tagPeople = tagPeople + tagID + " ";
    // console.log(tagPeople);
  }

  function addDepartmentTag(tagname, tagID) {
    var textAreaContent = $("#textinput").val();
    for (i = $("#textinput").getCursorPosition(); i > 0; i--) {
      if ($("#textinput").val().charAt(i - 1) == "#" || $("#textinput").val().charAt(i - 1) == "＃") {
        $("textarea#textinput").val($("#textinput").val().substr(0, i - 1) + "#" + tagname + " " + $("#textinput").val().substr(i));
        break;
      } else {
        $("textarea#textinput").val($("#textinput").val().substr(0, i - 1) + $("#textinput").val().substr(i));
      }
    }
    $('#dropupTag').hide();
    if (tagDepartment.indexOf(tagID) == -1)
      tagDepartment = tagDepartment + tagID + " ";
    //console.log(tagname);
  }

  function getAllEmployee() {
    $('#tagPeople').empty();
    $.ajax({
      url: '/chat/member/' + chatID,
      type: 'get',
      dataType: 'json',
      success: function(response) {
        //console.log(response);
        $(response).each(function() {
          if (tagPeople.indexOf(this.id) > -1 && $("#textinput").val().indexOf('@' + this.name) > -1) {
            return;
          }
          tagPeople = tagPeople.replace(this.id + ' ', '');
          $('#tagPeople').append(
            '<button class="dropdown-item" name="dropdownitemTag" data-id=' + this.id + ' data-name= ' + this.name + ' href="#" onclick="addTag(\'' + this.name + '\',\'' + this.id + '\');">' + this.name + "   " + this.id +
            '</button>'
          );
        });
      }
    });
    $('.dropup').show();
  }

  function getDepartment() {
    $('#tagPeople').empty();
    $.ajax({
      url: '/chat/department/' + chatID,
      type: 'get',
      dataType: 'json',
      success: function(response) {
        //console.log(response);
        $(response).each(function() {
          if (tagDepartment.indexOf(this.id) > -1 && $("#textinput").val().indexOf('#' + this.name) > -1) {
            return;
          }
          tagDepartment = tagDepartment.replace(this.id + ' ', '');
          $('#tagPeople').append(
            '<button class="dropdown-item" name="dropdownitemTag" data-id=' + this.id + ' data-name= ' + this.name + ' href="#" onclick="addDepartmentTag(\'' + this.name + '\',\'' + this.id + '\');">' + this.name + "   " + this.id +
            '</button>'
          );
        });
      }
    });
    $('.dropup').show();

  }

  function uploadFile(button) {
    $('#basicModal').modal('hide');
    $('[name=inputFile]').val('');
    $('[name=inputFile]').click();
  }

  function uploadPicture(button) {
    $('#basicModal').modal('hide');
    $('[name=inputPicture]').click();
  }
  $('[name=inputFile]').on('change', function() {
    var file_data = $(this).prop('files')[0];
    var form_data = new FormData();
    form_data.append('inputFile', file_data);
    $.ajax({
      url: '/chat/file/' + chatID,
      cache: false,
      contentType: false,
      processData: false,
      data: form_data, //data只能指定單一物件                 
      type: 'post',
      success: function(data) {

      }
    });
  });
  $('[name=inputPicture]').on('change', function() {
    var file_data = $(this).prop('files')[0];
    var form_data = new FormData();
    form_data.append('inputFile', file_data);
    $.ajax({
      url: '/chat/picture/' + chatID,
      cache: false,
      contentType: false,
      processData: false,
      data: form_data, //data只能指定單一物件                 
      type: 'post',
      success: function(data) {

      }
    });
  });

  function tagNotification(type, tagPerson, chatName, tmpTime) {
    // console.log(type,tagPerson,chatName,tmpTime);
    if (type == "person") {
      $.ajax({
        url: '/chat/notification/tag',
        type: 'POST',
        data: {
          data: JSON.stringify({
            type: type,
            chatID: chatID,
            id: tagPerson,
            chatName: chatName,
            tmpTime: tmpTime
            // name : groupname
          })
        },
        dataType: 'json',
        success: function(response) {
          //console.log(response);
        }
      });
    } else if (type == "department") {
      $.ajax({
        url: '/staff/name/' + tagPerson + '/departmentMember/' + chatID,
        type: 'GET',
        dataType: 'json',
        success: function(response) {

          $(response).each(function() {
            tagNotification("person", this.id, chatName, tmpTime)
            //console.log(this.id);
          });
        }
      });

      // $.ajax({
      //   url:'/chat/notification/tag',
      //   type:'POST',
      //   data:{data:JSON.stringify({
      //           type:type,
      //           chatID:chatID,
      //           id : tagPerson,
      //           chatName : chatName,
      //           tmpTime : tmpTime
      //           // name : groupname
      //         })},
      //   dataType:'json',
      //   success:function(response){
      //     console.log(response);
      //   } 
      // });
    }

  }

  function beforeTag(tmpSplit, tmpFullTime) {

    // console.log(tmpSplit);
    if (tagDepartment != "") {
      var checkTagDepartmentID = tagDepartment.split(" ");
      $(checkTagDepartmentID).each(function() {
        // console.log(this);
        var tagItem = this;

        if (this != "") {
          $.ajax({
            url: '/staff/department/' + this,
            type: 'get',
            dataType: 'json',
            success: function(response) {
              // getTarget(chatID,chatName);
              $(tmpSplit).each(function() {
                //console.log(this);
                if ("#" + response[0].department_name == this) {
                  //console.log("success");
                  tagNotification("department", tagItem, chatName, tmpFullTime);
                }
              });
            }
          });
        }
      });
    }
    if (tagPeople != "") {
      var checkTagID = tagPeople.split(" ");
      $(checkTagID).each(function() {

        var tagPerson = this;

        if (this != "") {
          $.ajax({
            url: '/staff/name/' + this + '/tag',
            type: 'get',
            dataType: 'json',
            success: function(response) {
              // getTarget(chatID,chatName);
              $(tmpSplit).each(function() {
                if ("@" + response[0].staff_name == this) {
                  // console.log("in");
                  tagNotification("person", tagPerson, chatName, tmpFullTime);
                }
              });
            }
          });
        }
      });
    }
    tagDepartment = "";
    tagPeople = "";
  }

  function sendMsg() {
    Msg = $("#textinput").val();
    $('[name=chatBox]').append(
      '<div class="text-right outgoing_msg" name="msgSendNow" data-sentTime="">' +
      '<div class="d-flex flex-row-reverse bd-highlight">' +
      '<div class="p-2 bd-highlight bg-secondary text-white rounded-pill">' +
      Msg +
      '</div>' +
      '</div>' +
      '</div>'
    );

    if (!scrollable) {
      $('.msg_history').scrollTop($('.msg_history')[0].scrollHeight);
    }
    var tmpFullTime;
    var tmpSplit = $('#textinput').val().split(" ");
    Msg = Msg.replace(/\r?\n/g, '<br />');
    $.ajax({
      url: '/chat/message',
      type: 'post',
      data: {
        Msg: Msg,
        chatID: chatID,
        _METHOD: 'PATCH'
      },
      dataType: 'json',
      success: function(response) {
        // getTarget(chatID,chatName);
        tmpFullTime = response.time;

        // console.log(tmpFullTime);
        beforeTag(tmpSplit, tmpFullTime);
      }
    });
  }

  function sendCemmene(msgsender, msgtime, data) {
    Msg = $("#commentinput").val();
    Msg = Msg.replace(/\r?\n/g, '<br />');
    $.ajax({
      url: '/chat/comment',
      type: 'post',
      data: {
        Msg: Msg,
        chatID: chatID,
        chatOrigin: msgsender,
        chatTime: msgtime,
        _METHOD: 'PATCH'
      },
      dataType: 'json',
      success: function(response) {
        getCommentContent(data);
      }
    });
  }
  $('#basicModal').on('show.bs.modal', function(e) {
    $('#basicModal .modal-footer').html(basicModalFooter);
    var type = $(e.relatedTarget).data('type');
    if (type == 'create') {
      Chatroom(type);
    } else if (type == 'update') {
      Chatroom(type);
    } else if (type == 'member') {
      getMember();
    } else if (type == 'delete') {
      Chatroom(type);
    } else if (type == 'readlist') {
      getReadlist($(e.relatedTarget).data());
    } else if (type == 'attach') {
      attachType();
    } else if (type == 'photo') {
      viewPhoto($(e.relatedTarget).data('src'));
    } else if (type == 'comments') {
      getComment($(e.relatedTarget).data());
    } else if (type == 'addClass') {
      addIssue();
    } else if (type == 'chooseIssues') {
      Chatroom(type);
    } else if (type == 'insertClass') {
      insertIssue();
    } else if (type == 'file') {
      getFile($(e.relatedTarget).data());
    } else if (type == 'deleteMessage') {
      deleteMessage($(e.relatedTarget).data('senttime'));
    } else if (type == 'reportMessage') {
      reportMessage($(e.relatedTarget).data('senttime'), $(e.relatedTarget).data('content'));
    }
  });

  function getComment(relatedData) {
    $('#basicModal .modal-title').text('留言板');
    $('#basicModal .modal-body').html(`
    <div class="spinner-border text-primary" role="status">
      <span class="sr-only">Loading...</span>
    </div>`);


    var scrollableComment = false;
    var data = new Object();
    data['UID'] = relatedData['uid'];
    data['sentTime'] = relatedData['senttime'];
    data['content'] = decodeURIComponent(relatedData['content']);
    data['chatID'] = chatID;

    $.ajax({
      url: '/chat/commentID',
      type: 'get',
      data: {
        chatID: chatID,
        senttime: encodeURIComponent(relatedData['senttime'])
      },
      dataType: 'json',
      success: function(response) {


        $('#basicModal .modal-title').text('留言板');
        $('#basicModal .modal-body').html(
          `<h5>訊息</h5>
        <div name="message">${decodeURIComponent(relatedData['content'])}</div>
        已讀:${relatedData['readcount']}
        <hr>
        <h5>留言</h5>
        <div class="card">
          <div class="card-body overflow-auto" name="comment" style="height:30vh">
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Loading...</span>
            </div>
          </div>
        </div>
          <div class="dropup" id = "dropupTag">
          </div>
          <div class="d-flex">
            <a class="scroll-to-down rounded">
              <i class="fas fa-angle-down"></i>
            </a>
            <div class="w-100">
              <textarea class="form-control" style="word-wrap:break-word;width:100%;"placeholder="請在此輸入訊息，ENTER可以換行&#13;&#10;SHIFT+ENTER送出訊息" id="commentinput"></textarea>
            </div>
            <input style="display:none;" type="file" name="inputFile">
            <div class="btn-group dropup">
              <div class="flex-shrink-1  align-self-center ml-1">
                <button type="button" style="display:none;" class="btn btn-secondary btn-block far fa-smile "  aria-haspopup="true" aria-expanded="false" id="btnEmoji">
                </button>
              </div>
             <div class="dropdown-menu overflow-auto"  aria-labelledby="dropdownMenuEmoji" id="dropdownMenuEmoji" style="display:none;">
                <div class="btn-group" role="group" aria-label="Basic example">
                  <button type="button" name="emojiPic" class="btn badge badge-light ml-1-">&#128512;</button>
                  <button type="button" name="emojiPic" class="btn badge badge-light ml-1-">&#128513;</button>
                  <button type="button" name="emojiPic" class="btn badge badge-light ml-1-">&#128514;</button>
                </div>
                <a class="dropdown-item" href="#">Action</a>
                <a class="dropdown-item" href="#">Another action</a>
                <a class="dropdown-item" href="#">Something else here</a>
              </div>
            </div>
            
            <div class="flex-shrink-1  align-self-center ml-1">
                <button class="btn btn-secondary btn-block far fa-paper-plane msg_send_btn" id="commentbutton" data-commentID="${response}" type="button" onclick="sendComment(\'${response}\',\'${relatedData['senttime']}\');"></button>

            </div>
            <div style="display:none;" class="flex-shrink-1  align-self-center ml-1">
                <button class="btn btn-secondary " type="button"onclick="uploadFile(this)">+</button>
            </div>
          </div>`

        );
        getCommentReadList(response);

        $("#commentinput").unbind().keypress(function(e) {
          var code = e.which;
          if ((code && e.shiftKey) && code == 13) {
            e.preventDefault();
            $('#commentbutton').click();
          }
        });
      }
    });
    $("#commentinput").unbind().keypress(function(e) {
      var code = e.which;
      if ((code && e.shiftKey) && code == 13) {
        e.preventDefault();
        $('#commentbutton').click();
      }
    });
    $("#commentinput").keyup(function(e) {
      var code = e.which;
      if ((code && e.shiftKey) && code == 13) {}
    });
  }

  function getCommentReadList(commentID) {
    getCommentContent(commentID);
    updateCommentReadTime(commentID);
  }

  function updateCommentReadTime(commentID) {

    $.ajax({
      url: '/chat/commentReadTime/' + commentID,
      type: 'POST',
      data: {
        _METHOD: 'PATCH'
      },
      dataType: 'json'
    });
  }

  function getCommentContent(commentID) {
    $.ajax({
      url: '/chat/comment/' + commentID,
      type: 'get',
      data: {},
      dataType: 'json',
      success: function(response) {
        console.log(response)
        $('[name=comment]').html("");
        $(response).each(function() {
          console.log(this.case);
          if (this.case == 'me') {
            $('[name=comment]').append(
              `<div class="text-right outgoing_msg" data-sentTime="${this.sentTime}">
              <div class="d-flex flex-row-reverse bd-highlight">
                <div class="p-2 bd-highlight bg-secondary text-white rounded">
                  ${this.content.replace('<a href="/chat/,<a href="#" data-toggle="modal" data-target="#basicModal" data-type="file" data-href="/chat/')}
                </div>
              </div>
              <small>${this.showSentTime}</small>
              <a target="_blank" href="#" data-toggle="modal" data-target="#newModal"  data-content="${encodeURIComponent(this.content)}" data-sentTime="${this.sentTime}" data-UID="${this.UID}" data-commentID="${commentID}"><i class="fa fa-eye" aria-hidden="true"></i>${this.readNum==null? 0 :this.readNum }
              </a>
            </div>
          `);
          } else {
            $('[name=comment]').append(
              `<div class="text-left incoming_msg" data-sentTime="${this.fullsentTime}">
            <div class=""> <span name="tooltipOnlineTime" data-id=${this.UID}  data-toggle="tooltip" data-placement="right" title="搜尋中...">${this.UID},${this.staff_name}</span></div>
              <div class="d-flex bd-highlight">
                <div class="p-2 bd-highlight bg-dark text-white rounded">
                  ${this.content.replace(/style="color:#FFFFFF;"/g,'style="color:#CCEEFF;"').replace('<a href="/chat/','<a href="#" data-toggle="modal" data-target="#basicModal" data-type="file" data-href="/chat/')}
                </div>
              </div>
            </div>
            <small>${this.showSentTime}</small>
            <a target="_blank" href="#" data-toggle="modal" data-target="#newModal" data-content="${encodeURIComponent(this.content)}" data-sentTime="${this.sentTime}" data-UID="${this.UID}" data-commentID="${commentID}"><i class="fa fa-eye" aria-hidden="true"></i>${this.readNum==null? 0 :this.readNum }
            </a>
          </div>
          `);
          }
        });
        console.log($('[name=comment]').scrollHeight);
        $('[name=comment]').scrollTop($('[name=comment]')[0].scrollHeight);
      }
    });
  }


  function sendComment(commentID, senttime) {
    if ($("#commentinput").val() != "") {
      Msg = $("#commentinput").val();
      Msg = Msg.replace(/\r?\n/g, '<br />');
      $.ajax({
        url: '/chat/comment/' + commentID,
        type: 'post',
        data: {
          commentID: commentID,
          Msg: Msg
        },
        dataType: 'json',
        success: function(response) {
          // console.log(response);

          getCommentReadList(commentID);
        }
      });
      var tmpsenter;
      $.ajax({
        url: '/chat/comment/senter/' + commentID,
        type: 'get',
        data: {},
        dataType: 'json',
        success: function(response) {
          console.log(response);
          tmpsenter = response.UID;
          getCommentMember(response.UID, commentID, senttime);

        }
      });


      $("#commentinput").val("");
    }

  }

  function getCommentMember(tmpUsr, commentID, senttime) {
    $.ajax({
      url: '/chat/comment/member/' + commentID + '/' + tmpUsr,
      type: 'get',
      data: {},
      dataType: 'json',
      success: function(response) {
        // console.log(response.num);
        if (response.status == 'success') {
          if (response.textSender != 'dontSend') {
            addCommentNotice(tmpUsr, response.textSender, Msg, senttime);
          }

          $(response.people).each(function() {
            // console.log(this);
            addCommentNotice(this.UID, response.text, Msg, senttime);
          });
        } else if (response.status == 'no') {
          if (response.textSender != 'dontSend') {
            addCommentNotice(tmpUsr, response.text, Msg, senttime);
          }
        }

      }
    });
  }

  function addCommentNotice(tmpStaff, tmpDetail, tmpMsg, senttime) {
    console.log(tmpStaff, tmpDetail, tmpMsg);

    $.ajax({
      url: '/chat/notification/comment',
      type: 'post',
      data: {
        data: JSON.stringify({
          UID: tmpStaff,
          detail: tmpDetail,
          msg: tmpMsg,
          senttime: senttime,
          chatID: chatID
        })
      },
      dataType: 'json',
      success: function(response) {
        // console.log(response);
      }
    });

  }
  $('#newModal').on('show.bs.modal', function(e) {
    $('#newModal .modal-footer').html(basicModalFooter);
    var tmpTarget = $(e.relatedTarget);

    // console.log($(e.relatedTarget).data());
    $('#newModal .modal-title').text('已讀清單');
    $('#newModal .modal-body').html(
      '<h5>已讀</h5>' +
      '<div name="commentReadList"></div>' +
      '<hr>' +
      '<div class="alert alert-secondary" role="alert">' +
      '<h5 class="font-weight-bold">未讀</h5>' +
      '<div name="commentUnreadList"></div>' +
      '</div>'
    );

    $.ajax({
      url: `/chat/commentReadlist`,
      type: 'get',
      data: {
        commentID: tmpTarget.data('commentid'),
        UID: tmpTarget.data('uid'),
        senttime: encodeURIComponent(tmpTarget.data('senttime')),
        chatID: chatID
      },
      dataType: 'json',
      success: function(response) {
        $('[name=commentReadList]').html("");
        $('[name=commentUnreadList]').html("");
        $(response).each(function() {
          if (this.haveread == 1) {
            $('[name=commentReadList]').append('<p>' + this.name + '</p>')
          } else if (this.haveread == 0) {
            $('[name=commentUnreadList]').append('<p class="font-weight-bold">' + this.name + '</p>')
          }
        });
      }
    });

  });

  function getFile(relatedData) {
    $('#basicModal .modal-title').text('讀取中...');
    $('#basicModal .modal-body').html('<div class="spinner-border" role="status"> <span class="sr-only">Loading...</span> </div>');
    $.ajax({
      url: relatedData['href'].replace(/chat\/file/g, 'chat\/fileFormat'),
      type: 'get',
      dataType: 'json',
      success: function(response) {
        if (response.type == 'file') {
          var win = window.open(relatedData['href']);
          if (win) {
            //Browser has allowed it to be opened
            win.focus();
          }
          setTimeout(function() {
            $('#basicModal').modal('hide');
          }, 3000);
        } else if (response.type == 'picture') {
          viewPhoto(relatedData['href'].replace(/chat\/file/g, 'chat\/picture'));
        }
      }
    });
  }

  function insertIssue() {
    $('#basicModal .modal-title').text('選擇分類');
    //console.log(chatID);
    $('#basicModal .modal-body').html('<h6 class="card-subtitle mb-2 text-muted listBox">');
    $.ajax({
      url: '/chat/class/',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        $(response).each(function() {
          $('#basicModal .listBox').append(
            '<div class="input-group mb-2 listItem">' +
            '<div class="input-group-prepend">' +
            '<button type="button" class="btn btn-dark" name="buttonInsetClass" data-id=' + this.id + '>' +
            this.name +
            '</button>' +
            '</div>' +
            '</div>'
          );
        });
        $('[name=buttonInsetClass]').on('click', function(e) {
          var classId = $(this).data('id');
          var newclass;
          $.ajax({
            url: '/chat/class/' + classId + '/',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
              newclass = this.name;
            }
          });

          $.ajax({
            url: '/chat/class/' + classId + '/' + chatID + '/',
            type: 'POST',
            data: {
              _METHOD: 'patch'
            },
            dataType: 'json',
            success: function(response) {
              var extendObject = $('[name=chatBox]').children().clone(true);
              extendObject.find('[name="room' + classId + '"]').remove();
              $('[name="' + newclass + '"]').append(
                '<div class="card-body" name="room' + classId + '">' +
                '</div>'
              );
              $('[name="room' + classId + '"]').append(extendObject);
              // $(function(){
              //   $('#copy').clone().appendTo('#copied');
              // });
            }
          });
          $('#basicModal').modal('hide');
        });
      }
    });
    $('#basicModal .modal-footer').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>');
  }

  function addIssue() {
    $('#basicModal .modal-title').text('新增議題群組');
    $('#basicModal .modal-body').html(
      '<div class="sticky-top">' +
      '<div class="input-group mb-3">' +
      '<span class="input-group-text" id="inputGroup-sizing-default">分類名稱</span>' +
      '<input type="text" class="form-control" name="inputAddClassName" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default">' +
      '<button type="button" class="btn btn-dark" name="buttonAddClass">新增</button>' +
      '</div>' +
      '<h6 class="card-subtitle mb-2 text-muted listBox">' +
      '</div>'
    );
    $('#basicModal .modal-footer').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>');

    queue['search'] = null;
    $('.searchInput').unbind().on('keyup', function() {
      clearTimeout(queue['search']);
      queue['search'] = setTimeout(function() {
        $('.listItem').each(function() {
          if ($(this).find('.listName').val().indexOf($('.searchInput').val()) > -1) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      }, 300);
    });
    $.ajax({
      url: '/chat/class/',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        $(response).each(function() {
          $('#basicModal .listBox').append(
            '<div class="input-group mb-2 listItem">' +
            '<div class="input-group-prepend">' +
            '<input type="text" class="form-control listName" disabled value=' + this.name + '>' +
            '<button type="button" class="btn" name="buttonDeleteClass" data-id=' + this.id + '>' +
            '<i class="fa fa-times" aria-hidden="true"></i>' +
            '</button>' +
            '</div>' +
            '</div>'
          );
        });
        $('[name=buttonDeleteClass]').on('click', function(e) {
          var classId = $(this).data('id');
          //console.log(classId);
          $.ajax({
            url: '/chat/class/' + classId + '/',
            type: 'POST',
            data: {
              _METHOD: 'delete'
            },
            dataType: 'json',
            success: function(response) {
              //console.log(response);
            }
          });
          addIssue();
        });
      }
    });
    $('[name=buttonAddClass]').on('click', function(e) {
      var groupname = $('[name=inputAddClassName]').val();
      $.ajax({
        url: '/chat/class/',
        type: 'POST',
        data: {
          data: JSON.stringify({
            name: groupname
          })
        },
        dataType: 'json',
        success: function(response) {
          //console.log(response);
        }
      });
      $('#basicModal').modal('hide');
    });
  }

  function viewPhoto(src) {
    $('#basicModal .modal-title').text('圖片');
    $('#basicModal .modal-body').html('<img class="img-fluid"/>');
    $('#basicModal .modal-body img').attr('src', src);
    $('#basicModal .modal-dialog').addClass('modal-xl');
    $('#basicModal .modal-body').addClass('text-center');
  }
  $('#basicModal').on('hidden.bs.modal', function(e) {
    $('#basicModal .modal-dialog').removeClass('modal-xl');
    $('#basicModal .modal-body').removeClass('text-center');
  });

  function attachType() {
    $('#basicModal .modal-title').text('檔案類型');
    $('#basicModal .modal-body').html(
      '<div class="container-fluid">' +
      '<div class="row">' +
      '<div class="col-6">' +
      '<button type="button" class="btn btn-secondary float-right" onclick="uploadPicture(this)">分享圖片</button>' +
      '</div>' +
      '<div class="col-6">' +
      '<button type="button" class="btn btn-secondary" onclick="uploadFile(this)">上傳檔案</button>' +
      '</div>' +
      '</div>' +
      '</div>'
    );
  }

  function getReadcount() {
    var data = new Object();
    data['chatID'] = chatID;
    if (queue['readcount'] != null)
      queue['readcount'].abort();
    queue['readcount'] = $.ajax({
      url: '/chat/readcount',
      type: 'get',
      data: {
        data: JSON.stringify(data)
      },
      dataType: 'json',
      success: function(response) {
        if (response[response.length - 1].chatID != chatID) {
          return false;
        }
        response.pop();
        var readcountElement = response.shift();
        if (readcountElement === undefined)
          return false;
        $('a[data-type=readlist]').each(function() {
          $(this).html('<i class="fa fa-eye" aria-hidden="true"></i>' + readcountElement.sum);
          if ($(this).attr('data-sentTime') == readcountElement.sentTime) {
            readcountElement = response.shift();
            if (readcountElement === undefined)
              return false;
          }
        });
        setTimeout(getReadcount, 3000);
      }
    });
  }

  function getReadlist(relatedData) {
    $('#basicModal .modal-title').text('已讀清單');
    $('#basicModal .modal-body').html(
      '<h5>已讀</h5>' +
      '<div name="readList"></div>' +
      '<hr>' +
      '<div class="alert alert-secondary" role="alert">' +
      '<h5 class="font-weight-bold">未讀</h5>' +
      '<div name="unreadList"></div>' +
      '</div>'
    );

    var data = new Object();
    data['UID'] = relatedData['uid'];
    data['sentTime'] = relatedData['senttime'];
    data['content'] = decodeURIComponent(relatedData['content']);
    data['chatID'] = chatID;
    $.ajax({
      url: '/chat/readlist',
      type: 'get',
      data: {
        data: JSON.stringify(data)
      },
      dataType: 'json',
      success: function(response) {
        $('[name=readList]').html("");
        $('[name=unreadList]').html("");
        $(response).each(function() {
          if (this.checkread == 'true')
            $('[name=readList]').append('<p>' + this.staff_name + '</p>')
          else
            $('[name=unreadList]').append('<p class="font-weight-bold">' + this.staff_name + '</p>')
        });
      }
    });
  }

  function getMember() {
    $('#basicModal .modal-title').text('議題成員');
    $('#basicModal .modal-body').html('<div class="spinner-border" role="status"> <span class="sr-only">Loading...</span> </div>');
    $.ajax({
      url: '/chat/member/' + chatID,
      type: 'get',
      dataType: 'json',
      success: function(response) {
        $('#basicModal .modal-body').html('');
        $('#basicModal .modal-body').append(
          '<div class="card">' +
          '<div class="card-body">' +
          '<button class="btn btn-secondary" onclick="Chatroom(\'update\');">修改議題</button></br>' +
          '<p class="card-text">' +
          '<h6 class="card-subtitle mb-2 text-muted listBox">' +
          '</h6>' +
          '</p>' +
          '</div>' +
          '</div>'
        );
        $(response).each(function() {
          $('#basicModal .listBox').append(
            '<div class="input-group mb-3 listItem">' +
            '<input type="text" class="form-control listID" disabled value=' + this.id + '>' +
            '<input type="text" class="form-control listName" disabled value=' + this.name + '>' +
            '</div>'
          );
        });
      }
    });
  }

  function Chatroom(type) {
    var _chatID = '';
    if (type == 'delete') {
      $('#basicModal .modal-title').text('離開議題');
      $('#basicModal .modal-body').html('');
      $('#basicModal .modal-body').append('確定要離開此議題嗎？');
      $('#basicModal .modal-footer').prepend('<button class="btn btn-dark" name="buttonDeleteChatroom">確認</button>');
      $('[name=buttonDeleteChatroom]').unbind().on('click', function() {
        var data = new Object();
        data['chatID'] = chatID;
        $.ajax({
          url: '/chat/chatroom',
          type: 'post',
          data: {
            data: JSON.stringify(data),
            _METHOD: 'DELETE'
          },
          dataType: 'json',
          success: function(response) {
            $('#basicModal').modal('hide');
            routine();
          }
        });
      });
      return;
    } else if (type == 'create') {
      $('#basicModal .modal-title').text('新增議題');
      $('#basicModal .modal-body').html('');
      $('#basicModal .modal-body').append(
        '<div class="sticky-top">' +
        '<div class="input-group mb-3">' +
        '<span class="input-group-text" id="inputGroup-sizing-default">議題名稱</span>' +
        '<input type="text" class="form-control" name="inputChatroomTitle" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default">' +
        '<button type="button" class="btn btn-dark buttonChatroomCreate">新增</button>' +
        '</div>' +
        '</div>'
      );
      $('#basicModal .buttonChatroomCreate').unbind().on('click', function() {
        var data = new Object();
        data['title'] = $('#basicModal [name=inputChatroomTitle]').val();
        data['member'] = [];
        $('#basicModal .checkItem:checked').each(function() {
          var member = new Object();
          member['UID'] = $(this).data('name');
          data['member'].push(member);
        });
        $.ajax({
          url: '/chat/chatroom',
          type: 'post',
          data: {
            data: JSON.stringify(data)
          },
          dataType: 'json',
          success: function(response) {
            if (response.status == 'success') {
              $('#basicModal').modal('hide');
              routine();
            } else {
              $('#basicModal').modal('hide');
            }
          }
        });
      });
    } else if (type == 'update') {
      _chatID = '/' + chatID;
      $('#basicModal .modal-title').text('修改議題');
      $('#basicModal .modal-body').html('');
      $('#basicModal .modal-body').append(
        '<div class="sticky-top">' +
        '<div class="input-group mb-3">' +
        '<span class="input-group-text" id="inputGroup-sizing-default">議題名稱</span>' +
        '<input type="text" class="form-control" name="inputChatroomTitle" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default">' +
        '<button type="button" class="btn btn-dark buttonChatroomUpdate">修改</button>' +
        '</div>' +
        '</div>'
      );
      $.ajax({
        url: '/chat/chatroom/title/' + chatID,
        type: 'get',
        dataType: 'json',
        success: function(response) {
          if (response.length > 0) {
            $('#basicModal [name=inputChatroomTitle]').val(response[0]['chatName']);
          }
        }
      });
      $('#basicModal .buttonChatroomUpdate').unbind().on('click', function() {
        var data = new Object();
        data['title'] = $('#basicModal [name=inputChatroomTitle]').val();
        data['member'] = [];
        data['chatID'] = chatID;
        $('#basicModal .checkItem:checked').each(function() {
          var member = new Object();
          member['UID'] = $(this).data('name');
          data['member'].push(member);
        });
        $.ajax({
          url: '/chat/chatroom',
          type: 'post',
          data: {
            data: JSON.stringify(data),
            _METHOD: 'PATCH'
          },
          dataType: 'json',
          success: function(response) {
            if (response.status == 'success') {
              $('#basicModal').modal('hide');
            } else {
              $('#basicModal').modal('hide');
            }
          }
        });
      });
    } else if (type == 'choooseIssues') {
      $('#basicModal .modal-title').text('選擇分類');
      $('#basicModal .modal-body').html('');

    }
    $('#basicModal .modal-body').append(
      '<div class="card">' +
      '<div class="card-body">' +
      '<h5 class="card-title">' +
      '<div class="srch_bar">' +
      '<div class="stylish-input-group">' +
      '<input type="text" class="search-bar searchInput" placeholder="搜尋" >' +
      '<span class="input-group-addon">' +
      '<button type="button">' +
      '<i class="fa fa-search" aria-hidden="true"></i>' +
      '</button>' +
      '</span>' +
      '</div>' +
      '</div>' +
      '</h5>' +
      '<h6 class="card-subtitle mb-2 text-muted listBox">' +
      '</h6>' +
      '</div>' +
      '</div>'
    );
    queue['search'] = null;
    $('.searchInput').unbind().on('keyup', function() {
      clearTimeout(queue['search']);
      queue['search'] = setTimeout(function() {
        $('.listItem').each(function() {
          if ($(this).find('.listName').val().indexOf($('.searchInput').val()) > -1) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      }, 300);
    });
    $.ajax({
      url: '/chat/list' + _chatID,
      type: 'get',
      dataType: 'json',
      success: function(response) {
        $(response).each(function() {
          $('#basicModal .listBox').append(
            '<div class="input-group mb-3 listItem">' +
            '<div class="input-group-prepend">' +
            '<div class="input-group-text">' +
            '<input type="checkbox" class="checkItem" data-name=' + this.id + '>' +
            '</div>' +
            '<input type="text" class="form-control listID" disabled value=' + this.id + '>' +
            '<input type="text" class="form-control listName" disabled value=' + this.name + '>' +
            '</div>' +
            '</div>'
          );
        });
      }
    });
  }

  $("#textinput").on('paste', function(e) {
    var clipboardData = e.originalEvent.clipboardData;
    var items = clipboardData.items;
    for (var i = 0; i < items.length; i++) {
      if (items[i].type.indexOf("image") == -1) continue;
      var file_data = items[i].getAsFile();
      var form_data = new FormData();
      form_data.append('inputFile', file_data);
      $.ajax({
        url: '/chat/file/' + chatID,
        cache: false,
        contentType: false,
        processData: false,
        data: form_data, //data只能指定單一物件                 
        type: 'post',
        success: function(data) {

        }
      });
    }
  });
  var searchBar = null;
  $('#searchChatroomInput').on('keyup', function() {
    if (searchBar != null) {
      clearTimeout(searchBar);
    }
    if ($('#searchChatroomInput').val() == '') {
      $('.chat_list').parent().show();
    }
    searchBar = setTimeout(function() {
      $('.chat_list').each(function() {
        if ($(this).find('h5').text().indexOf($('#searchChatroomInput').val()) > -1) {
          $(this).parent().show();
        } else {
          $(this).parent().hide();
        }
      });
    }, 500);
  });
</script>