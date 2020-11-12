<?php 
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");

    open_database_connection($sql);
    
    $statement = $sql->prepare("SELECT `id`, `hub`, `title` FROM `forum_categories` WHERE `id` = ?");
    $statement->execute([$_GET["id"]]);
    $category = $statement->fetch(PDO::FETCH_ASSOC);
    
    if (!$category)
    {
        include_page("/error/404.php");
    }

    $statement = $sql->prepare("SELECT * FROM `forum_hubs` WHERE `id` = ?");
    $statement->execute([$category["hub"]]);
    $hub = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$hub)
    {
        include_page("/error/404.php");
    }

    $limit = 25;
    $page_number = 1;
    if (isset($_GET["page"]))
    {
        if ((!filter_var($_GET["page"], FILTER_VALIDATE_INT) === false) && is_int($_GET["page"]))
        {
            $page_number = intval($_GET["page"]);
        }
    }

    $start_from = ($page_number - 1) * $limit;
?>

<!DOCTYPE HTML>

<html>
	<head>
		<?php
			build_header($category["title"]);
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
                        <a class="breadcrumb-item white-text" href="/forums/hub?id=<?= $hub["id"] ?>"><?= $hub["name"] ?></a>
                        <a class="breadcrumb-item white-text" href="#"><?= $category["title"] ?></a>
                    </ol>
                </nav>
            </div>

            <div class="mb-2 d-flex align-items-center">
                <?php if (isset($_SESSION["user"])): ?>
                    <!-- new thread button -->
                <?php endif; ?>

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
                        <div class="white-text col-6">Subject</div>
                        <div class="white-text col head-separator text-center">Author</div>
                        <div class="white-text col head-separator text-center">Replies</div>
                        <div class="white-text col head-separator text-center">Views</div>
                        <div class="white-text col head-separator text-center">Last Post</div>
                    </div>
                </div>

                <div class="card-body px-3 py-0">
                    <?php
                        $statement = $sql->prepare("SELECT * FROM `forum_threads` WHERE `category` = ? LIMIT ?, ?");
                        $statement->execute([$category["id"], $start_from, $limit]);

                        foreach ($statement as $result):
                            // out thread
                    ?>

                    <?php
                        endforeach;
                    ?>
                </div>
            </div>

            <div>
                <?php
                    $statement = $sql->prepare("SELECT COUNT(1) FROM `forum_threads` WHERE `category` = ?");
                    $statement->execute([$category["id"]]);
                    
                    $total_records = intval($statement->fetchColumn());
                    $total_pages = ceil($total_records / $limit);
                    
                    for ($i = 1; $i < $total_pages; $i++)
                    {
                        if ($i == $page_number)
                        {
                            // out page link :: current highlighted
                        }
                        else
                        {
                            // out page link
                        }
                    }

                    close_database_connection($sql, $statement);
                ?>
            </div>
		</div>

		<?php
			build_footer();
		?>
	</body>
</html>