

			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-12">
							<div class="col-md-12">
								<h3>Media Players</h3>
							</div>
						</div>
					</div>
					<table class="table table-bordered table-striped">
						<tr>
							<th>id</th>
							<th>name</th>
							<th>HDMI status</th>
							<th>size</th>
							<th>playlist</th>
							<th>lastcheckin</th>
							<th>status</th>
<?php if($USER->admin): ?>
							<th>owner</th>
<?php endif; ?>
						</tr>
			<?php foreach($visible_players as $playerInfo):
						$Online = (strtotime($playerInfo->lastcheckin)>time()-(60*7));
?>
						<tr>
							<td><?= $playerInfo->_id ?></td>
							<td>
								<form action="?page=players" method="post" class="form-inline">
									<div class="input-group">
										<input type="hidden" name="alterplayername" value="<?= $playerInfo->_id ?>">
										<input class="form-control" type="text" name="playername" value="<?= $playerInfo->name ?>">
										<span class="input-group-btn">
											<button class="btn btn-default" type="submit">Save</button>
										</span>
									</div>
								</form>
							</td>
							<td><?= ($playerInfo->screen_active == 1)?'On':'Off' ?></td>
							<td><?= toScrenSize($playerInfo) ?></td>
							<td>
								<form action="?page=players" method="post" class="form-inline">
									<input type="hidden" name="selectplayermaingroup" value="<?= $playerInfo->_id ?>">
									<div class="input-group">
										<select name="maingroup" autocomplete="off" class="form-control" onchange="this.form.submit();">
				<?php foreach($visible_playergroups as $playergroupInfo): ?>
											<option value="<?= $playergroupInfo->_id ?>" <?= ($playerInfo->mainplayergroup == $playergroupInfo->_id)?'selected':'' ?>><?= $playergroupInfo->name ?></option>
				<?php endforeach; ?>
										</select>
<!---
										<span class="input-group-btn">
											<button type="submit" class="btn btn-default">Save</button>
										</span>
-->
									</div>
								</form>
							</td>
							<td><?= $playerInfo->lastcheckin ?></td>
							<td style="background-color:<?= ($Online)?'#7abc65':'#f4737d' ?>;"><?= ($Online)?'Online':'Offline' ?></td>
<?php if($USER->admin): ?>
							<td>
								<form action="?page=players" method="post" class="form-inline">
									<input type="hidden" name="selectplayerowner" value="<?= $playerInfo->_id ?>">
									<div class="input-group">
										<select name="owner" autocomplete="off" class="form-control" onchange="this.form.submit();">
				<?php foreach($users as $user): ?>
											<option value="<?= $user->_id ?>" <?= ($playerInfo->user == $user->_id)?'selected':'' ?>><?= $user->name ?></option>
				<?php endforeach; ?>
										</select>
									</div>
								</form>
							</td>
<?php endif; ?>
						</tr>
			<?php endforeach; ?>
					</table>
				</div>
