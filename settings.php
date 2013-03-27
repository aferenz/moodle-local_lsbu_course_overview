<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

	
	// Start of academic year
	$settings->add(new admin_setting_heading('block_lsbu_course_overview_academicyearheader', get_string('academicyearheader', 'block_lsbu_course_overview'), ''));
			
    $options = array('1'=>get_string('january', 'block_lsbu_course_overview'),
    				'2'=>get_string('february', 'block_lsbu_course_overview'),
    				'3'=>get_string('march', 'block_lsbu_course_overview'),
    				'4'=>get_string('april', 'block_lsbu_course_overview'),
    				'5'=>get_string('may', 'block_lsbu_course_overview'),
    				'6'=>get_string('june', 'block_lsbu_course_overview'),
    				'7'=>get_string('july', 'block_lsbu_course_overview'),
    				'8'=>get_string('august', 'block_lsbu_course_overview'),
    				'9'=>get_string('september', 'block_lsbu_course_overview'),
    				'10'=>get_string('october', 'block_lsbu_course_overview'),
    				'11'=>get_string('november', 'block_lsbu_course_overview'),
    				'12'=>get_string('december', 'block_lsbu_course_overview'));

    $settings->add(new admin_setting_configselect('block_lsbu_course_overview/academicyearstart', get_string('academicyearstart', 'block_lsbu_course_overview'),
                       get_string('configacademicyearstart', 'block_lsbu_course_overview'), 'all', $options));
    
    // Field and pattern matching for academic year
    $settings->add(new admin_setting_configtext('block_lsbu_course_overview/academicyearfield', new lang_string('academicyearfield', 'block_lsbu_course_overview'),
    					get_string('configacademicyearfield', 'block_lsbu_course_overview'), get_string('academicyearfielddefault', 'block_lsbu_course_overview'), PARAM_TEXT));
    $settings->add(new admin_setting_configtext('block_lsbu_course_overview/academicyearregexp', new lang_string('academicyearregexp', 'block_lsbu_course_overview'),
			    		get_string('configacademicyearregexp', 'block_lsbu_course_overview'), get_string('academicyearregexpdefault', 'block_lsbu_course_overview'), PARAM_TEXT));
    
    // Field and pattern matching for courses
    $settings->add(new admin_setting_heading('block_lsbu_course_overview_courseidentificationheader', get_string('courseidentificationheader', 'block_lsbu_course_overview'), ''));
    
    $settings->add(new admin_setting_configtext('block_lsbu_course_overview/coursefield', new lang_string('coursefield', 'block_lsbu_course_overview'),
    					get_string('configcoursefield', 'block_lsbu_course_overview'), get_string('coursefielddefault', 'block_lsbu_course_overview'), PARAM_TEXT));
    $settings->add(new admin_setting_configtext('block_lsbu_course_overview/courseregexp', new lang_string('courseregexp', 'block_lsbu_course_overview'),
			    		get_string('configcourseregexp', 'block_lsbu_course_overview'), get_string('courseregexpdefault', 'block_lsbu_course_overview'), PARAM_TEXT));
     
    // Field and pattern matching for modules
    $settings->add(new admin_setting_heading('block_lsbu_course_overview_moduleidentificationheader', get_string('moduleidentificationheader', 'block_lsbu_course_overview'), ''));
    
    $settings->add(new admin_setting_configtext('block_lsbu_course_overview/modulefield', new lang_string('modulefield', 'block_lsbu_course_overview'),
    					get_string('configmodulefield', 'block_lsbu_course_overview'), get_string('modulefielddefault', 'block_lsbu_course_overview'), PARAM_TEXT));
    $settings->add(new admin_setting_configtext('block_lsbu_course_overview/moduleregexp', new lang_string('moduleregexp', 'block_lsbu_course_overview'),
    					get_string('configmoduleregexp', 'block_lsbu_course_overview'), get_string('moduleregexpdefault', 'block_lsbu_course_overview'), PARAM_TEXT));
    
    // Category for support
    $settings->add(new admin_setting_heading('block_lsbu_course_overview_supportcategoryheader', get_string('supportcategoryheader', 'block_lsbu_course_overview'), ''));
    
    $settings->add(new admin_setting_configtext('block_lsbu_course_overview/supportcategory', new lang_string('supportcategory', 'block_lsbu_course_overview'),
    		get_string('configsupportcategory', 'block_lsbu_course_overview'), get_string('supportcategorydefault', 'block_lsbu_course_overview'), PARAM_TEXT));
    
    // Category for student support
    $settings->add(new admin_setting_heading('block_lsbu_course_overview_studentsupportcategoryheader', get_string('studentsupportcategoryheader', 'block_lsbu_course_overview'), ''));
    
    $settings->add(new admin_setting_configtext('block_lsbu_course_overview/studentsupportcategory', new lang_string('studentsupportcategory', 'block_lsbu_course_overview'),
    		get_string('configstudentsupportcategory', 'block_lsbu_course_overview'), get_string('studentsupportcategorydefault', 'block_lsbu_course_overview'), PARAM_TEXT));
    
}
