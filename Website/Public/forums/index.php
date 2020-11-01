<?php 
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");
?>

<!DOCTYPE HTML>

<html>
	<head>
		<?php
			build_header("Forums");
		?>
	</head>
	<body class="d-flex flex-column">
		<?php
			build_navigation_bar();
		?>

        <div class="container">
            <?php
                open_database_connection($sql);

                $statement = $sql->query("SELECT * FROM `forum_hubs`");
                foreach ($statement as $result):
            ?>
            <div class="panel panel-primary">
                <div class="panel-heading"><h5><?= $result["name"] ?></h5></div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Name</th><th>Description</th><th>Threads</th><th>Replies</th>Last Post</th></thead>
                        <?php
                            $statement = $sql->prepare("SELECT * FROM `forum_categories` WHERE `hub` = ?");
                            $statement->execute([$result["id"]]);
                            $categories = $statement;

                            foreach ($categories as $category):
                                // Fetch the replies and posts
                                $statement = $sql->prepare("SELECT COUNT(*) FROM `forum_threads` WHERE `category` = ?");
                                $statement->execute([$category["id"]]);
                                $threads = intval($statement->rowCount());

                                $statement = $sql->prepare("SELECT COUNT(*) FROM `forum_replies` WHERE `category` = ?");
                                $statement->execute([$category["id"]]);
                                $replies = intval($statement->rowCount());

                                // Now output
                        ?>
                        <tr><td><a href="/forums/category?id=<?= $result["id"] ?>"><?= safe_out($result["name"]) ?></a></td><td><?= $category["description"] ?></td><td><?= $threads ?></td><td><?= $replies ?></td><td></td></tr>
                        <?php
                            endforeach;
                        ?>
                    </table>
                </div>
            </div>
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
