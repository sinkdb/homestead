<?php
PHPWS_Core::initModClass('hms', 'RoomDamage.php');
PHPWS_Core::initModClass('hms', 'PdoFactory.php');


/**
 * RoomDamageFactory - Factory class with various
 * static utilitiy methods for loading RoomDamage
 * objects from the database.
 *
 * @author jbooker
 * @package hms
 */
class RoomDamageFactory {

    /**
     * Returns a set of RoomDamage objects representing all
     * the room damages for the given room.
     *
     * @param HMS_Room $room
     * @throws DatabaseException
     * @return Array<RoomDamage> null
     */
    public static function getDamagesByRoom(HMS_Room $room)
    {
        $db = new PHPWS_DB('hms_room_damage');

        $db->addWhere('room_persistent_id', $room->getPersistentId());
        $db->addWhere('repaired', 0);
        $result = $db->getObjects('RoomDamageDb');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /**
     * Returns the set of RoomDamage objects that were created before
     * the give timestmap.
     *
     * @param HMS_Room $room
     * @param unknown $timestamp
     * @throws DatabaseException
     * @throws InvalidArgumentException
     * @return Array<RoomDamage> null
     */
    public static function getDamagesBefore(HMS_Room $room, $timestamp)
    {
        if(!isset($timestamp)){
            throw new InvalidArgumentException('Missing timestamp.');
        }

        $db = new PHPWS_DB('hms_room_damage');

        $db->addWhere('room_persistent_id', $room->getPersistentId());
        $db->addWhere('repaired', 0);

        $db->addWhere('reported_on', $timestamp, '<=');

        $result = $db->getObjects('RoomDamageDb');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /**
     * Returns an array of RoomDamage objects which have pending ('new') responsibilities
     * which need to be assessed. These are filtered by the array of floor objects passed in.
     *
     * @param array $floorList Array of HMS_Floor objects
     */
    public static function getDamagesToAssessByFloor(Array $floorList, $term)
    {
        $floorIdList = array();
        foreacH($floorList as $floor)
        {
            $floorIdList[] = $floor->getId();
        }

        $floorIn = implode($floorIdList, ', ');

        //TODO: find a good way to order this damage list by hall and room number
        $query = "select distinct hms_room_damage.* from hms_room_damage JOIN hms_room_damage_responsibility ON hms_room_damage.id = hms_room_damage_responsibility.damage_id JOIN hms_room ON hms_room_damage.room_persistent_id = hms_room.persistent_id JOIN hms_floor ON hms_room.floor_id = hms_floor.id WHERE hms_room_damage.term = :term AND hms_room.term = :term and hms_floor.id IN ($floorIn) and hms_room_damage_responsibility.state = 'new'";

        $db = PdoFactory::getPdoInstance();
        $stmt = $db->prepare($query);

        $params = array('term' => $term);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'RoomDamageDb');
    }

    public static function save(RoomDamage $dmg)
    {
        $db = PdoFactory::getPdoInstance();

        $id = $dmg->getId();
        if (isset($id)) {
            // Update
            // TODO
            throw new Exception('Not yet implemented.');

            $query = "";
            $params = array();

        }else{
            // Insert
            $query = "INSERT INTO hms_room_damage (id, room_persistent_id, term, damage_type, note, repaired, reported_by, reported_on, side) VALUES (nextval('hms_room_damage_seq'), :persistentId, :term, :damageType, :note, :repaired, :reportedBy, :reportedOn, :side)";

            $params = array(
                    'persistentId'  => $dmg->getRoomPersistentId(),
                    'term'          => $dmg->getTerm(),
                    'damageType'    => $dmg->getDamageType(),
                    'note'          => $dmg->getNote(),
                    'repaired'      => $dmg->isRepaired() ? 1 : 0,
                    'reportedBy'    => $dmg->getReportedBy(),
                    'reportedOn'    => $dmg->getReportedOn(),
                    'side'          => $dmg->getSide()
            );
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        // Update ID for a new object
        if (!isset($id)) {
            $dmg->setId($db->lastInsertId('hms_room_damage_seq'));
        }
    }
    
    public static function getAssessedDamagesStudentTotals($term)
    {
    	$db = PdoFactory::getPdoInstance();
        
        $query = "select banner_id, sum(amount) from hms_room_damage JOIN hms_room_damage_responsibility ON hms_room_damage.id = hms_room_damage_responsibility.damage_id where term = :term and state = 'assessed' group by banner_id";
        
        $params = array('term' => $term);
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
