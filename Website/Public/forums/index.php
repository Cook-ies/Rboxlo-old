<?php 
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");
?>

<!DOCTYPE HTML>

<html>
	<head>
		<?php
			build_header("Forums");
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
                        <a class="breadcrumb-item white-text">Forums</a>
                    </ol>
                </nav>
            </div>

            <div class="mb-2 d-flex align-items-center">
                <span><b>Current time:</b> <?= date("F j Y, g:i A") ?></span>
                <div class="ml-auto">
                    <div class="md-form input-group m-0">
                        <input class="form-control" type="text" placeholder="Search" aria-label="Search" aria-describedby="search" value="">
                        <div class="input-group-append">
                            <button id="share" class="btn btn-md btn-purple rboxlo-color-2 m-0 px-3" type="button">Go</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php
                open_database_connection($sql);

                $statement = $sql->query("SELECT * FROM `forum_hubs`");
                foreach ($statement as $result):
            ?>
            <div class="card">
                <div class="rounded-top mdb-color rboxlo-color-2 pt-3 px-3 pb-3 responsive-forum-grid">
                    <div class="row">
                        <div class="white-text col"><?= $result["name"] ?></div>
                        <div class="white-text col-2 head-separator text-center">Threads</div>
                        <div class="white-text col-2 head-separator text-center">Replies</div>
                        <div class="white-text col-2 head-separator text-center">Last Post</div>
                    </div>
                </div>
                    
                <div class="card-body px-3 py-0">
                    <?php
                        $statement = $sql->prepare("SELECT * FROM `forum_categories` WHERE `hub` = ?");
                        $statement->execute([$result["id"]]);
                        $categories = $statement;

                        foreach ($categories as $category):
                            // Fetch the replies and posts
                            $statement = $sql->prepare("SELECT * FROM `forum_threads` WHERE `category` = ?");
                            $statement->execute([$category["id"]]);
                            $threads = number_format(intval($statement->rowCount()));

                            $statement = $sql->prepare("SELECT * FROM `forum_replies` WHERE `category` = ?");
                            $statement->execute([$category["id"]]);
                            $replies = number_format(intval($statement->rowCount()));

                            // Now output
                    ?>
                    <a class="row inherit-color py-3 forum-row responsive-forum-text" href="/forums/category?id=<?= $category["id"] ?>">
                        <div class="col align-self-center"><span><b><?= $category["title"] ?></b></span><br><span><?= $category["description"] ?></div>
                        <div class="col-2 align-self-center text-center"><?= $threads ?></div>
                        <div class="col-2 align-self-center text-center"><?= $replies ?></div>
                        <div class="col-2 align-self-center text-center"><b>1:00 PM</b><br>TODO</div>
                    </a>
                    <?php
                        endforeach;
                    ?>
                </div>
            </div><br><br>
            <?php
                endforeach;

                close_database_connection($sql, $statement);
            ?>
		</div>

		<?php
			build_footer();
		?>
	</body>
</html>
