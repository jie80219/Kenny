<?php

use \Psr\Container\ContainerInterface;
use \Slim\Views\PhpRenderer;
use Slim\Http\UploadedFile;

class ChatController
{
    protected $container;
    protected $db;
    public function __construct()
    {
        global $container;
        $this->container = $container;
        $this->db = $container->db;
    }

    public function renderChat($request, $response, $args)
    {
        $renderer = new \Slim\Views\PhpRenderer($this->container->view);
        return $renderer->render($response, '/chat.php', []);
    }

    public function renderChatCorner($request, $response, $args)
    {
        $renderer = new \Slim\Views\PhpRenderer($this->container->view);
        return $renderer->render($response, '/chatCorner.php', []);
    }

    public function getStaffName($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getStaffName($args['id'], $args['type']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getStaffNameByChatId($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getStaffName($args['id'], $args['type'], $args['chatID']);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
    }
    public function getDepartment($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getDepartment($args['id']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getPosition($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getPosition();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getGender($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getGender();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getMarriage($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getMarriage();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getInsuredcompany($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getInsuredcompany();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getWorkStatus($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getWorkStatus();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getStaffType($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getStaffType();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getEducationCondition($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getEducationCondition();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getStaffNum($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->getStaffNum();
        $ack = array(
            'num' => $result
        );
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($ack);
        //echo $response['num'];
        return $response;
    }
    public function checkStaffId($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->checkStaffId($args['staff_id']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        //echo $response['num'];
        return $response;
    }
    public function checkRegister($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $result = $staff->checkRegister();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        // echo $response;
        return $response;
    }
    public function register($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $ack = $staff->register();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($ack);
        return $response;
    }
    public function modify($request, $response, $args)
    {
        $staff = new Staff($this->db);
        $ack = $staff->modify($args['staff_id']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($ack);
        return $response;
    }
    public function getTable($request, $response, $args)
    {
        $table = new Table($this->db);
        $ack = $table->getTable();

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($ack);
        return $response;
    }
    public function allInfo($request, $response, $args)
    {
        $staff = new Table($this->db);
        $result = $staff->allInfo($args['staff_id']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getProfile($request, $response, $args)
    {
        $staff = new Table($this->db);
        $result = $staff->getProfile($args['staff_id']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);

        return $response;
    }
    public function addDelete($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->addDelete();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function init($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->init();
        session_start();
        $_SESSION['last'][$args['timestamp']] = $result;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_chat_content_public($request, $response, $args){
        $data = $request->getQueryParams();
        $chat = new Chat($this->db);
        $data["UID"] = 0;
        $result = $chat->get_chat_content($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_chat_content($request, $response, $args){
        $data = $request->getQueryParams();
        $chat = new Chat($this->db);
        $data["UID"] = $_SESSION['id'];
        $result = $chat->get_chat_content($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_chatroom($request, $response, $args){
        $data = $request->getQueryParams();
        $chat = new Chat($this->db);
        $data["UID"] = $_SESSION['id'];
        $result = $chat->get_chatroom($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function routine($request, $response, $args)
    {
        $data = $_SESSION['last'][$args['timestamp']];
        $chat = new Chat($this->db);
        $result = $chat->routine($data, $args['chatID']);
        session_start();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        $_SESSION['last'][$args['timestamp']] = $result;
        $_SESSION['chat'][$_SESSION['id']][$args['chatID']] = $result;
        return $response;
    }
    public function routineWithLimit($request, $response, $args)
    {
        session_start();

        $data = $_SESSION['last'][$args['timestamp']];
        if (!isset($_SESSION['now'])) {
            $_SESSION['now'] = 0;
        } else {
            $_SESSION['now'] = $_SESSION['now'] + 1;
            if ($_SESSION['now'] == 65535)
                $_SESSION['now'] = 0;
        }
        if ($args['chatID'] == $data['result']['chat']['chatID'] && $data['limit'] != $args['limit']) {
            $result = $data;
            $_SESSION['last'][$args['timestamp']]['limit'] = $args['limit'];
            session_write_close();
            $chat = array();
            for ($i = $args['limit']; $i < count($result['chat']); $i++) {
                array_push($chat, $result['chat'][$i]);
            }
            $result['chat'] = $chat;
            if ($args['limit'] - 10 < 0) {
                $result['limit'] = 0;
            } else {
                $result['limit'] = $args['limit'];
            }
        } else {
            $chat = new Chat($this->db);
            $result = $chat->routine($data, $args['chatID']);
            if ($args['limit'] == -1) {
                if (count($result['chat']) - 10 < 0) {
                    $result['limit'] = 0;
                } else {
                    $result['limit'] = count($result['chat']) - 10;
                }
            } else {
                $result['limit'] = $args['limit'];
            }
            $chat = array();
            for ($i = $result['limit']; $i < count($result['chat']); $i++) {
                array_push($chat, $result['chat'][$i]);
            }
            session_start();
            $_SESSION['last'][$args['timestamp']] = $result;
            $_SESSION['chat'][$_SESSION['id']][$args['chatID']] = $result;
            $result['chat'] = $chat;
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getSaveChat($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getSaveChat($args['chatID']);
        if (empty($result)) {
            return $response;
        }
        $response = $response->withJson($result);
        return $response;
    }
    public function patchHeart($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $data = $request->getParsedBody();
        $result = $chat->patchHeart($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getCommentID($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $data = $request->getQueryParams();
        $result = $chat->getCommentID($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getCommentReadList($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $data = $request->getQueryParams();
        $result = $chat->getCommentReadList($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function updateCommentReadTime($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->updateCommentReadTime($args['commentID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getList($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getList();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getListByChatId($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getList($args['chatID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getMemberDepartment($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getMemberDepartment($args['chatID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getMember($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getMember($args['chatID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getReadList($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getReadList($_GET);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getReadListNew($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getReadListNew($_GET);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getReadCount($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getReadCount($_GET);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getChatroom($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getChatroom();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function createChatroom($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->createChatroom($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function updateChatroom($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->updateChatroom($request->getParsedBody());
        $result = array("status" => "success");
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function deleteChatroom($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->deleteChatroom($request->getParsedBody());
        $result = array("status" => "success");
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getChatroomTitle($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getChatroomTitle($args['chatID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getChatContent($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getChatContent($args['chatID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getChatContentNew($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getChatContentNew($args['chatID'], $_GET);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function updateMessage_public($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $data = $request->getParsedBody();
        $data['UID'] = 0;
        $result = $chat->updateMessage($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function updateMessage($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $data = $request->getParsedBody();
        $data['UID'] = $_SESSION['id'];
        $result = $chat->updateMessage($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getLastOnLine($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getLastOnLine($args['UID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function updateLastReadTime($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->updateLastReadTime($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function uploadFile($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->uploadFile($args['chatID'], $this->upload_directory, $request->getUploadedFiles(), false);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function downloadFile($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->downloadFile($args['fileID']);
        if (isset($result['data'])) {
            $file = $this->upload_directory . '/' . $result['data']['fileName'];
            $response = $response
                ->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', 'attachment;filename="' . $result['data']['fileNameClient'] . '"')
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Pragma', 'public')
                ->withHeader('Content-Length', filesize($file));
            readfile($file);
        } else {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
        }
        return $response;
    }
    public function getFileFormat($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getFileFormat($args['fileID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function downloadPicture($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->downloadFile($args['fileID']);
        if (isset($result['data'])) {
            $file = $this->upload_directory . '/' . $result['data']['fileName'];
            $image = @file_get_contents($file);
            $response->write($image);
            return $response->withHeader('Content-Type', FILEINFO_MIME_TYPE)
                ->withHeader('Content-Disposition', 'inline;filename="' . $result['data']['fileNameClient'] . '"');
        } else {
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
        }
        return $response;
    }
    public function getClass($request, $response, $args)
    {
        $class = new Chat($this->db);
        $result = $class->getClass();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getClassByClassId($request, $response, $args)
    {
        $class = new Chat($this->db);
        $result = $class->getClass($args['classId']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function deleteClass($request, $response, $args)
    {
        $class = new Chat($this->db);
        $result = $class->deleteClass($args['classId']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function insertClass($request, $response, $args)
    {
        $class = new Chat($this->db);
        $result = $class->insertClass($args['classId'], $args['chatID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function addClass($request, $response, $args)
    {
        $class = new Chat($this->db);
        $result = $class->addClass();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getComment($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getComment($args['commentID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function insertComment($request, $response, $args)
    {
        $content = $request->getParsedBody()['Msg'];
        $chat = new Chat($this->db);
        $result = $chat->insertComment($args['commentID'], $content);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function insertCommentByContent($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->insertComment($args['commentID'], $args['content']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getCommentMember($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getCommentMember($args['commentID'], $args['orgSender']);

        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getSenter($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->getSenter($args['commentID']);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function updateReport($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->updateReport($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function postDebug($request, $response, $args){
        $chat = new Chat($this->db);
        $member = $chat->getSystemUserId([]);
        $date = DateTime::createFromFormat('0.u00 U', microtime());
        $timezone = new DateTimeZone('Asia/Taipei');
        $date->setTimezone($timezone);
        $now = $date->format('Y-m-d H:i:s');
        $bug = [
            "data"=>json_encode([
                "title"=>"問題反應 時間：".$now,
                "member"=>$member
            ])
        ];
        $result = $chat->createChatroom($bug);

        $data = $request->getParsedBody();
        $data['UID'] = $_SESSION['id'];
        $data['chatID'] = $result['chatID'];
        $data['Msg'] = "感謝您反映問題：{$data['description']}，我們會盡快回覆您";
        $result = $chat->updateMessage($data);

        foreach ($member as $key => $access_token) {
            if(is_null($access_token['access_token'])) continue;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer {$access_token['access_token']}"
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            // In real life you should use something like:
            curl_setopt($ch, CURLOPT_POSTFIELDS, 
                http_build_query([
                    "message"=>"使用者{$access_token['name']}提出問題，問題內容：{$data['description']}"
                ])
            );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $head = curl_exec($ch);
            $result = json_decode($head,true);
        }
        $result['chatID'] = $data['chatID'];
        $result['chatName'] = "問題反應 時間：".$now;
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_template_type($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->get_template_type($request->getQueryParams());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_template_type($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->post_template_type($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function patch_template_type($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->patch_template_type($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function delete_template_type($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->delete_template_type($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function get_template($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->get_template($request->getQueryParams());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function post_template($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->post_template($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function patch_template($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->patch_template($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function delete_template($request, $response, $args)
    {
        $chat = new Chat($this->db);
        $result = $chat->delete_template($request->getParsedBody());
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
