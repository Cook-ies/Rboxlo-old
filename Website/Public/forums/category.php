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

                        foreach ($threads as $thread):
                            // Fetch replies for this thread
                            $statement = $sql->prepare("SELECT `id`, `creator_id`, `created` FROM `forum_replies` WHERE `thread_id` = ?");
                            $statement->execute([$thread["id"]]);
                            $replies = $statement->fetchAll(PDO::FETCH_ASSOC);

                            // Fetch views for this thread
                            $statement = $sql->prepare("SELECT `last_reply`, `ip` FROM `forum_views` WHERE `thread_id` = ?");
                            $statement->execute([$thread["id"]]);
                            $views = $statement->fetchAll(PDO::FETCH_ASSOC);

                            $thread = array_merge($thread, [
                                "replies" => [
                                    "rows" => $replies,
                                    "count" => [
                                        "raw" => count($replies),
                                        "human" => number_format(count($replies))
                                    ],
                                    "last" => null
                                ],
                                "views" => [
                                    "rows" => $views,
                                    "count" => [
                                        "raw" => count($views),
                                        "human" => number_format(count($views))
                                    ]
                                ],
                                "distinction" => "unread",
                                "type" => "thread",
                                "author" => null
                            ]);
                            
                            // Fetch the author of this thread
                            $statement = $sql->prepare("SELECT `username` FROM `users` WHERE `id` = ?");
                            $statement->execute([$thread["creator_id"]]);
                            $thread["author"] = $statement->fetch(PDO::FETCH_ASSOC)["username"];

                            // Get the last reply of this thread
                            if ($thread["replies"]["count"]["raw"] > 0)
                            {
                                // Replies exist for this thread, sort through them
                                $last = null;
                                foreach ($thread["replies"]["rows"] as $reply)
                                {
                                    if ($last == null)
                                    {
                                        $last = $reply;
                                    }
                                    else
                                    {
                                        if ($reply["created"] > $last["created"])
                                        {
                                            $last = $reply;
                                        }
                                    }
                                }

                                // Fetch the author for the last reply
                                $statement = $sql->prepare("SELECT `username` FROM `users` WHERE `id` = ?");
                                $statement->execute([$last["creator_id"]]);
                                $last["author"] = $statement->fetch(PDO::FETCH_ASSOC)["username"];
                                
                                // Format the creation date
                                $last["created"] = date("g:i A", $last["created"]);

                                // Set the last reply
                                $thread["replies"]["last"] = $last;
                            }
                            else
                            {
                                // No replies exist for this thread, use the thread itself as the last reply
                                $thread["replies"]["last"] = [
                                    "created" => date("g:i A", $thread["created"]),
                                    "id" => $thread["id"],
                                    "creator_id" => $thread["creator_id"],
                                    "author" => $thread["author"],
                                ];
                            }
                            
                            // Get our view
                            if ($thread["views"]["count"]["raw"] > 0)
                            {
                                // Views exist, are we one of them?
                                $our_views = []; // all views with our ip
                                $our_last_view = null; // our view with the highest last_reply
                                $ip = get_user_ip();

                                foreach ($thread["views"]["rows"] as $view)
                                {
                                    if ($view["ip"] == $ip)
                                    {
                                        array_push($our_views, $view);
                                    }
                                }

                                foreach ($our_views as $view)
                                {
                                    if ($our_last_view == null)
                                    {
                                        $our_last_view = $view;
                                    }
                                    else
                                    {
                                        if ($our_last_view["reply_id"] <= $view["reply_id"])
                                        {
                                            $our_last_view = $view;
                                        }
                                    }
                                }

                                if ($our_last_view["reply_id"] == $replies["last"]["id"])
                                {
                                    $thread["distinction"] = "read";
                                }
                            }

                            // Sort through the unique views to create a more realistic view count
                            $unique_views = [];
                            foreach ($thread["views"]["rows"] as $view)
                            {
                                foreach ($unique_views as $unique_view)
                                {
                                    if ($unique_view["ip"] != $view["ip"])
                                    {
                                        array_push($unique_views, $view);
                                    }
                                }
                            }

                            $thread["views"]["count"]["raw"] = count($unique_views);
                            $thread["views"]["count"]["human"] = number_format(count($unique_views));

                            // Mark the type of thread
                            if ($thread["replies"]["count"]["raw"] > 25 && !$thread["locked"])
                            {
                                $thread["type"] = "popular";
                            }
                            elseif ($thread["pinned"]) { $thread["type"] = "pinned"; }
                            elseif ($thread["locked"]) { $thread["type"] = "locked"; }

                            // Create a mini-pagination HTML output
                            // The format is the first 3 pages (always 1, 2, 3), and if there are more than 3 pages, put the last 2 pages
                            // For example, if there is 80 pages, the output will look like "1, 2, 3, ... 79, 80"
                            // There is a special implementation here though if there is 5 pages EXACTLY. That is so that it will look like "1, 2, 3, 4, 5" and not "1, 2, 3, ... 4, 5"
                            $true_reply_count = $thread["replies"]["count"]["raw"] + 1; // This is the *true* reply count in that it includes the thread itself as a reply. This is only for pagination.
                            $thread_pages = ceil($true_reply_count / 10); // 10 replies displayed per forum post
                            $thread_page_html_out = "";

                            if ($thread_pages == 5)
                            {
                                $thread_page_html_out == "1, 2, 3, 4, 5";
                            }
                            else if ($thread_pages > 1)
                            {
                                if ($thread_pages <= 3)
                                {
                                    for ($i = 0; $i < $thread_pages; $i++)
                                    {
                                        $thread_page_html_out .= ($i . " ");
                                    }
                                }
                                else
                                {
                                    for ($i = 0; $i < $thread_pages; $i++)
                                    {
                                        if ($i > 3)
                                        {
                                            $thread_page_html_out .= "... ";
                                            break;
                                        }
                                        $thread_page_html_out .= ($i . " ");
                                    }

                                    if ($i > 3)
                                    {
                                        // Populate the last two pages
                                        $thread_page_html_out .= (($thread_page_html_out - 1) . " ");
                                        $thread_page_html_out .= ($thread_pages . " ");
                                    }
                                }
                            }

                            // if you have a keen eye, you may have noticed that we are using just commas and periods; a text-visual representation of the output.
                            // That is the reason why a "parser" now begins to replace that visual output with actual HTML. This is not the best solution, but the best part is that this will result in much cleaner code.
                            if (!empty($thread_page_html_out))
                            {
                                $thread_page_html_out .= ","; // for the parser to replace everything with a link element
                                $thread_page_html_out = preg_replace("/(\d+) /i", "<a href=\"/forums/thread?id=". $thread["id"] . "&page=${1}\">${1}</a>, ", $thread_page_html_out);
                                $thread_page_html_out = str_replace(">", ">, ", $thread_page_html_out); // add the commas for stylistic purposes
                            }

                            // Now, echo it out
                    ?>
                    <a class="row inherit-color py-2 forum-row" href="/forums/thread?id=<?= $thread["id"] ?>">
                        <div class="col-6 align-self-center d-inline-flex align-items-center">
                            <img class="mr-2" src="/html/img/forums/<?= $thread["type"] ?>/<?= $thread["distinction"] ?>.png" width="35">
                            <span><?= safe_out($thread["title"]) ?></span>
                            <?php if (!empty($thread["pages"])): ?> <div><?= $thread["pages"] ?></div> <?php endif; ?>
                        </div>
                        <div class="col align-self-center text-center"><?= safe_out($thread["author"]) ?></div>
                        <div class="col align-self-center text-center"><?= $thread["replies"]["count"]["human"] ?></div>
                        <div class="col align-self-center text-center"><?= $thread["views"]["count"]["human"] ?></div>
                        <div class="col align-self-center text-center">
                            <b><?= $thread["replies"]["last"]["created"] ?></b><br>
                            <?= safe_out($thread["replies"]["last"]["author"]) ?>
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