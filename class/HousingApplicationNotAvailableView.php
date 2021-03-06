<?php

class HousingApplicationNotAvailableView extends hms\View {

    private $student;
    private $feature;
    private $term;

    public function __construct(Student $student, ApplicationFeature $feature = NULL, $term)
    {
        $this->student = $student;
        $this->feature = $feature;
        $this->term = $term;
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $tpl = array();

        if(is_null($this->feature) || !$this->feature->isEnabled()){
            $tpl['ENTRY_TERM'] = Term::toString($this->term);
            $tpl['NO_FEATURE'] = ""; // dummy var
        }else if($this->feature->getStartDate() > time()){
            $tpl['TERM'] = Term::toString($this->term);
            $tpl['DEADLINE'] = 'It is too soon to complete your application. The On-campus Housing Application will be availble here on ' . HMS_Util::getFriendlyDate($this->feature->getStartDate());
        }else if($this->feature->getEndDate() < time()){
            $tpl['TERM'] = Term::toString($this->term);
            $tpl['DEADLINE'] = 'The deadline to complete the On-campus Housing Application was ' . HMS_Util::getFriendlyDate($this->feature->getEndDate());
        }

        $contactCmd = CommandFactory::getCommand('ShowContactForm');
        $tpl['CONTACT_LINK'] = $contactCmd->getLink('click here to contact us');

        return PHPWS_Template::process($tpl, 'hms', 'student/welcomeScreenNotAvailable.tpl');
    }

}

?>
