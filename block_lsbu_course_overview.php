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


class block_lsbu_course_overview extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title   = get_string('pluginname', 'block_lsbu_course_overview');
    }
    
    /**
     *
     * function to check if the logged in user is a student
     *
     */
    private function isStudent($username)
    {
        global $DB;
        
        // TODO get database name from db extended config plugins setting
        $sql="SELECT role FROM mis_lsbu.moodle_users where username='$username'";
        
        $roles = array();
        
        $roles = $DB->get_records_sql($sql ,null);
        
        foreach($roles as $role)
        {
            if(!empty($role->role))
            {
                return true;    
            }
        }
        
        return false;    
    }
    
    
    private function print_lsbu_overview($courses, array $remote_courses=array()) {
        global $CFG, $USER, $DB, $OUTPUT;
    
        $htmlarray = array();
        
        $overview = '';
        
        // all course year categories
        $all_course_years = array();
            
        if ($modules = $DB->get_records('modules')) {
            foreach ($modules as $mod) {
                if (file_exists(dirname(dirname(__FILE__)).'/mod/'.$mod->name.'/lib.php')) {
                    include_once(dirname(dirname(__FILE__)).'/mod/'.$mod->name.'/lib.php');
                    $fname = $mod->name.'_print_overview';
                    if (function_exists($fname)) {
                        $fname($courses,$htmlarray);
                    }
                }
            }
        }
        
        foreach ($courses as $course) {
            $fullname = format_string($course->fullname, true, array('context' => get_context_instance(CONTEXT_COURSE, $course->id)));
            $attributes = array('title' => s($fullname));
            if (empty($course->visible)) {
                $attributes['class'] = 'dimmed';
            }
            
            // add shortname 
            $fullname = $fullname . ' (' .$course->shortname . ')';
            
            // djsomers - for students show hidden items but do not allow them to be navigable (e.g. hidden courses)
            if(empty($course->visible) && $this->isStudent($USER->username)==true) {
                $course_link = $fullname;
            } else {
                $course_link = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $fullname, $attributes);
            }
            
            // get category for course/module
            $category = $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
            
            // get course year category
            $path = $category->path;
            $path = ltrim($path,"/");
            $parent_categories = explode("/",$path);
            $course_year=$parent_categories[0];
            
            $category_year = $DB->get_record('course_categories', array('id'=>$course_year), '*', MUST_EXIST);
            
            // get the category for this course/module
            $course_year_name=$category->name;
            
            $is_course = stripos($category->idnumber,'CRS_');
            $is_module = stripos($category->idnumber,'MOD_');
                    
            // model course and module
            $course_module = new stdClass();
        
            // check if it is a course
            if($is_course !== false) {
                $course_module->type='course';
                $course_module->link=$course_link;    
            } else if($is_module !== false) { // check if it is a module
                $course_module->type='module';
                $course_module->link=$course_link; 
            } else {
                $course_module->type='unknown';
                $course_module->link=$course_link; 
            }
            
            // add course link to particular course year
            $all_course_years[$course_year_name][]=$course_module;
            
            if (array_key_exists($course->id,$htmlarray)) {
                foreach ($htmlarray[$course->id] as $modname => $html) {
                    echo $html;
                }
            }            
        }
    
        if (!empty($remote_courses)) {
            echo $OUTPUT->heading(get_string('remotecourses', 'mnet'));
        }
        
        foreach ($remote_courses as $course) {
            echo $OUTPUT->box_start('coursebox');
            $attributes = array('title' => s($course->fullname));
            echo $OUTPUT->heading(html_writer::link(
                new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                format_string($course->shortname),
                $attributes) . ' (' . format_string($course->hostname) . ')', 3);
            echo $OUTPUT->box_end();
        }
        
        
        // render output
        $overview .= $OUTPUT->box_start('coursebox');
        
        foreach($all_course_years as $key => $acy) {
            
            // course year
            $overview .= html_writer::start_tag('h3');
            $overview .= $key;
            $overview .= html_writer::end_tag('h3');
            
            $overview .= html_writer::start_tag('ul');
            
            // courses and modules
            foreach($acy as $cy) {
                $overview .= html_writer::start_tag('li',array('class'=>'lbbu_'.$cy->type));
                $overview .= $cy->link;
                $overview .= html_writer::end_tag('li');
            }
            
            $overview .= html_writer::end_tag('ul');
        }
        
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
