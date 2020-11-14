<?php 
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");
    
    open_database_connection($sql);
    
    $statement = $sql->prepare("SELECT * FROM `forum_hubs` WHERE `id` = ?");
    $statement->execute([$_GET["id"]]);
    $hub = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$hub)
    {
        include_page("/error/404.php");
    }
?>

<!DOCTYPE HTML>

<html>
	<head>
		<?php
			build_header($hub["name"]);
        ?>
        <link rel="stylesheet" href="<?= get_server_host() ?>/html/css/forum.min.css">
	</head>
	<body class="d-flex flex-column">
		<?php
			build_navigation_bar();
		?>

        <div class="container">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb rboxlo-color-muted">
                        <a class="breadcrumb-item white-text" href="/forums/">Forums</a>
                        <a class="breadcrumb-item white-text" href="#"><?= $hub["name"] ?></a>
                    </ol>
                </nav>
            </div>

            <div class="mb-2 d-flex align-items-center">
                <div class="mr-auto">
                    <span><b>Current time:</b> <?= date("F j Y, g:i A") ?></span>
                </div>

                <div class="ml-auto">
                    <div class="md-form input-group m-0">
                        <input class="form-control" type="text" placeholder="Search" aria-label="Search" aria-describedby="search" value="">
                        <div class="input-group-append">
                            <button id="share" class="btn btn-md btn-purple rboxlo-color-2 m-0 px-3" type="button">Go</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="rounded-top mdb-color rboxlo-color-2 pt-3 px-3 pb-3 hub-grid">
                    <div class="row">
                        <div class="white-text col"><a class="inherit-color" href="#"><?= $hub["name"] ?></a></div>
                        <div class="white-text col-2 head-separator text-center">Threads</div>
                        <div class="white-text col-2 head-separator text-center">Replies</div>
                        <div class="white-text col-2 head-separator text-center">Last Post</div>
                    </div>
                </div>
                    
                <div class="card-body px-3 py-0">
                    <?php
                        $statement = $sql->prepare("SELECT * FROM `forum_categories` WHERE `hub_id` = ?");
                        $statement->execute([$hub["id"]]);
                        $categories = $statement;

                        foreach ($categories as $category):
                            $category["threads"] = 0;
                            $category["replies"] = 0;
                            
                            // Fetch the replies and posts
                            $statement = $sql->prepare("SELECT COUNT(1) FROM `forum_threads` WHERE `category_id` = ?");
                            $statement->execute([$category["id"]]);
                            $category["threads"] = number_format(intval($statement->fetchColumn()));

                            $statement = $sql->prepare("SELECT COUNT(1) FROM `forum_replies` WHERE `category_id` = ?");
                            $statement->execute([$category["id"]]);
                            $category["replies"] = number_format(intval($statement->fetchColumn()));

                            // Now output
                    ?>
                    <a class="row inherit-color py-3 forum-row" href="/forums/category?id=<?= $category["id"] ?>">
                        <div class="col align-self-center"><span><b><?= $category["title"] ?></b></span><br><span><?= $category["description"] ?></div>
                        <div class="col-2 align-self-center text-center"><?= $category["threads"] ?></div>
                        <div class="col-2 align-self-center text-center"><?= $category["replies"] ?></div>
                        <div class="col-2 align-self-center text-center"><b>1:00 PM</b><br>TODO</div>
                    </a>
                    <?php
                        endforeach;
                    ?>
                </div>
            </div><br><br>
            <?php
                close_database_connection($sql, $statement);
            ?>
		</div>

		<?php
			build_footer();
		?>
	</body>
</html>
