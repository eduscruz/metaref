<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of interface functions and constants for module metaref
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the metaref specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_metaref
 * @copyright  2019 Eduardo Cruz <eduardo.cruz@ufabc.edu.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Example constant, you probably want to remove this :-)
 */
define('metaref_ULTIMATE_ANSWER', 42);

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function metaref_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the metaref into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $metaref Submitted data from the form in mod_form.php
 * @param mod_metaref_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted metaref record
 */
function metaref_add_instance(stdClass $metaref, mod_metaref_mod_form $mform = null) {
    global $DB;
    
    $metaref->timecreated = time();
    
    $metaref->prefbkmaavg = 0;
    $metaref->posfbkmaavg = 0;
    $metaref->course = $metaref->courseid;
    
    // Get data from form.
    if (isset($metaref->PreMetacognition)) {
        $metaref->prefeedback = $metaref->PreMetacognition;
    } else {
        $metaref->prefeedback = null;
    }
    
    if (isset($metaref->PosMetacognition)) {
        $metaref->posfeedback = $metaref->PosMetacognition;
    } else {
        $metaref->posfeedback = null;
    }
    
    if (isset($metaref->selactivityquiz)) {
        $metaref->activityquiz = $metaref->selactivityquiz;
    } else {
        $metaref->activityquiz = null;
    }
    
    if (isset($metaref->selectActivity)) {
        $metaref->idactivity = $metaref->selectActivity;
    } else {
        $metaref->idactivity = null;
    }
    
    if (isset($metaref->selectQuiz)) {
        $metaref->idquiz = $metaref->selectQuiz;
    } else {
        $metaref->idquiz = null;
    }
    
    if (isset($metaref->rightanswerchk)) {
        $metaref->showrightans = $metaref->rightanswerchk;
    } else {
        $metaref->showrightans = null;
    }
    
    if (isset($metaref->selectPreMetacognition)) {
        $metaref->idprefeedback = $metaref->selectPreMetacognition;
    } else {
        $metaref->idprefeedback = null;
    }
    
    if (isset($metaref->selectPosMetacognition)) {
        $metaref->idposfeedback = $metaref->selectPosMetacognition;
    } else {
        $metaref->idposfeedback = null;
    }
    
    if (isset($metaref->rightanswertxt)) {
        $metaref->rightanswer = $metaref->rightanswertxt;
    } else {
        $metaref->rightanswer = null;
    }
    
    $metaref->id = $DB->insert_record('metaref', $metaref);
    metaref_grade_item_update($metaref);

    // insert grades in metaref tables
    metaref_user_grades_populate($metaref);

    return $metaref->id;
}

/**
 * Updates an instance of the metaref in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $metaref An object from the form in mod_form.php
 * @param mod_metaref_mod_form $mform The form instance itself 
 * @return boolean Success/Fail
 */
function metaref_update_instance(stdClass $metaref, mod_metaref_mod_form $mform = null) {
    global $DB;

    $metaref->timemodified = time();
    $metaref->id = $metaref->instance;
    $metaref->course = $metaref->courseid;
    
    // Get data from form.
    if (isset($metaref->PreMetacognition)) {
        $metaref->prefeedback = $metaref->PreMetacognition;
    } else {
        $metaref->prefeedback = null;
    }
    
    if (isset($metaref->PosMetacognition)) {
        $metaref->posfeedback = $metaref->PosMetacognition;
    } else {
        $metaref->posfeedback = null;
    }
    
    if (isset($metaref->selactivityquiz)) {
        $metaref->activityquiz = $metaref->selactivityquiz;
    } else {
        $metaref->activityquiz = null;
    }
    
    if (isset($metaref->selectActivity)) {
        $metaref->idactivity = $metaref->selectActivity;
    } else {
        $metaref->idactivity = null;
    }
    
    if (isset($metaref->selectQuiz)) {
        $metaref->idquiz = $metaref->selectQuiz;
    } else {
        $metaref->idquiz = null;
    }
    
    if (isset($metaref->rightanswerchk)) {
        $metaref->showrightans = $metaref->rightanswerchk;
    } else {
        $metaref->showrightans = null;
    }
    
    if (isset($metaref->selectPreMetacognition)) {
        $metaref->idprefeedback = $metaref->selectPreMetacognition;
    } else {
        $metaref->idprefeedback = null;
    }
    
    if (isset($metaref->selectPosMetacognition)) {
        $metaref->idposfeedback = $metaref->selectPosMetacognition;
    } else {
        $metaref->idposfeedback = null;
    }
    
    if (isset($metaref->rightanswertxt)) {
        $metaref->rightanswer = $metaref->rightanswertxt;
    } else {
        $metaref->rightanswer = null;
    }
   
    $result = $DB->update_record('metaref', $metaref);

    metaref_grade_item_update($metaref);

    // update grades in metaref tables
    metaref_user_grades_populate($metaref, 1);
    
    return $result;
}


/**
 * This function will insert results of this module in the 
 * table metaref_user_grade
 * @param stdClass $metaref An object from the form in mod_form.php
 */
function metaref_user_grades_add(stdClass $fromform){
    global $DB,$COURSE;
    
    $metaref_user_grades = new stdClass();
    $metaref_user_grades->idmetaref = $fromform->metarefid;
    $metaref_user_grades->userid = $fromform->userid;
    $metaref_user_grades->course = $COURSE->id;

    // get recorset from metaref id
    $metaref = $DB->get_record('metaref', array('id' => $fromform->metarefid));

    // if metaref is set up with activity
    if($metaref->activityquiz){
        // get recorset from assign_grades
        $record = $DB->get_record('assign_grades', array('assignment' => $metaref->idactivity, 'userid' => $fromform->userid));
        $wfactor = 1.0;
    }
    // if metaref is set up with quiz
    else {
        // get recorset from assign_grades
        $record = $DB->get_record('quiz_grades', array('quiz' => $metaref->idquiz, 'userid' => $fromform->userid));
        $wfactor = 10.0;
    }
  
    // if metaref as set up with prefeback
    if(($metaref->prefeedback) && isset($record->grade)){  
        // Computes the kma by multiplying the activity grade
        $kma = calculate_kma($metaref->idprefeedback, $fromform->userid, $record->grade*$wfactor);          
        $metaref_user_grades->kmagrade = $kma;
        $kmb = calculate_kmb($metaref->idprefeedback, $fromform->userid, $record->grade*$wfactor);          
        $metaref_user_grades->kmbgrade = $kmb;
    }
    else{
        $metaref_user_grades->kmagrade = null;
        $metaref_user_grades->kmbgrade = null;
    }

    // if metaref as set up with posfeback
    if(($metaref->posfeedback) && isset($record->grade)){
        // Computes the eds1 and eds2      
        $metaref_user_grades->eds1 = calculate_eds1($metaref->idposfeedback, $fromform->userid, ($record->grade*$wfactor));
        $metaref_user_grades->eds2 = calculate_eds2($metaref->idposfeedback, $fromform->userid, ($record->grade*$wfactor));
    }
    else {
        $metaref_user_grades->eds1 = null;
        $metaref_user_grades->eds2 = null;
    }
    
    $metaref_user_grades->saagrade = calculate_saa($metaref->idposfeedback, $fromform->userid, ($record->grade*$wfactor), $fromform->selfregulation1);
    $metaref_user_grades->sabgrade = calculate_sab($metaref->idposfeedback, $fromform->userid, ($record->grade*$wfactor), $fromform->selfregulation1);          

    $metaref_user_grades->mcp1 = $fromform->selactprevious;
    $metaref_user_grades->performace1 = $fromform->realstatus;
    $metaref_user_grades->ep1 = $fromform->selfregulation;
    $metaref_user_grades->sr1 = $fromform->selfregulation1;
    $metaref_user_grades->timecreated = time();
    $metaref_user_grades->enable = 1;


    $metaref_user_grades->id = $DB->insert_record('metaref_user_grades', $metaref_user_grades);
 
}



/**
 * This function will update results of this module in the 
 * table metaref_user_grade
 * @param stdClass $metaref An object from the form in mod_form.php
 */
function metaref_user_grades_update(stdClass $fromform) {
    global $DB, $COURSE;

    // get recorset from metaref id
    $metaref = $DB->get_record('metaref', array('id' => $fromform->metarefid));
    // get recorset from metaref_user_grades id
    $metaref_user_grades = $DB->get_record('metaref_user_grades', array('idmetaref' => $fromform->metarefid, 'userid' => $fromform->userid ));
    
    $metaref_user_grades->mcp1 = $fromform->selactprevious;
    $metaref_user_grades->performace1 = $fromform->realstatus;
    $metaref_user_grades->ep1 = $fromform->selfregulation;
    $metaref_user_grades->sr1 = $fromform->selfregulation1;
    $metaref_user_grades->timemodified = time();
    $metaref_user_grades->course = $COURSE->id;
    
    
    // if metaref is set up with activity
    if($metaref->activityquiz){
        // get recorset from assign_grades
        $record = $DB->get_record('assign_grades', array('assignment' => $metaref->idactivity, 'userid' => $fromform->userid));
        $wfactor = 1.0;
    }
    // if metaref is set up with quiz
    else {
        // get recorset from assign_grades
        $record = $DB->get_record('quiz_grades', array('quiz' => $metaref->idquiz, 'userid' => $fromform->userid));
        $wfactor = 10.0;
    }

    // if metaref as set up with prefeback
    if(($metaref->prefeedback) && isset($record->grade)){  
        // Computes the kma by multiplying the activity grade
        $kma = calculate_kma($metaref->idprefeedback, $fromform->userid, ($record->grade*$wfactor));          
        $metaref_user_grades->kmagrade = $kma;
        $kmb = calculate_kmb($metaref->idprefeedback, $fromform->userid, ($record->grade*$wfactor));          
        $metaref_user_grades->kmbgrade = $kmb;
    } else {
        $metaref_user_grades->kmagrade = null;
        $metaref_user_grades->kmbgrade = null;
    }
    
    // if activity is set up as posfeedback
    if(($metaref->posfeedback) && isset($record->grade)){
        // Computes the eds1 and eds2      
        $metaref_user_grades->eds1 = calculate_eds1($metaref->idposfeedback, $fromform->userid, ($record->grade*$wfactor));
        $metaref_user_grades->eds2 = calculate_eds2($metaref->idposfeedback, $fromform->userid, ($record->grade*$wfactor));
    }
    else {
        $metaref_user_grades->eds1 = null;
        $metaref_user_grades->eds2 = null;
    }
    $metaref_user_grades->saagrade = calculate_saa($metaref->idposfeedback, $fromform->userid, ($record->grade*$wfactor), $fromform->selfregulation1);
    $metaref_user_grades->sabgrade = calculate_sab($metaref->idposfeedback, $fromform->userid, ($record->grade*$wfactor), $fromform->selfregulation1);

    $DB->update_record('metaref_user_grades', $metaref_user_grades, $bulk=false);
}


/**
 * This function will calculate the kma
 *  *
 * @param int $idfeedback 
 * @param int $iduser 
 * @param double $grade 
 * @return double
 */
function calculate_kma($idfeedback, $iduser, $grade){

    global $DB;

    // $resultfeedback = $DB->get_record('feedback_completed', array('feedback'=>$idfeedback, 'userid'=>$iduser));
    $sql = 'SELECT 
                fv.value
            FROM mdl_feedback AS f
            INNER JOIN mdl_feedback_completed AS fc  ON   f.id = fc.feedback
            INNER JOIN mdl_feedback_value AS fv  ON   fc.id = fv.completed
            WHERE fc.userid = ? AND f.id = ?';

    $resultfeedback = $DB->get_record_sql($sql, array($iduser, $idfeedback));
    $graderate = 0;

    // estimate grade by rate 
    if($grade >= 75.0){
        $graderate = 1;
    }
    // verify if the response is regular
    else if(($grade < 75.0) && ($grade >= 50.0)){
        $graderate = 2;
    }
    // verify if the response is bad
    else{
        $graderate = 3;
    }

    
    if ($resultfeedback) {
        // variable aux to calculate kma
        $auxabs = abs($graderate - $resultfeedback->value);

        // calculate the kma
        if ($auxabs == 0){
            $kma = 1;
        }
        elseif ($auxabs == 1) {
            $kma = -0.5;
        } else {
            $kma = -1.0;
        }
    } else {
        $kma = null;
    }
    
    
    return $kma;
}


/**
 * This function will calculate the kmb
 *  *
 * @param int $idfeedback 
 * @param int $iduser 
 * @param double $grade 
 * @return double
 */
function calculate_kmb($idfeedback, $iduser, $grade){

    global $DB;

    // $resultfeedback = $DB->get_record('feedback_completed', array('feedback'=>$idfeedback, 'userid'=>$iduser));
    // get prefeedback results
    $sql = 'SELECT 
                fv.value
            FROM mdl_feedback AS f
            INNER JOIN mdl_feedback_completed AS fc  ON   f.id = fc.feedback
            INNER JOIN mdl_feedback_value AS fv  ON   fc.id = fv.completed
            WHERE fc.userid = ? AND f.id = ?';

    $resultfeedback = $DB->get_record_sql($sql, array($iduser, $idfeedback));
    
    $graderate = 0;

    // estimate grade by rate 
    if($grade >= 75.0){
        $graderate = 1;
    }
    // verify if the response is regular
    else if(($grade < 75.0) && ($grade >= 50.0)){
        $graderate = 2;
    }
    // verify if the response is bad
    else{
        $graderate = 3;
    }

    
    // verify if theauxabs is zero then chage to one
    if ($resultfeedback) {
        // variable aux to calculate kma
        $auxabs = $graderate - $resultfeedback->value;
        
        if($auxabs == 0){
            $kmb = 0;
        } else {
            $kmb = $auxabs/2.0;
        }

    } else {
        $kmb = null;
    }
    
    
    return $kmb;
}


/**
 * This function will calculate the saa
 *  *
 * @param int $idfeedback 
 * @param int $iduser 
 * @param double $grade 
 * @param int $answer 
 * @return double
 */
function calculate_saa($idfeedback, $iduser, $grade, $answer){

    global $DB;

    // estimate grade by rate 
    if($grade >= 75.0){
        $graderate = 1;
    }
    // verify if the response is regular
    else if(($grade < 75.0) && ($grade >= 50.0)){
        $graderate = 2;
    }
    // verify if the response is bad
    else{
        $graderate = 3;
    }

    
    if (($answer>=1) && ($answer<=3)) {
        // variable aux to calculate kma
        $auxabs = abs($graderate - $answer);

        // calculate the kma
        if ($auxabs == 0){
            $kma = 1;
        }
        elseif ($auxabs == 1) {
            $kma = -0.5;
        } else {
            $kma = -1.0;
        }
    } else {
        $kma = null;
    }
    
    
    return $kma;
}


/**
 * This function will calculate the sab
 *  *
 * @param int $idfeedback 
 * @param int $iduser 
 * @param double $grade 
 * @param int $answer 
 * @return double
 */
function calculate_sab($idfeedback, $iduser, $grade, $answer){

    // estimate grade by rate 
    if($grade >= 75.0){
        $graderate = 1;
    }
    // verify if the response is regular
    else if(($grade < 75.0) && ($grade >= 50.0)){
        $graderate = 2;
    }
    // verify if the response is bad
    else{
        $graderate = 3;
    }

    
    // verify if theauxabs is zero then chage to one
    if (($answer>=1) && ($answer<=3)) {
        // variable aux to calculate kma
        $auxabs = $graderate - $answer;
        
        if($auxabs == 0){
            $kmb = 0;
        } else {
            $kmb = $auxabs/2.0;
        }

    } else {
        $kmb = null;
    }
    
    
    return $kmb;
}


/**
 * This function will delete a record from metaref_user_grades_delete
 *  *
 * @param stdClass $idfeedback 
 */
function metaref_user_grades_delete(stdClass $metaref){
    global $DB;
    
    $DB->delete_records('metaref_user_grades', array('idmetaref'=>$metaref->id));
}


/**
 * This function will calculate the eds1
 *  *
 * @param int $idfeedback 
 * @param int $iduser 
 * @param double $grade 
 * @return double
 */
function calculate_eds1($idfeedback, $iduser, $grade){

    global $DB;

    // $resultfeedback = $DB->get_record('feedback_completed', array('feedback'=>$idfeedback, 'userid'=>$iduser));
    $sql = 'SELECT 
                fv.value
            FROM mdl_feedback AS f
            INNER JOIN mdl_feedback_completed AS fc  ON   f.id = fc.feedback
            INNER JOIN mdl_feedback_value AS fv  ON   fc.id = fv.completed
            WHERE fc.userid = ? AND f.id = ?';

    $resultfeedback = $DB->get_record_sql($sql, array($iduser, $idfeedback));
    $graderate = 0;

    // estimate grade by rate 
    if($grade >= 75.0){
        $graderate = 1;
    }
    // verify if the response is regular
    else if(($grade < 75.0) && ($grade >= 50.0)){
        $graderate = 2;
    }
    // verify if the response is bad
    else{
        $graderate = 3;
    }

    
    if ($resultfeedback) {
        // variable aux to calculate kma
        $eds1 = ((($graderate - $resultfeedback->value)+2)*0.5)-1;
    } else {
        $eds1 = null;
    }
    
    
    return $eds1;
}


/**
 * This function will calculate the eds2
 *  *
 * @param int $idfeedback 
 * @param int $iduser 
 * @param double $grade 
 * @return double
 */
function calculate_eds2($idfeedback, $iduser, $grade){

    global $DB;

    // $resultfeedback = $DB->get_record('feedback_completed', array('feedback'=>$idfeedback, 'userid'=>$iduser));
    $sql = 'SELECT 
                fv.value
            FROM mdl_feedback AS f
            INNER JOIN mdl_feedback_completed AS fc  ON   f.id = fc.feedback
            INNER JOIN mdl_feedback_value AS fv  ON   fc.id = fv.completed
            WHERE fc.userid = ? AND f.id = ?';

    $resultfeedback = $DB->get_record_sql($sql, array($iduser, $idfeedback));
    $graderate = 0;

    // estimate grade by rate 
    if($grade >= 75.0){
        $graderate = 1;
    }
    // verify if the response is regular
    else if(($grade < 75.0) && ($grade >= 50.0)){
        $graderate = 2;
    }
    // verify if the response is bad
    else{
        $graderate = 3;
    }

    
    if ($resultfeedback) {
        // variable aux to calculate eds2
        $aux1 = pow((4-$graderate),2);
        $aux2 = abs($graderate - $resultfeedback->value - 0.1);
        $eds2 = $aux1 - $aux2 + 1.1;
    } else {
        $eds2 = null;
    }
    
    
    return $eds2;
}
/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every metaref event in the site is checked, else
 * only metaref events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function metaref_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$metarefs = $DB->get_records('metaref')) {
            return true;
        }
    } else {
        if (!$metarefs = $DB->get_records('metaref', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($metarefs as $metaref) {
        // Create a function such as the one below to deal with updating calendar events.
        // metaref_update_events($metaref);
    }

    return true;
}

/**
 * Removes an instance of the metaref from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function metaref_delete_instance($id) {
    global $DB;

    if (! $metaref = $DB->get_record('metaref', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.

    $DB->delete_records('metaref', array('id' => $metaref->id));

    metaref_grade_item_delete($metaref);

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $metaref The metaref instance record
 * @return stdClass|null
 */
function metaref_user_outline($course, $user, $mod, $metaref) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $metaref the module instance record
 */
function metaref_user_complete($course, $user, $mod, $metaref) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in metaref activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function metaref_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link metaref_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function metaref_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link metaref_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function metaref_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function metaref_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function metaref_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of metaref?
 *
 * This function returns if a scale is being used by one metaref
 * if it has support for grading and scales.
 *
 * @param int $metarefid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given metaref instance
 */
function metaref_scale_used($metarefid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('metaref', array('id' => $metarefid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of metaref.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any metaref instance
 */
function metaref_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('metaref', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given metaref instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $metaref instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
function metaref_grade_item_update(stdClass $metaref, $reset=false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($metaref->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($metaref->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $metaref->grade;
        $item['grademin']  = 0;
    } else if ($metaref->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$metaref->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/metaref', $metaref->course, 'mod', 'metaref',
            $metaref->id, 0, null, $item);
}

/**
 * Delete grade item for given metaref instance
 *
 * @param stdClass $metaref instance object
 * @return grade_item
 */
function metaref_grade_item_delete($metaref) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/metaref', $metaref->course, 'mod', 'metaref',
            $metaref->id, 0, null, array('deleted' => 1));
}

/**
 * Update metaref grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $metaref instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function metaref_update_grades(stdClass $metaref, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/metaref', $metaref->course, 'mod', 'metaref', $metaref->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function metaref_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for metaref file areas
 *
 * @package mod_metaref
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function metaref_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the metaref file areas
 *
 * @package mod_metaref
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the metaref's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function metaref_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding metaref nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the metaref module instance
 * @param stdClass $course current course record
 * @param stdClass $module current metaref instance record
 * @param cm_info $cm course module information
 */
function metaref_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Extends the settings navigation with the metaref settings
 *
 * This function is called when the context for the page is a metaref module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $metarefnode metaref administration node
 */
function metaref_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $metarefnode=null) {
    // TODO Delete this function and its docblock, or implement it.
}


/**
 * This function will convert and multiply the grade of quiz 
 *  *
 * @param double $value 
 * @return double
 */
function convertquiz($value){
    return convertgrade($value*10.0);
}


/**
 * This function will convert the feedback
 *  *
 * @param double $value 
 * @return int
 */
function convertfeedback($value){

    switch ($value) {
        case 1:
            return get_string('high', 'metaref');
            break;
        
        case 2:
            return get_string('medium', 'metaref');
            break;

        case 3:
            return get_string('low', 'metaref');
            break;

        default:
            return "Invalid value";
            break;
    }

}

/**
 * This function will convert the grade in a string
 *  *
 * @param double $value 
 * @return string
 */
function convertgrade($value){
        // estimate grade by rate 
        if($value >= 75.0){
            return get_string('high', 'metaref');
        }
        // verify if the response is regular
        else if(($value < 75.0) && ($value >= 50.0)){
            return get_string('medium', 'metaref');
        }
        // verify if the response is bad
        else{
            return get_string('low', 'metaref');
        }
}

/**
 * This function will convert the grade in a int
 *  *
 * @param double $value 
 * @return int
 */
function convertgradenum($value){
    // estimate grade by rate 
    if($value >= 75.0){
        return 1;
    }
    // verify if the response is regular
    else if(($value < 75.0) && ($value >= 50.0)){
        return 2;
    }
    // verify if the response is bad
    else{
        return 3;
    }
}


/*
* This function add all users results in metaref_user_grades
* @param stdClass $metaref An object from the form in mod_form.php
*/
function metaref_user_grades_populate(stdClass $metaref, $update = null) {   
    // if metaref is set up as activity
    if($metaref->activityquiz){
        
        // insert grades records in metaref_user_grades
        metaref_user_grades_popul_indiv($metaref, 'assign_grades', 'assignment', 'idactivity', 1.0, $update);
    }
    // if metaref is set up as quiz
    else{
        // insert grades records in metaref_user_grades
        metaref_user_grades_popul_indiv($metaref, 'quiz_grades', 'quiz', 'idquiz', 10.0, $update);    
    }
}

/*
* This function add all users results in metaref_user_grades
* @param stdClass $metaref is an object from the form in mod_form.php
* @param string $tablename is the name of a table 
* @param string $fieldtable is the name of a field of table 
* @param string $fieldmetaref is the id of a field of table 
* @param double $wfactor is the factor of multiplying  
* @param boolean $wfactor indate if this operation is update
*/
function metaref_user_grades_popul_indiv(stdClass $metaref, $tablename, $fieldtable, $fieldmetaref, $wfactor, $update){
    global $DB;
    
    $sql  = 'UPDATE mdl_metaref_user_grades
            SET mdl_metaref_user_grades.enable = null
            WHERE idmetaref = ?';
    $DB->execute($sql, array($metaref->id));

    // get all answers from activity or quiz table
    $rs = $DB->get_recordset($tablename, array($fieldtable=>$metaref->$fieldmetaref));
        
    $metaref_user_grades = new stdClass();
    
    foreach ($rs as $record) {
        if ($metaref->id){    
            $metaref_user_grades->idmetaref = $metaref->id;
        }
        if (isset($record->userid)){
            $metaref_user_grades->userid = $record->userid;
        }
        
        // if metaref as set up with prefeback
        if($metaref->prefeedback){  
            // Computes the kma by multiplying the activity grade
            $kma = calculate_kma($metaref->idprefeedback, $record->userid, $record->grade*$wfactor);          
            $metaref_user_grades->kmagrade = $kma;
            $kmb = calculate_kmb($metaref->idprefeedback, $record->userid, $record->grade*$wfactor);          
            $metaref_user_grades->kmbgrade = $kmb;
        }
        else{
            $metaref_user_grades->kmagrade = null;
            $metaref_user_grades->kmbgrade = null;
        }

        // if metaref as set up with posfeback
        if($metaref->posfeedback){
            // Computes the kma by multiplying the quiz grade by 10 because the range goes from 0 to 10 instead of 0 to 100
            $metaref_user_grades->eds1 = calculate_eds1($metaref->idposfeedback, $record->userid, ($record->grade*$wfactor));          
            $metaref_user_grades->eds2 = calculate_eds2($metaref->idposfeedback, $record->userid, ($record->grade*$wfactor));          
        }
        else{
            $metaref_user_grades->eds1 = null;
            $metaref_user_grades->eds2 = null;
        }
        
        $metaref_user_grades->saagrade = calculate_kma($metaref->idprefeedback, $record->userid, $record->grade*$wfactor);
        $metaref_user_grades->sabgrade = calculate_kmb($metaref->idprefeedback, $record->userid, $record->grade*$wfactor);

        if ($update) {
            // get recorset from metaref_user_grades id
            $metaref_user_grades_exists = $DB->get_record('metaref_user_grades', array('idmetaref'=>$metaref->id, 'userid'=>$record->userid));
            if ($metaref_user_grades_exists) {
                $metaref_user_grades->id = $metaref_user_grades_exists->id;
                $metaref_user_grades->timemodified = time();
                $metaref_user_grades->enable = 1;
                $DB->update_record('metaref_user_grades', $metaref_user_grades);
            } else {
                $metaref_user_grades->timecreated = time();
                $metaref_user_grades->enable = 1;
                $metaref_user_grades->id = $DB->insert_record('metaref_user_grades', $metaref_user_grades);
            }
        } else {
            // if update = null then populate with new registers
            $metaref_user_grades->timecreated = time();
            $metaref_user_grades->enable = 1;
            $metaref_user_grades->id = $DB->insert_record('metaref_user_grades', $metaref_user_grades);
        }
    }
    $rs->close(); 
}
