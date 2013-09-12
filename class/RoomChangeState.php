<?php


abstract class RoomChangeState {
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getValidTransitions()
    {
        return array();
    }

    public function canTransition(RoomChangeState $toState)
    {
        return in_array(get_class($toState), $this->getValidTransitions());
    }

    public function onEnter($from = NULL)
    {
        // pass;
    }

    public function onExit()
    {
        // pass;
    }

    // TODO Move this out of here
    public function reserveRoom($last_command)
    {
        $params = new CommandContext();
        $params->addParam('last_command', $last_command);
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->requested_bed_id);
        $cmd = CommandFactory::getCommand('ReserveRoom');
        $cmd = $cmd->execute($params);

        return $cmd;
    }

    // TODO move this out of here
    public function clearReservedFlag($last_command)
    {
        // clear reserved flag
        if (!isset($this->request->requested_bed_id) || is_null($this->request->requested_bed_id)) {
            return;
        }

        $params = new CommandContext();
        $params->addParam('last_command', $last_command);
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->requested_bed_id);
        $params->addParam('clear', true);
        $cmd = CommandFactory::getCommand('ReserveRoom');
        $cmd = $cmd->execute($params);

        return $cmd;
    }

    public function sendNotification()
    {
        // By default, don't send any notifications.
    }
}


class NewRoomChangeRequest extends RoomChangeState {

    public function getValidTransitions()
    {
        return array(
                'PendingRoomChangeRequest'
        );
    }

    public function checkPermissions()
    {
        // only students
    }
}


class PendingRoomChangeRequest extends RoomChangeState {

    public function getValidTransitions()
    {
        return array(
                'RDApprovedChangeRequest',
                'WaitingForPairing',
                'DeniedChangeRequest'
        );
    }

    public function getType()
    {
        return ROOM_CHANGE_PENDING;
    }

    public function onEnter($from = NULL)
    {
    }

    public function sendNotification()
    {
        $student = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());
        $assign = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());

        $bed = $assign->get_parent();
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $hall = $floor->get_parent();

        // Send confirmation to student
        $tpl = array();
        $tpl['NAME'] = $student->getName();
        $tpl['CURR_ASSIGN'] = $assign->where_am_i();
        $tpl['PHONE_NUM'] = $this->request->cell_phone;

        // Send confirmation to student
        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'Room Change Request Received', 'email/roomChange_submitted_student.tpl', $tpl);

        // Send 'New Room Change' to RD
        $approvers = HMS_Permission::getMembership('room_change_approve', $hall);
        foreach ($approvers as $user) {
            HMS_Email::send_template_message($user['username'] . TO_DOMAIN, 'New Room Change Request', 'email/roomChange_submitted_rd.tpl', $tpl);
        }
    }
}


class RDApprovedChangeRequest extends RoomChangeState {

    public function getValidTransitions()
    {
        $valid = array(
                'DeniedChangeRequest'
        );

        if (isset($this->request->requested_bed_id)) {
            $valid[] = 'HousingApprovedChangeRequest';
        } elseif ($this->request->is_swap) {
            $valid[] = 'WaitingForPairing';
        }

        return $valid;
    }

    public function onEnter($from = NULL)
    {
        $this->addParticipant('rd', UserStatus::getUsername(), 'University Housing');
        $cmd = $this->reserveRoom('RDRoomChange');

        if ($cmd instanceof Command) {
            $cmd->redirect();
        }

        $bed = new HMS_Bed();
        $bed->id = $this->request->requested_bed_id;
        $bed->load();

        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_APPROVED_RD, UserStatus::getUsername(false), "Selected " . $bed->where_am_i());
    }

    public function getType()
    {
        return ROOM_CHANGE_RD_APPROVED;
    }

    public function sendNotification()
    {
        $student = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());

        $tpl = array();
        $tpl['NAME'] = $student->getName();

        $tpl['USERNAME'] = $student->getUsername();
        $tpl['BANNER_ID'] = $student->getBannerId();

        // Notify the student
        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'Room Change Pending Approval', 'email/roomChange_rdApproved_student.tpl', $tpl);

        // Confirm with the user who did it and Housing
        HMS_Email::send_template_message(UserStatus::getUsername() . TO_DOMAIN, 'Room Change Pending Approval', 'email/roomChange_rdApproved_housing.tpl', $tpl);
        HMS_Email::send_template_message(EMAIL_ADDRESS . '@' . DOMAIN_NAME, 'Room Change Pending Approval', 'email/roomChange_rdApproved_housing.tpl', $tpl);
    }
}


class HousingApprovedChangeRequest extends RoomChangeState {
    private $isBuddy = false;

    public function __construct($isBuddy = false)
    {
        $this->isBuddy = $isBuddy;
    }

    public function getValidTransitions()
    {
        return array(
                'CompletedChangeRequest',
                'DeniedChangeRequest'
        );
    }

    public function onEnter($from = NULL)
    {
        $this->addParticipant('housing', EMAIL_ADDRESS, 'University Housing');

        $curr_assignment = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());
        $bed = $curr_assignment->get_parent();

        $newBed = new HMS_Bed($this->request->requested_bed_id);

        // if this is a move request
        if ($this->request->is_swap) {

            // so long as we aren't the pair... that leads to some non-finite recursion
            if (!$this->isBuddy) {
                $this->request->updateBuddy(new HousingApprovedChangeRequest(true));
            }

            $this->request->save();
            $this->request->load();
        }

        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_APPROVED_HOUSING, UserStatus::getUsername(false), "Approved Room Change to " . $newBed->where_am_i() . " from " . $bed->where_am_i());
    }

    public function getType()
    {
        return ROOM_CHANGE_HOUSING_APPROVED;
    }

    public function sendNotification()
    {
        $student = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());
        $assign = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());

        $oldBed = $assign->get_parent();
        $oldRoom = $oldBed->get_parent();
        $oldFloor = $oldRoom->get_parent();
        $oldHall = $oldFloor->get_parent();

        $newBed = new HMS_Bed($this->request->requested_bed_id);
        $newRoom = $newBed->get_parent();
        $newFloor = $newRoom->get_parent();
        $newHall = $newFloor->get_parent();

        $tpl = array();
        $tpl['NAME'] = $student->getName();
        $tpl['OLD_ROOM'] = $oldRoom->where_am_i();
        $tpl['NEW_ROOM'] = $newRoom->where_am_i();

        // Notify student
        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'Room Change Approved', 'email/roomChange_housingApproved_student.tpl', $tpl);

        // Notify new roommates
        $newRoommates = $newRoom->get_assignees();

        foreach ($newRoommates as $roommate) {
            $tpl['ROOMMATE'] = $roommate->getName();
            HMS_Email::send_template_message($roommate->getUsername() . TO_DOMAIN, 'New Roommate Notification', 'email/roomChange_approved_newRoommate.tpl', $tpl);
        }

        // Notify old roommates
        $oldRoommates = $oldRoom->get_assignees();

        foreach ($oldRoommates as $roommate) {
            if ($roommate->getUsername() == $student->getUsername()) {
                // Skip the student who actually made the request
                continue;
            }
            $tpl['ROOMMATE'] = $roommate->getName();
            HMS_Email::send_template_message($roommate->getUsername() . TO_DOMAIN, 'Roommate Change Notification', 'email/roomChange_approved_oldRoommate.tpl', $tpl);
        }

        // Notify old and new RDs
        $oldRDs = HMS_Permission::getMembership('room_change_approve', $oldHall);
        $newRDs = HMS_Permission::getMembership('room_change_approve', $newHall);
        $RDs = array_merge($oldRDs, $newRDs);

        foreach ($RDs as $rd) {
            HMS_Email::send_template_message($rd['username'] . TO_DOMAIN, 'Room Change Approved', 'email/roomChange_housingApproved_student.tpl', $tpl);
        }
    }
}


class CompletedChangeRequest extends RoomChangeState {

    // state cannot change
    public function onEnter($from = NULL)
    {
        // if this is a swap, then all of this is handled in the command
        // TODO: move this into the complete change command
        if ($this->request->is_swap) {
            return;
        }

        // clear reserved flag
        $cmd = $this->clearReservedFlag('HousingRoomChange');

        // if it fails...
        if ($cmd instanceof Command) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not clear the reserved flag, assignment was not changed.');
            $cmd->redirect();
        }

        $params = new CommandContext();
        $params->addParam('username', $this->request->username);
        $params->addParam('bed', $this->request->requested_bed_id);
        $cmd = CommandFactory::getCommand('RoomChangeAssign');
        $cmd = $cmd->execute($params);

        if ($cmd instanceof Command) {
            // redirect on failure
            $cmd->redirect();
        }

        // log
        $newBed = new HMS_Bed($this->request->requested_bed_id);
        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_COMPLETED, UserStatus::getUsername(false), "Completed Room change to " . $newBed->where_am_i());
    }

    public function getType()
    {
        return ROOM_CHANGE_COMPLETED;
    }

    public function sendNotification()
    {
        $student = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());

        $newBed = new HMS_Bed($this->request->requested_bed_id);
        $newRoom = $newBed->get_parent();
        $newFloor = $newRoom->get_parent();
        $newHall = $newFloor->get_parent();

        $newRDs = HMS_Permission::getMembership('room_change_approve', $newHall);

        $tpl = array();
        $tpl['NAME'] = $student->getName();
        $tpl['USER_NAME'] = $student->getUsername();

        // $tpl['MOVED_FROM'] =
        $tpl['MOVED_TO'] = $newRoom->where_am_i();

        // Notify new RD that move is complete
        foreach ($newRDs as $rd) {
            HMS_Email::send_template_message($rd['username'] . TO_DOMAIN, 'Room Change Completed', 'email/roomChange_completed.tpl', $tpl);
        }
    }
}


class DeniedChangeRequest extends RoomChangeState {
    private $isBuddy = false;

    public function __construct($isBuddy = false)
    {
        $this->isBuddy = $isBuddy;
    }

    // state cannot change
    public function onEnter($from = NULL)
    {
        $this->request->denied_by = UserStatus::getUsername();

        $other = is_null($this->request->switch_with) ? NULL : $this->request->search($this->request->switch_with);

        // this will break if from is null, but allowing null makes the interface cleaner
        // therefor ***MAKE SURE THIS ISN'T NULL***

        // if denied by RD
        if ($from instanceof PendingRoomChangeRequest) {
            // send back to RD screen
            $this->clearReservedFlag('RDRoomChange');
        } else { // denied by housing
                 // send back to housing screen
            $this->clearReservedFlag('HousingRoomChange');
        }

        // if it's a swap and the requests were paired
        if (!is_null($other) && $from instanceof PairedRoomChangeRequest && $other->state instanceof PairedRoomChangeRequest) {
            // deny the buddy too if we aren't the buddy
            if (!$this->isBuddy) {
                $this->request->updateBuddy(new DeniedChangeRequest(true));
            }
        }

        // save the state change to the db
        $this->request->save();
        $this->request->load();

        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_DENIED, UserStatus::getUsername(false), $this->request->denied_reason);
    }

    public function getType()
    {
        return ROOM_CHANGE_DENIED;
    }

    public function sendNotification()
    {
        $student = StudentFactory::getStudentByUsername($this->request->username, Term::getSelectedTerm());

        $tpl = array();
        $tpl['NAME'] = $student->getName();

        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'Room Change Denied', 'email/roomChange_denied_housing.tpl', $tpl);
    }
}


class WaitingForPairing extends RoomChangeState {

    public function getValidTransitions()
    {
        return array(
                'PairedRoomChangeRequest',
                'DeniedChangeRequest'
        );
    }

    public function getType()
    {
        return ROOM_CHANGE_PAIRING;
    }

    public function onEnter($from = NULL)
    {
        $student = StudentFactory::getStudentByUsername($this->request->switch_with, Term::getSelectedTerm());
        $assignment = HMS_Assignment::getAssignment($student->getUsername(), Term::getSelectedTerm());

        if (is_null($assignment)) {
            throw new Exception('Requested swap partner is not assigned, cannot complete.');
        }

        $this->request->requested_bed_id = $assignment->bed_id;
        $this->request->save();
        $this->request->load();

        $assignment = HMS_Assignment::getAssignment($this->request->username, Term::getSelectedTerm());
        $bed = $assignment->get_parent();

        HMS_Activity_Log::log_activity($this->request->username, ACTIVITY_ROOM_CHANGE_APPROVED_RD, UserStatus::getUsername(false), "Selected " . $bed->where_am_i());
    }

    public function attemptToPair()
    {
        $other = NULL;
        try {
            $other = $this->request->search($this->request->switch_with);
            if (!is_null($other)) {
                $other->load();
            }
        } catch (DatabaseException $e) {
            // pass; broken database is equivalent to NULL here
        }

        if (!is_null($other) && $other->state instanceof WaitingForPairing) {
            $this->request->change(new PairedRoomChangeRequest());
        }

        return !is_null($other);
    }
}


class PairedRoomChangeRequest extends RoomChangeState {
    private $isBuddy = false;

    public function __construct($isBuddy = false)
    {
        $this->isBuddy = $isBuddy;
    }

    public function getValidTransitions()
    {
        return array(
                'HousingApprovedChangeRequest',
                'DeniedChangeRequest'
        );
    }

    public function getType()
    {
        return ROOM_CHANGE_PAIRED;
    }

    public function onEnter($from = NULL)
    {
        // if we are not the buddy then notify our buddy of the change, otherwise we're done here
        if (!$this->isBuddy) {
            $this->request->updateBuddy(new PairedRoomChangeRequest(true));
        }

        $this->request->save();
        $this->request->load();
    }

    public function getOther()
    {
        return $other = $this->request->search($this->request->switch_with);
    }
}
?>