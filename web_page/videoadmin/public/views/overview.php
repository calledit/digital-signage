
			<div class="row">
				<div class="col-md-8">
						<h3>Players <small>A list of all your media players</small></h3>
					<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<tr>
							<th>id</th>
							<th>name</th>
							<th>HDMI status</th>
							<th>size</th>
							<th>playlist</th>
							<th>last checkin</th>
							<th>last ip</th>
							<th>status</th>
							<th>5 min</th>
							<th>realtime</th>
						</tr>
			<?php foreach($visible_players as $playerInfo):
					$Online = (strtotime($playerInfo->lastcheckin)>time()-(60*7));
?>
						<tr>
							<td><?= $playerInfo->_id ?></td>
							<td><?= $playerInfo->name ?></td>
							<td><?= ($playerInfo->screen_active == 1 && $Online)?'On':'Off' ?></td>
							<td><?= toScrenSize($playerInfo) ?></td>
							<td><?= isset($visible_playergroups[$playerInfo->mainplayergroup])?$visible_playergroups[$playerInfo->mainplayergroup]->name:'[private Playergroup]' ?></td>
							<td><?= $playerInfo->lastcheckin ?></td>
							<td><?= $playerInfo->lastip ?></td>
							<td style="background-color:<?= ($Online)?'#7abc65':'#f4737d' ?>;"><?= ($Online)?'Online':'Offline' ?></td>
							<td><?php
						if(file_exists("/opt/screenshots/old/".$playerInfo->_id.'.png') && $Online){
							?><img width="160" src="link_videos/screenshots/old/<?= $playerInfo->_id ?>.png"><?php
						}
							?></td>
							<td><?php
						if(file_exists("/opt/screenshots/".$playerInfo->_id.'.png') && $Online){
							?><img width="160" src="link_videos/screenshots/<?= $playerInfo->_id ?>.png"><?php
						}
							?></td>
						</tr>
			<?php endforeach; ?>
					</table>
					</div>
				</div>
				<div class="col-md-4">
					<div class="row">
						<div class="col-md-12">
							<div class="col-md-12">
								<h3>Playlists</h3>
							</div>
							<form action="?admin"method="post" class="form-inline">
								<div class="form-group">
									<label for="addgroup">Group Name</label>
									<input class="form-control" type="text" id="addgroup" name="addgroup" placeholder="Add group">
								</div>
								<button type="submit" class="btn btn-default">Add</button>
							</form>
						</div>
					</div>
					<table class="table table-bordered">
						<tr>
							<th>id</th>
							<th>name</th>
							<th>shedule</th>
						</tr>
			<?php foreach($visible_playergroups as $playergroupInfo):
					$dates = array_keys($Videos[$playergroupInfo->_id]);
					foreach($dates AS $dtk => $dt){
						$dates[$dtk] = date("Y-m-d", $dt);
					}
?>
						<tr>
							<td><?= $playergroupInfo->_id ?></td>
							<td><?= $playergroupInfo->name ?></td>
							<td><?= implode(',', $dates) ?></td>
						</tr>
			<?php endforeach; ?>
					</table>
				</div>
			</div>
