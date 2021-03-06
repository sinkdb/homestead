<?php

class LotteryConfirmRoommateRequestCommand extends Command {

    private $requestId;
    private $mealPlan;

    public function setRequestId($id){
        $this->requestId = $id;
    }

    public function setMealPlan($plan){
        $this->mealPlan = $plan;
    }

    public function getRequestVars(){
        $vars = array('action'=>'LotteryConfirmRoommateRequest');

        $vars['requestId'] = $this->requestId;
        $vars['mealPlan']   = $this->mealPlan;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'RlcMembershipFactory.php');
        PHPWS_Core::initModClass('hms', 'RlcAssignmentSelfAssignedState.php');

        $requestId = $context->get('requestId');
        $mealPlan = $context->get('mealPlan');

        $errorCmd = CommandFactory::getCommand('LotteryShowConfirmRoommateRequest');
        $errorCmd->setRequestId($requestId);
        $errorCmd->setMealPlan($mealPlan);

        // Confirm the captcha
        PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = Captcha::verify(TRUE);
        if($captcha === FALSE){
            NQ::simple('hms', hms\NotificationView::ERROR, 'The words you entered were incorrect. Please try again.');
            $errorCmd->redirect();
        }

        // Check for a meal plan
        if(!isset($mealPlan) || $mealPlan == '') {
        	NQ::simple('hms', hms\NotificationView::ERROR, 'Please choose a meal plan.');
            $errorCmd->redirect();
        }

        $term = PHPWS_Settings::get('hms', 'lottery_term');
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        // Update the meal plan field on the application
        $app = HousingApplication::getApplicationByUser(UserStatus::getUsername(), $term);

        $app->setMealPlan($mealPlan);

        try{
            $app->save();
        }catch(Exception $e){
            PHPWS_Error::log('hms', $e->getMessage());
            NQ::simple('hms', hms\NotificationView::ERROR,'Sorry, there was an error confirming your roommate invitation. Please contact University Housing.');
            $errorCmd->redirect();
        }

        // Try to actually make the assignment
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        try{
            $result = HMS_Lottery::confirm_roommate_request(UserStatus::getUsername(), $requestId, $mealPlan);
        }catch(Exception $e){
            PHPWS_Error::log('hms', $e->getMessage());
            NQ::simple('hms', hms\NotificationView::ERROR,'Sorry, there was an error confirming your roommate invitation. Please contact University Housing.');
            $errorCmd->redirect();
        }

        # Log the fact that the roommate was accepted and successfully assigned
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_CONFIRMED_ROOMMATE,UserStatus::getUsername(), "Captcha: \"$captcha\"");

        
        // Check for an RLC membership and update status if necessary
        // If this student was an RLC self-select, update the RLC memberhsip state
        $rlcAssignment = RlcMembershipFactory::getMembership($student, $term);
        if($rlcAssignment != null && $rlcAssignment->getStateName() == 'selfselect-invite') {
            $rlcAssignment->changeState(new RlcAssignmentSelfAssignedState($rlcAssignment));
        }

        $invite = HMS_Lottery::get_lottery_roommate_invite_by_id($requestId);
        $bed = new HMS_Bed($invite['bed_id']);

        $successCmd = CommandFactory::getCommand('LotteryShowConfirmedRoommateThanks');
        $successCmd->setRequestId($requestId);
        $successCmd->redirect();
    }
}

?>
