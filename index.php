<?php
	require_once ($_SERVER['DOCUMENT_ROOT']. '/woa-Loader.php');
	include_once ('inc/header.php');
?>

	<div class="container mt-5">
		<div class="row justify-content-center mb-5">
			<div class="col-md-4">
				<input type="text" class="form-control" id="videoID" placeholder="YouTube Video ID">
			</div>
			<div class="col-md-4">
				<button type="button" class="btn btn-primary btn-block" id="btn-ajax"><i class="fas fa-angle-double-right fa-fw"></i> İndirme Listesini Göster</button>
			</div>
		</div>
		<div class="row justify-content-center">
			<div class="col-md-8">
				<table class="table table-bordered table-hover" id="downloadList">
					<thead>
						<tr>
							<th>#</th>
							<td>iTag</td>
							<td>Bitrate</td>
							<td>Kalite</td>
							<td>Video/Audio</td>
							<td>Link</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th>-</th>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
							<td>-</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

<?php include_once ('inc/footer.php'); ?>