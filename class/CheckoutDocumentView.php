<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

class CheckoutDocumentView extends hms\View {

    private $checkin;

    public function __construct(Checkin $checkin)
    {
        $this->checkin = $checkin;
    }

    public function show()
    {
        $term = Term::getCurrentTerm();
        $student = StudentFactory::getStudentByBannerId($this->checkin->getBannerId(), $term);

        $bed = new HMS_Bed($this->checkin->getBedId());

        $tpl = array();

        $tpl['NAME'] = $student->getName();
        $tpl['ASSIGNMENT'] = $bed->where_am_i();

        $pdfCmd = CommandFactory::getCommand('GenerateInfoCard');
        $pdfCmd->setCheckinId($this->checkin->getId());

        $tpl['INFO_CARD_LINK'] = $pdfCmd->getLink('Resident Information Card');

        return PHPWS_Template::process($tpl, 'hms', 'admin/checkoutComplete.tpl');
    }
}

?>
