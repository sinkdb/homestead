<?php

class RemoveRlcAssignmentCommand extends Command{

    private $assignmentId;

    public function setAssignmentId($id){
        $this->assignmentId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'RemoveRlcAssignment', 'assignmentId'=>$this->assignmentId);
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve/deny RLC applications.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        $assignment = HMS_RLC_Assignment::getAssignmentById($context->get('assignmentId'));

        $rlcName = $assignment->getRlcName();

        if(!is_null($assignment)){
            $assignment->delete();
        }else{
            NQ::simple('hms', hms\NotificationView::ERROR, 'Could not find an RLC assignment with that id.');
        }

        $rlcApp = $assignment->getApplication();

        HMS_Activity_Log::log_activity($rlcApp->getUsername(), ACTIVITY_RLC_UNASSIGN, Current_User::getUsername(), "Removed from RLC: $rlcName");

        NQ::simple('hms', hms\NotificationView::SUCCESS, 'Assignment deleted.');

        $context->goBack();
    }
}
