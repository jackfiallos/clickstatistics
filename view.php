<?php
include('includes/plugconfig.inc.php');
include('includes/base.inc.php');

error_reporting(E_ALL ^ E_NOTICE);
if ((!isset($_REQUEST['eventype'])) && (empty($_REQUEST['eventype'])))
	$_REQUEST['eventype'] = 0;

$resolutions = array(
	'a'=>'1024x768',
	'b'=>'1280x940',
	'c'=>'1349x609',
	'd'=>'1280x1024',
	'e'=>'1400x1050',
);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>ClickStatistics por Qbit Mexhico</title>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	<link href="styles/my_layout.css" rel="stylesheet" type="text/css" />
	<link href="styles/styles.css" rel="stylesheet" type="text/css" />
	<!--[if lte IE 7]>
	<link href="styles/patch_my_layout.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<script type="text/javascript" src="js/mootools-1.2-core.js"></script>
	<script type="text/javascript" src="js/mootools-1.2-more.js"></script>
	<script type="text/javascript">
		window.addEvent('domready', function() {
			var el = $('myElement');
			var img = $('imgdiv');
			new Slider(el, el.getElement('.knob'), {
				range: [20, 80],
				steps: 100,
				wheel: 1,
				onChange: function(step){
					img.setStyle('opacity', step);
					img.set('opacity', step/100);
				}
			}).set(70);
		});
	</script>
</head>
<body>
	<?php
		/*echo "<pre>";
		print_r($config);
		echo "</pre>";*/
	?>
	<div id="page_margins">
		<div id="page">
			<div id="header">
				<form name="frmsetparam" id="frmsetparam" method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
					<table style="border:none; width:100%;">
						<tr>
							<td style="width:200px">
								Transparencia de la imagen
							</td>
							<td>
								<div class="slider" id="myElement">
									<div class="knob" style="position: relative; left: 100px;"></div>
								</div>
							</td>
							<td style="width:200px">
								P&aacute;gina de Resultado
							</td>
							<td>
								<select name="section" id="section" style="width: 200px;" onchange="this.form.submit();">
									<?php
										if (!$config['db']['usedb']) {
											foreach ($config['sections'][0]['Pages'] as $keySet => $valueSet) {
												$selected = ($keySet == $_REQUEST['section']) ? "selected='true'" : "";
												echo "<option ".$selected." value=\"".$keySet."\">".$valueSet."</option>\n";
											}
										}
										else {
											foreach (getResultPages() as $keySet => $valueSet) {
												$selected = ($keySet == $_REQUEST['section']) ? "selected='true'" : "";
												echo "<option ".$selected." value=\"".($keySet+1)."\">".$valueSet."</option>\n";
											}
										}
									?>
								</select>
							</td>
							<td style="width:200px">
								Resolucion de Salida
							</td>
							<td>
								<select name="screenres" id="screenres" style="width: 200px;" onchange="this.form.submit();">
									<?php
									foreach ($resolutions as $k=>$v){
										$sel = ($k == $_REQUEST['screenres']) ? "selected='true'" : "";
										echo "<option value='".$k."' ".$sel.">".$v."</option>";
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td style="width:200px">
								Modo de Captura
							</td>
							<td>
								<select name="eventype" id="eventype" style="width: 200px;" onchange="this.form.submit();">
									<?php
										foreach ($config['events'] as $keyType => $valueType) {
											$selected = ($keyType == $_REQUEST['eventype']) ? "selected='true'" : "";
											echo "<option ".$selected." value=\"".$keyType."\">".$valueType."</option>\n";
										}
									?>
								</select>
							</td>
							<td style="width:200px">
								Registro de Archivos
							</td>
							<td>
								<select name="filestat" id="filestat" style="width: 200px;" onchange="this.form.submit();">
									<?php
										if (!$config['db']['usedb']) {
											$result = array();
											$archivos = array_diff(scandir("logs//"), Array(".", ".."));
											foreach($archivos as $claveFile => $valorFile) {
												if (substr($valorFile, strlen($valorFile)-3, strlen($valorFile)) == 'log')
												{
													if (($_REQUEST['eventype'] == 0) && (strstr($valorFile, 'click'))) //Dato del evento click
													{
														$cstring = strstr($valorFile, 'click');
														array_push($result, $valorFile);
													}
													elseif (($_REQUEST['eventype'] == 1) && (strstr($valorFile, 'move'))) //Dato del evento move
													{
														$cstring = strstr($valorFile, 'move');
														array_push($result, $valorFile);
													}
												}
											}
											
											foreach ($result as $keyDay => $valueDay) {
												$selected = ($keyDay == $_REQUEST['filestat']) ? "selected='true'" : "";
												echo "<option ".$selected." value=\"".$keyDay."\">".$valueDay."</option>\n";
											}
										}
										else {
											foreach (getResultDates($_REQUEST['eventype']) as $keyDay => $valueDay) {
												$selected = ($keyDay == $_REQUEST['filestat']) ? "selected='true'" : "";
												echo "<option ".$selected." value=\"".$keyDay."\">".$valueDay."</option>\n";
											}
										}
									?>
								</select>
							</td>
							<td colspan="2"></td>
						</tr>
					</table>
				</form>
			</div>
			<div id="main">
				<?php
					switch($_REQUEST['screenres']){
						case 'a':
							$x = 1024;
							$y = 768;
							break;
						case 'b':
							$x = 1280;
							$y = 940;
							break;
						case 'c':
							$x = 1349;
							$y = 609;
							break;
						case 'd':
							$x = 1280;
							$y = 1024;
							break;
						case 'e':
							$x = 1400;
							$y = 1050;
							break;
						default:
							$x = 1024;
							$y = 768;
							break;
					}
				?>
				<div id="overlayer" style="width:<?php echo $x; ?>px;height:<?php echo $y; ?>px">
					<div id="imgdiv">
						<img src="image.php?page=<?php echo $_REQUEST['section']; ?>&type=<?php echo $_REQUEST['eventype']; ?>&file=<?php echo $_REQUEST['filestat']; ?>&w=<?php echo $x; ?>&h=<?php echo $y; ?>" style="position:absolute;top:0;left:0;"/>
					</div>
					<?php
						if (!isset($_REQUEST['section']))
							$_REQUEST['section'] = 1;
					
						if (array_key_exists($_REQUEST['section'],$config['sections'][0]['Pages']))
							$page = $config['sections'][0]['Pages'][$_REQUEST['section']];
					?>
					<iframe frameborder="0" scrolling="no" id="website" src="<?php echo $config['config']['uri'].$page; ?>?disable"></iframe>
				</div>
			</div>
		</div>
	</div>
</body>
</html>