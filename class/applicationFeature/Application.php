<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class ApplicationRegistration extends ApplicationFeatureRegistration {
	function __construct()
	{
		$this->name = 'Application';
		$this->description = 'Application';
		$this->startDateRequired = true;
		$this->endDateRequired = true;
		$this->priority = 1;
		$this->allowedTypes = array('F', 'C', 'T');
	}
}

class Application extends ApplicationFeature {
	
	public function getMenuBlockView(Student $student)
	{
		PHPWS_Core::initModClass('hms', 'HousingApplication.php');
		PHPWS_Core::initModClass('hms', 'ApplicationMenuBlockView.php');
		
		$application      = HousingApplication::getApplicationByUser($student->getUsername(), $this->term);
		
		return new ApplicationMenuBlockView($this->term, $this->getStartDate(), $this->getEndDate(), $application);
	}
}
?>