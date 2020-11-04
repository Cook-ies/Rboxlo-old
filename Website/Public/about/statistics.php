<?php 
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");
	open_database_connection($sql);

	$ut = get_uptime();
?>

<!DOCTYPE HTML>

<html>
	<head>
		<?php
			build_header("Statistics");
		?>
	</head>
	<body class="d-flex flex-column">
		<?php
			build_js();
			build_navigation_bar();
        ?>  

        <script type="text/javascript">
			function update() 
			{
				endpoint("/statistics", "GET", null, (response) =>
				{
					$("#cpu").text(response.cpu + "%")
					$("#ram").text(response.ram + "%"),
					$("#uptime").text(response.uptime)  // TODO: Do we need this? Can we just start a countdown from the first uptime (seamless transitions?)
				})
			}
			
			setInterval(() =>
            {
				update()
            }, 1000)
			
			update()
        </script>

        <div class="jumbotron card card-image" style="background-image: url(/html/img/backdrops/about.png)">
            <div class="text-white text-center">
                <div>
					<img src="/html/img/brand/big.png" class="img-fluid" style="width: 600px">
					<br>
					<h1 class="card-title h1-responsive">Statistics</h1>
                </div>
            </div>
        </div>

		<div class="container">
			<div class="card">
                <?php
                    $statement = $GLOBALS["sql"]->prepare("SELECT COUNT(*) FROM `users`");
                    $statement->execute();
					$users = $statement->fetchColumn();
					
					close_database_connection($sql, $statement);
                ?>

				<div class="rounded-top mdb-color rboxlo-color-2 pt-3 pl-3 pb-3">
					<span class="white-text">Website statistics</span>
				</div>
				<div class="card-body mx-4">
                    <span class="h3">Numbers</span><span> as of <?= date("m/d/Y") ?></span>
                    
                    <ul class="mt-1">
                        <li>There are currently <?= $users ?> unique users registered on <?= PROJECT["NAME"] ?>.</li>
                    </ul>

                    <br>
                        
                    <span class="h3">Website performance</span>
                    <p class="mt-1">
                        Currently, the CPU load on the website is at approximately <span id="cpu">0%</span>.
                        <br>
                        Currently, the RAM usage on the website is at approximately <span id="ram">0%</span>.
                        <br><br>
						Running <b><?= PROJECT["NAME"] . "-" . get_version() ?></b>.
						<br><br>
						Up for <b><span id="uptime"><?= "$ut[0] days, $ut[1] hours, $ut[2] minutes, $ut[3] seconds" ?>.</span></b>
                    </p>
				</div>  
            </div>
        </div>

		<?php
			build_footer();
		?>
	</body>
</html>