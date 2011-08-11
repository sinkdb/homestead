<?php

class ShowReportPdfCommand extends Command{
    
    private $reportId;
    
    public function setReportId($id){
        $this->reportId = $id;
    }
    
    public function getRequestVars()
    {
        if(!isset($this->reportId) || is_null($this->reportId)){
            throw new InvalidArgumentExection('Missing report id.');
        }
        
        return array('action'=>'ShowReportPdf', 'reportId'=>$this->reportId);
    }
    
    public function execute(CommandContext $context)
    {
        $reportId = $context->get('reportId');
    
        if(!isset($reportId) || is_null($reportId)){
            throw new InvalidArgumentExection('Missing report id.');
        }
    
        // Instantiate the report controller with the requested report id
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        $report = ReportFactory::getReportById($reportId);
    
        if(!file_exists($report->getPdfOutputFilename())){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not open report file.');
            $reportCmd = CommandFactory::getCommand('ShowReportDetail');
            $reportCmd->setReportClass($report->getClass());
            $reportCmd->redirect();
        }
        
        $pdf = file_get_contents($report->getPdfOutputFilename());
    
        header('Content-Type: application/pdf');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Length: '.strlen($pdf));
        header('Content-Disposition: inline; filename="'.basename($report->getPdfOutputFilename()).'";');

        echo $pdf;
        
        exit();
    }
}

?>