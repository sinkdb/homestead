<?php


/**
 * ShowCheckinStartCommand - Shows the iniital interface for beginning a checkin
 *
 * @author Jeremy Booker
 * @package hms
 */
class ShowCheckinStartCommand extends Command {

    public function getRequestVars()
    {
        return array (
                'action' => 'ShowCheckinStart'
        );
    }

    public function execute(CommandContext $context)
    {
        // Check permissions
        if (!Current_User::allow('hms', 'checkin')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to checkin students.');
        }

        $term = Term::getSelectedTerm();

        // Check role-based permissions for list of hall or all halls
        // TODO (for now just listing all halls)

        PHPWS_Core::initModClass('hms', 'ResidenceHallFactory.php');
        $halls = ResidenceHallFactory::getHallNamesAssoc($term);

        if (!isset($halls) || count($halls) < 1) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'No residence halls are setup for this term, so the check-in cannot be accessed.');
            $context->goBack();
        }

        PHPWS_Core::initModClass('hms', 'CheckinStartView.php');
        $view = new CheckinStartView($halls, $term);

        $context->setContent($view->show());
    }
}

?>