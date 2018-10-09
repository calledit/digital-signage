<div class="modal" id="video_modal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
				<video width="700" id="modal_video_dom" controls>
				</video>
	</div>
</div>
				<div class="row">
					<div class="col-md-8">
						<h1>File Upload</h1>
						<form action="?actionold=post-unsupported" method="post" enctype="multipart/form-data">
							<input type="file" name="upload_file" />
							<div>
								<input id="uploadButton" type="button" value="Start Upload" onclick="upload()" />
								<input type="button" value="Cancel" onclick="abort()" />
							</div>
						</form>
						<div id="bigUploadProgressBarContainer">
							<div id="bigUploadProgressBarFilled"></div>
						</div>
						<div id="bigUploadTimeRemaining"></div>
						<div id="bigUploadResponse"></div>
					</div>
				</div>
				
<?php if($USER->admin || true): ?>
				<div class="row">
					<div class="col-md-8">
						<h3>Videos for approval</h3>
						<table class="table table-bordered">
							<tr>
								<th>VideoFile</th>
								<th></th>
							</tr>
				<?php foreach($Files_for_approval as $VideoFile): ?>
							<form action="?page=videos" method="post" class="form-inline">
								<tr>
									<td><a onclick="$('#modal_video_dom')[0].src = this.href;$('#video_modal').modal('toggle');$('#modal_video_dom')[0].play();return false" href="/link_videos/files_for_approval/<?=$VideoFile?>"><?= $VideoFile ?></a></td>
									<td>
										<button class="btn btn-default" type="submit" name="approve_video" value="<?= $VideoFile ?>">Approve</button>
									</td>
								</tr>
							</form>
				<?php endforeach; ?>
						</table>
					</div>
				</div>
<?php endif; ?>
				<div class="row">
					<div class="col-md-4">
						<h3>AvalibleVideos</h3>
						<table class="table table-bordered">
							<tr>
								<th>VideoFile</th>
								<th></th>
							</tr>
				<?php foreach($AvalibleVideos as $VideoFile): ?>
							<form action="?page=videos" method="post" class="form-inline">
								<tr>
									<td><a onclick="$('#modal_video_dom')[0].src = this.href;$('#video_modal').modal('toggle');$('#modal_video_dom')[0].play();return false" href="/link_videos/videos/<?=$VideoFile?>"><?= $VideoFile ?></a></td>
									<td>
<?php if($USER->admin): ?>
<button class="btn btn-default" type="submit" name="delete_video" value="<?= $VideoFile ?>">Delete</button>
<?php endif; ?>
</td>
								</tr>
							</form>
				<?php endforeach; ?>
						</table>
					</div>
					<div class="col-md-4">
						<h3>UploadedVideos</h3>
						<table class="table table-bordered">
							<tr>
								<th>VideoFile</th>
								<th>Percent</th>
							</tr>
				<?php foreach($UploadedVideos as $VideoFile): ?>
							<tr>
								<td><?= $VideoFile['name'] ?></td>
								<td><?= $VideoFile['lastLine'] ?></td>
							</tr>
				<?php endforeach; ?>
						</table>
					</div>
					<div class="col-md-4">
						<h3>ConvertingVideos</h3>
						<table class="table table-bordered">
							<tr>
								<th>VideoFile</th>
							</tr>
				<?php foreach($ConvertingVideos as $VideoFile): ?>
							<tr>
								<td><?= $VideoFile ?></td>
							</tr>
				<?php endforeach; ?>
						</table>
					</div>
				</div>
<script>
			var FileInputElement = document.querySelector('input[type=file]')
			function OnUploadStatusInfo(status, percent, textinfo){
				console.log('New upload status:', status, 'Percent uploaded:', percent, 'TextInfo:', textinfo);
				if(status == 'starting'){
					document.querySelector('#bigUploadResponse').textContent = 'Uploading...';
					document.querySelector('#uploadButton').value = 'Pause';
					document.querySelector('#bigUploadProgressBarFilled').style.backgroundColor = 'green';
				}else if(status == 'pause'){
					document.querySelector('#uploadButton').value = 'Resume';
				}else if(status == 'resume'){
					document.querySelector('#uploadButton').value = 'Pause';
					
				}else if(status == 'progress'){
					document.querySelector('#bigUploadProgressBarFilled').style.width = percent + '%'
					document.querySelector('#bigUploadProgressBarFilled').textContent = percent + '%'
				}else if(status == 'timeleft'){
					document.querySelector('#bigUploadTimeRemaining').textContent = textinfo;
				}else if(status == 'server_response'){
					document.querySelector('#bigUploadResponse').textContent = textinfo;
					document.querySelector('#bigUploadTimeRemaining').textContent = '';
				}else if(status == 'error'){
					document.querySelector('#bigUploadResponse').textContent = textinfo;
					document.querySelector('#bigUploadTimeRemaining').textContent = '';
					document.querySelector('#bigUploadProgressBarFilled').style.backgroundColor = 'red';
				}else if(status == 'done'){
					document.querySelector('#uploadButton').value = 'Start Upload';
					document.querySelector('#bigUploadResponse').textContent = 'File uploaded successfully.';
				}else if(status == 'cancel' || status == 'canceling'){
					document.querySelector('#uploadButton').value = 'Start Upload';
					document.querySelector('#bigUploadTimeRemaining').textContent = '';
					document.querySelector('#bigUploadProgressBarFilled').textContent = '';
					document.querySelector('#bigUploadProgressBarFilled').style.width = '0%'
				}
			}
			
			bigUpload = new bigUpload(FileInputElement, OnUploadStatusInfo);
			function upload() {
				bigUpload.fire();
			}
			function abort() {
				bigUpload.abortFileUpload();
			}
</script>
