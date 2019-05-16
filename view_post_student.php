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

// Replace metaref with the name of your module and remove this line.

global $COURSE, $USER, $DB;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/view_results_form.php');
require_once(dirname(__FILE__).'/view_levelstudent_form.php');
require_once(dirname(__FILE__).'/pre_student.php');
require_once(dirname(__FILE__).'/post_student.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... metaref instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('metaref', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $metaref      = $DB->get_record('metaref', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $metaref      = $DB->get_record('metaref', array('id'=>$n),'*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $metaref->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('metaref', $metaref->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}


require_login($course, true, $cm);

$event = \mod_metaref\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $metaref);
$event->trigger();

// Print the page header.
$PAGE->set_url('/mod/metaref/view_post_student.php', array('id' => $cm->id));
$PAGE->set_title(format_string($metaref->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_cacheable(false);

// Output starts here.
echo $OUTPUT->header();

$post_student_form = new post_student();
if ($post_student_form->is_cancelled()) {
    $returnurl = '/course/view.php?id='.$course->id;
    redirect($returnurl);
} elseif ($fromform = $post_student_form->get_data()) {

    if($fromform->metarefuserid == 0){
        metaref_user_grades_add($fromform);
    }
    // update record in metaref_user_grades
    else{
        metaref_user_grades_update($fromform);
    }

    $levelstudent = new view_levelstudent_form();
    $levelstudent->display();
} elseif ($post_student_form->is_submitted()) {
    echo $OUTPUT->box('submitido');
} else {
    $post_student_form->display();
}

// Finish the page.
echo $OUTPUT->footer();
