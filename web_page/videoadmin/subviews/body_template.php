		<div class="container-fluid">
			<div class="page-header">
				<h1>Media Player Admin</h1>
			</div>
			<div class="row">
				<ul class="nav nav-tabs">
					<li role="presentation" class="<?= ($PAGE == 'overview')?'active':'' ?>"><a href="?page=overview">Overview</a></li>
					<li role="presentation" class="<?= ($PAGE == 'players')?'active':'' ?>"><a href="?page=players">Players</a></li>
					<li role="presentation" class="<?= ($PAGE == 'playlists')?'active':'' ?>"><a href="?page=playlists">Playlists</a></li>
					<li role="presentation" class="<?= ($PAGE == 'videos')?'active':'' ?>"><a href="?page=videos">Videos</a></li>
					<li role="presentation" class=""><a href="?logout_now">Logout</a></li>
				</ul>
			</div>
<?php
			include('views/'.$PAGE.'.php');

?>
		<script src="/js/jquery.min.js"></script>
		<script src="/js/jquery-ui.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script>
			$(".datepicker").datepicker({ dateFormat: "yy-mm-dd" });
			$('#video_modal').on('hide.bs.modal', function(){
				$('#modal_video_dom')[0].pause()
			});
		</script>
