function manage_cwa_announcements_func() {
	global $wpdb;

	// --- SECTION 1: INITIALIZATION ---
	$initializationArray = data_initialization_func();
	if ($initializationArray['validUser'] !== "Y") {
		return "YOU'RE NOT AUTHORIZED!<br />Goodbye";
	}

	$siteURL      = $initializationArray['siteurl'];
	$userName     = $initializationArray['userName'];
	$jobname	  = "Manage CWA Announcements";
	$versionNumber = '1';
	$tableName    = "wpw1_cwa_announcements";
	$theURL       = "$siteURL/cwa-manage-cwa-announcements/"; // Update to your actual page slug
	
/// get the time that the process started
	$startingMicroTime			= microtime(TRUE);

	// Get State Variables
	$strPass      = filter_input(INPUT_POST, 'strpass') ?: "1";
	$mode      = filter_input(INPUT_POST, 'inp_mode') ?: "LIST"; 
	$record_id = filter_input(INPUT_POST, 'inp_id', FILTER_SANITIZE_NUMBER_INT);

	ob_start();
	echo "<div id='cwa-admin-wrapper'>";

	switch ($strPass) {
		// --- PASS 1: THE DASHBOARD / SELECTOR ---
		case "1":
			$results = $wpdb->get_results("SELECT * FROM $tableName ORDER BY ann_date_created DESC");
			?>
			<div style="margin-bottom: 20px;">
				<h3>Announcement Manager</h3>
				<form method="post" action="<?php echo $theURL; ?>">
					<input type="hidden" name="strpass" value="2">
					<input type="hidden" name="inp_mode" value="ADD">
					<input type="submit" class="formInputButton" value="Create New Announcement">
				</form>
				<div style="clear:both;"></div> 
			</div>
		
			<table style="width:100%; border-collapse: collapse;">
				<thead>
					<tr>
						<th>Title</th>
						<th>Target</th>
						<th>Occurrences</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if ($results): ?>
						<?php foreach ($results as $row): ?>
							<tr>
								<td><strong><?php echo esc_html($row->ann_title); ?></strong></td>
								<td><?php echo esc_html($row->ann_target_role); ?></td>
								<td><?php echo esc_html($row->ann_occurances); ?></td>
								<td><?php echo ($row->ann_completed == 'Y') ? 'Completed' : 'Active'; ?></td>
								<td>
									<form method="post" action="<?php echo $theURL; ?>" style="margin:0; display:inline-block;">
										<input type="hidden" name="strpass" value="2">
										<input type="hidden" name="inp_id" value="<?php echo $row->ann_record_id; ?>">
										<input type="submit" name="inp_mode" value="EDIT" class="formInputButton" style="float:none; display:inline-block;">
										<input type="submit" name="inp_mode" value="DELETE" class="formInputButton" style="float:none; display:inline-block; background:#d9534f; color:#fff;" onclick="return confirm('Delete this announcement?')">
									</form>
			
									<form method="post" action="<?php echo $theURL; ?>" style="display:inline-block;">
										<input type="hidden" name="strpass" value="4">
										<input type="hidden" name="inp_id" value="<?php echo $row->ann_record_id; ?>">
										<input type="submit" value="View Log" class="formInputButton" style="background:#5bc0de; color:#fff;">
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr><td colspan="5">No announcements found.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
			<?php
			break;
    
		// --- PASS 2: ACTION HANDLER (FORM OR EXECUTION) ---
		case "2":
			// 1. Capture the intent (Add, Edit, or Delete)
			$mode = filter_input(INPUT_POST, 'inp_mode');
		
			// --- SECTION A: ADD & EDIT (Form & Preview) ---
			if ($mode === 'ADD' || $mode === 'EDIT') {
				// Initialize default values
				$title = ""; $text = ""; $occ = "Once"; $comp = "N"; $target = "All";
		
				// Check if we are re-loading the page to show a Preview
				$isPreview = (filter_input(INPUT_POST, 'preview_mode') === 'Y');
		
				if ($isPreview) {
					// "Sticky" form data: Pull from $_POST so user doesn't lose progress
					$title  = sanitize_text_field($_POST['ann_title']);
					$text   = $_POST['ann_text']; 
					$occ    = sanitize_text_field($_POST['ann_occurances']);
					$comp   = sanitize_text_field($_POST['ann_completed']);
					$target = sanitize_text_field($_POST['ann_target_role']);
		
					// Render the Mockup at the top of the management page
					echo '<div id="preview-area" style="margin-bottom: 30px; border: 2px dashed #d9534f; padding: 20px; background: #fff; clear: both;">';
					echo '<h3 style="color:#d9534f; margin-top:0;">Live Preview:</h3>';
					echo '<div class="announcement-item" style="border: 1px solid #d3d3d3; padding: 15px; border-left: 5px solid #d9534f; background: #fff;">';
					echo '<h4 style="margin-top: 0; color: #333;">' . esc_html($title) . '</h4>';
					echo '<div class="ann_text">' . wpautop(wp_kses_post($text)) . '</div>';
					echo '<small style="color: #999;">Posted on: ' . date('F j, Y') . '</small>';
					echo '</div>';
					echo '<p style="margin-top:10px;"><em>If this looks correct, click <strong>Save Announcement</strong> below.</em></p>';
					echo '</div>';
				} 
				elseif ($mode === 'EDIT') {
					// Standard Edit: Fetch the record from the database
					$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableName WHERE ann_record_id = %d", $record_id));
					if ($row) {
						$title  = $row->ann_title;
						$text   = $row->ann_text;
						$occ    = $row->ann_occurances;
						$comp   = $row->ann_completed;
						$target = $row->ann_target_role;
					} else {
						echo "<div class='error'><p>Error: Could not find announcement #$record_id.</p></div>";
					}
				}
		
				// Render the actual input form
				?>
				<h3><?php echo ($mode === 'EDIT') ? 'Edit' : 'Create'; ?> Announcement</h3>
				<form method="post" action="<?php echo $theURL; ?>" id="ann-form">
					<input type="hidden" name="strpass" value="3" id="next-pass">
					<input type="hidden" name="inp_mode" value="<?php echo $mode; ?>">
					<input type="hidden" name="inp_id" value="<?php echo $record_id; ?>">
					<input type="hidden" name="preview_mode" value="N" id="preview-flag">
					
					<fieldset>
						<legend>Details</legend>
		
						<div style="margin-bottom: 15px; clear: both;">
							<label for="ann_target_role">Target Audience:</label>
							<select id="ann_target_role" name="ann_target_role" class="formSelect">
								<option value="All" <?php selected($target, 'All'); ?>>Everyone</option>
								<option value="student" <?php selected($target, 'student'); ?>>Students Only</option>
								<option value="advisor" <?php selected($target, 'advisor'); ?>>Advisors Only</option>
							</select>
						</div>
		
						<div style="margin-bottom: 15px; clear: both;">
							<label for="title">Title:</label>
							<input type="text" id="title" name="ann_title" class="formInputText" style="width:400px;" value="<?php echo esc_attr($title); ?>" required>
						</div>
						
						<div style="margin-bottom: 15px; clear: both;">
							<label>Text:</label>
							<textarea name="ann_text" class="formInputText" style="width:400px; height:150px;" required><?php echo esc_textarea($text); ?></textarea>
						</div>
						
						<div style="margin-bottom: 15px; clear: both;">
							<label>Occurrences:</label>
							<input type="text" name="ann_occurances" class="formInputText" placeholder="Once or YYYY-MM-DD" value="<?php echo esc_attr($occ); ?>">
						</div>
						
						<div style="margin-bottom: 15px; clear: both;">
							<label>Completed?</label>
							<select name="ann_completed" class="formSelect">
								<option value="N" <?php selected($comp, 'N'); ?>>No (Active)</option>
								<option value="Y" <?php selected($comp, 'Y'); ?>>Yes (Hide)</option>
							</select>
						</div>
					</fieldset>
		
					<div style="margin-top:20px; clear:both;">
						<input type="submit" value="Save Announcement" class="formInputButton">
						<button type="button" class="formInputButton" style="background:#666; color:#fff; margin-left:10px;" onclick="runPreview()">Show Preview</button>
						<a href="<?php echo $theURL; ?>" class="formInputButton" style="background:#ddd; color:#333; margin-left:10px; text-decoration:none;">Cancel</a>
					</div>
				</form>
				<?php
			} 
			// --- SECTION B: DELETE ---
			elseif ($mode === 'DELETE') {
				if ($record_id) {
					$wpdb->delete($tableName, array('ann_record_id' => $record_id), array('%d'));
					$wpdb->delete("wpw1_cwa_announcements_tracking", array('ann_id' => $record_id), array('%d'));
					echo "<div class='updated'><p>Announcement #$record_id and tracking data deleted.</p></div>";
				}
				echo "<a href='$theURL' class='formInputButton' style='text-decoration:none;'>Return to List</a>";
			}
			break;		// --- PASS 3: DATABASE SAVE ---

		case "3":
			$data = array(
				'ann_title'       => sanitize_text_field($_POST['ann_title']),
				'ann_text'        => wp_kses_post($_POST['ann_text']),
				'ann_target_role' => sanitize_text_field($_POST['ann_target_role']), // Added this
				'ann_occurances'  => sanitize_text_field($_POST['ann_occurances']),
				'ann_completed'   => sanitize_text_field($_POST['ann_completed'])
			);

			if ($mode === 'EDIT') {
				$wpdb->update($tableName, $data, array('ann_record_id' => $record_id));
				echo "<p>Announcement Updated Successfully.</p>";
			} else {
				$wpdb->insert($tableName, $data);
				echo "<p>Announcement Created Successfully.</p>";
			}
			echo "<a href='$theURL' class='formInputButton'>Return to List</a>";
			break;

		case "4":
			// Fetch the announcement details first
			$ann = $wpdb->get_row($wpdb->prepare("SELECT ann_title FROM $tableName WHERE ann_record_id = %d", $record_id));
			
			// Fetch the list of users who viewed it
			$trackTable = "wpw1_cwa_announcements_tracking";
			$viewers = $wpdb->get_results($wpdb->prepare("
				SELECT t.date_viewed, u.display_name, u.user_email 
				FROM $trackTable t
				JOIN {$wpdb->prefix}users u ON t.user_id = u.ID
				WHERE t.ann_id = %d
				ORDER BY t.date_viewed DESC
			", $record_id));
		
			echo "<h3>Read Receipts for: " . esc_html($ann->ann_title) . "</h3>";
			
			if ($viewers) {
				echo "<table style='width:100%; border-collapse: collapse;'>
						<thead>
							<tr>
								<th>User Name</th>
								<th>Email</th>
								<th>Date Viewed</th>
							</tr>
						</thead>
						<tbody>";
				foreach ($viewers as $v) {
					echo "<tr>
							<td>" . esc_html($v->display_name) . "</td>
							<td>" . esc_html($v->user_email) . "</td>
							<td>" . esc_html($v->date_viewed) . "</td>
						  </tr>";
				}
				echo "</tbody></table>";
			} else {
				echo "<p>No one has viewed this announcement yet.</p>";
			}
			
			echo "<br /><a href='$theURL' class='formInputButton' style='text-decoration:none;'>Back to Manager</a>";
			break;
	}
	$endingMicroTime = microtime(TRUE);
	$elapsedTime	= $endingMicroTime - $startingMicroTime;
	$elapsedTime	= number_format($elapsedTime, 4, '.', ',');
	echo "<p>Report V$versionNumber pass $strPass took $elapsedTime seconds to run</p>";
	$nowDate		= date('Y-m-d');
	$nowTime		= date('H:i:s');
	$thisStr		= 'Production';
	$ipAddr			= get_the_user_ip();
	$theTitle		= esc_html(get_the_title());
	$jobmonth		= date('F Y');
	$updateData		= array('jobname' 		=> $jobname,
							'jobdate' 		=> $nowDate,
							'jobtime'		=> $nowTime,
							'jobwho' 		=> $userName,
							'jobmode'		=> 'Time',
							'jobdatatype' 	=> $thisStr,
							'jobaddlinfo'	=> "$strPass: $elapsedTime",
							'jobip' 		=> $ipAddr,
							'jobmonth' 		=> $jobmonth,
							'jobcomments' 	=> '',
							'jobtitle' 		=> $theTitle,
							'doDebug'		=> FALSE);
	$result			= write_joblog2_func($updateData);
	if ($result === FALSE){
		echo"<p>writing to joblog failed</p>";
	}



	echo "</div>";
	return ob_get_clean();
}
add_shortcode('manage_cwa_announcements', 'manage_cwa_announcements_func');