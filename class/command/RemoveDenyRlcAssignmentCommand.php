<?php

  /**
   * RemoveDenyRlcAssignment
   *
   * This is basically a macro command for RemoveRlcAssignmentCommand
   * and DenyRlcApplicationCommand.
   *
   * @author Robert Bost <bostrt at tux dot appstate dot edu>
   */

class RemoveDenyRlcAssignmentCommand extends Command
{
    private $appId;
    private $assignId;
    public function getRequestVars()
    {
        return array('action'   => 'RemoveDenyRlcAssignment',
                     'appId'    => $this->appId,
                     'assignId' => $this->assignId);
    }

    public function setAppId($id){
        $this->appId =$id;
    }
    public function setAssignmentId($id){
        $this->assignId = $id;
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve/deny RLC applications.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        // Remove assignment
        $assignment = HMS_RLC_Assignment::getAssignmentById($context->get('assignId'));

        $rlcName = $assignment->getRlcName();

        $rlcApp = $assignment->getApplication();

        if(!is_null($assignment)){
            $assignment->delete();
        } else {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Could not find an RLC assignment with that id.');
        }

        HMS_Activity_Log::log_activity($rlcApp->getUsername(), ACTIVITY_RLC_UNASSIGN, Current_User::getUsername(), "Removed from $rlcName");
        NQ::simple('hms', hms\NotificationView::SUCCESS, 'Removed from RLC');

        // Deny application
        $rlcApp->denied = 1;
        $rlcApp->save();

        NQ::simple('hms', hms\NotificationView::SUCCESS, 'RLC Application denied');
        HMS_Activity_Log::log_activity($rlcApp->getUsername(), ACTIVITY_DENIED_RLC_APPLICATION, Current_User::getUsername(), 'RLC Application Denied');

        $context->goBack();
    }

}
?>
