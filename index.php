<?
require_once('vendor/autoload.php');

use \Rutubeloader\RutubeClient;

$client = new RutubeClient();

?><!DOCTYPE html>
<html xml:lang="ru" lang="ru">
<head>
    <title>Rutube Loader</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">

    <link rel="stylesheet" href="assets/bootstrap-5.2.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap-5.2.0-dist/css/bootstrap-reboot.min.css">
    <link rel="stylesheet" href="assets/bootstrap-5.2.0-dist/css/bootstrap-utilities.min.css">

    <script src="assets/bootstrap-5.2.0-dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bootstrap-5.2.0-dist/js/bootstrap.min.js"></script>

    <script src="assets/jquery-3.6.0.js"></script>
    <script src="assets/script.js"></script>
</head>
<body class="">
    <header class="bg-info sticky-top p-4 mb-5">
            <div class="row d-flex justify-content-between">
                <div class="col-4">
                    <h3>Rutube Loader</h3>
                </div>
                <?if($client->isAuth()){?>
                    <div class="col-4 d-flex justify-content-end">
                        <span class="d-flex align-items-center mx-3"><?=$client->getUsername()?></span>
                        <div class="d-flex justify-content-center">
                            <a href="?logout=Y" type="submit" class="btn btn-primary">Выйти</a>
                        </div>
                    </div>
                <?}?>
            </div>
    </header>

    <div class="container mb-5">
        <?if(!$client->isAuth()){?>

            <div class="row  d-flex justify-content-center">
                <div class="col-4">
                    <form id="authForm" class="row" action="/" method="post">
                        <div class="mb-3 row">
                            <label for="email" class="col-sm-2 col-form-label">Email</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="email" name="email" value="">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="password" class="col-sm-2 col-form-label">Пароль</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="password" name="password" value="">
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary">Авторизоваться</button>
                        </div>
                    </form>
                </div>
            </div>

        <?}else{?>

            <div id="testFileLink" class="mb-5">
                <a href="rutube_test_load.xlsx" download target="_blank">Скачать тестовый файл</a>
            </div>

            <div id="manual" class="mb-5">
                <div>Файл должен иметь структуру:</div>
                <div class="fw-bold">Адрес видео, Заголовок, Описание, Адрес картинки, Категория, Скрытое</div>
                <br>
                <div>Адрес видео - ресурс в сети, доступный для скачивания</div>
                <div>Заголовок - название для видео</div>
                <div>Описание - подробное описание для видео</div>
                <div>Адрес картинки - ресурс в сети, доступный для скачивания</div>
                <div>Категория - id категории для видео. <a href="/api/category.json">Список категорий</a></div>
                <div>Скрытое - если указано 1 или y, видео будет скрыто</div>
            </div>

            <form  id="fileForm" class="mb-5">
                <div class="col-8 col-sm-6 col-md-4 col-lg-3 d-flex justify-content-center flex-column m-auto">
                    <input type="file" name="file" class="mb-3">
                    <button type="submit" class="btn btn-primary mb-3">Загрузить файл</button>
                </div>
            </form>

            <table id="dataTable" class="table table-striped table-bordered mb-5 d-none">
                <colgroup>
                    <col>
                    <col style="width: 150px;">
                    <col>
                    <col>
                    <col>
                    <col style="width: 155px;">
                </colgroup>

                <thead>
                <tr>
                    <th>Адрес видео / картинки</th>
                    <th>Заголовок</th>
                    <th>Описание</th>
                    <th>Категория</th>
                    <th>Скрытое</th>
                    <th><div class="d-flex justify-content-end">Статус</div></th>
                </tr>
                </thead>
            </table>

            <div>
                <h3 class="mb-4">Мои видео</h3>
                <div id="videoList" class="row">
                    <?
                    $response = $client->getVideos();
                    $items = $response->results;
                    if($items){?>
                        <?foreach ($items as $item) {?>
                            <div class="col-3 d-flex mb-4">
                                <div class="card">
                                    <img src="<?=$item->thumbnail_url?>" class="card-img-top" alt="...">

                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?=$item->title?></h5>
                                        <div class="card-text mb-3"><?=$item->description?></div>
                                        <a href="<?=$item->video_url?>" class="btn btn-primary mt-auto">Открыть</a>
                                    </div>
                                </div>
                            </div>
                        <?}?>
                    <?}?>
                </div>
            </div>
        <?}?>

    </div>
</body>



