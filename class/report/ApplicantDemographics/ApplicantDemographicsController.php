<?php

class ApplicantDemographicsController extends ReportController {

    const allowSyncExec = true;
    const allowAsyncExec = true;
    const allowScheduledExec = true;

    public function setParamsFromContext(CommandContext $context)
    {
        $this->report->setTerm(Term::getSelectedTerm());
    }

    /*
    public function getHtmlView()
    {
        
    }
	*/
    
    /*
    public function getPdfView()
    {

    }
    */

    public function getCsvView()
    {

    }
}

?>