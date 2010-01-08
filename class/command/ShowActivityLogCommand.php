<?php

class ShowActivityLogCommand extends Command {


    function getRequestVars(){
        $vars = array('action'=>'ShowActivityLog');

        return $vars;
    }

    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'ActivityLogView.php');

        $actee	= $context->get('actee');
        $actor	= $context->get('actor');
        $notes	= $context->get('notes');
        $exact	= $context->get('exact');

        if(PHPWS_Form::testDate('begin')){
            $begin = PHPWS_Form::getPostedDate('begin');
        }else{
            $begin = null;
        }

        if(PHPWS_Form::testDate('end')){
            $end = PHPWS_Form::getPostedDate('end');
        }else{
            $end = null;
        }

        if(!is_null($begin) && !is_null($end) && $end <= $begin) {
            unset($_REQUEST['begin_year'],
            $_REQUEST['begin_month'],
            $_REQUEST['begin_day'],
            $_REQUEST['end_year'],
            $_REQUEST['end_month'],
            $_REQUEST['end_day']);
            $begin = null;
            $end = null;

            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'Invalid date range. The search results will not be filtered by date.');
        }

        $activityMap = HMS_Activity_Log::getActivityMapping();
        $activities = array();

        foreach($activityMap as $i => $t){
            $act = $context->get("a$i");
            if(!is_null($act)){
                $activities[] = $i;
            }
        }

        $activityLogView = new ActivityLogView($actee, $actor, $notes, $exact, $begin, $end, $activities);
        $context->setContent($activityLogView->show());
    }
}

?>