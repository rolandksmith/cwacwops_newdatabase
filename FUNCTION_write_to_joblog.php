private function writeToJoblog($pass, $elapsed) {
    $updateData = array(
        'jobname' => $this->jobname,
        'jobwho' => $this->user['name'],
        'jobmode' => 'Time',
        'jobdatatype' => $this->config['testMode'] ? 'Testmode' : 'Production',
        'jobaddlinfo' => "{$pass}: {$elapsed}",
        'jobip' => $this->user['ipAddress'],
        'doDebug' => $this->config['doDebug']
    );
    
    $result = write_joblog2_func($updateData);
    
    if ($result === false && $this->config['doDebug']) {
        error_log("{$this->jobname}: Failed to write to joblog");
    }
}