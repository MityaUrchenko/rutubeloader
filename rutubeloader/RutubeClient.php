<?php

namespace Rutubeloader;

class RutubeClient
{

    public function __construct()
    {
        session_start();

        if($_GET["logout"]) {
            $this->logout();
        }

        $this->username = $_SESSION["rutube_username"];
        $this->password = $_SESSION["rutube_password"];
        $this->token = $_SESSION["rutube_token"];
        $this->userid = $_SESSION["rutube_userid"];

        if(!$this->getToken()){
            if($_POST["email"] && $_POST["password"]){
                $this->generateToken($_POST["email"],$_POST["password"]);
            }
        }else{
            if(!$this->ping()){
                $this->generateToken($this->getUsername(),$this->getPassword());
            }
        }
    }

    public function logout(){
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 1,
                      $params["path"], $params["domain"],
                      $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: /');
        exit;
    }

    public function isAuth(){
        return $this->getToken() ? true : false;
    }

    public function generateToken($username,$password)
    {
        $fields = array(
            'username' => $username,
            'password' => $password,
        );
        $response = $this->request("https://rutube.ru/api/accounts/token_auth/", "PUT", $fields);
        $this->setData($username,$password,$response->token,$response->userid);
    }

    public function requestToken($username,$password)
    {
        $fields = array(
            'username' => $username,
            'password' => $password,
        );

        /*
        $i = 0;
        while ($i < 3) {
            $response = $this->request("https://rutube.ru/api/accounts/token_auth/", "POST", $fields);
            if ($response->token) return $response->token;
            $i++;
            sleep(1);
        }
        */

        $response = $this->request("https://rutube.ru/api/accounts/token_auth/", "POST", $fields);

        //return $response;
        //return $this->generateToken();
    }

    public function setData($username,$password,$token,$userid)
    {
        $this->username = $username;
        $this->password = $password;
        $this->token = $token;
        $this->userid = $userid;

        $_SESSION["rutube_username"] = $username;
        $_SESSION["rutube_password"] = $password;
        $_SESSION["rutube_token"] = $token;
        $_SESSION["rutube_userid"] = $userid;

        /*
        setcookie("rutube_username", $username, time() + 60 * 60 * 24);
        setcookie("rutube_password", $password, time() + 60 * 60 * 24);
        setcookie("rutube_token", $token, time() + 60 * 60 * 24);
        setcookie("rutube_userid", $userid, time() + 60 * 60 * 24);
        */
    }


    public function getUsername()
    {
        return $this->username ? $this->username : $_SESSION["rutube_username"];
    }
    public function getPassword()
    {
        return $this->password ? $this->password : $_SESSION["rutube_password"];
    }
    public function getToken()
    {
        return $this->token ? $this->token : $_SESSION["rutube_token"];
    }
    public function getUserId()
    {
        return $this->userid ? $this->userid : $_SESSION["rutube_userid"];
    }

    public function ping()
    {
        $response = $this->getVideos();
        return $response->num_pages > 0;
    }


    public function uploadVideo($url, $title, $description = "", $thumbnail = "", $category_id = "", $is_hidden = "")
    {
        $fields = array(
            'url' => $url,
            'title' => $title,
            'description' => $description,
            'category_id' => $category_id,
            'is_hidden' => $is_hidden,

            //'callback_url' => $callback_url,
            //'errback_url' => $errback_url,
            //'query_fields' => $query_fields,
            //'extra' => $extra,
            //'quality_report' => $quality_report,
            //'converter_params' => $converter_params,
            //'author' => $author,
            //'protected' => $protected,
        );

        $response = $this->request("http://rutube.ru/api/video/", "POST", $fields, true);
        if($thumbnail) $this->uploadThumbnail($response->video_id,$thumbnail);

        return $response;
    }

    public function updateVideo($video_id, $fields)
    {
        /**
        title - string Название ролика, до 200 символов
        description - string  Описание ролика, до 5000 символов
        is_hidden - boolean Статус приватности ролика
        category - int id категории Rutube
        author - int id канала в который загружен ролик
        обратите внимание, что канал в который загружен ролик,
        а так же канал, в который переносится ролик,
        должны быть доступны для редактирования пользователю,
        производящему изменение
         */

        $response = $this->request("http://rutube.ru/api/video/$video_id/", "PUT", $fields, true);
        return $response;
    }

    public function uploadThumbnail($video_id,$thumbnail)
    {
        if(!$thumbnail) return;

        $headers = [];
        $headers[] = "Content-Type: multipart/form-data";

        $path = parse_url($thumbnail, PHP_URL_PATH);
        if ($path) $filename = time() . "_" . basename($path);
        file_put_contents("./$filename", fopen($thumbnail, 'r'));
        $fields = array('file' => new \CURLFile("./$filename"));

        $response = $this->request("http://rutube.ru/api/video/$video_id/thumbnail/", "POST", $fields, true, $headers);
        unlink("./$filename");
        return $response;
    }

    public function getVideo($id)
    {
        return $this->request("http://rutube.ru/api/video/" . $id, "GET", [], true);
    }

    public function getVideos()
    {
        return $this->request("http://rutube.ru/api/video/person/", "GET", [], true);
    }

    public function request($url, $type, $fields, $need_auth = false, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($need_auth) {
            $headers[] = 'Authorization: Token ' . $this->getToken();
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $json = curl_exec($ch);
        $response = json_decode($json);
        curl_close($ch);
        return $response;
    }
}