<html>
<head>
    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
	<title>Simple crawler project</title>
	<meta name="robots" content="noindex, nofollow"/>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<style>
		.top-bar{
			background-color: #a7a7a7;
		}
		.logo{
			font-size: 27px;
			font-weight: bold;
			margin-top: 15px;
			margin-bottom: 10px;
			margin-left: 25px;
		}
		.content{
			background-color: #d3d3d3;
		}
		.form-group{
			text-align: center;
		}
        #panels{
            display: none;
        }
        #loading_gif{
            display: none;
            margin-top: 10px;
        }
        .panel_body{
            display: none;
        }
	</style>
</head>
<body>
	<div class="top-bar">
		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4">
					<p class="logo">Simple Crawler Project</p>
				</div>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="row">
			<div class="col-lg-6 col-md-4 col-md-offset-4 col-lg-offset-3 content">
				<h1 style="text-align: center">Crawler</h1>
<!--				<form action="/crawl.php" method="post">-->
					<div class="form-group">
						<label for="url">URL:</label>
						<input type="text" id="url" name="url" class="form-control">
					</div>
					<div class="form-group">
						<label for="deep">Deep - how many subpages to check:</label>
						<input type="number" min="1" name="deep" id="deep">
					</div>
					<div class="form-group">
							<input type="submit" value="Crawl the url" id="executeCrawl">
					</div>
<!--				</form>-->
			</div>

		</div>
        <div class="row" class="loading">
            <div class="col-lg-12 col-md-2 col-md-offset-4 col-lg-offset-5">
                <img src="loading.gif" alt="loading" width="200" id="loading_gif">
            </div>
        </div>
        <div class="row">
            <div id="panels">

            </div>
        </div>
	</div>
    <script>
        function showPanel(id) {
            if($("#panel_body_" + id).css("display") === "none"){
                $("#panel_body_" + id).fadeIn();
            } else {
                $("#panel_body_" + id).fadeOut();
            }
        }
        $("#executeCrawl").click(function(){
            $("#panels>div").remove();
            $('#execution').remove();
            $('#loading_gif').show();
            let data = {url:$("#url").val(), deep: $("#deep").val()};
            $.ajax({
                url: 'crawl.php',
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(o){
                    $('#loading_gif').hide();
                    $('#result').show();
                    let i = 0;
                    let max = o.length - 1;
                    while(i<max){
                        $("#panels").show();
                        let rowPanel = '<div class="panel panel-default"><div class="panel-heading" id="' + i + '" onclick="showPanel(id)">' + o[i]['url'] +'</div><div class="panel-body panel_body" id="panel_body_' + i + '"><strong>Title: </strong>' + o[i]['title'] + '<hr><strong>Description: </strong>' + o[i]['desc'] + '<hr><strong>Keywords: </strong> ' + o[i]['keywords'] + ' </div></div>';
                        $("#panels").append(rowPanel);
                        i++;
                    }
                    let execution =(o[max]['executionTime']);
                    $("#panels").prepend('<p id="execution"> Script executed in: ' + execution.toFixed(2) + ' sec</p>');
                }
            });
        });
    </script>
</body>
</html>