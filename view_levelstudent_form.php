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

class view_levelstudent_form extends moodleform {

    //Add elements to form
    public function definition() {

        global $COURSE, $USER, $DB, $OUTPUT, $PAGE;

        $id = optional_param('id', 0, PARAM_INT);

        //create an metaref objetct of instance
        $metarefresult = $DB->get_record('metaref', array('coursemodule'=>$id));

        //create an metaref_user_grades objetct of instance
        $metaref_user_result = $DB->get_record('metaref_user_grades', array('idmetaref'=>$metarefresult->id, 'userid'=>$USER->id));

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

        // if exists result in metaref_user_grades
        if ($metaref_user_result) {

            echo $OUTPUT->heading(get_string('header10', 'metaref'));
            $mform->addElement('html', '<p>'.get_string('textactivity17', 'metaref').': ');

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
            $mform->addElement('html', '<br><strong><p>'.get_string('textactivity13', 'metaref').$muavg->avgprekma.'. '.get_string('textactivity14', 'metaref').$muavg->avgprekmb);
            $mform->addElement('html', '<p>'.get_string('textactivity15', 'metaref').$muavg->avgposkma.'. '.get_string('textactivity16', 'metaref').$muavg->avgposkmb.'</strong>');

            $mform->addElement('html', '<p>'.get_string('textactivity18', 'metaref').'<br>');

            if ($metaref_user_result->kmagrade != null) {
                if ($metaref_user_result->kmagrade < (-0.25)) {
                   $prekma = get_string('low', 'metaref');
                   $kmaknowledge = get_string('textactivity25', 'metaref');
                } elseif (($metaref_user_result->kmagrade >= (-0.25)) && (($metaref_user_result->kmagrade <= (0.5)))) {
                    $prekma = get_string('medium', 'metaref');
                    $kmaknowledge = get_string('textactivity26', 'metaref');
                    
                } else {
                    $prekma = get_string('high', 'metaref');
                    $kmaknowledge = get_string('textactivity27', 'metaref');
                }

                $text1 = "<strong>".get_string('textactivity19', 'metaref')."</strong><br><br>";
                $text1 .= get_string('textactivity20', 'metaref')."<strong>";
                $text1 .= $prekma;
                $text1 .= " </strong>";
                $text1 .= get_string('textactivity21', 'metaref');
                $text1 .= " <strong>".get_string('textactivity22', 'metaref').$kmaknowledge." </strong>";
                $text1 .= get_string('textactivity23', 'metaref');
                echo $OUTPUT->box($text1);
            }
            
            if ($metaref_user_result->kmbgrade  != null) {
                if ($metaref_user_result->kmbgrade < (-0.25)) {
                   $prekmb = get_string('textactivity32', 'metaref');
                   $kmbknowledge = get_string('textactivity35', 'metaref');
                } elseif (($metaref_user_result->kmbgrade >= (-0.25)) && (($metaref_user_result->kmbgrade <= (0.25)))) {
                    $prekmb = get_string('textactivity33', 'metaref');
                    $kmbknowledge = get_string('textactivity36', 'metaref');
                } else {
                    $prekmb = get_string('textactivity34', 'metaref');
                    $kmbknowledge = get_string('textactivity37', 'metaref');
                }

                $text2 = '<strong>'.get_string('textactivity28', 'metaref').'</strong><br><br>';
                $text2 .= get_string('textactivity29', 'metaref').' <strong> ';
                $text2 .= $prekmb;
                $text2 .= '</strong> '.get_string('textactivity30', 'metaref').'<strong> ';
                $text2 .= $kmbknowledge;
                $text2 .= ' </strong> '.get_string('textactivity31', 'metaref');
                echo $OUTPUT->box($text2);
            }
        }
        else{
            echo $OUTPUT->box('error: metaref_user_grades record found');
        }

        $redirect = '<p><a href="/course/view.php?id='.$COURSE->id.'">';
        $mform->addElement('html', $redirect);
        $mform->addElement('button', 'finish', get_string('finish', 'metaref'));
        $redirect = '</a>';
        $mform->addElement('html', $redirect);
    }
}
