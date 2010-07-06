<?php

PHPWS_Core::initModClass('hms', 'HousingApplication.php');
PHPWS_Core::initModClass('hms', 'HMS_Room.php');

class Autoassigner {

    private $term;

    private $applications;
    private $pairs;
    private $rooms;

    private $pairingStrategies;
    private $assignmentStrategies;

    public function __construct($term)
    {
        $this->term = $term;

        echo "Term is $term\n\n";

        PHPWS_Core::initModClass('hms', 'RoommatePairingStrategy.php');
        PHPWS_Core::initModClass('hms', 'RequestedRoommatePairingStrategy.php');
        PHPWS_Core::initModClass('hms', 'PreferencesRoommatePairingStrategy.php');

        PHPWS_Core::initModClass('hms', 'AssignmentStrategy.php');
        PHPWS_Core::initModClass('hms', 'SpecialAssignmentStrategy.php');
        PHPWS_Core::initModClass('hms', 'SingleGenderAssignmentStrategy.php');
        PHPWS_Core::initModClass('hms', 'CoedAssignmentStrategy.php');
        PHPWS_Core::initModClass('hms', 'RandomAssignmentStrategy.php');

        # Load all the unassigned applicants for this term
        $this->applications     = HousingApplication::getUnassignedFreshmenApplications($term, null);

        # Setup the pairing strategies
        $this->pairingStrategies = array();
        $this->pairingStrategies[] = new RequestedRoommatePairingStrategy($term);
        $this->pairingStrategies[] = new PreferencesRoommatePairingStrategy($term);

        # Setup the assignment strategies
        $this->assignmentStrategies = array();
        $this->assignmentStrategies[] = new SpecialAssignmentStrategy($term);
        $this->assignmentStrategies[] = new SingleGenderAssignmentStrategy($term);
        $this->assignmentStrategies[] = new CoedAssignmentStrategy($term);
        $this->assignmentStrategies[] = new RandomAssignmentStrategy($term);
    }

    public function autoassign()
    {
        echo "Apps:  " . count($this->applications) . "\n";
        echo "Pairs: " . count($this->pairs) . "\n";
        # Run each pairing strategy
        foreach($this->pairingStrategies as $strategy){
            $strategy->doPairing($this->applications, $this->pairs);
        }
        echo "Apps:  " . count($this->applications) . "\n";
        echo "Pairs: " . count($this->pairs) . "\n";

        // Randomize the array of pairs
        shuffle($this->pairs);

        # Run each assignment strategy
        foreach($this->assignmentStrategies as $strategy) {
            foreach($this->pairs as $pair) {
                if($pair->isAssigned()) continue;
                $strategy->doAssignment($pair);
            }
        }

        // TODO: Print report of assigned vs unassigned.
    }
}

?>
