	<!DOCTYPE html>
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/drawingboard.css">
		<link rel="stylesheet" href="css/drawingboard.min.css">
		<script src="js/jquery-3.5.1.min.js"></script>
		<script src="js/drawingboard.js"></script>
		<script src="js/drawingboard.min.js"></script>
	</head>
	<body>


		<style>

		</style>


		
		<div class="grid-container">

			<div class="grid-item">
			<form class="drawing-form" action="index.php?save=true" method="post">

			<!-- this will be the drawingboard container -->
			<div id="board">
			</div>
			<!-- this will be the input used to pass the drawingboard content to the server -->
			<input type="hidden" name="image" value="">
			<button>Kép mentése</button>

			</form>
				<form action="index.php?upload=true" method="post" enctype="multipart/form-data" id="upload">
					<input type="file" name="fileToUpload" id="fileToUpload">
					<input type="submit" value="Kép feltöltése" name="submit">
				</form>
				<form action='index.php?predict=true' method="POST" id="predictform">
					
					<button type="submit" form="predictform" value="Karakter" class="predictbutton">Karakter észlelése</button>
				</form>
				
			</div>

			<div class="grid-item3">
				<img src= 
				<?php 
				ini_set('display_errors', 0);
				$arg = "images/image.png";
				echo $arg ; ?> 
				alt=karakter width="64" height="64">
				<table cellspacing="0" cellpadding="0">
					<tr>
						<tr>
							<th class="wide">Sorszám</th>
							<th class="wide">Karakter</th>
							<th class="wide">Valószínűsége:</th>
						</tr>

						<?php
						if (isset($_GET['predict'])) {
							predict();
						}
						function predict(){
							$arg = "images/image.png";
							$pyscript = 'predict.py';
							$python = 'C:\Users\Robi\AppData\Local\Programs\Python\Python38\python.exe';
							try{
								exec("$python $pyscript $arg",$output);
								$k = 1;
								for($i = 18;$i >= 0;$i-=2)
								{
									echo '<tr class="tall" id="grad' . $k . '">';
									echo "<td class='spacious'>" . $k .".</td>
									<td class='spacious'> <a href='https://jisho.org/search/" . toUTF8($output[$i]) . "'>" . toUTF8($output[$i]) ."</td> 
									<td class='spacious'>" . $output[$i+1]*100 . " % </td>";
									echo "</tr>";
									$k++;
								}
							}catch(Eception $e){
								for($i = 18;$i >= 0;$i-=2)
								{
									echo '<tr class="tall"  id="grad' . $k . '">';
									echo "<td class='spacious'>" . $k .".</td> <td class='spacious'> - </td> <td class='spacious'> - % </td>";
									echo "</tr>";
									$k++;
								}
							}
						}
						if (isset($_GET['upload'])) 
						{
							upload();
						}
						if (isset($_GET['save'])) 
						{
							save();
						}
						function upload()
						{
							$target_dir = "images/";
							$target_file = $target_dir . "image.png";
							$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
							if (!(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file))) 
							{
								echo "Fájl feltöltése sikertelen.";
							}
						}
						function save()
						{
							$img = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL);

							$img = str_replace(' ', '+', str_replace('data:image/png;base64,', '', $img));
							$data = base64_decode($img);

							file_put_contents(__DIR__.'/images/'. 'image' .'.png', $data);
						}
						?>
					</table>


					<?php
					function toUTF8($data,$from_encode = 'Shift-JIS')
					{
						return mb_convert_encoding($data, "UTF-8", "$from_encode, Shift-JIS, JIS, SJIS, JIS-ms, eucJP-win, SJIS-win, ISO-2022-JP,
							ISO-2022-JP-MS, SJIS-mac, SJIS-Mobile#DOCOMO, SJIS-Mobile#KDDI,
							SJIS-Mobile#SOFTBANK, UTF-8-Mobile#DOCOMO, UTF-8-Mobile#KDDI-A,
							UTF-8-Mobile#KDDI-B, UTF-8-Mobile#SOFTBANK, ISO-2022-JP-MOBILE#KDDI");
					}
					?>

<script>
// The MIT License (MIT)
// Copyright (c) 2015 Emmanuel "@Leimina" Pelletier

// Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

var myBoard = new DrawingBoard.Board('board', {
	background: "#000000",
	color: "#ffffff",
	size: 7,
	controls: [
	{ DrawingMode: { filler: false } },
	'Navigation'
	],
});

$('.drawing-form').on('submit', function(e) {
		   //get drawingboard content
		   var img = myBoard.getImg();

		  //we keep drawingboard content only if it's not the 'blank canvas'
		  var imgInput = (myBoard.blankCanvas == img) ? '' : img;
		  
		  //put the drawingboard content in the form field to send it to the server
		  $(this).find('input[name=image]').val( imgInput );

		  //we can also assume that everything goes well server-side
		  //and directly clear webstorage here so that the drawing isn't shown again after form submission
		  //but the best would be to do when the server answers that everything went well
		  myBoard.clearWebStorage();
});
</script>
</body>
</html>
