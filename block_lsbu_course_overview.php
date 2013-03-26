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

require_once($CFG->dirroot.'/lib/weblib.php');
require_once($CFG->dirroot . '/lib/formslib.php');

/* An lsbu 'course' (not be confused with a Moodle course) can be one of the following types...
    - course
    - module
    - support
    - student support
*/
class lsbu_course {
    const COURSETYPE_COURSE = 0;
    const COURSETYPE_MODULE = 1;
    const COURSETYPE_SUPPORT = 2;
    const COURSETYPE_STUDENTSUPPORT = 3;
    const COURSETYPE_UNKNOWN = 4;

    // Instance attributes
    /* The Moodle course this lsbu course represents */
    private $moodle_course = null;

    private $academic_year = 'n/a';

    private $category = null;

    private $type = lsbu_course::COURSETYPE_UNKNOWN;

    /** @var null Where in LSBU's course structure this lsbu_course belongs. Not to be confused with a Moodle context */
    private $context = null;

    // Behaviour
    public function __construct($moodle_course)
    {
        global $DB;

        // contruct an instance of an lsbu course from a Moodle course
        $this->moodle_course = $moodle_course;

        // get category for course/module
        $this->category = $DB->get_record('course_categories', array('id'=>$this->moodle_course->category), '*', MUST_EXIST);

        if(preg_match('/CRS_/', $this->category->idnumber)) {
            $this->type = lsbu_course::COURSETYPE_COURSE;
        }
        if (preg_match('/MOD_/', $this->category->idnumber)) {
            $this->type = lsbu_course::COURSETYPE_MODULE;
        }

        // Academic year is last 4 characters of shortname, e.g. 1213 - or 'n/a' if it's not applicable
        if((strlen($this->moodle_course->idnumber) > 4) && (preg_match('/[0-9]{4}$/', $this->moodle_course->shortname))) {
            $this->academic_year = substr($this->moodle_course->idnumber, -4, 2).'/'.substr($this->moodle_course->idnumber, -2, 2);
        } else {
            // This is going to be either a 'support' or 'student support' course. We can tell from the category
            // they are in.
            if(strcasecmp($this->category->name, 'support') == 0) {
                $this->type = lsbu_course::COURSETYPE_SUPPORT;
            } elseif (strcasecmp($this->category->name, 'student support') == 0) {
                $this->type = lsbu_course::COURSETYPE_STUDENTSUPPORT;
            }
        }
    }

    private function isStudent($username)
    {
        global $DB;

        $result = false;

        // TODO get database name from db extended config plugins setting
        $sql="SELECT role FROM mis_lsbu.moodle_users where username='$username'";

        $roles = $DB->get_records_sql($sql ,null);

        foreach($roles as $role)
        {
            if(!empty($role->role))
            {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Returns the HTML for this object.
     * @return string
     */
    public function get_html() {
        global $USER;

        $fullname = format_string($this->moodle_course->fullname, true, array('context' => get_context_instance(CONTEXT_COURSE, $this->moodle_course->id)));
        // add shortname
        $fullname = $fullname . ' (' .$this->moodle_course->shortname . ')';

        $attributes = array('title' => s($fullname));
        if (empty($this->moodle_course->visible)) {
            $attributes['class'] = 'dimmed';
        }

        // djsomers - for students show hidden items but do not allow them to be navigable (e.g. hidden courses)
        if(empty($this->moodle_course->visible) && $this->isStudent($USER->username)==true) {
            $result = $fullname;
        } else {
            $result = html_writer::link(new moodle_url('/course/view.php', array('id' => $this->moodle_course->id)), $fullname, $attributes);
        }

        return $result;
    }

    // 'getting and setting' functions
    public function get_type() {
        return $this->type;
    }

    public function get_academic_year() {
        return $this->academic_year;
    }
}

/**
 * This class manages the course hierarchy. It is provided with a 'raw' struture which it then
 * rearranges subject to LSBU requirements. Currently these requirements are subject to change - hence
 * this class.
 *
 * Class lsbu_course_hierarchy_manager
 */
class lsbu_course_hierarchy_manager {

    /** @var array The hierarchy, which is build when objects of this class are instantiated */
    private $hierarchy = array();


    /**
     * This function assumes the 'raw_structure' contains a multi-dimensional array that looks
     * something like...
     *
     *      <<< >>>
     *
     * @param $raw_structure
     */
    public function __construct($raw_structure)
    {
        // What is the current academic year?
        $time = time();
        $year = date('y', $time);

        if(date('n', $time) < 10){
            $currentacademicyear = ($year - 1).'/'.$year;
            $previousacademicyear = ($year - 2).'/'.($year-1);
        }else{
            $currentacademicyear = ($year).'/'.($year + 1);
            $previousacademicyear = ($year-1).'/'.$year;
        }




        // current academic year goes first
     /*   $this->hierarchy = array($currentacademicyear = array($raw_structure[$currentacademicyear][lsbu_course::COURSETYPE_COURSE],
                                                         $raw_structure[$currentacademicyear][lsbu_course::COURSETYPE_MODULE],
                                                         $raw_structure[lsbu_course::COURSETYPE_STUDENTSUPPORT]),
                                $previousacademicyear = array($raw_structure[$previousacademicyear][lsbu_course::COURSETYPE_COURSE],
                                                               $raw_structure[$previousacademicyear][lsbu_course::COURSETYPE_MODULE])

        );*/

        $this->hierarchy[$currentacademicyear][lsbu_course::COURSETYPE_COURSE] = $raw_structure[$currentacademicyear][lsbu_course::COURSETYPE_COURSE];
        $this->hierarchy[$currentacademicyear][lsbu_course::COURSETYPE_MODULE] = $raw_structure[$currentacademicyear][lsbu_course::COURSETYPE_MODULE];
        $this->hierarchy[$currentacademicyear][lsbu_course::COURSETYPE_STUDENTSUPPORT] = $raw_structure['n/a'][lsbu_course::COURSETYPE_STUDENTSUPPORT];

        $this->hierarchy[$previousacademicyear][lsbu_course::COURSETYPE_COURSE] = $raw_structure[$previousacademicyear][lsbu_course::COURSETYPE_COURSE];
        $this->hierarchy[$previousacademicyear][lsbu_course::COURSETYPE_MODULE] = $raw_structure[$previousacademicyear][lsbu_course::COURSETYPE_MODULE];
    }

    public function get_hierarchy() {
        return $this->hierarchy;
    }

    public function get_rendered_hierarchy() {
        $result = '';

        if(!empty($this->hierarchy)) {
            $result .= html_writer::start_tag('ul');

            foreach ($this->hierarchy as $year=>$courses) {
                $result .= html_writer::start_tag('li');

                // Heading for the year
                $result .= html_writer::tag('h3', $year);
                // display courses and modules if there are any
                if(!empty($courses)) {
                    // Display courses
                    if(isset($courses[lsbu_course::COURSETYPE_COURSE])) {

                        $courses_html = html_writer::tag('h3', 'Courses '.$year);
                        foreach ($courses[lsbu_course::COURSETYPE_COURSE] as $course) {
                            $courses_html .= html_writer::tag('li', $course->get_html());
                        }

                        $courses_html = html_writer::tag('ul', $courses_html);

                        $result .= $courses_html;
                    }
                    // Display modules
                    if(isset($courses[lsbu_course::COURSETYPE_MODULE])) {

                        $modules_html = html_writer::tag('h3', 'Modules '.$year);
                        foreach ($courses[lsbu_course::COURSETYPE_MODULE] as $module) {
                            $modules_html .= html_writer::tag('li', $module->get_html());
                        }

                        $modules_html = html_writer::tag('ul', $modules_html);

                        $result .= $modules_html;
                    }
                    // Display student support
                    if(isset($courses[lsbu_course::COURSETYPE_STUDENTSUPPORT])) {

                        $studentsupport_html = html_writer::tag('h3', 'Student Support');

                        foreach ($courses[lsbu_course::COURSETYPE_STUDENTSUPPORT] as $studentsupport) {
                            $studentsupport_html .= html_writer::tag('li', $studentsupport->get_html());
                        }

                        $studentsupport_html = html_writer::tag('ul', $studentsupport_html);

                        $result .= $studentsupport_html;
                    }
                    // Display support
                    if(isset($courses[lsbu_course::COURSETYPE_SUPPORT])) {

                        $support_html = html_writer::tag('h3', 'Support');

                        foreach ($courses[lsbu_course::COURSETYPE_SUPPORT] as $support) {
                            $support_html .= html_writer::tag('li', $support->get_html());
                        }
                        $support_html = html_writer::tag('ul', $support_html);

                        $result .= $support_html;
                    }//if(isset($courses[lsbu_course::COURSETYPE_SUPPORT])) {
                }//if(!empty($courses))
            }//foreach ($this->hierarchy as $year=>$courses)

            $result .= html_writer::end_tag('ul');
        }//if(!empty($this->hierarchy))

        return $result;

    }
}


class block_lsbu_course_overview extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title   = get_string('pluginname', 'block_lsbu_course_overview');
    }


    private function print_lsbu_overview($moodle_courses, array $remote_courses=array()) {
        global $DB, $OUTPUT;
    
        $htmlarray = array();
        
        $overview = '';
        
        // all course year categories
        $all_course_years = array();
            
        if ($activity_modules = $DB->get_records('modules')) {
            foreach ($activity_modules as $mod) {
                if (file_exists(dirname(dirname(__FILE__)).'/mod/'.$mod->name.'/lib.php')) {
                    include_once(dirname(dirname(__FILE__)).'/mod/'.$mod->name.'/lib.php');
                    $fname = $mod->name.'_print_overview';
                    if (function_exists($fname)) {
                        $fname($moodle_courses,$htmlarray);
                    }
                }
            }
        }

        // We contruct a multidimentional array of academic years, modules and courses
        $course_tree = array();

        foreach ($moodle_courses as $moodle_course) {

            $course_instance = new lsbu_course($moodle_course);

            /* An lsbu course instance knows
                - what academic year it belongs to, it
                - if it is a module, course, support, student support, unknown, etc.

            */

            if(isset($course_instance)) {
                $academic_year = $course_instance->get_academic_year();
                $course_type = $course_instance->get_type();

                $course_tree[$academic_year][$course_type][] = $course_instance;
            }
        }
    
        if (!empty($remote_courses)) {
            echo $OUTPUT->heading(get_string('remotecourses', 'mnet'));

            // render remote courses
            foreach ($remote_courses as $course) {
                echo $OUTPUT->box_start('coursebox');
                $attributes = array('title' => s($course->fullname));
                echo $OUTPUT->heading(html_writer::link(
                    new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                    format_string($course->shortname),
                    $attributes) . ' (' . format_string($course->hostname) . ')', 3);
                echo $OUTPUT->box_end();
            }
        }

        /*
            Structure output tree for internal courses...

            Current Year (12/13)
                |
                --- Courses
                |
                --- Modules
                |
                --- Student support

            Previous Year (11/12)
                |
                --- Courses
                |
                --- Modules
        */

        $hierarchy_manager = new lsbu_course_hierarchy_manager($course_tree);

        // render internal courses
        $overview .= $OUTPUT->box_start('coursebox');

        // The hierarchy manager knows how to render the course hierarchy
        $overview .= $hierarchy_manager->get_rendered_hierarchy();

        $overview .= $OUTPUT->box_end();
        
        echo $overview;
    }
    
    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $USER, $CFG;
        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $content = array();

        // limits the number of courses showing up
        $courses_limit = 21;
        // FIXME: this should be a block setting, rather than a global setting
        if (isset($CFG->mycoursesperpage)) {
            $courses_limit = $CFG->mycoursesperpage;
        }

        $morecourses = false;
        if ($courses_limit > 0) {
            $courses_limit = $courses_limit + 1;
        }

        $courses = enrol_get_my_courses('id, shortname, modinfo, sectioncache', 'visible DESC,sortorder ASC', $courses_limit);
        $site = get_site();
        $course = $site; //just in case we need the old global $course hack

        if (is_enabled_auth('mnet')) {
            $remote_courses = get_my_remotecourses();
        }
        if (empty($remote_courses)) {
            $remote_courses = array();
        }

        if (($courses_limit > 0) && (count($courses)+count($remote_courses) >= $courses_limit)) {
            // get rid of any remote courses that are above the limit
            $remote_courses = array_slice($remote_courses, 0, $courses_limit - count($courses), true);
            if (count($courses) >= $courses_limit) {
                //remove the 'marker' course that we retrieve just to see if we have more than $courses_limit
                array_pop($courses);
            }
            $morecourses = true;
        }


        if (array_key_exists($site->id,$courses)) {
            unset($courses[$site->id]);
        }

        foreach ($courses as $c) {
            if (isset($USER->lastcourseaccess[$c->id])) {
                $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
            } else {
                $courses[$c->id]->lastaccess = 0;
            }
        }

        if (empty($courses) && empty($remote_courses)) {
            $content[] = get_string('nocourses','my');
        } else {
            ob_start();

            $this->print_lsbu_overview($courses, $remote_courses);
          
            $content[] = ob_get_contents();
            ob_end_clean();
        }

        // if more than 20 courses
        if ($morecourses) {
            $content[] = '<br />...';
        }

        $this->content->text = implode($content);

        return $this->content;
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my-index'=>true);
    }
}
?>
