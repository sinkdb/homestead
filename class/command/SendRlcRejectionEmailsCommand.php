<?php

  /**
   * This command will notify all rejected students that their
   * RLC Application has been denied.
   *
   * @author Robert Bost <bostrt at tux dot appstate dot edu>
   */

class SendRlcRejectionEmailsCommand extends Command
{

    private $application;
    
    public function getRequestVars()
    {
        return array('action' => 'SendRlcRejectionEmails');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'email_rlc_rejections')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to send RLC rejections.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'Term.php');

        $term = Term::getSelectedTerm();
        $deniedStudents = HMS_RLC_Application::getDeniedApplicantsByTerm($term);

        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        $email = new HMS_Email();

        foreach($deniedStudents as $student){
            $email->sendRlcApplicationRejected($student, $term);
        }

        NQ::Simple('hms', hms\NotificationView::SUCCESS, 'RLC rejection emails sent.');
        $context->goBack();
    }

    public function setApplication($app)
    {
        $this->application = $app;
    }

}

?>