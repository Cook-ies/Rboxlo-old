<?php 
    require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");

    open_database_connection($sql);
    
    // fetch current category
    $statement = $sql->prepare("SELECT `id`, `hub_id`, `title` FROM `forum_categories` WHERE `id` = ?");
    $statement->execute([$_GET["id"]]);
    $category = $statement->fetch(PDO::FETCH_ASSOC);
    
    if (!$category)
    {
        include_page("/error/404.php");
    }

    // fetch current hub
    $statement = $sql->prepare("SELECT * FROM `forum_hubs` WHERE `id` = ?");
    $statement->execute([$category["hub_id"]]);
    $hub = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$hub)
    {
        include_page("/error/404.php");
    }

    // pagination
    $limit = 25;
    $page_number = 1;
    if (isset($_GET["page"]))
    {
        if ((!filter_var($_GET["page"], FILTER_VALIDATE_INT) === false) && is_int($_GET["page"])) // check is page an int
        {
            $page_number = intval($_GET["page"]);
        }
    }

    $start_from = ($page_number - 1) * $limit;
    $ip = get_user_ip();
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
                <div class="mr-auto">
                    <a class="btn btn-md rboxlo-color-2 waves-effect waves-light d-inline-flex align-items-center px-4 mx-0">
                        <i class="material-icons mr-2 fs-1rem">add</i> New Thread
                    </a>
                </div>
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
                        $statement = $sql->prepare("SELECT `id`, `creator_id`, `title`, `created`, `locked`, `pinned` FROM `forum_threads` WHERE `category_id` = ? LIMIT ?, ?");
                        $statement->execute([$category["id"], $start_from, $limit]);
                        $threads = $statement;

                        // FIXME: THIS IS REALLY TERRIBLE!!!!!!!!
                        //        WE EXECUTE 6 QUERIES PER THREAD, AND DISPLAY 25 THREADS MAX!!! 6*25=150 QUERIES!!
                        //        THAT'S THE BEST CASE SCENARIO!! MAXIMUM QUERIES IS 8!! 8*25=200 QUERIES!!
                        //        ADD THE OTHRE 3 QUERIES, AND YOU HAVE A TOTAL OF 153/203 SQL QUERIES PER PAGE LOAD!!!!!!!!!!!!!!! FIX THIS!!!!
                        //        SPAM F5 AND BOOM!!! SITE IS DOWN!!
                        foreach ($threads as $thread):
                            // TODO: Do we need to pre declare everything?
                            $thread["distinction"] = "unread"; // type-- of ["unread","read"]
                            $thread["type"] = "thread"; // type-- of ["thread","popular","pinned","locked"]
                            $thread["author"] = ""; // username of thread author
                            $thread["views"] = 0; // view count
                            $thread["replies"] = 0; // reply count
                            $thread["last_reply"] = [
                                "created" => date("g:i A", $thread["created"]),
                                "id" => $thread["id"],
                                "creator_id" => $thread["creator_id"],
                                "author" => ""
                            ]; // last post column
                            $thread["pages"] = ""; // output for threads stuff

                            // fetch username of the thread author
                            $statement = $sql->prepare("SELECT `username` FROM `users` WHERE `id` = ?"); // Q1
                            $statement->execute([$thread["creator_id"]]);
                            $thread["author"] = $statement->fetch(PDO::FETCH_ASSOC)["username"]; // set it as such

                            // get the last reply of the thread
                            $statement = $sql->prepare("SELECT MAX(`id`) FROM `forum_replies` WHERE `thread_id` = ?"); // Q2
                            $statement->execute([$thread["id"]]);
                            $id_column = $statement->fetchColumn();
                            if ($id_column != null) // there is a reply!
                            {
                                // set the id into the array
                                $thread["last_reply"]["id"] = intval($id_column);
                                
                                // fetch the creators id of the reply, and when it was created
                                $statement = $sql->prepare("SELECT `created`, `creator_id` FROM `forum_replies` WHERE `id` = ?"); // Q2.1
                                $statement->execute([$thread["last_reply"]["id"]]);
                                $thread["last_reply"] = array_merge($thread["last_reply"], $statement->fetch(PDO::FETCH_ASSOC)); // TODO: Do we need to merge this? Can we ask DB for id, and just use the entire returned result?

                                // fetch the creators username
                                $statement = $sql->prepare("SELECT `username` FROM `users` WHERE `id` = ?"); // Q2.2
                                $statement->execute([$thread["last_reply"]["creator_id"]]);
                                
                                $thread["last_reply"]["author"] = $statement->fetch(PDO::FETCH_ASSOC)["username"]; // set last reply author
                                $thread["last_reply"]["created"] = date("g:i A", $thread["last_reply"]["created"]); // set last reply creation
                            }
                            else // there is not a reply, so substitute last post column with the post itself
                            {
                                $thread["last_reply"]["author"] = $thread["author"]; // we fetched the username earlieer, so set the username as the thread author
                            }

                            // fetch view count (unique IP only.)
                            $statement = $sql->prepare("SELECT COUNT(DISTINCT `ip`) FROM `forum_views` WHERE `thread_id` = ?"); // Q3
                            $statement->execute([$thread["id"]]);
                            $thread["views"] = number_format(intval($statement->fetchColumn())); // format

                            // fetch reply count
                            $statement = $sql->prepare("SELECT COUNT(1) FROM `forum_replies` WHERE `thread_id` = ?"); // Q4

                            $thread["replies"] = intval($statement->fetchColumn()); // declare it into thread column but DONT format it into a string yet
                            if ($thread["replies"] > 25 && !$thread["locked"] && !$thread["pinned"]) // see? we need to do math to see if its popular
                            {
                                $thread["type"] = "popular"; // if it is popular mark it
                            }
                            $thread["replies"] = number_format($thread["replies"]); // NOW format it

                            // ok, but did we even read this? get last reply that we personally have read
                            $statement = $sql->prepare("SELECT `last_reply` FROM `forum_views` WHERE `thread_id` = ? AND `ip` = ?"); // Q5
                            $statement->execute([$thread["id"], $ip]);

                            if ($statement->rowCount() > 0) // if a view entry exists
                            {
                                if (($thread["last_reply"]["id"] - intval($statement->fetch(PDO::FETCH_ASSOC)["last_reply"])) < 0) // if the id we last read is higehr than the current last reply, then we have read it
                                {
                                    $thread["distinction"] = "read";
                                }
                            }

                            // we need to put a small pagination. this is like [1, 2, 3, .. last two pages]
                            // the max pages that can be fully displayed is [1, 2, 3, 4, 5]
                            // so for example if there is 81 pages, we do [1, 2, 3, ... 80, 81]
                            $statement = $sql->prepare("SELECT COUNT(1) FROM `forum_replies` WHERE `thread_id` = ?"); // Q6
                            $statement->execute([$thread["id"]]);
                            
                            $replies = intval($statement->fetchColumn()) + 1; // add 1 because technically thread itself counts as an entry
                            $pages = ceil($replies / 10); // 10 replies displayed per forum post

                            if ($pages > 1)
                            {
                                if ($pages <= 5)
                                {
                                    for ($i = 0; $i < $pages; $i++)
                                    {
                                        $thread["pages"] .= ($i . " ");
                                    }
                                }
                                else
                                {
                                    for ($i = 0; $i < $pages; $i++)
                                    {
                                        if ($i > 5)
                                        {
                                            $thread["pages"] .= "... "; // ... to collapse other pages,
                                            break; // and break to stop reading pages
                                        }
                                        $thread["pages"] .= ($i . " ");//space is delimeter
                                    }

                                    if ($i > 5)
                                    {
                                        // populate last two pages
                                        $thread["pages"] .= (($pages - 1) . " ");
                                        $thread["pages"] .= $pages . " ";
                                    }
                                }

                                // replace the pages with links for HTML out
                                if (!empty($thread["pages"]))
                                {
                                    // this is an icky parser made to get rid of spread out ickiness
                                    $thread["pages"] .= ","; // for the parser
                                    $thread["pages"] = preg_replace("/(\d+) /i", "<a href=\"/forums/thread?id=". $thread["id"] . "&page=${1}\">${1}</a>, ", $thread["pages"]);
                                    // add commas
                                    $thread["pages"] = str_replace(">", ">, ", $thread["pages"]);
                                }
                            }

                            // spit it out
                    ?>
                    <a class="row inherit-color py-2 forum-row" href="/forums/thread?id=<?= $thread["id"] ?>">
                        <div class="col-6 align-self-center d-inline-flex align-items-center">
                            <img class="mr-2" src="/html/img/forums/<?= $thread["type"] ?>/<?= $thread["distinction"] ?>.png" width="35">
                            <span><?= safe_out($thread["title"]) ?></span>
                            <?php if (!empty($thread["pages"])): ?> <div><?= $thread["pages"] ?></div> <?php endif; ?>
                        </div>
                        <div class="col align-self-center text-center"><?= safe_out($thread["author"]) ?></div>
                        <div class="col align-self-center text-center"><?= $thread["replies"] ?></div>
                        <div class="col align-self-center text-center"><?= $thread["views"] ?></div>
                        <div class="col align-self-center text-center">
                            <b><?= $thread["last_reply"]["created"] ?></b><br>
                            <?= safe_out($thread["last_reply"]["author"]) ?>
                        </div>
                    </a>
                    <?php
                        endforeach;
                    ?>
                </div>
            </div>

            <div>
                <?php
                    $statement = $sql->prepare("SELECT COUNT(1) FROM `forum_threads` WHERE `category_id` = ?");
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