<?php 
    session_start();
    $connect = mysqli_connect("localhost","root","","u525933064_dashboard");

    if (isset($_POST["statusText"])) {
        $statusText = '';

        $activityLogQuery = "SELECT * FROM `activity_log` WHERE action='refresh selro orders' ORDER BY id DESC LIMIT 1"; 
        $activityLogResult = mysqli_query($connect, $activityLogQuery);
        $activityLogRow = mysqli_fetch_array($activityLogResult);
        
        $rowcount=mysqli_num_rows($activityLogResult);

        $datetime1 = new DateTime($activityLogRow['actionStart_date_time']);
        $datetime2 = new DateTime($activityLogRow['actionEnd_date_time']);
        $interval = $datetime1->diff($datetime2);
        $elapsed = $interval->format('%h hours %i minutes %s seconds');

        if($rowcount>0){
            $statusText .= 'Last time orders refreshed on '.$activityLogRow['actionStart_date_time'].' by <b>'.$activityLogRow['action_by'].'</b>. Status is <b>'.$activityLogRow['status'].'</b>.';
            if($activityLogRow['status'] == "completed"){
                $statusText .= ' It takes <b>'.$elapsed.'</b>';
            }
        }

        $activityLogQueryActivityLog = "SELECT * FROM `activity_log` WHERE action='label print' AND action_by='".$_SESSION['name_']."'";
        $activityLogResultActivityLog = mysqli_query($connect, $activityLogQueryActivityLog);
        $activityLogRowActivityLog = mysqli_fetch_array($activityLogResultActivityLog);
        
        $rowcountActivityLog = mysqli_num_rows($activityLogResultActivityLog);

        $datetime1ActivityLog = new DateTime($activityLogRowActivityLog['actionStart_date_time']);
        $datetime2ActivityLog = new DateTime($activityLogRowActivityLog['actionEnd_date_time']);
        $intervalActivityLog = $datetime1ActivityLog->diff($datetime2ActivityLog);
        $elapsedActivityLog = $intervalActivityLog->format('%h hours %i minutes %s seconds');
        
        if($rowcountActivityLog>0){
            if($rowcount>0){
                $statusText .= '<br>';
            }

            $statusText .= 'You have printed label on '.$activityLogRowActivityLog['actionStart_date_time'].'. Status is <b>'.$activityLogRowActivityLog['status'].'</b>.';
            if($activityLogRowActivityLog['status'] == "completed"){
                $statusText .= ' It takes '.$elapsedActivityLog;
            }
        }

        $data = array(
            'statusText' => $statusText,
        );
        echo json_encode($data);
    }
?>