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
 * Prints a particular instance of metaref
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_metaref
 * @copyright  2019 Eduardo Cruz <eduardo.cruz@ufabc.edu.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

// include css
$style = '/mod/metaref/style.css';
$PAGE->requires->css($style);


class post_student extends moodleform {

    /** @var stdClass the metaref record that contains */
    public $metaref;

    //Add elements to form
    public function definition() {

        global $DB, $PAGE, $USER, $OUTPUT, $COURSE;
        $id = optional_param('id', 0, PARAM_INT);

        //create an metaref objetct of instance
        $metarefresult = $DB->get_record('metaref', array('coursemodule'=>$id));

        //create an metaref_user_grades objetct of instance
        $user_grade_result = $DB->get_record('metaref_user_grades', array('idmetaref'=>$metarefresult->id, 'userid'=>$USER->id));

        // inicialize mform
        $mform = $this->_form;  
        
        // binds this instance to the metaref coursemodule
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id);
   
        // binds this instance to the  user id
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', $USER->id);

        // binds this instance to the  user id
        $mform->addElement('hidden', 'metarefid');
        $mform->setType('metarefid', PARAM_INT);
        $mform->setDefault('metarefid', $metarefresult->id);

        // binds course id
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $COURSE->id);
       
        // binds this instance to the  metaref_user_grade id
        $mform->addElement('hidden', 'metarefuserid');
        $mform->setType('metarefuserid', PARAM_INT);
        if ($user_grade_result) {
            $mform->setDefault('metarefuserid', $user_grade_result->id);        
        }
        else{
            $mform->setDefault('metarefuserid', 0);
        }
  
        //add section header
        $mform->addElement('header', 'metareffieldset', get_string('header6', 'metaref'));

        // echo $OUTPUT->heading(get_string('header9', 'metaref'));
        $mform->addElement('html', '<p>'.get_string('textactivity12', 'metaref').'<br>');

        // insert table results
        $mform->addElement('html', '<div>');
        $mform->addElement('html', '<table>');
        $mform->addElement('html', '<tr>');
        $mform->addElement('html', '<th>'.get_string('table1', 'metaref').'</th>');
        $mform->addElement('html', '<th>'.get_string('table2', 'metaref').'</th>');
        $mform->addElement('html', '<th>'.get_string('table3', 'metaref').'</th>');
        $mform->addElement('html', '<th>'.get_string('table4', 'metaref').'</th>');
        $mform->addElement('html', '<th>'.get_string('table5', 'metaref').'</th>');
        $mform->addElement('html', '<th>'.get_string('table6', 'metaref').'</th>');
        $mform->addElement('html', '</tr>');
        
        // SQL query to select tables metaref_user_grades and user
        $sql = 'SELECT 
                    g.id,g.idmetaref,g.userid,g.kmagrade,g.saagrade,
                    g.kmbgrade,g.sabgrade,l.coursemodule,
                    l.prefeedback,l.posfeedback,l.idprefeedback,
                    l.idposfeedback,l.activityquiz,l.idactivity,l.idquiz
               FROM mdl_metaref_user_grades AS g
               INNER JOIN mdl_metaref AS l ON g.idmetaref = l.id
               INNER JOIN mdl_course_modules AS c ON  c.id = l.coursemodule 
               WHERE g.userid = ? AND c.course = ?';
        
        $rs = $DB->get_recordset_sql($sql, array($USER->id, $COURSE->id));
        // if exists any result from rs
        if ($rs) {
            foreach ($rs as $record) {
                
                $mform->addElement('html', '<tr>');
                
                // if recordset is activity
                if ($record->activityquiz) {
                    // get activity result
                    $activity = $DB->get_record('assign', array('id' => $record->idactivity));
                    $mform->addElement('html', "<td>$activity->name</td>");
                    $activityresult = $DB->get_record('assign_grades', array('assignment' => $record->idactivity, 'userid' => $USER->id));
                    // verify is $activityresult is not empty
                    if ($activityresult) {
                        $valuegrade = convertgradenum($activityresult->grade);
                        $mform->addElement('html', "<td>".convertAssigment($activityresult->grade)."</td>");
                    } else {
                        $mform->addElement('html', "<td>Empty</td>");
                    }
                }
                // if recordset is quiz
                else {
                    $quiz = $DB->get_record('quiz', array('id' => $record->idquiz));
                    $mform->addElement('html', "<td>$quiz->name</td>");
                    $quizresult = $DB->get_record('quiz_grades', array('quiz' => $record->idquiz, 'userid' => $USER->id));
                    if ($quizresult) {
                        $valuegrade = convertgradenum($quizresult->grade*10.0);
                        $mform->addElement('html', "<td>".convertquiz($quizresult->grade)."</td>");
                    } else {
                        $mform->addElement('html', "<td>Empty</td>");
                    }
                }
                
                // if recordset is set as prefeedback
                if ($record->prefeedback) {
                    
                    // get prefeedback results
                    $sql = 'SELECT 
                                fv.value
                            FROM mdl_feedback AS f
                            INNER JOIN mdl_feedback_completed AS fc  ON   f.id = fc.feedback
                            INNER JOIN mdl_feedback_value AS fv  ON   fc.id = fv.completed
                            WHERE fc.userid = ? AND f.id = ?';

                    // $resultprefb = $DB->get_record('feedback_completed', array('feedback'=>$record->idprefeedback, 'userid'=>$USER->id));
                    $resultprefb = $DB->get_record_sql($sql, array($USER->id, $record->idprefeedback));
                    
                    // verify if is not empty
                    if ($resultprefb) {
                        $mform->addElement('html', "<td>".convertfeedback($resultprefb->value)."</td>");
                        if ($valuegrade != $resultprefb->value) {
                            $difference = abs(($valuegrade - $resultprefb->value)/2)*100.0;
                        } else {
                            $difference = 0;
                        }
                    } else {
                        $difference = 'Error: prefeedback empty<td>Error: prefeedback empty</td>';
                    }
                    
                    $mform->addElement('html', "<td>$difference %</td>");
                }
                else {
                    // insert empty cell
                    $mform->addElement('html', "<td></td><td></td>");
                }
                
                // if recordset is set as prefeedback
                if ($record->posfeedback) {
                    
                    $sql = 'SELECT 
                                fv.value
                            FROM mdl_feedback AS f
                            INNER JOIN mdl_feedback_completed AS fc  ON   f.id = fc.feedback
                            INNER JOIN mdl_feedback_value AS fv  ON   fc.id = fv.completed
                            WHERE fc.userid = ? AND f.id = ?';

                    // get posfeedback results
                    // $resultposfb = $DB->get_record('feedback_completed', array('feedback'=>$record->idposfeedback, 'userid'=>$USER->id));
                    $resultposfb = $DB->get_record_sql($sql, array($USER->id, $record->idposfeedback));
                    
                    // verify if $resultposfb is not empty
                    if ($resultposfb) {
                        $mform->addElement('html', "<td>".convertfeedback($resultposfb->value)."</td>");
                        if ($valuegrade != $resultposfb->value) {
                            $difference = abs(($valuegrade - $resultposfb->value)/2)*100.0;
                        } else {
                            $difference = 0;
                        }
                    } else {
                        $difference = 'Error: posfeedback empty<td>Error: posfeedback empty</td>';
                    }
                    
                    $mform->addElement('html', "<td>$difference %</td>");
                }
                else {
                    // insert empty cell
                    $mform->addElement('html', "<td></td><td></td>");
                }
                
                $mform->addElement('html', '</tr>');
            }
            $rs->close();
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</table>');   
        }
        else {
            $mform->addElement('html', '<tr><td>AINDA NAO FEZ NENHUMA AVALIACAO DO metaref</td></tr>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</table>');
        }
        
        $sql ='SELECT 	
                    AVG(g.kmagrade) AS avgprekma,
                    AVG(g.saagrade) AS avgposkma,
                    AVG(g.kmbgrade) AS avgprekmb,
                    AVG(g.sabgrade) AS avgposkmb
                    FROM mdl_metaref_user_grades AS g
                    INNER JOIN mdl_metaref AS l ON g.idmetaref = l.id
                    INNER JOIN mdl_course_modules AS c ON  c.id = l.coursemodule 
                    WHERE g.userid = ? AND c.course = ?';

        $muavg = $DB->get_record_sql($sql, array($USER->id, $COURSE->id));
        $mform->addElement('html', '<br><br><strong><p>'.get_string('textactivity13', 'metaref').$muavg->avgprekma.'. '.get_string('textactivity14', 'metaref').$muavg->avgprekmb);
        $mform->addElement('html', '<p>'.get_string('textactivity15', 'metaref').$muavg->avgposkma.'. '.get_string('textactivity16', 'metaref').$muavg->avgposkmb.'</strong>');
        
        // add header activity;
        $mform->addElement('header', 'tablekmakmb','Tabela');
       
        $tableKMA = "<div>
                <table>
                    <tr>
                        <th>"
                        .get_string('kmavalue', 'metaref').
                        "</th>
                        <th>"
                        .get_string('classification', 'metaref').
                        "</th>
                        <th>"
                        .get_string('interpretation', 'metaref').
                        "</th>
                    </tr>
                    <tr>
                        <td>
                            [-1 , -0,25)
                        </td>
                        <td>"
                        .get_string('lowkma', 'metaref').
                        "</td>
                        <td>"
                        .get_string('texttable1', 'metaref').
                        "</td>
                    </tr>
                    <tr>
                        <td>
                            [-0,25 , 0,50)
                        </td>
                        <td>"
                        .get_string('intermkma', 'metaref').
                        "</td>
                        <td>"
                        .get_string('texttable2', 'metaref').    
                        "</td>
                        </tr>
                        <tr>
                        <td>
                        [0,50 , 1]
                        </td>
                        <td>"
                        .get_string('highkma', 'metaref').    
                        "</td>
                        <td>"
                        .get_string('texttable3', 'metaref').    
                        "</td>
                    </tr>
                </table>
            </div>";

        $tableKMB = "<div>
                        <table>
                            <tr>
                                <th>"
                                    .get_string('kmbvalue', 'metaref').
                                "</th>
                                <th>"
                                    .get_string('classification', 'metaref').
                                "</th>
                                <th>"
                                    .get_string('interpretation', 'metaref').
                                "</th>
                                </tr>
                                <tr>
                                <td>"
                                    .get_string('highkma', 'metaref').
                                "</td>
                                <td>"
                                    .get_string('realist', 'metaref').
                                "</td>
                                <td>"
                                    .get_string('texttable4', 'metaref').   
                                "</td>
                                </tr>
                                <tr>
                                <td>
                                    [0,25 , 1]
                                </td>
                                <td>"
                                    .get_string('optimistic', 'metaref').
                                "</td>
                                <td>"
                                    .get_string('texttable5', 'metaref').
                                "</td>
                                </tr>
                                <tr>
                                <td>
                                    [-1 , -0,25]
                                </td>
                                <td>"
                                    .get_string('pessimistic', 'metaref').
                                "</td>
                                <td>"
                                    .get_string('texttable6', 'metaref').
                                "</td>
                                </tr>
                                <tr>
                                <td>
                                    (-0,25 , 0,25)
                                </td>
                                <td>"
                                    .get_string('random', 'metaref').
                                "</td>
                                <td>"
                                    .get_string('texttable7', 'metaref').
                                "</td>
                            </tr>
                        </table>
                    </div>";                        

        $mform->addElement('html', '<br><br>'.$tableKMA);            
        $mform->addElement('html', '<br><br>'.$tableKMB);
       
        $mform->closeHeaderBefore('tablekmakmb');

        // add header activity;
        $mform->addElement('header', 'metareffieldset', get_string('header7', 'metaref'));
        $mform->addElement('html', '<p>'.get_string('textactivity1', 'metaref'));

        if (!$metarefresult->showrightans) {
            // add radiobox selfregulation
            $radioarray=array();
            $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity8', 'metaref'), 1);
            $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity9', 'metaref'), 2);
            $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity10', 'metaref'), 3);
            $mform->addGroup($radioarray, 'sr1', get_string('textactivity7', 'metaref'), array(' '), false);

            // if($user_grade_result->sr1){
            if($user_grade_result){
                $mform->setDefault('selfregulation1', $user_grade_result->sr1);
            }
            else{
                $mform->setDefault('selfregulation1', 1);
            }
        } else {
            $data = $this->_customdata;
            $mform->addElement('hidden', 'selfregulation1');
            $mform->setType('selfregulation1', PARAM_INT);
            if (!empty($data['selfregulation1'])) {
                $mform->setDefault('selfregulation1', $data['selfregulation1']);
            } else {
                $mform->setDefault('selfregulation1', null);
            }
        }


        $mform->addElement('html', '<p>'.get_string('textactivity2', 'metaref'));
        
        // add radiobox previous avaliation
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'selactprevious', '', get_string('high', 'metaref'), 0);
        $radioarray[] = $mform->createElement('radio', 'selactprevious', '', get_string('medium', 'metaref'), 1);
        $radioarray[] = $mform->createElement('radio', 'selactprevious', '', get_string('low', 'metaref'), 2);
        $mform->addGroup($radioarray, 'mcp1', get_string('textactivity4', 'metaref'), array(' '), false);
        if($user_grade_result){
            $mform->setDefault('selactprevious', $user_grade_result->mcp1);
        }
        else{
            $mform->setDefault('selactprevious', 2);
        }
        
        // add radiobox real status
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'realstatus', '', get_string('high', 'metaref'), 0);
        $radioarray[] = $mform->createElement('radio', 'realstatus', '', get_string('medium', 'metaref'), 1);
        $radioarray[] = $mform->createElement('radio', 'realstatus', '', get_string('low', 'metaref'), 2);
        $mform->addGroup($radioarray, 'performace1',  get_string('textactivity5', 'metaref'), array(' '), false);
        if($user_grade_result){
            $mform->setDefault('realstatus', $user_grade_result->performace1);
        }
        else{
            $mform->setDefault('realstatus', 2);
        }
        
        $mform->addElement('html', '<p>'.get_string('textactivity3', 'metaref'));
        
        // add radiobox selfregulation
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('decreasing', 'metaref'), 0);
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('increasing', 'metaref'), 1);
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('constant', 'metaref'), 2);
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('random', 'metaref'), 3);
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('undefined', 'metaref'), 4);
        $mform->addGroup($radioarray, 'ep1', get_string('textactivity6', 'metaref'), array(' '), false);
        if($user_grade_result){
            $mform->setDefault('selfregulation', $user_grade_result->ep1);
        }
        else{
            $mform->setDefault('selfregulation', 4);
        }

        // summit button
        $this->add_action_buttons();
    }
  
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}