
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-12">
							<div class="col-md-12">
								<h3>Playlists</h3>
							</div>
							<form action="?page=playlists" method="post" class="form-inline">
								<div class="form-group">
									<label for="addgroup">Playlist Name</label>
									<input class="form-control" type="text" id="addgroup" name="addgroup" placeholder="Add playlist">
								</div>
								<button type="submit" class="btn btn-default">Add</button>
							</form>
						</div>
					</div>
					<table class="table table-bordered table-striped">
						<tr>
							<th>id</th>
							<th>name</th>
							<th>public</th>
							<th>startdate</th>
							<th>videos</th>
							<th></th>
						</tr>
			<?php foreach($visible_playergroups as $playergroupInfo):
					$groupdates = array_keys($Videos[$playergroupInfo->_id]);
			//array(strtotime("2015-03-24"), strtotime("2015-05-24"));
					if(count($groupdates) == 0){
						$groupdates = array(NULL);
					}
					foreach($groupdates AS $dateid => $grdate):
						
			?>
						<tr>
							<form action="?page=playlists" method="post" class="form-inline">
<?php					if($dateid == 0): ?>
								<td rowspan="<?= count($groupdates) ?>"><?= $playergroupInfo->_id ?></td>
								<td rowspan="<?= count($groupdates) ?>"><?= $playergroupInfo->name ?></td>
								<td rowspan="<?= count($groupdates) ?>">
								<select class="form-control" name="public" <?= ($USER->_id == $playergroupInfo->user || $USER->admin)?'':'readonly' ?>>
										<option <?=(!$playergroupInfo->public)?'selected':''?> value="0">private</option>
										<option <?=($playergroupInfo->public)?'selected':''?> value="1">public</option>
									</select>
								</td>
<?php					endif; ?>
								<td><input type="text" value="<?= isset($grdate)?date("Y-m-d", $grdate):'' ?>" name="videogroupstartdate" class="datepicker form-control" <?= isset($grdate)?'':'readonly' ?>></td>
								<td>
									<input type="hidden" name="groupdate" value="<?= isset($grdate)?$grdate:'' ?>">
									<input type="hidden" name="selectvideosforgroup" value="<?= $playergroupInfo->_id ?>">
									<select name="groupvideos[]" autocomplete="off" class="form-control" <?= isset($grdate)?'':'readonly' ?>>
										<option value="" >---</option>
			<?php foreach($AvalibleVideos as $Video): ?>
										<option value="<?= $Video ?>" <?= (isset($Videos[$playergroupInfo->_id][$grdate]) && in_array($Video, $Videos[$playergroupInfo->_id][$grdate]))?'selected':'' ?>><?= $Video ?></option>
			<?php endforeach; ?>
									</select>
								</td>
								<td>
									<button name="save_date" type="submit" class="btn btn-default">Save</button>
									<button name="delete_date" type="submit" class="btn btn-default" <?= isset($grdate)?'':'disabled' ?>>Delete</button>
									<button name="add_date" type="submit" class="btn btn-default">Add</button>
<?php					if($dateid == 0): ?>
									<button name="delete_playlist" type="submit" value="<?= $playergroupInfo->_id ?>" class="btn btn-default">Delete Playlist</button>
<?php					endif; ?>
								</td>
							</form>
						</tr>
			<?php endforeach;
				endforeach; ?>
					</table>
				</div>
