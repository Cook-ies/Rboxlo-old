<?php 
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");
	http_response_code(404);
?>

<!DOCTYPE HTML>

<html>
	<head>
		<?php
			build_header("404");
		?>
	</head>
	<body class="d-flex flex-column">
		<?php
			build_js();
			build_navigation_bar();
		?>

        <div class="container">
			<div class="row flex-center">
                <div class="card" style="width: 40rem">
					<div class="card-header purple accent-3 white-text">
                        Error
                    </div>

                    <div class="card-body mx-4">
                        <div class="text-center">
                            <h1>404</h1>
                            Sorry, but that page could not be found, or no longer exists.
                        </div>
                    </div>

					<div class="modal-footer mx-5 pt-3 mb-1">
						<button class="btn purple-gradient accent-1 btn-block btn-rounded z-depth-1a waves-effect waves-light" onclick="window.history.back()">Go back</button>
					</div>
				</div>

				<div class="mt-5">
					<iframe src="https://www.youtube.com/embed/_bVD9y-flOA" frameborder="0" allowfullscreen="" width="600" height="300"></iframe>
				</div>
			</div>
		</div>

		<?php
			build_footer();
		?>
	</body>
</html>