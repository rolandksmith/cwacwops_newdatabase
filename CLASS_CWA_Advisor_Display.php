/**
 * Helper class for displaying advisor info
 */
class CWA_Advisor_Display {
    
    private $config;
    private $wpdb;
    
    public function __construct($config, $wpdb) {
        $this->config = $config;
        $this->wpdb = $wpdb;
    }
    
    public function render($callsign, $semester, $noUpdate) {
        // Get advisor and user master
        $advisorTable = $this->config['tables']['advisor'];
        $userTable = $this->config['tables']['userMaster'];
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$advisorTable} 
             LEFT JOIN {$userTable} ON user_call_sign = advisor_call_sign 
             WHERE advisor_call_sign = %s 
             AND advisor_semester = %s",
            $callsign,
            $semester
        );
        
        $advisor = $this->wpdb->get_row($sql);
        
        if (!$advisor) {
            return '<p>Advisor record not found.</p>';
        }
        
        $content = $this->renderUserMasterInfo($advisor);
        $content .= $this->renderAdvisorClasses($callsign, $semester, $noUpdate);
        
        return $content;
    }
    
    private function renderUserMasterInfo($advisor) {
        $siteURL = $this->config['siteURL'];
        $doDebug = $this->config['doDebug'] ? 'Y' : 'N';
        $testMode = $this->config['testMode'] ? 'TESTMODE' : 'Production';
        
        $content = <<<HTML
<h4>User Master Data</h4>
<table style='width:900px;'>
    <tr>
        <td><b>Callsign</b><br />{$advisor->user_call_sign}</td>
        <td><b>Name</b><br />{$advisor->user_last_name}, {$advisor->user_first_name}</td>
        <td><b>Phone</b><br />+{$advisor->user_ph_code} {$advisor->user_phone}</td>
        <td><b>Email</b><br />{$advisor->user_email}</td>
    </tr>
    <tr>
        <td><b>City</b><br />{$advisor->user_city}</td>
        <td><b>State</b><br />{$advisor->user_state}</td>
        <td><b>Zip Code</b><br />{$advisor->user_zip_code}</td>
        <td><b>Country</b><br />{$advisor->user_country}</td>
    </tr>
    <tr>
        <td><b>WhatsApp</b><br />{$advisor->user_whatsapp}</td>
        <td><b>Telegram</b><br />{$advisor->user_telegram}</td>
        <td><b>Signal</b><br />{$advisor->user_signal}</td>
        <td><b>Messenger</b><br />{$advisor->user_messenger}</td>
    </tr>
    <tr>
        <td><b>Timezone ID</b><br />{$advisor->user_timezone_id}</td>
        <td><b>Date Created</b><br />{$advisor->user_date_created}</td>
        <td><b>Date Updated</b><br />{$advisor->user_date_updated}</td>
        <td></td>
    </tr>
</table>
<p>Click <a href='{$siteURL}/cwa-display-and-update-user-master-information/?strpass=2&request_type=callsign&request_info={$advisor->user_call_sign}&inp_depth=one' target='_blank'>HERE</a> to update the advisor Master Data</p>
HTML;
        
        return $content;
    }
    
    /**
     * Render advisor classes with Modify/Delete/Add functionality
     * If only one class exists, Delete removes entire advisor registration
     * If multiple classes exist, Delete is only available for classes 2+
     * Includes confirmation prompts before deletion
     */
    private function renderAdvisorClasses($callsign, $semester, $noUpdate) {
        $classTable = $this->config['tables']['advisorClass'];
        
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$classTable} 
             WHERE advisorclass_call_sign = %s 
             AND advisorclass_semester = %s 
             ORDER BY advisorclass_sequence",
            $callsign,
            $semester
        );
        
        $classes = $this->wpdb->get_results($sql);
        
        if (!$classes) {
            return '<p>No classes found.</p>';
        }
        
        $content = '';
        $theURL = $this->config['siteURL'] . '/cwa-advisor-registration/';
        $classCount = count($classes);
        
        foreach ($classes as $class) {
            $incomplete = $class->advisorclass_class_incomplete === 'Y' ? 
                '<tr><td colspan="3"><b>Class Incomplete</b> - Please update this class</td></tr>' : '';
            
            $content .= <<<HTML
<br /><b>Class {$class->advisorclass_sequence}:</b>
<table style='width:900px;'>
    <tr>
        <td><b>Level</b><br />{$class->advisorclass_level}</td>
        <td><b>Class Size</b><br />{$class->advisorclass_class_size}</td>
        <td><b>Language</b><br />{$class->advisorclass_language}</td>
    </tr>
    <tr>
        <td><b>Teaching Days</b><br />{$class->advisorclass_class_schedule_days}</td>
        <td colspan='2'><b>Start Time</b><br />{$class->advisorclass_class_schedule_times}</td>
    </tr>
    {$incomplete}
HTML;
            
            if (!$noUpdate) {
                // Modify button - always available
                $content .= <<<HTML
    <tr>
        <td>
            <form method='post' action='{$theURL}'>
                <input type='hidden' name='strpass' value='15'>
                <input type='hidden' name='classID' value='{$class->advisorclass_id}'>
                <input type='hidden' name='inp_callsign' value='{$callsign}'>
                <input type='hidden' name='inp_semester' value='{$semester}'>
                <input class='formInputButton' type='submit' value='Modify'>
            </form>
        </td>
        <td>
HTML;
                
                // Delete button logic:
                // - If only 1 class: show Delete on class 1 (will delete entire registration)
                // - If multiple classes: only show Delete on classes 2+
                if ($classCount == 1 || $class->advisorclass_sequence > 1) {
                    // Use pass 20 (delete advisor) if only one class, pass 17 (delete class) otherwise
                    $deletePass = ($classCount == 1) ? '20' : '17';
                    $deleteLabel = ($classCount == 1) ? 'Delete Registration' : 'Delete';
                    
                    // Confirmation message based on delete type
                    if ($classCount == 1) {
                        $confirmMessage = 'Are you sure you want to delete your entire advisor registration? This action cannot be undone.';
                    } else {
                        $confirmMessage = 'Are you sure you want to delete this class? This action cannot be undone.';
                    }
                    
                    $content .= <<<HTML
            <form method='post' action='{$theURL}' onsubmit="return confirm('{$confirmMessage}');">
                <input type='hidden' name='strpass' value='{$deletePass}'>
                <input type='hidden' name='classID' value='{$class->advisorclass_id}'>
                <input type='hidden' name='inp_callsign' value='{$callsign}'>
                <input type='hidden' name='inp_semester' value='{$semester}'>
                <input class='formInputButton' type='submit' value='{$deleteLabel}'>
            </form>
HTML;
                }
                
                $content .= <<<HTML
        </td>
        <td></td>
    </tr>
HTML;
            }
            
            $content .= '</table>';
        }
        
        if (!$noUpdate) {
            $nextSequence = count($classes) + 1;
            $content .= <<<HTML
<p>
    <form method='post' action='{$theURL}'>
        <input type='hidden' name='strpass' value='5'>
        <input type='hidden' name='inp_callsign' value='{$callsign}'>
        <input type='hidden' name='inp_semester' value='{$semester}'>
        <input type='hidden' name='classcount' value='{$nextSequence}'>
        <input class='formInputButton' type='submit' value='Add Another Class'>
    </form>
</p>
HTML;
        }
        
        return $content;
    }
}