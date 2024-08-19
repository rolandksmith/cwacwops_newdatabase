function RKSTEST_test_user_master_data_func() {

			$dataArray			= array('callsign'=>'K7OJL',
									'action'=>'updatet',
									'last_name'=>'Smitty',
									'debugging'=> TRUE,
									'testing'=> FALSE);
		$dataResult				= user_master_data($dataArray);
		
		echo "dataResult:<br /><pre>";
		print_r($dataResult);
		echo "</pre><br />";		
		
		

}
add_shortcode('RKSTEST_test_user_master_data','RKSTEST_test_user_master_data_func');