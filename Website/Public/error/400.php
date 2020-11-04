<?php 
	require_once($_SERVER["DOCUMENT_ROOT"] . "/../Application/Includes.php");
	http_response_code(400);
?>

<!DOCTYPE HTML>

<html>
	<head>
		<?php
			build_header("400");
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
					<div class="card-header rboxlo-color-2 white-text">
                        Error
                    </div>

                    <div class="card-body mx-4">
                        <div class="text-center">
                            <h1>400</h1>
                            Bad Request - we could not process the request you sent. Please try again later.
                        </div>
                    </div>

					<div class="modal-footer mx-5 pt-3 mb-1">
						<button class="btn purple-gradient accent-1 btn-block btn-rounded z-depth-1a waves-effect waves-light" onclick="window.history.back()">Go back</button>
					</div>
				</div>
            </div>
        </div>

		<?php
			build_footer();
		?>
	</body>
</html>